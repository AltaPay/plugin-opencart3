<?php
require_once(dirname(__FILE__).'/../lib/bootstrap_integration.php');

class AltapayReservationOfFixedAmountTest extends MockitTestCase
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
	
	public function testSimplePayment()
	{
		$response = $this->merchantApi->reservationOfFixedAmount(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'testorder'
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011110000' 
				, '2020'
				, '12'
				, '123'
				, 'eCommerce');
		
		$this->assertTrue($response->wasSuccessful());
	}

	public function testPaymentWithCustomerInfo()
	{
		$customerInfo = array(
				'billing_postal'=> '2860',
				'billing_country'=> 'DK', // 2 character ISO-3166
				'billing_address'=> 'Rosenkæret 13',
				'billing_city'=> 'Søborg',
				'billing_region'=> 'some region',
				'billing_firstname'=> 'Kødpålæg >-) <script>alert(42);</script>',
				'billing_lastname'=> 'Lyn',
				'email'=>'testperson@mydomain.com',
		); // See the documentation for further details
		
		$response = $this->merchantApi->reservationOfFixedAmount(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'with-billing-and-transinfo-'.time()
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011110000' 
				, '2020'
				, '12'
				, '123'
				, 'eCommerce'
				, $customerInfo);
		
		$this->assertTrue($response->wasSuccessful());
		
		$this->assertEquals("2860", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getPostalCode());
		$this->assertEquals("DK", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getCountry());
		$this->assertEquals("Rosenkæret 13", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getAddress());
		$this->assertEquals("Søborg", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getCity());
		$this->assertEquals("some region", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getRegion());
		$this->assertEquals("Kødpålæg >-) <script>alert(42);</script>", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getFirstName());
		$this->assertEquals("Lyn", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getLastName());
		$this->assertEquals("testperson@mydomain.com", $response->getPrimaryPayment()->getCustomerInfo()->getEmail());
	}

	public function testPaymentWithPaymentInfo()
	{
		$transaction_info = array('auxkey'=>'aux data (<æøå>)', 'otherkey'=>'MyValue');
		
		$response = $this->merchantApi->reservationOfFixedAmount(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'with-billing-and-transinfo-'.time()
				, 42.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011110000' 
				, '2020'
				, '12'
				, '123'
				, 'eCommerce'
				, array()
				, $transaction_info);
		
		$this->assertTrue($response->wasSuccessful());
		$this->assertEquals("aux data (<æøå>)", $response->getPrimaryPayment()->getPaymentInfo('auxkey'));
		$this->assertEquals("MyValue", $response->getPrimaryPayment()->getPaymentInfo('otherkey'));
	}

	public function testPaymentSchemeNameIsVisa()
	{
		$response = $this->merchantApi->reservationOfFixedAmount(
				ALTAPAY_INTEGRATION_TERMINAL
				, 'testorder'
				, 43.00
				, ALTAPAY_INTEGRATION_CURRENCY
				, '4111000011110000' 
				, '2020'
				, '12'
				, '123'
				, 'eCommerce');
		$this->assertType('object',$response->getPrimaryPayment());
		$this->assertEquals('Visa', $response->getPrimaryPayment()->getPaymentSchemeName());
	}
}