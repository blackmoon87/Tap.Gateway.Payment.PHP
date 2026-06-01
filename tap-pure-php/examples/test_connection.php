<?php

require_once __DIR__ . '/config.php';

echo "=== Tap Payments API Connection Test ===\n";
echo "Testing connectivity using Secret Key: " . substr($tapConfig->getSecretKey(), 0, 8) . "...\n";

// Attempt to retrieve a non-existent charge to verify credentials and connectivity
// Tap API should return HTTP 404 (Charge not found) or similar, proving our credentials reached their server.
$result = $tapClient->getCharge('chg_non_existent_id_for_testing_connection');

echo "HTTP Code: " . $result['http_code'] . "\n";
echo "Success Status: " . ($result['success'] ? 'YES' : 'NO') . "\n";

if ($result['success']) {
    echo "Unexpected Success! Data returned:\n";
    print_r($result['data']);
} else {
    echo "API responded with expected error details:\n";
    if (isset($result['errors'])) {
        foreach ($result['errors'] as $error) {
            echo " - Code: " . $error['code'] . "\n";
            echo " - Description: " . $error['description'] . "\n";
        }
    } else {
        echo " - Error message: " . ($result['error'] ?? 'Unknown network error') . "\n";
    }
}
