<?php

require_once __DIR__ . '/config.php';

// Capture and process webhook
$result = $tapClient->handleWebhook();

if ($result['success']) {
    $webhookData = $result['data'];
    
    // Extract key details
    $chargeId = $webhookData['id'] ?? 'unknown';
    $status = $webhookData['status'] ?? 'unknown';
    $orderRef = $webhookData['reference']['order'] ?? 'unknown';
    $amount = $webhookData['amount'] ?? 0;
    $currency = $webhookData['currency'] ?? '';

    // Log the webhook request locally for testing and debugging purposes
    $logEntry = sprintf(
        "[%s] Webhook received - ChargeID: %s, OrderRef: %s, Status: %s, Amount: %f %s\nPayload: %s\n\n",
        date('Y-m-d H:i:s'),
        $chargeId,
        $orderRef,
        $status,
        $amount,
        $currency,
        json_encode($webhookData)
    );

    file_put_contents(__DIR__ . '/webhook_log.txt', $logEntry, FILE_APPEND);

    // Business Logic transition for pure PHP env:
    // Here you would run database queries to update your order status based on $status:
    // Example:
    // if ($status === 'CAPTURED') {
    //     // Database: UPDATE orders SET payment_status = 'paid', transaction_id = :chargeId WHERE id = :orderRef
    // } elseif ($status === 'DECLINED') {
    //     // Database: UPDATE orders SET payment_status = 'failed' WHERE id = :orderRef
    // }

    // Respond with 200 OK to Tap gateway
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Webhook logged"]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => $result['error']]);
}
