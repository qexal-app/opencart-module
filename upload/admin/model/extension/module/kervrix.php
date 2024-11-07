<?php
class ModelExtensionModuleKervrix extends Model {
    public function install() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "kervrix_crm` (`order_id` int(11) NOT NULL, PRIMARY KEY (`order_id`));");
    }

    public function uninstall()
    {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "kervrix_crm`;");
    }
}