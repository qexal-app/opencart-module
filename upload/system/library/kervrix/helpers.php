<?php

class KervrixHelpers
{
    private $opencart_this;

    function __construct($_this)
    {
        $this->opencart_this = $_this;
    }

    /**
     * Sending data to Kervrix CRM
     * @param $data
     * @param $token
     * @param $host
     * @return bool
     * @throws Exception
     */
    public function send($data, $token, $host): bool
    {
        if (empty($token))            
            return false;

        if (empty($host))
            return false;

        $url = 'https://' . $host . '/modules/opencart/orders';

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
        $this->opencart_this->load->model('extension/module/kervrix');
        return $this->opencart_this->model_extension_module_kervrix->getOrderId($order_id);
    }


    /**
     * Save history, that we send status
     *
     * @param $order_id
     */
    public function saveOrderHistory($order_id)
    {
        $this->opencart_this->load->model('extension/module/kervrix');
        return $this->opencart_this->model_extension_module_kervrix->saveOrderId($order_id);
    }
}