<?php
require_once(__DIR__.'/base.php');

// Different variables, which are used as arguments
$terminal = 'AltapayOmni Klarna DK';
$orderId = 'Example_Klarna_' . time();
$amount = 5.5;
$currency = 'DKK';
$paymentType = 'payment';

$customerInfo = array(
    'billing_postal'=> '6800',
    'billing_country'=> 'DK', // 2 character ISO-3166
    'billing_address'=> 'Sæffleberggate 56,1 mf',
    'billing_city'=> 'Varde',
    'billing_region'=> 'DK',
    'billing_firstname'=> 'Testperson-dk',
    'billing_lastname'=> 'Approved',
    'shipping_postal'=> '6800',
    'shipping_country'=> 'DK', // 2 character ISO-3166
    'shipping_address'=> 'Sæffleberggate 56,1 mf',
    'shipping_city'=> 'Varde',
    'shipping_region'=> 'DK',
    'shipping_firstname'=> 'Testperson-dk',
    'shipping_lastname'=> 'Approved',
    'email'=>'myuser@mymail.com',
    'username' => 'myuser',
    'customer_phone' => '20123456',
    'bank_name' => 'My Bank',
    'bank_phone' => '+45 12-34 5678'
);

$orderLines = array(
    array(
        'description' => 'description 1',
        'itemId' => 'id1',
        'quantity' => 1,
        'unitPrice' => 1.1,
        'goodsType' => 'item'
    ),
    array(
        'description' => 'description 2',
        'itemId' => 'id2',
        'quantity' => 2,
        'unitPrice' => 2.2,
        'goodsType' => 'item'
    )
);

$response = $api->createPaymentRequest(
    $terminal,
    $orderId,
    $amount,
    $currency,
    $paymentType,
    $customerInfo,
    null,
    null,
    array(),
    array(),
    $orderLines
);

if($response->wasSuccessful())
{
	// Access the url below and use the social security number 0801363945
	// to complete the Klarna order
	print($response->getRedirectURL());
} else
{
	throw new Exception('Could not create the payment request: '.$response->getErrorMessage());
}

