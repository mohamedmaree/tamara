<?php
namespace Maree\Tamara;


use Exception;
use Illuminate\Support\Facades\Http;

class Tamara
{
    /**
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $token;

    /**
     * @var string
     */
    private string $countryCode;

    /**
     * @var string
     */
    private string $currency;


    public function __construct()
    {
        $this->url         = (config('tamara.mode') == 'live')? config('tamara.live_url') : config('tamara.test_url');
        $this->token       = config('tamara.token');
        $this->countryCode = config('tamara.country_code');
        $this->currency    = config('tamara.currency');
    }

    public function createCheckoutSession($order = [],$products = [],$consumer = [],$billing_address= [],$shipping_address=[],$urls =[])
    {
        $this->url .= "/checkout";

        $data = [
            'order_reference_id' => $order['order_num',
            'order_number'       => $order['order_num'],
            'total_amount'       =>
                [
                    'amount'   => $order['total'],
                    'currency' => $this->currency,
                ],
            'description'        => $order['notes'],
            'country_code'       => $this->countryCode,
            'payment_type'       => 'PAY_BY_INSTALMENTS',
            'instalments'        => NULL,
            'locale'             => 'en_US',
            'items'              => $this->getOrderItems($products),
            'consumer'           =>
                [
                    'first_name'   => $consumer['first_name'],
                    'last_name'    => $consumer['last_name'],
                    'phone_number' => $consumer['phone'],
                    'email'        => $consumer['email'],
                ],
            'billing_address'    =>
                [
                    'first_name'   => $billing_address['first_name'],
                    'last_name'    => $billing_address['last_name'],
                    'line1'        => $billing_address['line1'],
                    'city'         => $billing_address['city'],
                    'country_code' => $this->countryCode,
                    'phone_number' => $billing_address['phone'],
                ],
            'shipping_address'   =>
                [
                    'first_name'   => $shipping_address['first_name'],
                    'last_name'    => $shipping_address['last_name'],
                    'line1'        => $shipping_address['line1'],
                    'city'         => $shipping_address['city'],
                    'country_code' => $this->countryCode,
                    'phone_number' => $shipping_address['phone'],
                ],
            'discount'           =>
                [
                    'name'   => $order['discount_name']?? "",
                    'amount' =>
                        [
                            'amount'   => $order['discount_amount'] ?? 0,
                            'currency' => $this->currency,
                        ],
                ],
            'tax_amount'         =>
                [
                    'amount'   => $order['vat_amount'],
                    'currency' => $this->currency,
                ],
            'shipping_amount'    =>
                [
                    'amount'   => $order['shipping_amount'],
                    'currency' => $this->currency,
                ],
            'merchant_url'       =>
                [
                    'success'      => $urls['success'],
                    'failure'      => $urls['failure'],
                    'cancel'       => $urls['cancel'],
                    'notification' => $urls['notification'],
                ]
        ];

        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->post($this->url, $data);

        $responseResult = json_decode($response->getBody()->getContents(), true);

        if (isset($responseResult['errors'])) {
            throw new Exception($responseResult['message']);
        }

        return $responseResult;
    }

    public function checkPaymentOptionsAvailability($order)
    {
        $this->url .= "/checkout/payment-options-pre-check";

        $data = [
            'country'      => $this->countryCode,
            'phone_number' => $order['phone'],
            'order_value'  => [
                'amount'   => $order['total'],
                'currency' => $this->currency
            ]
        ];

        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->post($this->url, $data);

        $responseResult = json_decode($response->getBody()->getContents(), true);

        if (isset($responseResult['errors'])) {
            throw new Exception($responseResult['message']);
        }

        if ($responseResult['has_available_payment_options'] == false) {
            throw new Exception("Can't split this order Value to Installments");
        }

        return true;

    }

    public function getOrderDetails($orderId)
    {
        $this->url .= "/merchants/orders/reference-id/4" . $orderId;

        $response = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->get($this->url);

        $responseResult = json_decode($response->getBody()->getContents(), true);

        $responseResult = json_decode($responseResult, true);

        if (isset($responseResult['errors'])) {
            throw new Exception($responseResult['message']);
        }

        return $responseResult;
    }

    public function cancelOrder($order)
    {
        $this->url .= "/orders/{$orderId}/cancel";
        $data      = [
            'orderId'      => $order['id'],
            'total_amount' => [
                'amount'   => $order['amount'],
                'currency' => $this->currency,
            ]
        ];
        $response  = Http::withHeaders([
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $this->token
        ])->post($this->url, $data);

        $responseResult = json_decode($response->getBody()->getContents(), true);

        if (isset($responseResult['errors'])) {
            throw new Exception($responseResult['message']);
        }
    }

    private function getOrderItems($products = [])
    {
        $items = [];
        foreach ($products as $product) {
            $items[] = [
                'reference_id'    => $product['id'],
                'type'            => $product['type'],
                'name'            => $product['name'],
                'sku'             => $product['sku'],
                'image_url'       => $product['image_url'],
                'quantity'        => $product['quantity'],
                'unit_price'      => [
                    'amount'   => $product['unit_price'],
                    'currency' => $this->currency,
                ],
                'discount_amount' => [
                    'amount'   => $product['discount_amount'],
                    'currency' => $this->currency,
                ],
                'tax_amount'      => [
                    'amount'   => $product['tax_amount'],
                    'currency' => $this->currency,
                ],
                'total_amount'    => [
                    'amount'   => $product['total'],
                    'currency' => $this->currency,
                ],
            ];
        }
        return $items;
    }
}
