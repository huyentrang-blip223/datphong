<?php

return [
    'resend' => ['key' => env('RESEND_API_KEY')],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'python_data' => [
        'url' => env('PY_SERVICE_URL', 'http://127.0.0.1:8001'),
        'token' => env('PY_SERVICE_TOKEN', ''),
        'timeout' => env('PY_SERVICE_TIMEOUT', 3),
    ],

    'vnpay' => [
        'url' => env('VNPAY_PAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
        'tmn_code' => env('VNPAY_TMN_CODE', ''),
        'secret_key' => env('VNPAY_SECRET_KEY', ''),
        'return_url' => env('VNPAY_RETURN_URL', env('APP_URL').'/api/v1/payment/vn-pay-callback'),
        'version' => env('VNPAY_VERSION', '2.1.0'),
        'command' => env('VNPAY_COMMAND', 'pay'),
        'order_type' => env('VNPAY_ORDER_TYPE', 'other'),
    ],

];
