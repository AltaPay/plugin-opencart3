<?php
require_once(__DIR__.'/base.php');

// Different variables which are used as arguments
$orderId = 'TestOrder_'.time();
$amount = 125.55;
$currency ='DKK';
$config = array(
    'callback_form' => 'http://demoshop.altapay.com/Form'
    ,'callback_ok' => 'http://demoshop.altapay.com/Ok'
    ,'callback_fail' => 'http://demoshop.altapay.com/Fail'
    ,'callback_redirect' => ''     // See documentation
    ,'callback_open' => ''         // See documentation
    ,'callback_notification' => '' // See documentation
);

/**
 * @var $api AltapayMerchantAPI
 * @var $response AltapayCreatePaymentRequestResponse
 */
$response = $api->createPaymentRequest(
			$terminal,
			$orderId,
			$amount,
			$currency,
			NULL,
			NULL,
			NULL,
			NULL,
			$config
);
if($response->wasSuccessful())
{
	print('Successful createPaymentRequest' . PHP_EOL);
    print('Redirect URL: ' . $response->getRedirectURL());
}
else
{
	throw new Exception('Create payment failed: '. $response->getErrorMessage());
}