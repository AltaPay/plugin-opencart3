<?php
require_once(dirname(__FILE__).'/../lib/bootstrap_integration.php');

class AltapayReservationTest extends MockitTestCase
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

	public function testSuccessfulReservation()
	{
		$response = $this->merchantApi->reservation(
			ALTAPAY_INTEGRATION_TERMINAL
			, 'ReservationTest_'.time()
			, 42.00
			, ALTAPAY_INTEGRATION_CURRENCY
			, null
			, '4111000011110000'
			, 1
			, 2018
			, '123');

		$this->assertTrue($response->wasSuccessful());
	}

	public function testFailedReservation()
	{
		$response = $this->merchantApi->reservation(
			ALTAPAY_INTEGRATION_TERMINAL
			, 'ReservationTest_'.time()
			, 5.66
			, ALTAPAY_INTEGRATION_CURRENCY
			, null
			, '4111000011110000'
			, 1
			, 2018
			, '123');

		$this->assertTrue($response->wasDeclined());
	}

	public function testErroneousReservation()
	{
		$response = $this->merchantApi->reservation(
			ALTAPAY_INTEGRATION_TERMINAL
			, 'ReservationTest_'.time()
			, 5.67
			, ALTAPAY_INTEGRATION_CURRENCY
			, null
			, '4111000011110000'
			, 1
			, 2018
			, '123');

		$this->assertTrue($response->wasErroneous());
	}

	public function testSuccessfulReservationUsingToken()
	{
		$response = $this->merchantApi->reservation(
			ALTAPAY_INTEGRATION_TERMINAL
			, 'ReservationTest_'.time()
			, 42.00
			, ALTAPAY_INTEGRATION_CURRENCY
			, null
			, '4111000011110000'
			, 1
			, 2018
			, '123');

		$response2 = $this->merchantApi->reservation(
			ALTAPAY_INTEGRATION_TERMINAL
			, 'ReservationTest_'.time()
			, 42.00
			, ALTAPAY_INTEGRATION_CURRENCY
			, $response->getPrimaryPayment()->getCreditCardToken()
			, null
			, null
			, null
			, '123');

		$this->assertTrue($response2->wasSuccessful());
	}

	public function testReservationUsingAllParameters()
	{
		$orderLines = array(
			array('description'=>'SomeDescription','itemId'=>'KungFuBoy','quantity'=>1.00,'unitPrice'=>21.12,'taxAmount'=>0.00,'unitCode'=>'kg','discount'=>0.00,'goodsType'=>'item'),
			array('description'=>'SomeDescription','itemId'=>'KarateKid','quantity'=>1.00,'unitPrice'=>21.12,'taxAmount'=>0.00,'unitCode'=>'kg','discount'=>0.00,'goodsType'=>'item')
		);

		$customerInfo = array(
				'billing_postal'=> '1111',
				'billing_country'=> 'DK', // 2 character ISO-3166
				'billing_address'=> 'bil address 1',
				'billing_city'=> 'bil city',
				'billing_region'=> 'bil region',
				'billing_firstname'=> 'bil name',
				'billing_lastname'=> 'bil last',
				'shipping_postal'=> '2222',
				'shipping_country'=> 'BR', // 2 character ISO-3166
				'shipping_address'=> 'ship address 1',
				'shipping_city'=> 'ship city',
				'shipping_region'=> 'ship region',
				'shipping_firstname'=> 'ship name',
				'shipping_lastname'=> 'ship last',
				'email'=>'testperson@mydomain.com',
				'username' => 'user name',
				'customer_phone' => '11 22 33 44'
		);

		$shop_orderid = 'ReservationTest_' . time();

		$response = $this->merchantApi->reservation(
			ALTAPAY_INTEGRATION_TERMINAL
			, $shop_orderid
			, 42.00
			, ALTAPAY_INTEGRATION_CURRENCY
			, null
			, '4111000011110000'
			, 1
			, 2018
			, '123'
			, array('info1' => 'desc1', 'info2' => 'desc2')
			, 'paymentAndCapture'
			, 'mail_order'
			, 'test'
			, 2.5
			, '2017-12-30'
			, 'International'
			, $customerInfo
			, $orderLines);
		
		$this->assertTrue($response->wasSuccessful(), $response->getErrorMessage());

		$this->assertEquals(ALTAPAY_INTEGRATION_TERMINAL, $response->getPrimaryPayment()->getTerminal());
		$this->assertEquals($shop_orderid, $response->getPrimaryPayment()->getShopOrderId());
		$this->assertEquals('paymentAndCapture', $response->getPrimaryPayment()->getAuthType());
		$this->assertEquals(42, $response->getPrimaryPayment()->getCapturedAmount());
		$this->assertEquals(42, $response->getPrimaryPayment()->getReservedAmount());
		$this->assertEquals('411100******0000', $response->getPrimaryPayment()->getMaskedPan());
		$this->assertEquals(1, $response->getPrimaryPayment()->getCreditCardExpiryMonth());
		$this->assertEquals(2018, $response->getPrimaryPayment()->getCreditCardExpiryYear());

		$this->assertEquals("1111", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getPostalCode());
		$this->assertEquals("DK", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getCountry());
		$this->assertEquals("bil address 1", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getAddress());
		$this->assertEquals("bil city", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getCity());
		$this->assertEquals("bil region", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getRegion());
		$this->assertEquals("bil name", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getFirstName());
		$this->assertEquals("bil last", $response->getPrimaryPayment()->getCustomerInfo()->getBillingAddress()->getLastName());

		$this->assertEquals("2222", $response->getPrimaryPayment()->getCustomerInfo()->getShippingAddress()->getPostalCode());
		$this->assertEquals("BR", $response->getPrimaryPayment()->getCustomerInfo()->getShippingAddress()->getCountry());
		$this->assertEquals("ship address 1", $response->getPrimaryPayment()->getCustomerInfo()->getShippingAddress()->getAddress());
		$this->assertEquals("ship city", $response->getPrimaryPayment()->getCustomerInfo()->getShippingAddress()->getCity());
		$this->assertEquals("ship region", $response->getPrimaryPayment()->getCustomerInfo()->getShippingAddress()->getRegion());
		$this->assertEquals("ship name", $response->getPrimaryPayment()->getCustomerInfo()->getShippingAddress()->getFirstName());
		$this->assertEquals("ship last", $response->getPrimaryPayment()->getCustomerInfo()->getShippingAddress()->getLastName());
		
		$this->assertEquals("testperson@mydomain.com", $response->getPrimaryPayment()->getCustomerInfo()->getEmail());
		$this->assertEquals("user name", $response->getPrimaryPayment()->getCustomerInfo()->getUsername());
		$this->assertEquals("11 22 33 44", $response->getPrimaryPayment()->getCustomerInfo()->getPhone());

		$this->assertEquals("desc1", $response->getPrimaryPayment()->getPaymentInfo('info1'));
		$this->assertEquals("desc2", $response->getPrimaryPayment()->getPaymentInfo('info2'));
	}


}