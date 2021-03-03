<?php
require_once(__DIR__.'/base.php');

// Different variables which are used as arguments
$amount = 215.00;
$orderLines = array(
	array(
		'description' => 'Item1',
		'itemId' => 'Item1',
		'quantity' => 5,
		'unitPrice' => 12.0,
		'taxAmount' => 0.0,
		'taxPercent' => 0.0,
		'unitCode' => 'g',
		'goodsType' => 'item'
	),
	array(
		'description' => 'Item2',
		'itemId' => 'Item2',
		'quantity' => 2,
		'unitPrice' => 15.0,
		'taxAmount' => 0.0,
		'taxPercent' => 0.0,
		'unitCode' => 'g',
		'goodsType' => 'item'
	),
	array(
		'description' => 'Shipping fee',
		'itemId' => 'ShippingItem',
		'quantity' => 1,
		'unitPrice' => 5,
		'goodsType' => 'shipping'
	)
);
$transactionId = reserveAmount($api, $terminal, $amount, $orderLines);

/**
 * Helper method for reserving the payment amount
 * Obs: the amount cannot be captured if is not reserved firstly
 * @param $api AltapayMerchantAPI
 * @param $terminal string
 * @param $amount float
 * @param $orderLines
 * @return mixed
 * @throws Exception
 */
function reserveAmount($api, $terminal, $amount, $orderLines)
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
		$paymentSource,
		null,
		null,
		null,
		null,
		null,
		$orderLines
	);
	if($response->wasSuccessful())
	{
		return $response->getPrimaryPayment()->getId();
	}
	else
	{
		throw new Exception('Amount reservation failed: '.$response->getErrorMessage());
	}
}

/**
 * @return AltapayCaptureResponse
 * @var $api AltapayMerchantAPI
 */
$response = $api->captureReservation($transactionId, $amount, $orderLines);
if ($response->wasSuccessful()) 
{
    print('Successful capture');
}
else 
{
	throw new Exception('Capture failed: '.$response->getErrorMessage());
}