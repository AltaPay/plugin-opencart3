<?php

use Altapay\Api\Ecommerce\Callback;
use Altapay\Api\Payments\RefundCapturedReservation;
use Altapay\Api\Payments\ReleaseReservation;
use Altapay\Api\Test\TestAuthentication;
use Altapay\Authentication;

trait traitTransactionInfo
{

    public function transactionInfo($transactionInfo = array())
    {
        $otherinfo       = 'storeName-' . $this->config->get('config_name');
        $transactionInfo = array(
            'ecomPlatform'         => 'OpenCart',
            'ecomVersion'          => VERSION,
            'altapayPluginName'    => 'AltaPay',
            'altapayPluginVersion' => '3.16',
            'otherInfo'            => $otherinfo,
        );

        return $transactionInfo;
    }

    /**
     * @return Authentication
     */
    public function getAuth()
    {
        return new Authentication($this->config->get('module_altapay_gateway_username'),
            $this->config->get('module_altapay_gateway_password'), $this->config->get('module_altapay_gateway_url'));
    }

    /**
     * Method for AltaPay api login.
     *
     * @return bool
     */
    public function altapayApiLogin()
    {
        try {
            $api      = new TestAuthentication($this->getAuth());
            $response = $api->call();
            if (!$response) {
                return false;
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return false;
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param $order_id
     * @param $txn_id
     * @param $posted_data
     * @param $fraud_recommendation
     *
     * @return bool
     */
    public function detectFraud($order_id, $txn_id, $posted_data, $fraud_recommendation)
    {
        $return             = false;
        $detect_fraud       = $this->config->get('module_altapay_fraud_detection');
        $do_action_on_fraud = $this->config->get('module_altapay_fraud_detection_action');
        $callback           = new Callback($posted_data);
        $response           = $callback->call();

        if ($response && is_array($response->Transactions) and $detect_fraud and $do_action_on_fraud and $fraud_recommendation === 'Deny') {
            $return      = true;
            $transaction = $response->Transactions[0];
            try {
                $auth = $this->getAuth();
                if ($transaction->TransactionStatus === 'captured') {
                    $reconciliation_id = sha1($order_id . time() . $txn_id);
                    $api               = new RefundCapturedReservation($auth);
                    $api->setReconciliationIdentifier($reconciliation_id);
                } else {
                    $api = new ReleaseReservation($auth);
                }
                $api->setTransaction($transaction->TransactionId);
                $response = $api->call();
                if ($response->Result === 'Success') {
                    if (!empty($reconciliation_id)) {
                        $this->model_extension_module_altapay->saveOrderReconciliationIdentifier($order_id, $reconciliation_id, 'refunded');
                        $this->model_extension_module_altapay->updateOrderMeta($order_id, false, true);

                    } else {
                        $this->model_extension_module_altapay->updateOrderMeta($order_id, false, false, true);
                    }
                } else {
                    error_log("altapay_fraud_detection_action error: $response->MerchantErrorMessage");
                }
            } catch (Exception $e) {
                error_log("altapay_fraud_detection_action exception: {$e->getMessage()}");
            }
        }
        return $return;
    }


    /**
     * @param $input_data
     * @param $shared_secret
     *
     * @return string
     */
    public function calculateChecksum($input_data, $shared_secret)
    {
        $checksum_data = array(
            'amount' => trim($input_data['amount']),
            'currency' => trim($input_data['currency']),
            'shop_orderid' => trim($input_data['shop_orderid']),
            'secret' => $shared_secret,
        );

        ksort($checksum_data);
        $data = array();
        foreach ($checksum_data as $name => $value) {
            $data[] = $name . '=' . $value;
        }

        return md5(join(',', $data));
    }

}