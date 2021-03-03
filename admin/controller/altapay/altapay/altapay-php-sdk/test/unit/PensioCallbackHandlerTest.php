<?php
require_once(dirname(__FILE__).'/../lib/bootstrap.php');

class AltapayCallbackHandlerTest extends MockitTestCase
{
	/**
	 * @var AltapayCallbackHandler
	 */
	private $handler;

	public function setup()
	{
		$this->handler = new AltapayCallbackHandler();
	}

	public function testErrorCaseDueToTooLongCardNumber()
	{
		$response = $this->handler->parseXmlResponse('<'.'?xml version="1.0" ?>
<APIResponse version="20121016">
    <Header>
        <Date>2013-10-29T02:33:15+01:00</Date>
        <Path>API/reservationOfFixedAmount</Path>
        <ErrorCode>0</ErrorCode>
        <ErrorMessage></ErrorMessage>
    </Header>
    <Body>
        <Result>Success</Result>
        <Transactions>
            <Transaction>
                <TransactionId></TransactionId>
                <AuthType>paymentAndCapture</AuthType>
                <CardStatus>NoCreditCard</CardStatus>
                <CreditCardToken></CreditCardToken>
                <CreditCardMaskedPan></CreditCardMaskedPan>
                <ThreeDSecureResult>Not_Applicable</ThreeDSecureResult>
                <BlacklistToken></BlacklistToken>
                <ShopOrderId>c8110a86403911e39a8e101f742d064a</ShopOrderId>
                <Shop>Wargaming</Shop>
                <Terminal>Wargaming CC JPY SEA</Terminal>
                <TransactionStatus>created</TransactionStatus>
                <MerchantCurrency>392</MerchantCurrency>
                <CardHolderCurrency>392</CardHolderCurrency>
                <ReservedAmount>0.00</ReservedAmount>
                <CapturedAmount>0.00</CapturedAmount>
                <RefundedAmount>0.00</RefundedAmount>
                <RecurringDefaultAmount>0.00</RecurringDefaultAmount>
                <CreatedDate>-0001-11-30 00:00:00</CreatedDate>
                <UpdatedDate>-0001-11-30 00:00:00</UpdatedDate>
                <PaymentNature>CreditCard</PaymentNature>
                <PaymentSource>eCommerce</PaymentSource>
                <PaymentSchemeName>CreditCard</PaymentSchemeName>
                <PaymentNatureService name="">
                    <SupportsRefunds>false</SupportsRefunds>
                    <SupportsRelease>false</SupportsRelease>
                    <SupportsMultipleCaptures>false</SupportsMultipleCaptures>
                    <SupportsMultipleRefunds>false</SupportsMultipleRefunds>
                </PaymentNatureService>
                <ChargebackEvents/>
                <PaymentInfos>
                    <PaymentInfo name="item_name"><![CDATA[6 500 Gold]]></PaymentInfo>
                    <PaymentInfo name="original_amount"><![CDATA[2695]]></PaymentInfo>
                    <PaymentInfo name="payment_method"><![CDATA[creditcard]]></PaymentInfo>
                    <PaymentInfo name="signature"><![CDATA[75e4046386a835f362831169924aeed8]]></PaymentInfo>
                    <PaymentInfo name="wg_server"><![CDATA[asia]]></PaymentInfo>
                </PaymentInfos>
                <CustomerInfo>
                    <UserAgent>Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; WOW64; Trident/6.0; MANMJS)
                    </UserAgent>
                    <IpAddress>175.108.245.131</IpAddress>
                    <Email><![CDATA[takut_takut1128@yahoo.co.jp]]></Email>
                    <Username><![CDATA[]]></Username>
                    <CustomerPhone></CustomerPhone>
                    <OrganisationNumber></OrganisationNumber>
                </CustomerInfo>
                <ReconciliationIdentifiers/>
            </Transaction>
        </Transactions>
    </Body>
</APIResponse>');
		$this->assertFalse($response->wasSuccessful());
	}

	public function testEpaymentCancelled ()
	{

		$response = $this->handler->parseXmlResponse('<' . '?xml version="1.0"?>
<APIResponse version="20160719">
    <Header>
        <Date>2017-02-06T11:43:10+01:00</Date>
        <Path>API/ePaymentVerify</Path>
        <ErrorCode>0</ErrorCode>
        <ErrorMessage/>
    </Header>
    <Body>
        <Result>Cancelled</Result>
        <Transactions>
            <Transaction>
                <TransactionId>1034</TransactionId>
                <PaymentId>f66c19ca-1268-497f-9578-c1cd4919c365</PaymentId>
                <AuthType>paymentAndCapture</AuthType>
                <CardStatus>NoCreditCard</CardStatus>
                <CreditCardToken/>
                <CreditCardMaskedPan/>
                <ThreeDSecureResult>Not_Applicable</ThreeDSecureResult>
                <LiableForChargeback>Merchant</LiableForChargeback>
                <CVVCheckResult>Not_Applicable</CVVCheckResult>
                <BlacklistToken/>
                <ShopOrderId>145000154</ShopOrderId>
                <Shop>AltapayOmni Functional Test Shop</Shop>
                <Terminal>AltapayOmni Test EPayment Terminal</Terminal>
                <TransactionStatus>epayment_cancelled</TransactionStatus>
                <ReasonCode>NONE</ReasonCode>
                <MerchantCurrency>840</MerchantCurrency>
                <MerchantCurrencyAlpha>USD</MerchantCurrencyAlpha>
                <CardHolderCurrency>840</CardHolderCurrency>
                <CardHolderCurrencyAlpha>USD</CardHolderCurrencyAlpha>
                <ReservedAmount>0.00</ReservedAmount>
                <CapturedAmount>0.00</CapturedAmount>
                <RefundedAmount>0.00</RefundedAmount>
                <CreditedAmount>0.00</CreditedAmount>
                <RecurringDefaultAmount>0.00</RecurringDefaultAmount>
                <SurchargeAmount>0.00</SurchargeAmount>
                <CreatedDate>2017-02-06 11:42:52</CreatedDate>
                <UpdatedDate>2017-02-06 11:43:10</UpdatedDate>
                <PaymentNature>EPayment</PaymentNature>
                <PaymentSource>mobi</PaymentSource>
                <PaymentSchemeName>TestEPayment</PaymentSchemeName>
                <PaymentNatureService name="TestAcquirerEPayment">
                    <SupportsRefunds>true</SupportsRefunds>
                    <SupportsRelease>false</SupportsRelease>
                    <SupportsMultipleCaptures>false</SupportsMultipleCaptures>
                    <SupportsMultipleRefunds>true</SupportsMultipleRefunds>
                </PaymentNatureService>
                <ChargebackEvents/>
                <PaymentInfos>
                    <PaymentInfo name="qoute">
                        <![CDATA[835]]>
                    </PaymentInfo>
                </PaymentInfos>
                <CustomerInfo>
                    <UserAgent>Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/55.0.2883.87 Chrome/55.0.2883.87 Safari/537.36</UserAgent>
                    <IpAddress>127.0.0.1</IpAddress>
                    <Email>
                        <![CDATA[x@c.com]]>
                    </Email>
                    <Username/>
                    <CustomerPhone>x</CustomerPhone>
                    <OrganisationNumber/>
                    <CountryOfOrigin>
                        <Country>US</Country>
                        <Source>BillingAddress</Source>
                    </CountryOfOrigin>
                    <BillingAddress>
                        <Firstname>
                            <![CDATA[x]]>
                        </Firstname>
                        <Lastname>
                            <![CDATA[x]]>
                        </Lastname>
                        <Address>
                            <![CDATA[x
                            x]]>
                        </Address>
                        <City>
                            <![CDATA[x]]>
                        </City>
                        <Region>
                            <![CDATA[Alabama]]>
                        </Region>
                        <Country>
                            <![CDATA[US]]>
                        </Country>
                        <PostalCode>
                            <![CDATA[x]]>
                        </PostalCode>
                    </BillingAddress>
                    <ShippingAddress>
                        <Firstname>
                            <![CDATA[x]]>
                        </Firstname>
                        <Lastname>
                            <![CDATA[x]]>
                        </Lastname>
                        <Address>
                            <![CDATA[x
                            x]]>
                        </Address>
                        <City>
                            <![CDATA[x]]>
                        </City>
                        <Region>
                            <![CDATA[Alabama]]>
                        </Region>
                        <Country>
                            <![CDATA[US]]>
                        </Country>
                        <PostalCode>
                            <![CDATA[x]]>
                        </PostalCode>
                    </ShippingAddress>
                </CustomerInfo>
                <ReconciliationIdentifiers/>
            </Transaction>
        </Transactions>
    </Body>
</APIResponse>');

		$this->assertFalse($response->wasSuccessful());
		$this->assertEquals('epayment_cancelled', $response->getPrimaryPayment()->getCurrentStatus());

	}


	public function testMerchantErrorMessageWithoutTransactionParameter() {
		$xml = file_get_contents(__DIR__.'/xml/CallbackXML_MobilePayError.xml');
		try{
			$this->handler->parseXmlResponse($xml);
			$this->fail("Expected an exception");
		}catch (AltapayXmlException $e){
			$this->assertType('SimpleXMLElement',$e->getXml());
			$merchantErrorMessage = (string)$e->getXml()->Body[0]->MerchantErrorMessage;
			$this->assertEquals('Unable to register MobilePay payment',$merchantErrorMessage);
		}
	}

	public function testReadCardHolderErrorMessageMustBeShown() {

		$xml = file_get_contents(__DIR__.'/xml/CardHolderMessageMustBeShownFalse.xml');
		$response = $this->handler->parseXmlResponse($xml);
		$this->assertEquals('false', $response->getCardHolderMessageMustBeShown());

		$xml = file_get_contents(__DIR__.'/xml/CardHolderMessageMustBeShownTrue.xml');
		$response = $this->handler->parseXmlResponse($xml);
		$this->assertEquals('true', $response->getCardHolderMessageMustBeShown());

	}

	public function testReadReasonCode() {

		$xml = file_get_contents(__DIR__.'/xml/ReasonCode.xml');
		$response = $this->handler->parseXmlResponse($xml);
		$this->assertEquals('NONE', $response->getPrimaryPayment()->getReasonCode());

	}

	public function testReadPaymentId() {

		$xml = file_get_contents(__DIR__.'/xml/ReasonCode.xml');
		$response = $this->handler->parseXmlResponse($xml);
		$this->assertEquals('17794956-9bb6-4854-9712-bce5931e6e3a', $response->getPrimaryPayment()->getPaymentId());

	}
}