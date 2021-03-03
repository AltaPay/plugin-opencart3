<?php
require_once(dirname(__FILE__).'/../lib/bootstrap_integration.php');

class AltapayFOpenBasedHttpUtils_AltapayNetworkProblemTest extends MockitTestCase
{
	/**
	 * @var ArrayCachingLogger
	 */
	private $logger;
	
	/**
	 * @var AltapayMerchantAPI
	 */
	private $merchantApi;
	
	public function setup()
	{
		$this->logger = new ArrayCachingLogger();
		$this->httpUtils = new AltapayFOpenBasedHttpUtils(5, 3);
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
		$this->merchantApi->login();
	}
	
	/**
	 * @expectedException AltapayRequestTimeoutException
	 * Disabled due to the unstable nature of the php fopen timeout code. DHAKA DHAKA DHAKA
	 */
	public function _testRequestTimeout()
	{
		$this->merchantApi = new AltapayMerchantAPI(
				'https://testbank.altapay.com/Sleep?time=21&'
				, 'username'
				, 'password'
				, $this->logger
				, $this->httpUtils);
		try
		{
			$this->merchantApi->login();
		}
		catch(Exception $exception)
		{
			if(!($exception instanceof AltapayRequestTimeoutException))
			{
				print_r($this->logger->getLogs());
			}
			throw $exception;
		}
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
		$this->merchantApi->login();
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