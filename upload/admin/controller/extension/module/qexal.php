<?php

class ControllerExtensionModuleQexal extends Controller
{	private $version = '2.0.0';
    private $error = array();

    public function index()
    {
        include_once(DIR_SYSTEM . 'library/qexal/helpers.php');
        $this->load->language('extension/module/qexal');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
            $this->save();

        $data['heading_title'] = $this->language->get('heading_title') . ' ' . $this->version;

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
            'href' => $this->url->link('extension/module/qexal', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['action'] = $this->url->link('extension/module/qexal', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['module_qexal_token'] = $this->config->get('module_qexal_token');
        $data['module_qexal_status'] = $this->config->get('module_qexal_status');

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
            
        $data['error_warning'] = '';
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/qexal', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/qexal'))
            $this->error['warning'] = $this->language->get('error_permission');

        return !$this->error;
    }

    /**
     * When we save
     */
    public function save(): void
    {
        $this->load->model('setting/setting');
        $data = $this->model_setting_setting->getSetting('module_qexal');

        $helper = new QexalHelpers($this);

        if (!empty($this->request->post['module_qexal_token']))
            $data['module_qexal_token'] = $this->request->post['module_qexal_token'];

        $data['module_qexal_status'] = $this->request->post['module_qexal_status'];

        $this->model_setting_setting->editSetting('module_qexal', $data);

        $this->session->data['success'] = $this->language->get('text_success');

        $this->response->redirect($this->url->link('extension/module/qexal', 'user_token=' . $this->session->data['user_token'], true));
    }

    public function install()
    {
        //Create DB
        $this->load->model('extension/module/qexal');
        $this->model_extension_module_qexal->install();

        //Add Events
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent('qexal', 'catalog/model/checkout/order/addOrderHistory/after', 'extension/module/qexal/status');
    }

    public function uninstall()
    {
        $this->load->model('setting/setting');
        $this->model_setting_setting->deleteSetting('qexal');

        //Delete DB
        $this->load->model('extension/module/qexal');
        $this->model_extension_module_qexal->uninstall();

        //Delete Events
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('qexal');
    }
}