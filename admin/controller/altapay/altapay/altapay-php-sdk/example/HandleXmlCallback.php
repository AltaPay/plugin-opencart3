<?php
require_once(__DIR__.'/../lib/AltapayCallbackHandler.class.php');

$callbackHandler = new AltapayCallbackHandler();
// Load an example of reservation and capture request
// The XML would normally be POST'ed back to the okay/fail page as a parameter named 'xml'
$xml = file_get_contents(__DIR__.'/xml/CallbackXML_subscriptionAndCharge_released.xml');

/**
 * @var $response AltapayCaptureRecurringResponse
 */
try{
	$response = $callbackHandler->parseXmlResponse($xml);
	if($response->wasSubscriptionReleased())
	{
		print('The subscription was released' . PHP_EOL);
		if($response->getPrimaryPayment()->getCapturedAmount() > 0)
		{
			print('The capture was successful for the amount '.number_format($response->getPrimaryPayment()->getCapturedAmount(), 2) . PHP_EOL);
		}
	}
}catch (Exception $e) {
	echo "Error in the response: ". $e->getMessage();
}
