<?php
require_once(__DIR__.'/base.php');

// Different variables which are used as arguments
$amount = 125.55;
$transactionId = reserveAmount($api, $terminal, $amount);

/**
 * Helper method for reserving the payment amount
 * @param $api AltapayMerchantAPI
 * @param $terminal string
 * @param $amount float
 * @return mixed
 * @throws Exception
 */
function reserveAmount($api, $terminal, $amount)
{
	$orderId = 'order_'.time();
	$transactionInfo = array();
	$cardToken = null;
	// Credit card details
	$currencyCode = 'DKK';
	$paymentType = 'payment';
	$paymentSource = 'eCommerce';
	$pan = '4111000011110000';
	$cvc = '111';
	$expiryMonth = '12';
	$expiryYear = '2018';

	/**
	 * @var $api AltapayMerchantAPI
	 * @var $response AltapayCreatePaymentRequestResponse
	 */
	$response = $api->reservation(
		$terminal,
		$orderId,
		$amount,
		$currencyCode,
		$cardToken,
		$pan,
		$expiryMonth,
		$expiryYear,
		$cvc,
		$transactionInfo,
		$paymentType,
		$paymentSource
	);
	if($response->wasSuccessful())
	{
		return $response->getPrimaryPayment()->getId();
	}
	else
	{
		throw new Exception('Amount reservation failed: '. $response->getErrorMessage());
	}
}

/**
 * @var $api AltapayMerchantAPI
 * @var $response AltapayCaptureResponse
 */
$response = $api->captureReservation($transactionId);
if ($response->wasSuccessful()) 
{
    print('Successful capture');
}
else 
{
	throw new Exception('Capture failed: '. $response->getErrorMessage());
}