<?php

use CryPay\Client;

require_once(DIR_SYSTEM . 'library/crypay/crypay-php/init.php');

class ControllerExtensionPaymentCrypay extends Controller
{
    private $error = array();

    public function index()
    {
        $data = $this->load->language('extension/payment/crypay');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/geo_zone');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('crypay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true));
        }

        $data['action'] = $this->url->link('extension/payment/crypay', 'token=' . $this->session->data['token'], true);
        $data['cancel'] = $this->url->link('extension/extension', 'token=' . $this->session->data['token'] . '&type=payment', true);
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'token=' . $this->session->data['token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/crypay', 'token=' . $this->session->data['token'], true)
        );

        $fields = $this->getFields();

        foreach ($fields as $field) {
            if (isset($this->request->post[$field])) {
                $data[$field] = $this->request->post[$field];
            } else {
                $data[$field] = $this->config->get($field);
            }
        }

        $data['callback_url'] = $this->getUrl('extension/payment/crypay/callback');

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/crypay', $data));
    }

    private function getFields()
    {
        return [
            'crypay_sort_order',
            'crypay_status',
            'crypay_api_key',
            'crypay_api_secret',
            'crypay_order_status_id',
            'crypay_confirming_status_id',
            'crypay_paid_status_id',
            'crypay_expired_status_id',
//            'crypay_total',
//            'crypay_geo_zone_id',
            'crypay_test_mode',
        ];
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/crypay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (!class_exists('CryPay\Client')) {
            $this->error['warning'] = $this->language->get('error_composer');
        }

        return !$this->error;
    }

    public function install()
    {
        $this->load->model('extension/payment/crypay');

        $this->model_extension_payment_crypay->install();
    }

    private function getUrl($route)
    {
        $protocol = ($_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];

        return "$protocol://$host/index.php?route=$route";
    }
}
