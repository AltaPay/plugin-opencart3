<?php
require_once(__DIR__.'/base.php');

// Different variables which are used as arguments
$fullAmount = 1290.00;
$partialAmount = 560.00;
$transactionId = reserveAmount($api, $terminal, $fullAmount);

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
$response = $api->captureReservation($transactionId, $partialAmount);
if ($response->wasSuccessful())
{
	print('Successful partial capture');
}
else
{
	throw new Exception('Partial capture failed: '. $response->getErrorMessage());
}