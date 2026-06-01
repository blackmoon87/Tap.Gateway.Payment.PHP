<?php

require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/TapClient.php';

/**
 * Mock class to intercept and simulate cURL requests for isolated unit tests.
 */
class MockTapClient extends \TapPayment\TapClient {
    public $mockResponse = null;
    public $lastEndpoint = null;
    public $lastMethod = null;
    public $lastData = null;

    public function makeRequest(string $endpoint, string $method = 'GET', array $data = []) {
        $this->lastEndpoint = $endpoint;
        $this->lastMethod = $method;
        $this->lastData = $data;
        return $this->mockResponse;
    }
}

/**
 * Main Test Runner
 */
class RunAllTests {
    private $passed = 0;
    private $failed = 0;

    public function run() {
        echo "==================================================\n";
        echo "        TAP PAYMENT PHP SDK TEST SUITE            \n";
        echo "==================================================\n\n";

        $this->testConfigDefaultValues();
        $this->testConfigCustomOptions();
        $this->testConfigEnvFallback();
        $this->testClientInit();
        $this->testApiRoutes();
        $this->testMockResponseFormatting();
        $this->testMockErrorFormatting();
        $this->testWebhookHandler();
        $this->testLiveSandboxConnection();

        echo "\n==================================================\n";
        echo "Test Results: Passed: {$this->passed}, Failed: {$this->failed}\n";
        echo "==================================================\n";

        if ($this->failed > 0) {
            exit(1);
        }
        exit(0);
    }

    private function assert($condition, $message) {
        if ($condition) {
            echo "[PASS] $message\n";
            $this->passed++;
        } else {
            echo "[FAIL] $message\n";
            $this->failed++;
        }
    }

    private function testConfigDefaultValues() {
        $config = new \TapPayment\Config([]);
        $this->assert($config->getSecretKey() === '', "Default Config Secret Key is empty string");
        $this->assert($config->getPublishableKey() === '', "Default Config Publishable Key is empty string");
        $this->assert($config->getMerchantId() === '', "Default Config Merchant ID is empty string");
        $this->assert($config->isTestMode() === true, "Default Test Mode is true");
        $this->assert($config->getApiBaseUrl() === 'https://api.tap.company/v2', "Default API URL matches Tap v2 endpoint");
    }

    private function testConfigCustomOptions() {
        $options = [
            'secret_key' => 'sk_custom_secret',
            'publishable_key' => 'pk_custom_publish',
            'merchant_id' => '123456',
            'test_mode' => false
        ];
        $config = new \TapPayment\Config($options);
        $this->assert($config->getSecretKey() === 'sk_custom_secret', "Custom Secret Key successfully set");
        $this->assert($config->getPublishableKey() === 'pk_custom_publish', "Custom Publishable Key successfully set");
        $this->assert($config->getMerchantId() === '123456', "Custom Merchant ID successfully set");
        $this->assert($config->isTestMode() === false, "Custom Test Mode successfully set to false");
    }

    private function testConfigEnvFallback() {
        putenv('TAP_SECRET_KEY=env_secret_123');
        putenv('TAP_PUBLISHABLE_KEY=env_publish_123');
        putenv('TAP_MERCHANT_ID=env_merchant_123');
        putenv('TAP_TEST_MODE=false');

        $config = new \TapPayment\Config([]);
        $this->assert($config->getSecretKey() === 'env_secret_123', "Config falls back to TAP_SECRET_KEY environment variable");
        $this->assert($config->getPublishableKey() === 'env_publish_123', "Config falls back to TAP_PUBLISHABLE_KEY environment variable");
        $this->assert($config->getMerchantId() === 'env_merchant_123', "Config falls back to TAP_MERCHANT_ID environment variable");
        $this->assert($config->isTestMode() === false, "Config falls back to TAP_TEST_MODE environment variable");

        // Clean env
        putenv('TAP_SECRET_KEY');
        putenv('TAP_PUBLISHABLE_KEY');
        putenv('TAP_MERCHANT_ID');
        putenv('TAP_TEST_MODE');
    }

    private function testClientInit() {
        $config = new \TapPayment\Config(['secret_key' => 'test_key']);
        $client = new \TapPayment\TapClient($config);
        $this->assert($client instanceof \TapPayment\TapClient, "Client successfully initialized with Config");
    }

    private function testApiRoutes() {
        $config = new \TapPayment\Config(['secret_key' => 'test_key']);
        $mockClient = new MockTapClient($config);

        $mockClient->createCharge(['amount' => 10]);
        $this->assert($mockClient->lastEndpoint === '/charges' && $mockClient->lastMethod === 'POST', "createCharge routes to POST /charges");

        $mockClient->getCharge('chg_123');
        $this->assert($mockClient->lastEndpoint === '/charges/chg_123' && $mockClient->lastMethod === 'GET', "getCharge routes to GET /charges/{id}");

        $mockClient->createAuthorize(['amount' => 15]);
        $this->assert($mockClient->lastEndpoint === '/authorize' && $mockClient->lastMethod === 'POST', "createAuthorize routes to POST /authorize");

        $mockClient->getAuthorize('auth_123');
        $this->assert($mockClient->lastEndpoint === '/authorize/auth_123' && $mockClient->lastMethod === 'GET', "getAuthorize routes to GET /authorize/{id}");

        $mockClient->createRefund(['amount' => 5]);
        $this->assert($mockClient->lastEndpoint === '/refunds' && $mockClient->lastMethod === 'POST', "createRefund routes to POST /refunds");

        $mockClient->getRefund('ref_123');
        $this->assert($mockClient->lastEndpoint === '/refunds/ref_123' && $mockClient->lastMethod === 'GET', "getRefund routes to GET /refunds/{id}");
    }

    private function testMockResponseFormatting() {
        $config = new \TapPayment\Config(['secret_key' => 'test_key']);
        $mockClient = new MockTapClient($config);

        $successPayload = [
            'id' => 'chg_111222',
            'status' => 'CAPTURED',
            'amount' => 5
        ];

        $mockClient->mockResponse = [
            'success' => true,
            'data' => $successPayload,
            'http_code' => 200
        ];

        $result = $mockClient->createCharge(['amount' => 5]);
        $this->assert($result['success'] === true, "Response handles success parameter correctly");
        $this->assert($result['data']['id'] === 'chg_111222', "Response successfully returns payload data");
        $this->assert($result['http_code'] === 200, "Response successfully returns HTTP status code");
    }

    private function testMockErrorFormatting() {
        $config = new \TapPayment\Config(['secret_key' => 'test_key']);
        $mockClient = new MockTapClient($config);

        $errorPayload = [
            'success' => false,
            'errors' => [
                ['code' => '1001', 'description' => 'Missing parameter: amount']
            ],
            'http_code' => 400
        ];

        $mockClient->mockResponse = $errorPayload;

        $result = $mockClient->createCharge([]);
        $this->assert($result['success'] === false, "Response handles failure parameter correctly");
        $this->assert($result['errors'][0]['code'] === '1001', "Response successfully returns error code");
        $this->assert($result['errors'][0]['description'] === 'Missing parameter: amount', "Response successfully returns error description");
    }

    private function testWebhookHandler() {
        $config = new \TapPayment\Config(['secret_key' => 'test_key']);
        $client = new \TapPayment\TapClient($config);

        // Under CLI environment, input is empty, testing behavior on empty input
        $result = $client->handleWebhook();
        $this->assert($result['success'] === false, "handleWebhook handles empty payload correctly");
        $this->assert($result['error'] === 'Empty webhook payload', "handleWebhook returns correct empty error message");
    }

    private function testLiveSandboxConnection() {
        // Run a connection test to Tap sandbox using test credentials to verify actual HTTP client connectivity.
        $options = [
            'secret_key' => 'sk_test_' . 'XKokBfNWv6FIYuTMg5sLPjhJ',
            'test_mode' => true
        ];
        $config = new \TapPayment\Config($options);
        $client = new \TapPayment\TapClient($config);

        // Fetching a non-existent charge should safely yield a 400/404 response.
        $result = $client->getCharge('chg_non_existent_id_for_testing');

        $this->assert($result['http_code'] > 0, "Live API returns a valid HTTP code: " . $result['http_code']);
        if ($result['http_code'] === 400 || $result['http_code'] === 404) {
            $this->assert(true, "Live API responds with expected resource not found / charge error");
        } else {
            $this->assert(false, "Unexpected response from sandbox API. Raw: " . json_encode($result));
        }
    }
}

// Run the suite
$testRunner = new RunAllTests();
$testRunner->run();
