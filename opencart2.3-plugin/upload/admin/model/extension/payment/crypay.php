<?php

class ModelExtensionPaymentCrypay extends Model
{
    public function install() {
        $this->load->model('setting/setting');

        $defaults = array();

        $defaults['crypay_test_mode'] = 0;
        $defaults['crypay_order_status_id'] = 1;
        $defaults['crypay_confirming_status_id'] = 1;
        $defaults['crypay_paid_status_id'] = 2;
        $defaults['crypay_expired_status_id'] = 14;
        $defaults['crypay_sort_order'] = 0;

        $this->model_setting_setting->editSetting('crypay', $defaults);
    }
}