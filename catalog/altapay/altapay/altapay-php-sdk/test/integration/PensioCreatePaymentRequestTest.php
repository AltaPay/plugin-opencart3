<?php
require_once(dirname(__FILE__).'/../lib/bootstrap_integration.php');

class AltapayCreatePaymentRequestTest extends MockitTestCase
{
	/**
	 * @var AltapayMerchantAPI
	 */
	private $merchantApi;
	
	public function setup()
	{
		$this->merchantApi = new AltapayMerchantAPI(ALTAPAY_INTEGRATION_INSTALLATION, ALTAPAY_INTEGRATION_USERNAME, ALTAPAY_INTEGRATION_PASSWORD);
		$this->merchantApi->login();
	}
	
	public function testCreatePaymentRequest()
	{
		$response = $this->merchantApi->createPaymentRequest(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'testorder'
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, 'payment');
		
		$this->assertTrue($response->wasSuccessful());
	}

	public function testCreatePaymentRequestWithMoreData()
	{
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
		); // See the documentation for further details
		$cookie = 'some cookie';
		$language = 'da';
		$config = array(
				'callback_form' => 'http://127.0.0.1/altapaypayment/form.php'
				, 'callback_ok' => 'http://127.0.0.1/altapaypayment/ok.php'
				, 'callback_fail' => 'http://127.0.0.1/altapaypayment/fail.php'
				, 'callback_redirect' => ''     // See documentation if this is needed
				, 'callback_open' => ''         // See documentation if this is needed
				, 'callback_notification' => '' // See documentation if this is needed
		);
		$transaction_info = array('auxkey'=>'aux data'); // this can be left out.
		
		$response = $this->merchantApi->createPaymentRequest(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'testorder'
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, 'payment'
				, $customerInfo
				, $cookie
				, $language
				, $config
				, $transaction_info);
		
		$this->assertTrue($response->wasSuccessful(), $response->getErrorMessage());
	}
}