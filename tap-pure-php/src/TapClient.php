<?php

namespace TapPayment;

/**
 * Class TapClient
 * Interacts with Tap Payments API using cURL.
 */
class TapClient {
    private $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * Sends HTTP requests to Tap API.
     */
    public function makeRequest(string $endpoint, string $method = 'GET', array $data = []) {
        $url = rtrim($this->config->getApiBaseUrl(), '/') . '/' . ltrim($endpoint, '/');
        
        $curl = curl_init();
        $headers = [
            'authorization: Bearer ' . $this->config->getSecretKey(),
            'content-type: application/json',
            'accept: application/json'
        ];

        $curlOptions = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if (in_array(strtoupper($method), ['POST', 'PUT'])) {
            $curlOptions[CURLOPT_POSTFIELDS] = json_encode($data);
        }

        curl_setopt_array($curl, $curlOptions);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            return [
                'success' => false,
                'error' => 'cURL Error: ' . $err,
                'http_code' => $httpCode
            ];
        }

        $decoded = json_decode($response, true);

        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $decoded,
                'http_code' => $httpCode
            ];
        }

        // Return error details from API response
        return [
            'success' => false,
            'errors' => $decoded['errors'] ?? [['code' => 'HTTP_ERROR', 'description' => $response]],
            'http_code' => $httpCode
        ];
    }

    /**
     * Create a Charge transaction.
     */
    public function createCharge(array $params) {
        return $this->makeRequest('/charges', 'POST', $params);
    }

    /**
     * Retrieve details of a Charge.
     */
    public function getCharge(string $chargeId) {
        return $this->makeRequest('/charges/' . $chargeId, 'GET');
    }

    /**
     * Create an Authorize transaction.
     */
    public function createAuthorize(array $params) {
        return $this->makeRequest('/authorize', 'POST', $params);
    }

    /**
     * Retrieve details of an Authorization.
     */
    public function getAuthorize(string $authId) {
        return $this->makeRequest('/authorize/' . $authId, 'GET');
    }

    /**
     * Create a Refund.
     */
    public function createRefund(array $params) {
        return $this->makeRequest('/refunds', 'POST', $params);
    }

    /**
     * Retrieve details of a Refund.
     */
    public function getRefund(string $refundId) {
        return $this->makeRequest('/refunds/' . $refundId, 'GET');
    }

    /**
     * Processes incoming webhook payloads from Tap Payments.
     */
    public function handleWebhook() {
        $payload = file_get_contents('php://input');
        if (empty($payload)) {
            return [
                'success' => false,
                'error' => 'Empty webhook payload'
            ];
        }

        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'success' => false,
                'error' => 'Invalid JSON payload'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }
}
