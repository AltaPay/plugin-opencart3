<?php
require_once(dirname(__FILE__).'/../lib/bootstrap.php');

class AltapayMerchantAPITest extends MockitTestCase
{
	/**
	 * @var AltapayMerchantAPI
	 */
	private $merchantAPI;
	
	/**
	 * @var Mock_IAltapayCommunicationLogger
	 */
	private $logger;
	/**
	 * @var Mock_IAltapayHttpUtils
	 */
	private $httpUtils;
	/**
	 * @var Mock_AltapayHttpResponse
	 */
	private $response;
	
	public function setup()
	{
		Mockit::initMocks($this);
		$this->response->when()->getConnectionResult()->thenReturn(AltapayHttpResponse::CONNECTION_OKAY);
		$this->response->when()->getContentType()->thenReturn("text/xml");

		$this->merchantAPI = new AltapayMerchantAPI('http://base.url', 'username', 'password', $this->logger->instance(), $this->httpUtils->instance());
	}

	/**
	 * @expectedException AltapayInvalidResponseException
	 */
	public function testHandlesNonXmlNicely()
	{
		$this->response->when()->getHttpCode()->thenReturn(200);
		$this->response->when()->getContent()->thenReturn('<html>
<head><title>504 Gateway Time-out</title></head>
<body bgcolor="white">
<center><h1>504 Gateway Time-out</h1></center>
<hr><center>nginx/0.7.67</center>
</body>
</html>');
		$this->httpUtils->when()->requestURL()->thenReturn($this->response->instance());
		
		$loginResponse = $this->merchantAPI->login();
		$this->assertEquals('Error: String could not be parsed as XML', $loginResponse->getErrorMessage());
	}

	/**
	 * @expectedException AltapayInvalidResponseException
	 */
	public function testNon200ReturnCodeIsHandled()
	{
		$this->response->when()->getHttpCode()->thenReturn(500);
		$this->httpUtils->when()->requestURL()->thenReturn($this->response->instance());
		
		$loginResponse = $this->merchantAPI->login();
	}

	/**
	 * @expectedException AltapayUnauthorizedAccessException
	 */
	public function testUnAuthorizedReturnCodeIsHandled()
	{
		$this->response->when()->getHttpCode()->thenReturn(401);
		$this->httpUtils->when()->requestURL()->thenReturn($this->response->instance());
		
		$loginResponse = $this->merchantAPI->login();
	}


	public function testGetPayment_Parses20110831XmlCorrectly()
	{
		$this->response->when()->getHttpCode()->thenReturn(200);
		$this->response->when()->getContent()->thenReturn(file_get_contents(dirname(dirname(dirname(__FILE__))).'/example/xml/20110831_get_payment.xml'));
		$this->httpUtils->when()->requestURL()->thenReturn($this->response->instance());

		$this->merchantAPI->login();
		$getPaymentResponse = $this->merchantAPI->getPayment('123');
		$this->assertTrue($getPaymentResponse instanceof AltapayGetPaymentResponse);
		$this->assertTrue($getPaymentResponse->wasSuccessful());
	}

	public function testGetPayment_Parses20130430XmlCorrectly()
	{
		$this->response->when()->getHttpCode()->thenReturn(200);
		$this->response->when()->getContent()->thenReturn(file_get_contents(dirname(dirname(dirname(__FILE__))).'/example/xml/20130430_get_payment.xml'));
		$this->httpUtils->when()->requestURL()->thenReturn($this->response->instance());

		$this->merchantAPI->login();
		$getPaymentResponse = $this->merchantAPI->getPayment('123');
		$this->assertTrue($getPaymentResponse instanceof AltapayGetPaymentResponse);
		$this->assertTrue($getPaymentResponse->wasSuccessful());
	}


	public function testGetPayment_WithNoPayment_IsNotSuccessful()
	{
		$this->response->when()->getHttpCode()->thenReturn(200);
		$this->response->when()->getContent()->thenReturn(file_get_contents(dirname(dirname(dirname(__FILE__))).'/example/xml/20130430_get_payment_empty.xml'));
		$this->httpUtils->when()->requestURL()->thenReturn($this->response->instance());

		$this->merchantAPI->login();
		$getPaymentResponse = $this->merchantAPI->getPayment('123');
		$this->assertFalse($getPaymentResponse->wasSuccessful());
	}
}