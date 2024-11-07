<?php

class ControllerExtensionModuleQexal extends Controller
{
    public function status(&$route, &$args, &$out)
    {
        include_once(DIR_SYSTEM . 'library/qexal/helpers.php');

        if (!$this->config->get('module_qexal_status'))
            return false;

        if (!isset($args['0']))
            return false;

        $order_id = (int)$args['0'];
        $helper = new \QexalHelpers($this);
        
        if (!$helper->isAlreadySentOrder($order_id)) {
            $helper->saveOrderHistory($order_id);
            $this->sendOrder($order_id);
        }
    }

    private function sendOrder(int $order_id): bool
    {
        $helper = new \QexalHelpers($this);

        $token = $this->config->get('module_qexal_token');

        $this->load->model('extension/module/qexal');

        $order_data = $this->model_extension_module_qexal->getOrder($order_id);
        
        try {
            return $helper->send($order_data, $token);
        } catch (\Exception $e) {
            return false;
        }
    }
}
