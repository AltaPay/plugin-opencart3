<?php
require_once(__DIR__.'/base.php');

// Different variables, which are used as arguments
/**
 * @var $amount float
 */
$amount = 215.00;
/**
 * @var $transactionId string
 */
$transactionId = reserveAndCapture($api, $terminal, $amount);

/**
 * Helper method for reserving the order amount
 * If success then the amount is captured
 * Obs: the amount cannot be captured if is not reserved firstly
 * @param $api AltapayMerchantAPI
 * @param $terminal string
 * @param $amount float
 * @return mixed
 * @throws Exception
 */
function reserveAndCapture($api, $terminal, $amount)
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
	 * @var $response AltapayReservationResponse
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
		/**
		 * @var $transactionId string
		 */
		$transactionId = $response->getPrimaryPayment()->getId();
		/**
		 * Capture the amount based on the fetched transaction ID
		 * @var $captureResponse AltapayCaptureResponse
		 */
		$captureResponse = $api->captureReservation($transactionId);
		if ($captureResponse->wasSuccessful())
		{
			return $transactionId;
		}
		else
		{
			throw new Exception('Capture failed: '.$response->getErrorMessage());
		}
	}
	else
	{
		throw new Exception('Reservation failed: '.$response->getErrorMessage());
	}
}

/**
 * @var $response AltapayRefundResponse
 * @var $api AltapayMerchantAPI
 */
$response = $api->refundCapturedReservation($transactionId, $amount);
if ($response->wasSuccessful())
{
	print('Successful refund');
}
else
{
	throw new Exception('Refund failed: '.$response->getErrorMessage());
}