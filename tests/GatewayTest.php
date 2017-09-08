<?php

namespace Omnipay\MOLPayMY;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\GatewayTestCase;
use Dotenv\Dotenv;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var \Omnipay\MOLPayMY\Gateway
     */
    protected $gateway;

    protected $MerchantId;
    protected $VerifyKey;

    protected $APP_ENV;

    protected $APP_DEBUG;

    /** get env **/
    protected $Dev_MerchantId;
    protected $Dev_VerifyKey;

    protected $Pro_MerchantId;
    protected $Pro_VerifyKey;

    public function setUp()
    {
        parent::setUp();

        $dotenv = new Dotenv(__DIR__);
        $dotenv->load();

        /** set/get env **/
        $this->APP_ENV   = getenv('APP_ENV');

        $this->APP_DEBUG = getenv('APP_DEBUG');

        $this->Dev_MerchantId = getenv('Dev_MerchantId');
        $this->Dev_VerifyKey  = getenv('Dev_VerifyKey');

        $this->Pro_MerchantId = getenv('Pro_MerchantId');
        $this->Pro_VerifyKey  = getenv('Pro_VerifyKey');

        /**** Set ENV TEST ****/
        $this->MerchantId = ( $this->APP_ENV == "local" and $this->APP_DEBUG == "true") ? $this->Dev_MerchantId : $this->Pro_MerchantId;
        $this->VerifyKey = ( $this->APP_ENV == "local" and $this->APP_DEBUG == "true") ? $this->Dev_VerifyKey : $this->Pro_VerifyKey;
        /**** End Set ENV TEST ****/


        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->gateway->setCurrency('MYR');
        $this->gateway->setLocale('en');
        $this->gateway->setMerchantId($this->MerchantId);
        $this->gateway->setVerifyKey($this->VerifyKey);

        $this->options = array(
            'amount' => '10.00',
            'card' => new CreditCard(array(
                'country' => 'MY',
                'email' => 'abc@example.com',
                'name' => 'Lee Siong Chan',
                'phone' => '0123456789',
            )),
            'description' => 'Test Payment',
            'transactionId' => '20160331082207680000',
            'paymentMethod' => 'credit',
        );
    }

    public function testPurchase()
    {
        $response = $this->gateway->purchase($this->options)->send();
        var_dump( $response->getRedirectUrl());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertEquals(
            'https://www.onlinepayment.com.my/MOLPay/pay/test1234/?amount=10.00&bill_desc=Test+Payment&bill_email=abc%40example.com&bill_mobile=0123456789&bill_name=Lee+Siong+Chan&channel=credit&country=MY&currency=MYR&langcode=en&orderid=20160331082207680000&vcode=f3d5496b444ae3d11e09fa92a753ac60',
            $response->getRedirectUrl()
        );
    }

    public function testCompletePurchaseSuccess()
    {
        var_dump($this->MerchantId );
        var_dump($this->VerifyKey );

        $this->getHttpRequest()->request->replace(array(
            'appcode' => $this->VerifyKey,
            'domain' => $this->MerchantId,
            'paydate' => '2016-03-29 04:02:21',
            'skey' => 'f3d5496b444ae3d11e09fa92a753ac60',
            'status' => '00',
            'tranID' => '000001',
        ));

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     */
    public function testCompletePurchaseInvalidSKey()
    {
        $this->getHttpRequest()->request->replace(array(
            'appcode' => $this->VerifyKey,
            'domain' => $this->MerchantId,
            'paydate' => '2016-03-29 04:02:21',
            'skey' => 'I_AM_INVALID_SKEY',
            'status' => '11',
            'tranID' => '000001',
        ));

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    /**
     * @expectedException \Omnipay\Common\Exception\InvalidResponseException
     */
    public function testCompletePurchaseError()
    {
        $this->getHttpRequest()->request->replace(array(
            'appcode' => $this->VerifyKey,
            'domain' => $this->MerchantId,
            'paydate' => 'I am not a date',
            'skey' => 'ef0903d1906d0968605155f85ec9fcd5',
            'status' => '11',
            'error_desc' => 'Invalid date',
            'tranID' => '000001',
        ));

        $response = $this->gateway->completePurchase($this->options)->send();
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertEquals('Invalid date', $response->getMessage());
    }
}
