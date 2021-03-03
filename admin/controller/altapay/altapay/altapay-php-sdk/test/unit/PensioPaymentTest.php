<?php
require_once(dirname(__FILE__).'/../lib/bootstrap.php');

class AltapayPaymentTest extends MockitTestCase
{
	private $xml;
	
	public function setup()
	{
		$this->xml = new SimpleXMLElement('
            <Transaction>
                <TransactionId>14398495</TransactionId>
                <AuthType>paymentAndCapture</AuthType>
                <CardStatus>Valid</CardStatus>
                <CreditCardExpiry>
                    <Year>2015</Year>
                    <Month>11</Month>
                </CreditCardExpiry>
                <CreditCardToken>37ad3ff596164142876df477e13336e0aeef0905</CreditCardToken>
                <CreditCardMaskedPan>424374******7275</CreditCardMaskedPan>
                <IsTokenized>true</IsTokenized>
                <ThreeDSecureResult>Disabled</ThreeDSecureResult>
                <CVVCheckResult>Matched</CVVCheckResult>
                <BlacklistToken>185c1c823a9b94731d9c6ba035d9b967587187bc</BlacklistToken>
                <ShopOrderId>ceae3968b82640e38a24ac162d8c2738</ShopOrderId>
                <Shop>Wargaming</Shop>
                <Terminal>Wargaming CC EUR</Terminal>
                <TransactionStatus>captured</TransactionStatus>
                <MerchantCurrency>978</MerchantCurrency>
                <CardHolderCurrency>978</CardHolderCurrency>
                <ReservedAmount>20.00</ReservedAmount>
                <CapturedAmount>19.95</CapturedAmount>
                <RefundedAmount>0.00</RefundedAmount>
                <RecurringDefaultAmount>0.00</RecurringDefaultAmount>
                <CreatedDate>2014-03-21 20:49:38</CreatedDate>
                <UpdatedDate>2014-03-21 20:49:41</UpdatedDate>
                <PaymentNature>CreditCard</PaymentNature>
                <PaymentSource>eCommerce</PaymentSource>
                <PaymentSchemeName>Visa</PaymentSchemeName>
                <PaymentNatureService name="AltapayAcquirer">
                    <SupportsRefunds>true1</SupportsRefunds>
                    <SupportsRelease>true2</SupportsRelease>
                    <SupportsMultipleCaptures>true3</SupportsMultipleCaptures>
                    <SupportsMultipleRefunds>true4</SupportsMultipleRefunds>
                </PaymentNatureService>
                <FraudRiskScore>42</FraudRiskScore>
                <FraudExplanation>For the test fraud service the risk score is always equal mod 101 of the created amount for the payment</FraudExplanation>
                <FraudRecommendation>Deny</FraudRecommendation>
                <ChargebackEvents/>
                <PaymentInfos>
                    <PaymentInfo name="item_name"><![CDATA[5 500 Gold]]></PaymentInfo>
                    <PaymentInfo name="original_amount"><![CDATA[19.95]]></PaymentInfo>
                    <PaymentInfo name="payment_method"><![CDATA[creditcard]]></PaymentInfo>
                    <PaymentInfo name="signature"><![CDATA[affe8e4f628ca55cbd07aa6b0b4fdffb]]></PaymentInfo>
                    <PaymentInfo name="wg_server"><![CDATA[eu]]></PaymentInfo>
                </PaymentInfos>
                <CustomerInfo>
                    <UserAgent>Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0</UserAgent>
                    <IpAddress>91.152.252.214</IpAddress>
                    <Email><![CDATA[timo.k.honkanen@elisanet.fi]]></Email>
                    <Username/>
                    <CustomerPhone>22 22 22 22</CustomerPhone>
                    <OrganisationNumber>345678</OrganisationNumber>
                    <CountryOfOrigin>
                        <Country>FI</Country>
                        <Source>CardNumber</Source>
                    </CountryOfOrigin>
                </CustomerInfo>
                <ReconciliationIdentifiers>
                    <ReconciliationIdentifier>
                        <Id>5c73f256-c096-43c5-8b07-e2b61c887e80</Id>
                        <Amount currency="978">19.95</Amount>
                        <Type>captured</Type>
                        <Date>2014-03-21T20:49:41+01:00</Date>
                    </ReconciliationIdentifier>
                </ReconciliationIdentifiers>
            </Transaction>');

	}
	
	public function testParsingOfSimpleXml()
	{
		$xml = new SimpleXMLElement('<Transaction><PaymentNatureService /><ReconciliationIdentifiers /></Transaction>');
		$payment = new AltapayAPIPayment($xml);
	}

	public function testParsing_of_CurrentStatus()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('captured', $payment->getCurrentStatus());
	}

	public function testParsing_of_Id()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('14398495', $payment->getId());
	}

	public function testParsing_of_AuthType()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('paymentAndCapture', $payment->getAuthType());
	}

	public function testParsing_of_ShopOrderId()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('ceae3968b82640e38a24ac162d8c2738', $payment->getShopOrderId());
	}

	public function testParsing_of_MaskedPan()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('424374******7275', $payment->getMaskedPan());
	}

	public function testParsing_of_CreditCardToken()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('37ad3ff596164142876df477e13336e0aeef0905', $payment->getCreditCardToken());
	}

	public function testParsing_of_CardStatus()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('Valid', $payment->getCardStatus());
	}

	public function testParsing_of_PaymentNature()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('CreditCard', $payment->getPaymentNature());
	}

	public function testParsing_of_PaymentSchemeName()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('Visa', $payment->getPaymentSchemeName());
	}

	public function testParsing_of_PaymentNatureService()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$paymentNature = $payment->getPaymentNatureService();
		$this->assertEquals('AltapayAcquirer', $paymentNature->getName());
		$this->assertEquals('true1', $paymentNature->getSupportsRefunds());
		$this->assertEquals('true2', $paymentNature->getSupportsRelease());
		$this->assertEquals('true3', $paymentNature->getSupportsMultipleCaptures());
		$this->assertEquals('true4', $paymentNature->getSupportsMultipleRefunds());
	}

	public function testParsing_of_FraudRiskScore()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('42', $payment->getFraudRiskScore());
	}

	public function testParsing_of_FraudExplanation()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('For the test fraud service the risk score is always equal mod 101 of the created amount for the payment', $payment->getFraudExplanation());
	}

	public function testParsing_of_FraudRecommendation()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('Deny', $payment->getFraudRecommendation());
	}

	public function testParsing_of_CustomerInfo()
	{
		$payment = new AltapayAPIPayment($this->xml);
		$customerInfo = $payment->getCustomerInfo();

		$this->assertEquals('Mozilla/5.0 (Windows NT 6.1; WOW64; rv:27.0) Gecko/20100101 Firefox/27.0', $customerInfo->getUserAgent());
		$this->assertEquals('91.152.252.214', $customerInfo->getIpAddress());
		$this->assertEquals('timo.k.honkanen@elisanet.fi', $customerInfo->getEmail());
		$this->assertEquals('22 22 22 22', $customerInfo->getPhone());
		$this->assertEquals('345678', $customerInfo->getOrganisationNumber());
		$this->assertEquals('FI', $customerInfo->getCountryOfOrigin()->getCountry());
		$this->assertEquals('CardNumber', $customerInfo->getCountryOfOrigin()->getSource());
	}

	public function testParsing_of_PaymentInfo()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('5 500 Gold', $payment->getPaymentInfo('item_name'));
		$this->assertEquals('19.95', $payment->getPaymentInfo('original_amount'));
		$this->assertEquals('creditcard', $payment->getPaymentInfo('payment_method'));
		$this->assertEquals('affe8e4f628ca55cbd07aa6b0b4fdffb', $payment->getPaymentInfo('signature'));
		$this->assertEquals('eu', $payment->getPaymentInfo('wg_server'));
	}

	public function testParsing_of_Currency()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('978', $payment->getCurrency());
	}

	public function testParsing_of_ReservedAmount()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('20.00', $payment->getReservedAmount());
	}

	public function testParsing_of_CapturedAmount()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('19.95', $payment->getCapturedAmount());
	}
	public function test_IsTokenized()
	{
		$payment = new AltapayAPIPayment($this->xml);

		$this->assertEquals('true', $payment->isTokenized());
		$this->assertTrue($payment->isTokenized());
	}
}