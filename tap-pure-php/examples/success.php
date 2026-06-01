<?php

require_once __DIR__ . '/config.php';

$tapId = filter_input(INPUT_GET, 'tap_id', FILTER_SANITIZE_SPECIAL_CHARS);
$transactionDetails = null;
$error = null;

if ($tapId) {
    // Check if it is a charge or an authorize transaction
    if (strpos($tapId, 'auth_') === 0) {
        $result = $tapClient->getAuthorize($tapId);
    } else {
        $result = $tapClient->getCharge($tapId);
    }

    if ($result['success']) {
        $transactionDetails = $result['data'];
    } else {
        $error = $result['errors'][0]['description'] ?? 'Failed to retrieve transaction details.';
    }
} else {
    $error = 'Missing tap_id parameter.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success (نجاح الدفع)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f7f9fc; padding: 40px; color: #333; text-align: center; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin: 0 auto; text-align: left; }
        h2 { color: #28a745; margin-top: 0; text-align: center; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-table th, .details-table td { padding: 12px; border-bottom: 1px solid #eee; }
        .details-table th { text-align: left; font-weight: bold; color: #555; }
        .back-btn { display: inline-block; background-color: #4b3ec4; color: white; padding: 12px 25px; border: none; border-radius: 6px; text-decoration: none; margin-top: 30px; width: calc(100% - 50px); text-align: center; }
        .error { color: #d00; background: #ffebeb; padding: 15px; border-radius: 6px; text-align: center; }
    </style>
</head>
<body>

<div class="container">
    <h2>Payment Successful (تم الدفع بنجاح)</h2>

    <?php if ($error): ?>
        <div class="error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php elseif ($transactionDetails): ?>
        <p style="text-align: center; color: #777;">Thank you for your payment! Here are your transaction details:</p>
        
        <table class="details-table">
            <tr>
                <th>Transaction ID</th>
                <td><?php echo htmlspecialchars($transactionDetails['id']); ?></td>
            </tr>
            <tr>
                <th>Status (الحالة)</th>
                <td style="color: #28a745; font-weight: bold;"><?php echo htmlspecialchars($transactionDetails['status']); ?></td>
            </tr>
            <tr>
                <th>Amount (المبلغ)</th>
                <td><?php echo htmlspecialchars($transactionDetails['amount'] . ' ' . $transactionDetails['currency']); ?></td>
            </tr>
            <tr>
                <th>Reference Order</th>
                <td><?php echo htmlspecialchars($transactionDetails['reference']['order'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Customer Name</th>
                <td><?php echo htmlspecialchars(($transactionDetails['customer']['first_name'] ?? '') . ' ' . ($transactionDetails['customer']['last_name'] ?? '')); ?></td>
            </tr>
            <tr>
                <th>Customer Email</th>
                <td><?php echo htmlspecialchars($transactionDetails['customer']['email'] ?? 'N/A'); ?></td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td><?php echo htmlspecialchars($transactionDetails['source']['payment_method'] ?? 'Card'); ?></td>
            </tr>
            <tr>
                <th>Card Brand</th>
                <td><?php echo htmlspecialchars($transactionDetails['card']['brand'] ?? 'N/A'); ?></td>
            </tr>
        </table>
    <?php endif; ?>

    <a href="index.php" class="back-btn">Back to Dashboard (العودة إلى لوحة التحكم)</a>
</div>

</body>
</html>
