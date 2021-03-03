<?php
require_once(__DIR__.'/../lib/AltapayCallbackHandler.class.php');

$callbackHandler = new AltapayCallbackHandler();
// Load an example of reservation and capture request
$xml = file_get_contents(__DIR__.'/xml/CallbackXML_reservationAndCapture.xml');

/**
 * @var $response AltapayReservationResponse
 */
$response = $callbackHandler->parseXmlResponse($xml);
if($response->wasSuccessful())
{
	print('Reservation and capture was successful'.PHP_EOL);
	if($response->getPrimaryPayment()->getCapturedAmount() > 0)
	{
		print('The capture was successful, the amount of ' . number_format($response->getPrimaryPayment()->getCapturedAmount(), 2) . ' was captured'.PHP_EOL);
		$avsResponse = $response->getPrimaryPayment()->getAddressVerification();
		if ($avsResponse != '')
		{
			print('AVS response: ' . $avsResponse . PHP_EOL);
			print('AVS description: ' . $response->getPrimaryPayment()->getAddressVerificationDescription() . PHP_EOL);
		} else
		{
			print('No AVS response'. PHP_EOL);
		}
	}
} else
{
	throw new Exception('Reservation and capture failed: '. $response->getErrorMessage());
}