<?php

class ControllerApiKervrix extends Controller
{
    private function initializeApiResponse()
    {
        $this->load->language('extension/module/kervrix');

        $token = $this->config->get('module_kervrix_token');
        $apiKey = isset($this->request->server['HTTP_X_API_KEY']) ? $this->request->server['HTTP_X_API_KEY'] : null;

        if (empty($apiKey) || empty($token) || ($apiKey != $token))
            return false;

        return array();
    }

    private function sendResponse($json)
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getOrders()
    {
        $json = $this->initializeApiResponse();

        if ($json === false)
            return false;

        if (!isset($json['error'])) {
            $this->load->model('extension/module/kervrix');

            $data = array(
                'limit' => isset($this->request->get['limit']) ? (int) $this->request->get['limit'] : 500,
                'order_status_id' => isset($this->request->get['order_status_id']) ? (int) $this->request->get['order_status_id'] : 1,
                'orderby' => isset($this->request->get['orderby']) ? $this->request->get['orderby'] : 'o.order_id',
                'sort' => isset($this->request->get['sort']) ? $this->request->get['sort'] : 'DESC',
                'start_date' => isset($this->request->get['start_date']) ? $this->request->get['start_date'] : null,
                'end_date' => isset($this->request->get['end_date']) ? $this->request->get['end_date'] : null,
            );

            $json['success'] = true;
            $json['result'] = $this->model_extension_module_kervrix->getOrders($data);
        }

        $this->sendResponse($json);
    }

    public function editOrderStatus()
    {
        $json = $this->initializeApiResponse();

        if ($json === false)
            return false;

        if (!isset($json['error'])) {
            $this->load->model('checkout/order');

            $order_id = isset($this->request->post['order_id']) ? $this->request->post['order_id'] : null;
            $order_status_id = isset($this->request->post['order_status_id']) ? $this->request->post['order_status_id'] : null;

            if ($order_id && $order_status_id) {
                $this->model_checkout_order->addOrderHistory($order_id, $order_status_id);
                $json['success'] = true;
            } else {
                $json['error'] = $this->language->get('error_not_found');
            }
        }

        $this->sendResponse($json);
    }

    public function getOrderStatuses()
    {
        $json = $this->initializeApiResponse();

        if ($json === false)
            return false;

        if (!isset($json['error'])) {
            $this->load->model('extension/module/kervrix');
            $json['success'] = true;
            $json['result'] = $this->model_extension_module_kervrix->getOrderStatuses();
        }

        $this->sendResponse($json);
    }

    public function getShippingMethods()
    {
        $json = $this->initializeApiResponse();

        if ($json === false)
            return false;

        if (!isset($json['error'])) {
            $this->load->model('extension/module/kervrix');
            $json['success'] = true;
            $json['result'] = $this->model_extension_module_kervrix->getShippingMethods();
        }

        $this->sendResponse($json);
    }

    public function getPaymentMethods()
    {
        $json = $this->initializeApiResponse();

        if ($json === false)
            return false;

        if (!isset($json['error'])) {
            $this->load->model('extension/module/kervrix');
            $json['success'] = true;
            $json['result'] = $this->model_extension_module_kervrix->getPaymentMethods();
        }

        $this->sendResponse($json);
    }
}