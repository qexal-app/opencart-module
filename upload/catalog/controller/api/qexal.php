<?php

class ControllerApiQexal extends Controller
{
    private function initializeApiResponse()
    {
        $this->load->language('extension/module/qexal');

        $json = array();

        $token = $this->config->get('module_qexal_token');
        $apiKey = $this->request->server['HTTP_X_API_KEY'];

        if (!isset($apiKey) || !isset($token) || ($apiKey != $token)) {
            $json['error'] = $this->language->get('error_permission');
        }

        return $json;
    }

    private function sendResponse($json)
    {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getOrders()
    {
        $json = $this->initializeApiResponse();

        if (!isset($json['error'])) {
            $this->load->model('extension/module/qexal');

            $data = array(
                'limit' => isset($this->request->get['limit']) ? (int) $this->request->get['limit'] : 500,
                'order_status_id' => isset($this->request->get['order_status_id']) ? (int) $this->request->get['order_status_id'] : 1,
                'orderby' => isset($this->request->get['orderby']) ? $this->request->get['orderby'] : 'o.order_id',
                'sort' => isset($this->request->get['sort']) ? $this->request->get['sort'] : 'DESC',
                'start_date' => isset($this->request->get['start_date']) ? $this->request->get['start_date'] : null,
                'end_date' => isset($this->request->get['end_date']) ? $this->request->get['end_date'] : null,
            );

            $json['success'] = true;
            $json['result'] = $this->model_extension_module_qexal->getOrders($data);
        }

        $this->sendResponse($json);
    }

    public function editOrderStatus()
    {
        $json = $this->initializeApiResponse();

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

        if (!isset($json['error'])) {
            $this->load->model('extension/module/qexal');
            $json['success'] = true;
            $json['result'] = $this->model_extension_module_qexal->getOrderStatuses();
        }

        $this->sendResponse($json);
    }

    public function getShippingMethods()
    {
        $json = $this->initializeApiResponse();

        if (!isset($json['error'])) {
            $this->load->model('extension/module/qexal');
            $json['success'] = true;
            $json['result'] = $this->model_extension_module_qexal->getShippingMethods();
        }

        $this->sendResponse($json);
    }

    public function getPaymentMethods()
    {
        $json = $this->initializeApiResponse();

        if (!isset($json['error'])) {
            $this->load->model('extension/module/qexal');
            $json['success'] = true;
            $json['result'] = $this->model_extension_module_qexal->getPaymentMethods();
        }

        $this->sendResponse($json);
    }

    public function callback()
    {
        $json = $this->initializeApiResponse();

        if (!isset($json['error'])) {
            try {
                $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "callback` o WHERE o.status_id = 0");
                $json['success'] = true;
                $json['result'] = $query->rows;
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        $this->sendResponse($json);
    }

    public function submitCallback()
    {
        $json = $this->initializeApiResponse();

        if (!isset($json['error'])) {

            $order_id = $this->request->get['order_id'];

            try {
                $this->db->query("UPDATE `" . DB_PREFIX . "callback` SET status_id = 1 WHERE call_id = '" . (int) $order_id . "'");
                $json['success'] = true;
            } catch (Exception $e) {
                $json['error'] = $e->getMessage();
            }
        }

        $this->sendResponse($json);
    }
}