<?php

use crypay\Client;

require_once(DIR_SYSTEM . 'library/crypay/crypay-php/init.php');
require_once(DIR_SYSTEM . 'library/crypay/version.php');

class ControllerExtensionPaymentCrypay extends Controller
{

    /** @var array */
    protected $requestData;

    public function index()
    {
        $this->load->language('extension/payment/crypay');
        $this->load->model('checkout/order');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['action'] = $this->url->link('extension/payment/crypay/checkout', '', true);

        return $this->load->view('extension/payment/crypay', $data);
    }

    public function checkout()
    {
        $crypayClient = $this->getCrypayClient();
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/crypay');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $token = md5(uniqid(rand(), true));

        $params = [
            "symbol" => $order_info['currency_code'],
            "amount" => (float)number_format($order_info['total'] * $this->currency->getvalue($order_info['currency_code']), 2, '.', ''),
            "currency" => $order_info['currency_code'],
            "variableSymbol" => (string)$order_info['order_id'],
            'failUrl' => $this->url->link('extension/payment/crypay/cancel', '', true),
            'successUrl' => $this->url->link('extension/payment/crypay/success', array('cg_token' => $token), true),
            'timestamp' => time(),
        ];

        $jsonResponse = [
            'state' => 'ok',
        ];

        try {
            $cg_order = $crypayClient->payment->create($params);

        } catch (\Exception $e) {
            $jsonResponse['state'] = 'error';
            $jsonResponse['error'] = $e->getMessage();
            $this->log->write("[Crypay]".$e->getMessage());
        }


        if (isset($cg_order)) {
            $this->model_checkout_order->addOrderHistory($order_info['order_id'], $this->config->get('crypay_order_status_id'));

            $jsonResponse['url'] = $cg_order->shortLink;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($jsonResponse));
    }

    public function cancel()
    {
        $this->response->redirect($this->url->link('checkout/cart', ''));
    }

    public function success()
    {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/crypay');

        $order = $this->model_extension_payment_crypay->getOrder($this->session->data['order_id']);

        if (empty($order) || strcmp($order['token'], $this->request->get['cg_token']) !== 0) {
            $this->response->redirect($this->url->link('common/home', '', true));
        } else {
            $this->response->redirect($this->url->link('checkout/success', '', true));
        }
    }

    public function callback()
    {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/crypay');

        $crypayClient = $this->getcrypayClient();

        $this->request = file_get_contents('php://input');
        $headers = $this->get_ds_headers();
        if (!array_key_exists("XSignature", $headers)) {
            $error_message = 'CryPay X-SIGNATURE: not found';
            $this->log->write($error_message);
            exit;
        }

        $signature = $headers["XSignature"];

        $this->requestData = json_decode($this->request, true);
        if (false === $this->checkIfRequestIsValid()) {
            $error_message = 'CryPay Request: not valid request data';
            $this->log->write($error_message);
            exit;
        }

        if ($this->requestData['type'] !== 'PAYMENT') {
            $error_message = 'CryPay Request: not valid request type';
            $this->log->write($error_message);
            exit;
        }

        $token = $crypayClient->generateSignature($this->request, $this->config->get('crypay_api_secret'));

        if (empty($signature) || strcmp($signature, $token) !== 0) {
            $error_message = 'CryPay X-SIGNATURE: ' . $signature . ' is not valid';
            $this->log->write($error_message);
            exit;
        }

        $order_id = (int)$this->requestData['variableSymbol'];

        $this->requestData = json_decode($this->request, true);

        if (isset($this->requestData['state'])) {
            switch ($this->requestData['state']) {
                case 'SUCCESS':
                    $cg_order_status = 'crypay_paid_status_id';
                    break;
                case 'WAITING_FOR_PAYMENT':
                    $cg_order_status = 'crypay_pending_status_id';
                    break;
                case 'WAITING_FOR_CONFIRMATION':
                    $cg_order_status = 'crypay_confirming_status_id';
                    break;
                case 'EXPIRED':
                    $cg_order_status = 'crypay_expired_status_id';
                    break;
                default:
                    $cg_order_status = NULL;
            }

            if (!is_null($cg_order_status)) {
                $this->model_checkout_order->addOrderHistory($order_id, $this->config->get($cg_order_status));
            }
        }


        $this->response->addHeader('HTTP/1.1 200 OK');
    }

    private function getCrypayClient()
    {
        Client::setAppInfo('OpenCart', CRYPAY_OPENCART_EXTENSION_VERSION);

        return new Client(
            $this->config->get('crypay_api_key'),
            $this->config->get('crypay_test_mode') == 1
        );
    }

    private function checkIfRequestIsValid()
    {

        return true;
    }

    private function get_ds_headers()
    {
        $headers = array();
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
            }
        }
        return $headers;
    }
}
