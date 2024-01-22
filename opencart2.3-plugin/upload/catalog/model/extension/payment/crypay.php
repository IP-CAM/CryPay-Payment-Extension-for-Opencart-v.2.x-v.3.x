<?php

class ModelExtensionPaymentCrypay extends Model
{
    public function getMethod($address, $total)
    {
        $this->load->language('extension/payment/crypay');


        $method_data = array(
            'code' => 'crypay',
            'title' => $this->language->get('text_title'),
            'terms' => '',
            'sort_order' => $this->config->get('crypay_sort_order')
        );

        return $method_data;
    }
}