# tamara
Tamara is a pay-later service with zero interest and no additional fees.
Shop for the items you're interested in from our partners and split the amount into 3 or 4 easy monthly payments, the first of which is upon purchase.

## Installation
You can install the package via [Composer](https://getcomposer.org).

```bash
composer require maree/tamara
```
Publish your tamara config file with

```bash
php artisan vendor:publish --provider="Maree\Tamara\TamaraServiceProvider" --tag="tamara"
```
then change your tamara config from config/tamara.php file
```php
    "token"        => "" , //from your tamara account 
    "mode"         => "test", //test or live
    'country_code' => "SA",
    'currency'     => "SAR",

```
## Usage

## Check payment available options
```php
use Maree\Tamara\Tamara;

        $order = ['phone' => '00966511111110', 'total' => 600];
        $response = (new Tamara())->checkPaymentOptionsAvailability($order);

```

## Create checkout order
```php
use Maree\Tamara\Tamara;

        $order       = ['order_num' => '123', 'total' => 500,'notes' => 'notes ', 'discount_name' => 'discount coupon','discount_amount' => 50,'vat_amount' => 50,'shipping_amount' => 20];
        $products[0] = ['id' => '123','type' => 'mobiles' ,'name' => 'iphone','sku' => 'SA-12436','image_url' => 'https://example.com/image.png','quantity' => 1,'unit_price'=>50,'discount_amount' => 5,'tax_amount'=>10,'total' => 70];
        $products[1] = ['id' => '345','type' => 'labtops' ,'name' => 'macbook air','sku' => 'SA-789','image_url' => 'https://example.com/image.png','quantity' => 1,'unit_price'=>200,'discount_amount' => 50,'tax_amount'=>100,'total' => 300];
        $consumer    = ['first_name' => 'mohamed','last_name' => 'maree' ,'phone' => '01234567890','email' => 'm7mdmaree26@gmail.com'];
        $billing_address  = ['first_name' => 'mohamed','last_name' => 'maree','line1' => 'mehalla' ,'city' => 'mehalla','phone' => '01234567890'];
        $shipping_address = ['first_name' => 'mohamed','last_name' => 'maree','line1' => 'mehalla' ,'city' => 'mehalla','phone' => '01234567890'];
        $urls = ['success' => 'http://yoursite/success','failure' => 'http://yoursite/failure','cancel' => 'http://yoursite/cancel','notification' => 'http://yoursite/notification'];
        $response = (new Tamara())->createCheckoutSession($order,$products,$consumer,$billing_address,$shipping_address,$urls);
        return redirect()->to($response['checkout_url']);

```
- urls array contain the callback urls for each status ['success' ,'failure','cancel','notification'] so that create route for each status
- if you passed route('tamara.result') in urls array success key then reponse will return in 'PaymentController@tamaraResult' function
- in routes.php put
```php
    Route::get('tamara-response', 'PaymentController@tamaraResult')->name('tamara.result');

```
- in controllers/PaymentController create callback function to check response
```php

    public function tamaraResult(Request $request)
    {
        if ($request->paymentStatus == 'approved') {
        	//update order payment status 
            return view('success_payment');
        } else {
            return view('fail_payment');
        }
    }

```

## Get order details
```php
use Maree\Tamara\Tamara;

        //use id that you used in createCheckoutSession function $order['order_num']
        $response = (new Tamara())->getOrderDetails($orderId = '123');

```

## Cancel order
```php
use Maree\Tamara\Tamara;

        //get id from createCheckoutSession function response
        $order = ['id' => '9d7546e6-59e5-46ab-884c-9dbf95e2877c' ,'amount' => 100];
        $response = (new Tamara())->cancelOrder($order);

```

## documentaion
- https://api-reference.tamara.co

