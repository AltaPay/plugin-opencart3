<?php
require_once(dirname(__FILE__).'/../lib/bootstrap_integration.php');

class AltapaySubscriptionTest extends MockitTestCase
{
	/**
	 * @var AltapayMerchantAPI
	 */
	private $merchantApi;
	
	public function setup()
	{
		$this->logger = new ArrayCachingLogger();
		$this->merchantApi = new AltapayMerchantAPI(
				ALTAPAY_INTEGRATION_INSTALLATION
				, ALTAPAY_INTEGRATION_USERNAME
				, ALTAPAY_INTEGRATION_PASSWORD
				, $this->logger);
		$this->merchantApi->login();
	}
	
	public function testSuccessfullSetupSubscription()
	{
		$response = $this->merchantApi->setupSubscription(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'subscription'.time()
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011110000' 
				, '2020'
				, '12'
				, '123'
				, 'eCommerce');
		
		$this->assertTrue($response->wasSuccessful(), $response->getMerchantErrorMessage());
	}
	
	public function testDeclinedSetupSubscription()
	{
		$response = $this->merchantApi->setupSubscription(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'subscription-declined'.time()
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011111466'
				, '2020'
				, '12'
				, '123'
				, 'eCommerce');
	
		$this->assertTrue($response->wasDeclined(), $response->getMerchantErrorMessage());
	}
	
	public function testErroneousSetupSubscription()
	{
		$response = $this->merchantApi->setupSubscription(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'subscription-error'.time()
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011111467'
				, '2020'
				, '12'
				, '123'
				, 'eCommerce');
	
		$this->assertTrue($response->wasErroneous(), $response->getMerchantErrorMessage());
	}

	public function testSuccessfulChargeSubscription()
	{
		$subscriptionResponse = $this->merchantApi->setupSubscription(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'subscription-charge'.time()
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011110000'
				, '2020'
				, '12'
				, '123'
				, 'eCommerce');
	
		$chargeResponse = $this->merchantApi->chargeSubscription($subscriptionResponse->getPrimaryPayment()->getId());

		$this->assertTrue($chargeResponse->wasSuccessful(), $chargeResponse->getMerchantErrorMessage());
	}

	public function testSuccessfulChargeSubscriptionWithToken()
	{
		$verifyCardResponse = $this->merchantApi->verifyCard(
			ALTAPAY_INTEGRATION_TERMINAL
			, 'verify-card'.time()
			, ALTAPAY_INTEGRATION_CURRENCY
			, '4111000011110000'
			, '2020'
			, '12'
			, '123'
			, 'eCommerce');

		$subscriptionResponseWithToken = $this->merchantApi->setupSubscriptionWithToken(
			ALTAPAY_INTEGRATION_TERMINAL
			, 'subscription-with-token'.time()
			, 42.00
			, ALTAPAY_INTEGRATION_CURRENCY
			, $verifyCardResponse->getPrimaryPayment()->getCreditCardToken()
			, '123'
			, 'eCommerce');

		$this->assertTrue($subscriptionResponseWithToken->wasSuccessful(), $subscriptionResponseWithToken->getMerchantErrorMessage());
	}
	
}