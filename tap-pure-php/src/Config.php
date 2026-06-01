<?php

namespace TapPayment;

/**
 * Class Config
 * Handles dynamic configuration settings for the Tap Payment Client.
 */
class Config {
    private $secretKey;
    private $publishableKey;
    private $merchantId;
    private $isTestMode;
    private $apiBaseUrl = 'https://api.tap.company/v2';

    /**
     * Config constructor.
     * Looks at options array or falls back to environment variables.
     */
    public function __construct(array $options = []) {
        $this->secretKey = $options['secret_key'] ?? getenv('TAP_SECRET_KEY') ?: '';
        $this->publishableKey = $options['publishable_key'] ?? getenv('TAP_PUBLISHABLE_KEY') ?: '';
        $this->merchantId = $options['merchant_id'] ?? getenv('TAP_MERCHANT_ID') ?: '';
        $this->isTestMode = isset($options['test_mode']) ? (bool)$options['test_mode'] : (getenv('TAP_TEST_MODE') !== 'false');
    }

    public function getSecretKey() {
        return $this->secretKey;
    }

    public function getPublishableKey() {
        return $this->publishableKey;
    }

    public function getMerchantId() {
        return $this->merchantId;
    }

    public function isTestMode() {
        return $this->isTestMode;
    }

    public function getApiBaseUrl() {
        return $this->apiBaseUrl;
    }
}
