# Omnipay: MOLPayMY

**MOLPayMY driver for the Omnipay PHP payment processing library**

 **MOLPayMY API Specification (Version 12.1: Updated on 12 April 2015)**.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "gstearmit/omnipay-molpaymy": "~2.0"
    }
}
```

And run composer to update your dependencies:

    $ curl -s http://getcomposer.org/installer | php
    $ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* MOLPayMY (MOLPayMY Payment)

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## Example

### Create a purchase request

The example below explains how you can create a purchase request then send it.

```php
$gateway = Omnipay::create('MOLPayMY');

$gateway->setCurrency('MYR');
$gateway->setEnableIPN(true); // Optional
$gateway->setLocale('en'); // Optional
$gateway->setMerchantId('test1234');
$gateway->setVerifyKey('abcdefg');

$options = [
    'amount' => '10.00',
    'card' => new CreditCard(array(
        'country' => 'MY',
        'email' => 'abc@example.com',
        'name' => 'Lee Siong Chan',
        'phone' => '0123456789',
    )),
    'description' => 'Test Payment',
    'transactionId' => '20160331082207680000',
    'paymentMethod' => 'credit', // Optional
];

$response = $gateway->purchase($options)->send();

// Get the MOLPayMY payment URL (https://www.onlinepayment.com.my/MOLPayMY/pay/...)
$redirectUrl = $response->getRedirectUrl(); 
```

### Complete a purchase request

When the user submit the payment form, the gateway will redirect you to the return URL that you have specified in MOLPayMY. The code below gives an example how to handle the server feedback answer.

```php
$response = $gateway->completePurchase($options)->send();

if ($response->isSuccessful()) {
    // Do something
    echo $response->getTransactionReference();
} elseif ($response->isPending()) {
    // Do something
} else {
    // Error
}
```

## Out Of Scope

Omnipay does not cover recurring payments or billing agreements, and so those features are not included in this package. Extensions to this gateway are always welcome. 


------------- SET ENV ---------------------

1. ENV 
  https://www.onlinepayment.com.my/MOLPay/pay/test1234/?amount=10.00&bill_desc=Test+Payment&bill_email=abc%40example.com&bill_mobile=0123456789&bill_name=Lee+Siong+Chan&channel=credit&country=MY&currency=MYR&langcode=en&orderid=20160331082207680000&vcode=f3d5496b444ae3d11e09fa92a753ac60
  
2.   