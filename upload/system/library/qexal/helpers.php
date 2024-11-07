<?php

class QexalHelpers
{
    private $opencart_this;

    function __construct($_this)
    {
        $this->opencart_this = $_this;
    }

    /**
     * Sending data to Qexal Opencart plugin
     * @param $data
     * @param $token
     * @return bool
     * @throws Exception
     */
    public function send($data, $token): bool
    {
        if (empty($token))
            throw new \Exception('Empty token!');

        $url = 'https://opencart.qexal.dev/orders';

        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'X-API-Key: ' . $token;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return !($info['http_code'] != 200);
    }

    /**
     * Check if we already send status or no
     *
     * @param int $order_id
     * @return bool
     */
    public function isAlreadySentOrder(int $order_id): bool
    {
        $this->opencart_this->load->model('extension/module/qexal');
        return $this->opencart_this->model_extension_module_qexal->getOrderId($order_id);
    }


    /**
     * Save history, that we send status
     *
     * @param $order_id
     */
    public function saveOrderHistory($order_id)
    {
        $this->opencart_this->load->model('extension/module/qexal');
        return $this->opencart_this->model_extension_module_qexal->saveOrderId($order_id);
    }
}