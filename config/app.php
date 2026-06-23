<?php
require_once __DIR__ . '/env.php';

define('APP_NAME', env('APP_NAME', 'SHOEBOX'));
define('APP_ENV', env('APP_ENV', 'local'));
define('APP_DEBUG', filter_var(env('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN));
define('APP_URL', env('APP_URL', '/shoebox2'));
define('SESSION_SECURE', filter_var(env('SESSION_SECURE', 'false'), FILTER_VALIDATE_BOOLEAN));
define('FREE_SHIPPING_THRESHOLD', (int) env('FREE_SHIPPING_THRESHOLD', 15000));
define('SHIPPING_FEE', (int) env('SHIPPING_FEE', 350));
define('CURRENCY', env('CURRENCY', '₹'));

define('RAZORPAY_KEY_ID', env('RAZORPAY_KEY_ID', ''));
define('RAZORPAY_KEY_SECRET', env('RAZORPAY_KEY_SECRET', ''));
define('RAZORPAY_WEBHOOK_SECRET', env('RAZORPAY_WEBHOOK_SECRET', ''));
define('RAZORPAY_UPI_VPA', env('RAZORPAY_UPI_VPA', 'shoebox@razorpay'));
define('RAZORPAY_MOCK_MODE', filter_var(env('RAZORPAY_MOCK_MODE', 'true'), FILTER_VALIDATE_BOOLEAN));

define('DISTRICTS', [
    'Kochi (Ernakulam)',
    'Kozhikode',
    'Thrissur',
    'Thiruvananthapuram',
    'Kottayam',
    'Malappuram',
    'Kollam',
    'Palakkad',
    'Alappuzha',
    'Kannur',
]);
