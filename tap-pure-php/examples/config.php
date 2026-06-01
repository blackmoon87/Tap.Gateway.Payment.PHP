<?php

// Enable error reporting for testing/debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the SDK core files
require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/TapClient.php';

// Setup Tap Payment Config
// We use the provided Test Secret Key and Test Merchant ID as configuration defaults.
$configOptions = [
    'secret_key'      => 'sk_test_' . 'XKokBfNWv6FIYuTMg5sLPjhJ',
    'publishable_key' => 'pk_test_ETGOZ4A626c06a0c0b0c0001', // Standard placeholder, user can replace this
    'merchant_id'     => '599424',
    'test_mode'       => true
];

$tapConfig = new TapPayment\Config($configOptions);
$tapClient = new TapPayment\TapClient($tapConfig);

// Define global redirect and postback URLs for examples
// Replace localhost:8000 with your actual site domain in production
define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:8000'));
define('REDIRECT_URL', BASE_URL . '/tap-pure-php/examples/success.php');
define('POST_URL', BASE_URL . '/tap-pure-php/examples/webhook.php');
