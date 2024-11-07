<?php
class ModelExtensionModuleQexal extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "qexal_crm` (`order_id` int(11));");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "qexal_crm`;");
    }
}