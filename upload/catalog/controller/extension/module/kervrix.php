<?php

class ControllerExtensionModuleKervrix extends Controller
{
    private $utmFields = array('utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content');

    public function trackUtm(&$route, &$args)
    {
        if (!$this->config->get('module_kervrix_status'))
            return false;

        $utm = array();

        foreach ($this->utmFields as $field) {
            if (isset($this->request->get[$field]) && $this->request->get[$field] !== '')
                $utm[$field] = substr((string)$this->request->get[$field], 0, 255);
        }

        if (!$utm)
            return false;

        $this->session->data['kervrix_utm'] = $utm;
    }

    public function status(&$route, &$args, &$out)
    {
        include_once(DIR_SYSTEM . 'library/kervrix/helpers.php');

        if (!$this->config->get('module_kervrix_status'))
            return false;

        if (!isset($args['0']))
            return false;

        $order_id = (int)$args['0'];
        $helper = new \KervrixHelpers($this);

        if (!$helper->isAlreadySentOrder($order_id)) {
            $is_sent = $this->sendOrder($order_id);

            if ($is_sent)
                $helper->saveOrderHistory($order_id);
        }
    }

    private function getStoredUtm(): array
    {
        if (isset($this->session->data['kervrix_utm']) && is_array($this->session->data['kervrix_utm']))
            return $this->session->data['kervrix_utm'];

        return array();
    }

    private function sendOrder(int $order_id): bool
    {
        $helper = new \KervrixHelpers($this);

        $token = $this->config->get('module_kervrix_token');
        $host = $this->config->get('module_kervrix_host');

        if (empty($host))
            return false;

        $this->load->model('extension/module/kervrix');

        $order_data = $this->model_extension_module_kervrix->getOrder($order_id);

        if (!is_array($order_data))
            return false;

        $utm = $this->getStoredUtm();

        foreach ($this->utmFields as $field) {
            if (isset($utm[$field]) && $utm[$field] !== '')
                $order_data[$field] = $utm[$field];
        }
        
        try {
            return $helper->send($order_data, $token, $host);
        } catch (\Exception $e) {
            return false;
        }
    }
}