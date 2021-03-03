<?php
require_once(dirname(__FILE__).'/../lib/bootstrap_integration.php');

class AltapayCurlBasedHttpUtils_AltapayNetworkProblemTest extends MockitTestCase
{
	/**
	 * ArrayCachingLogger
	 */
	private $logger;
	
	/**
	 * @var AltapayMerchantAPI
	 */
	private $merchantApi;
	/**
	 * @var AltapayCurlBasedHttpUtils
	 */
	private $httpUtils;

	public function setup()
	{
		$this->logger = new ArrayCachingLogger();
		$this->httpUtils = new AltapayCurlBasedHttpUtils(5, 3, false);
	}
	
	/**
	 * @expectedException AltapayConnectionFailedException
	 */
	public function testConnectionRefused()
	{
		$this->merchantApi = new AltapayMerchantAPI(
				'http://localhost:28888/'
				, 'username'
				, 'password'
				, $this->logger
				, $this->httpUtils);
		$response = $this->merchantApi->login();
	}
	
	/**
	 * @expectedException AltapayConnectionFailedException
	 */
	public function testNoConnection()
	{
		$this->merchantApi = new AltapayMerchantAPI(
				'http://testgateway.altapay.com:28888/'
				, 'username'
				, 'password'
				, $this->logger
				, $this->httpUtils);
		$response = $this->merchantApi->login();
	}
	
	/**
	 * @expectedException AltapayRequestTimeoutException
	 */
	public function testRequestTimeout()
	{
		$this->merchantApi = new AltapayMerchantAPI(
				'https://testbank.altapay.com/Sleep?time=21&'
				, 'username'
				, 'password'
				, $this->logger
				, $this->httpUtils);
		$this->merchantApi->login();
	}
	
	/**
	 * @expectedException AltapayInvalidResponseException
	 */
	public function testNonXMLResponse()
	{
		$this->merchantApi = new AltapayMerchantAPI(
				'https://testbank.altapay.com'
				, 'username'
				, 'password'
				, $this->logger
				, $this->httpUtils);
		$response = $this->merchantApi->login();
	}

	/**
	 * @expectedException AltapayUnauthorizedAccessException
	 */
	public function testUnauthorizedResponse()
	{
		$this->merchantApi = new AltapayMerchantAPI(
				'https://testgateway.altapay.com/'
				, 'username'
				, 'password'
				, $this->logger
				, $this->httpUtils);
		$response = $this->merchantApi->login();
	}

	/**
	 * @expectedException AltapayInvalidResponseException
	 */
	public function testNonHTTP200Response()
	{
		$this->merchantApi = new AltapayMerchantAPI(
				'http://www.altapay.com/'
				, 'username'
				, 'password'
				, $this->logger
				, $this->httpUtils);
		$response = $this->merchantApi->login();
	}
}