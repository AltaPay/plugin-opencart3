<?php

if(!is_file(dirname(__FILE__).'/integration_config.php'))
{
	throw new Exception("The file integration_config.php must be created");
}

require_once(dirname(__FILE__)."/integration_config.php");
require_once(dirname(__FILE__)."/bootstrap.php");

class ArrayCachingLogger implements IAltapayCommunicationLogger
{
	private $logs = array();
	
	/**
	 * Will get a string representation of the request being sent to Altapay.
	 * @param string $message
	 * @return string - A log-id used to match the request and response
	 */
	public function logRequest($message)
	{
		$logId = md5(microtime().rand(0, 2000000000));
		$this->logs[$logId] = array('request'=>$message,'response'=>null);
		return $logId;
	}
	
	/**
	 * Will get a string representation of the response from Altapay for the request identified by the logId
	 *
	 * @param string $logId
	 * @param string $message
	*/
	public function logResponse($logId, $message)
	{
		$this->logs[$logId]['response'] = $message;
	}
	
	public function getLogs()
	{
		return $this->logs;
	}
}