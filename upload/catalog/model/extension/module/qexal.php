<?php

class ModelExtensionModuleQexal extends Model
{
    public function saveOrderId(int $order_id)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "qexal_crm (order_id) VALUES ('{$order_id}');");

        return true;
    }

    public function getOrderId(int $order_id)
    {
        $query = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "qexal_crm WHERE order_id='{$order_id}' LIMIT 1");

        return (bool) $query->row;
    }

    public function getOrder($order_id)
    {
        $this->load->model('checkout/order');
        $this->load->model('localisation/country');

        $order_data = $this->model_checkout_order->getOrder($order_id);

        if (is_array($order_data)) {
            $payment_country_info = $this->model_localisation_country->getCountry($order_data['payment_country_id']);
            $shipping_country_info = $this->model_localisation_country->getCountry($order_data['shipping_country_id']);

            $order_data["payment_country_code"] = $payment_country_info['iso_code_2'] ?? null;
            $order_data["shipping_country_code"] = $shipping_country_info['iso_code_2'] ?? null;
            $order_data['products'] = $this->model_extension_module_qexal->getOrderProducts($order_id);
            $order_data['totals'] = $this->model_extension_module_qexal->getOrderTotals($order_id);
        }

        return $order_data;
    }

    public function getOrders($data = array())
    {
        $sql = "SELECT * FROM `" . DB_PREFIX . "order` o";

        $this->load->model('localisation/country');

        $where = array();

        if (!empty($data['order_status_id'])) {
            $where[] = "o.order_status_id = '" . (int) $data['order_status_id'] . "'";
        }

        if (!empty($data['start_date'])) {
            $where[] = "DATE(o.date_added) >= DATE('" . $this->db->escape($data['start_date']) . "')";
        }

        if (!empty($data['end_date'])) {
            $where[] = "DATE(o.date_added) <= DATE('" . $this->db->escape($data['end_date']) . "')";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        } else {
            $sql .= " WHERE o.order_status_id > '0'";
        }

        if (isset($data['orderby'])) {
            $sql .= "ORDER BY " . $data['orderby'] . " ";
        }

        if (isset($data['sort'])) {
            $sql .= $data['sort'] . " ";
        }

        if (isset($data['limit'])) {
            $sql .= "LIMIT " . (int) $data['limit'] . " ";
        }

        $order_query = $this->db->query($sql);

        $orders = array();
        foreach ($order_query->rows as $order_data) {

            $payment_country_info = $this->model_localisation_country->getCountry($order_data['payment_country_id']);
            $shipping_country_info = $this->model_localisation_country->getCountry($order_data['shipping_country_id']);

            $order_data["payment_country_code"] = $payment_country_info['iso_code_2'] ?? null;
            $order_data["shipping_country_code"] = $shipping_country_info['iso_code_2'] ?? null;
            $order_data['products'] = $this->getOrderProducts($order_data['order_id']);
            $order_data['totals'] = $this->getOrderTotals($order_data['order_id']);

            $orders[] = $order_data;
        }

        return $orders;
    }

    public function getOrderStatuses()
    {
        $query = $this->db->query("SELECT order_status_id as id, name FROM " . DB_PREFIX . "order_status WHERE language_id = '" . (int) $this->config->get('config_language_id') . "' ORDER BY order_status_id ASC");
        return $query->rows;
    }

    public function getShippingMethods()
    {
        $this->load->model('setting/extension');
        return $this->model_setting_extension->getExtensions('shipping');
    }

    public function getPaymentMethods()
    {
        $this->load->model('setting/extension');
        return $this->model_setting_extension->getExtensions('payment');
    }

    public function getOrderProducts($order_id)
    {
        $query = $this->db->query("
			SELECT 
				op.*, 
				oo.value AS option_value,
				p.sku
			FROM " . DB_PREFIX . "order_product op
			JOIN " . DB_PREFIX . "product p ON op.product_id = p.product_id
			LEFT JOIN " . DB_PREFIX . "order_option oo ON op.order_product_id = oo.order_product_id AND op.order_id = oo.order_id
			WHERE op.order_id = '" . (int) $order_id . "'
		");

        return $query->rows;
    }

    public function getOrderTotals($order_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' ORDER BY sort_order ASC");
		
		return $query->rows;
	}	
}