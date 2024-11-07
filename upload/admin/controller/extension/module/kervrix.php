<?php

class ControllerExtensionModuleKervrix extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/kervrix');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
            $this->save();

        $data['heading_title'] = $this->language->get('heading_title');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/kervrix', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/kervrix', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->post['module_kervrix_host']))
            $data['module_kervrix_host'] = $this->normalizeHost($this->request->post['module_kervrix_host']);
        else
            $data['module_kervrix_host'] = $this->normalizeHost($this->config->get('module_kervrix_host'));

        if (isset($this->request->post['module_kervrix_token']))
            $data['module_kervrix_token'] = $this->request->post['module_kervrix_token'];
        else
            $data['module_kervrix_token'] = $this->config->get('module_kervrix_token');

        if (isset($this->request->post['module_kervrix_status']))
            $data['module_kervrix_status'] = $this->request->post['module_kervrix_status'];
        else
            $data['module_kervrix_status'] = $this->config->get('module_kervrix_status');
        $data['entry_host'] = $this->language->get('entry_host');
        $data['entry_token'] = $this->language->get('entry_token');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');

        /**
         * CURL
         */
        if (extension_loaded('curl'))
            $data['curl_loaded'] = true;
        else
            $data['curl_loaded'] = false;

        /**
         * PHP
         */
        if (version_compare(PHP_VERSION, '7.2.0') >= 0)
            $data['is_good_php'] = true;
        else
            $data['is_good_php'] = false;
            
        if (isset($this->error['warning']))
            $data['error_warning'] = $this->error['warning'];
        else
            $data['error_warning'] = '';
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/kervrix', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/kervrix'))
            $this->error['warning'] = $this->language->get('error_permission');

        $host = '';

        if (isset($this->request->post['module_kervrix_host']))
            $host = $this->normalizeHost($this->request->post['module_kervrix_host']);

        if (!isset($this->error['warning']) && empty($host))
            $this->error['warning'] = $this->language->get('error_host');
        elseif (!isset($this->error['warning']) && !preg_match('/^[a-z0-9.-]+(:[0-9]+)?$/i', $host))
            $this->error['warning'] = $this->language->get('error_host');

        return !$this->error;
    }

    /**
     * When we save
     */
    public function save(): void
    {
        $this->load->model('setting/setting');
        $data = $this->model_setting_setting->getSetting('module_kervrix');

        if (isset($this->request->post['module_kervrix_host']))
            $data['module_kervrix_host'] = $this->normalizeHost($this->request->post['module_kervrix_host']);

        if (isset($this->request->post['module_kervrix_token']))
            $data['module_kervrix_token'] = $this->request->post['module_kervrix_token'];

        $data['module_kervrix_status'] = $this->request->post['module_kervrix_status'];

        $this->model_setting_setting->editSetting('module_kervrix', $data);

        $this->session->data['success'] = $this->language->get('text_success');

        $this->response->redirect($this->url->link('extension/module/kervrix', 'user_token=' . $this->session->data['user_token'], true));
    }

    private function normalizeHost($host): string
    {
        $host = trim((string)$host);
        $host = preg_replace('/^https?:\/\//i', '', $host);
        $host = preg_replace('/\/.*$/', '', $host);

        return $host;
    }

    public function install()
    {
        //Create DB
        $this->load->model('extension/module/kervrix');
        $this->model_extension_module_kervrix->install();

        //Add Events
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent('kervrix', 'catalog/controller/common/header/before', 'extension/module/kervrix/trackUtm');
        $this->model_setting_event->addEvent('kervrix', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/module/kervrix/status');
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('module_kervrix');

        //Delete DB
        $this->load->model('extension/module/kervrix');
        $this->model_extension_module_kervrix->uninstall();

        //Delete Events
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('kervrix');
    }
}
