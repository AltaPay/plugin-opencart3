<?php

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
            'altapayPluginVersion' => '3.5',
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
}
