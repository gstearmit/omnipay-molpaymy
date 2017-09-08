<?php

namespace Omnipay\MOLPayMY\Message;

use Omnipay\Tests\TestCase;
use Dotenv\Dotenv;
class CompletePurchaseRequestTest extends TestCase
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

        $this->request = new CompletePurchaseRequest($this->getHttpClient(), $this->getHttpRequest());

        $this->request->initialize(array(
            'amount' => '10.00',
            'appCode' => $this->VerifyKey,
            'currency' => 'MYR',
            'domain' => $this->MerchantId,
            'payDate' => '2016-03-29 04:02:21',
            'sKey' => '9b8be764cc5bad1b4a5d58a3ba4daf58',
            'status' => '00',
            'transactionId' => '20160331082207680000',
            'transactionReference' => '000001',
            'verifyKey' => $this->MerchantId,
        ));
    }

    public function testGetData()
    {
        $data = $this->request->getData();

        $this->assertEquals('00', $data['status']);
        $this->assertEquals('000001', $data['transactionReference']);
    }

    public function testSendSuccess()
    {
        $this->request->setStatus('00');
        $this->request->setSKey('9b8be764cc5bad1b4a5d58a3ba4daf58');

        $response = $this->request->send();

        $this->assertEquals('000001', $response->getTransactionReference());
        $this->assertFalse($response->isCancelled());
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isRedirect());
        $this->assertTrue($response->isSuccessful());
    }

    public function testSendPending()
    {
        $this->request->setStatus('22');
        $this->request->setSKey('9d65ed0b785fea1c8fc80b8316555ee3');

        $response = $this->request->send();

        $this->assertEquals('000001', $response->getTransactionReference());
        $this->assertFalse($response->isCancelled());
        $this->assertTrue($response->isPending());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isSuccessful());
    }
}
