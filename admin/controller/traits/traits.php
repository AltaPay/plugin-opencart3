<?php

trait traitTransactionInfo{

    public function transactionInfo($transactionInfo = array())
    {

        $otherinfo = 'storeName-'.$this->config->get('config_name'); 
			

			$transactionInfo = array(
				'ecomPlatform' => 'Opencart',
				'ecomVersion' => VERSION,
				'altapayPluginName' => 'Altapay',
				'altapayPluginVersion' => '3.2',
			    'otherInfo' => $otherinfo,
			);
       return $transactionInfo;
    }


}

?>