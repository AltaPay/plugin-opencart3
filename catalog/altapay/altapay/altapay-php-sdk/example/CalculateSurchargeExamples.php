<?php
require_once(__DIR__.'/base.php');

// Different variables used as arguments
$subscriptionId = 2;
$amount = 2500.00;
$currency = 'DKK';
$cardToken = '193c93414f049ee89ef9381320adb334d7e49afc';
$terminalName = 'Altapay Surcharge Test Terminal';

// Example 1
/**
 * @return AltapayCalculateSurchargeResponse
 * @var $api AltapayMerchantAPI
 */
$response = $api->calculateSurchargeForSubscription($subscriptionId, $amount);
if(!$response->wasSuccessful())
{
	throw new Exception("Could not calculate surcharge amount: ".$response->getErrorMessage());
}
print("[Case 1] Surcharge Amount: ".$response->getSurchargeAmount()."\n");

/**
 * @return AltapayCaptureRecurringResponse
 * @var $api AltapayMerchantAPI
 */
$response = $api->chargeSubscription($subscriptionId, bcadd($amount, $response->getSurchargeAmount(), 2));
if(!$response->wasSuccessful())
{
	throw new Exception("Could not capture including surcharge amount: ".$response->getErrorMessage());
}
print("[Case 1] Captured Amount: ".$response->getPrimaryPayment()->getCapturedAmount()."\n");

// Example 2
$currency = 'XXX';
/**
 * @return AltapayCalculateSurchargeResponse
 * @var $api AltapayMerchantAPI
 */
$response = $api->calculateSurcharge($terminalName, $cardToken, $amount, $currency);
if(!$response->wasSuccessful())
{
	throw new Exception("Could not get surcharge amount: ".$response->getErrorMessage());
}
print("[Case 2] Surcharge Amount: ".$response->getSurchargeAmount()."\n");

/**
 * @return AltapayCaptureRecurringResponse
 * @var $api AltapayMerchantAPI
 */
$response = $api->chargeSubscription($subscriptionId, bcadd($amount, $response->getSurchargeAmount(), 2));
if(!$response->wasSuccessful())
{
	throw new Exception("Could not capture including surcharge amount: ".$response->getErrorMessage());
}
print("[Case 2] Captured Amount: ".$response->getPrimaryPayment()->getCapturedAmount()."\n");
