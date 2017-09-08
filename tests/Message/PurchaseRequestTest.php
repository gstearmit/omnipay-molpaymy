<?php

namespace Omnipay\MOLPayMY\Message;

use Omnipay\Common\CreditCard;
use Omnipay\Tests\TestCase;
use Dotenv\Dotenv;

class PurchaseRequestTest extends TestCase
{
    protected $MerchantId;
    protected $VerifyKey;

    protected $APP_ENV;

    protected $APP_DEBUG;

    /** get env **/
    protected $Dev_MerchantId;
    protected $Dev_VerifyKey;

    protected $Pro_MerchantId;
    protected $Pro_VerifyKey;

    /**
     * @var string
     */
    private $fixturesFolder;

    public function setUp()
    {
        $this->fixturesFolder = dirname(__DIR__);

        $dotenv = new Dotenv($this->fixturesFolder);
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

        $this->request = new PurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize(array(
            'amount' => '10.00',
            'card' => new CreditCard(array(
                'country' => 'MY',
                'email' => 'abc@example.com',
                'name' => 'Lee Siong Chan',
                'phone' => '0123456789',
            )),
            'currency' => 'MYR',
            'description' => 'Test Payment',
            'locale' => 'en',
            'merchantId' => $this->MerchantId,
            'paymentMethod' => 'credit',
            'transactionId' => '20160331082207680000',
            'verifyKey' => $this->VerifyKey,
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals('10.00', $data['amount']);
        $this->assertEquals('MY', $data['country']);
        $this->assertEquals('abc@example.com', $data['bill_email']);
        $this->assertEquals('Lee Siong Chan', $data['bill_name']);
        $this->assertEquals('0123456789', $data['bill_mobile']);
        $this->assertEquals('MYR', $data['currency']);
        $this->assertEquals('Test Payment', $data['bill_desc']);
        $this->assertEquals('en', $data['langcode']);
        $this->assertEquals('credit', $data['channel']);
        $this->assertEquals('20160331082207680000', $data['orderid']);
        $this->assertEquals('f3d5496b444ae3d11e09fa92a753ac60', $data['vcode']);
    }

    public function testSendSuccess()
    {
        $response = $this->request->send();

        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertNull($response->getTransactionReference());
        $this->assertEquals(
            'https://www.onlinepayment.com.my/MOLPay/pay/test1234/?amount=10.00&bill_desc=Test+Payment&bill_email=abc%40example.com&bill_mobile=0123456789&bill_name=Lee+Siong+Chan&channel=credit&country=MY&currency=MYR&langcode=en&orderid=20160331082207680000&vcode=f3d5496b444ae3d11e09fa92a753ac60',
            $response->getRedirectUrl()
        );
    }
}
