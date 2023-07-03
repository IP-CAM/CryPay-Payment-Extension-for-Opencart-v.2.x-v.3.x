<?php

class ModelExtensionPaymentCrypay extends Model {
    public function install() {
        $this->db->query("
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "crypay_order` (
        `crypay_order_id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `cg_invoice_id` VARCHAR(120),
        `token` VARCHAR(100) NOT NULL,
        PRIMARY KEY (`crypay_order_id`)
      ) ENGINE=MyISAM DEFAULT COLLATE=utf8_general_ci;
    ");

        $this->load->model('setting/setting');

        $defaults = array();

        $defaults['payment_crypay_test_mode'] = 0;
        $defaults['payment_crypay_order_status_id'] = 1;
        $defaults['payment_crypay_confirming_status_id'] = 1;
        $defaults['payment_crypay_paid_status_id'] = 2;
        $defaults['payment_crypay_expired_status_id'] = 14;
        $defaults['payment_crypay_sort_order'] = 0;

        $this->model_setting_setting->editSetting('payment_crypay', $defaults);
    }

    public function uninstall() {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "crypay_order`;");
    }
}
