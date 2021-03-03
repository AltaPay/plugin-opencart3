<?php
require_once(dirname(__FILE__).'/../lib/bootstrap_integration.php');

class AltapayLoginTest extends MockitTestCase
{
	/**
	 * @var AltapayMerchantAPI
	 */
	private $merchantApi;
	
	public function setup()
	{
		$this->merchantApi = new AltapayMerchantAPI(ALTAPAY_INTEGRATION_INSTALLATION, ALTAPAY_INTEGRATION_USERNAME, ALTAPAY_INTEGRATION_PASSWORD);
	}
	
	public function testSuccessfullLogin()
	{
		$response = $this->merchantApi->login();
		
		$this->assertTrue($response->wasSuccessful());
	}
}