<?php
require_once(__DIR__.'/base.php');
// For the purpose of this example will be assumed that there is one page
$pageCount = 1;
for($page = 1; $page <= $pageCount; $page++)
{
	/**
	 * @var $response AltapayFundingListResponse
	 */
	$response = $api->getFundingList($page);
	if($response->wasSuccessful())
	{
		/**
        * @var $funding AltapayAPIFunding
		 */
		foreach($response->getFundings() as $funding)
		{
			print('There is a funding of '.$funding->getAmount().' '.$funding->getCurrency().', made on '.$funding->getFundingDate(). PHP_EOL);
			/**
			 * @var $csv boolean|string
			 */
			$csv = $api->downloadFundingCSV($funding);
			if (!$csv)
			{
				//throw new Exception('The funding CSV file '. $funding->getFilename() .' could not be found');
				//or
				print('The funding CSV file ' . $funding->getFilename(). ' could not be found.' . PHP_EOL);
			} else
			{
				print('The content of the funding CSV file('.$funding->getFilename().') is:'. PHP_EOL . $csv . PHP_EOL);
			}
		}
	}
	else
	{
		throw new Exception('The funding list could not be fetched: '.$response->getErrorMessage());
	}
}
