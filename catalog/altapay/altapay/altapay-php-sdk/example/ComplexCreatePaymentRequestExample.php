<?php
require_once(__DIR__.'/base.php');

// Order details
$orderId = 'order_'.time();
$amount = 45.25;
$currency = 'DKK';
$paymentType = 'payment';
$cookie = isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : 'somecookie=tastesgood';
$language = 'en';
$config = array(
				'callback_form' => 'http://demoshop.altapay.com/Form',
				'callback_ok' => 'http://demoshop.altapay.com/Ok',
				'callback_fail' => 'http://demoshop.altapay.com/Fail',
				'callback_redirect' => '',
				'callback_open' => '',
				'callback_notification' => ''
			);
$customerInfo = array(
					'billing_postal'=> '2860',
					'billing_country'=> 'DK', // 2 character ISO-3166
					'billing_address'=> 'Rosenkæret 13',
					'billing_city'=> 'Søborg',
					'billing_region'=> null,
					'billing_firstname'=> 'Jens',
					'billing_lastname'=> 'Lyn',
					'email'=>'testperson@mydomain.com',
					'shipping_postal'=> '2860',
					'shipping_country'=> 'DK', // 2 character ISO-3166
					'shipping_address'=> 'Rosenkæret 17',
					'shipping_city'=> 'Søborg',
					'shipping_region'=> null,
					'shipping_firstname'=> 'Snej',
					'shipping_lastname'=> 'Nyl',
				);
// Optional
$transactionInfo = array('auxkey'=>'aux data');

// Initialize order lines
$orderLines = array(
	array(
		'description' => 'An even faster Santa Claus',
		'itemId' => 'SantaClausTurbo',
		'quantity' => 165.43,
		'unitPrice' => 13.37,
		'taxAmount' => 0.42,
		'taxPercent' => 15,
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

/**
 * @return AltapayCreatePaymentRequestResponse
 * @var $api AltapayMerchantAPI
 */
$response = $api->createPaymentRequest(
					$terminal,
					$orderId,
					$amount,
					$currency,
					$paymentType,
					$customerInfo,
					$cookie,
					$language,
					$config,
					$transactionInfo,
					$orderLines
);
if($response->wasSuccessful())
{

	print('Redirect URL is: '.$response->getRedirectURL());
}
else
{
	throw new Exception('Create payment failed: '.$response->getErrorMessage());
}