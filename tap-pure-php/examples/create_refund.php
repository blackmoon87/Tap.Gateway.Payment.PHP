<?php

require_once __DIR__ . '/config.php';

$refundResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $chargeId = filter_input(INPUT_POST, 'charge_id', FILTER_SANITIZE_SPECIAL_CHARS);
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'KWD';
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Customer Request';

    $refundPayload = [
        "charge_id" => $chargeId,
        "amount" => $amount,
        "currency" => $currency,
        "description" => "Custom PHP Refund",
        "reason" => $reason,
        "reference" => [
            "merchant" => "ref_" . time()
        ],
        "metadata" => [
            "reason_code" => "RET"
        ],
        "post" => [
            "url" => POST_URL
        ]
    ];

    $refundResult = $tapClient->createRefund($refundPayload);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tap Refund (Pure PHP)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f7f9fc; padding: 40px; color: #333; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin: 0 auto; }
        h2 { margin-top: 0; color: #dc3545; }
        label { display: block; margin-top: 15px; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { background-color: #dc3545; color: white; padding: 12px 20px; border: none; border-radius: 6px; font-size: 16px; margin-top: 20px; cursor: pointer; width: 100%; transition: background-color 0.2s; }
        button:hover { background-color: #bd2130; }
        .error { background: #ffebeb; border-left: 4px solid #ff4d4d; color: #d00; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .success { background: #ebffeb; border-left: 4px solid #28a745; color: #1e7e34; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        pre { background: #eee; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Refund (طلب استرجاع)</h2>

    <?php if ($refundResult): ?>
        <?php if ($refundResult['success']): ?>
            <div class="success">
                <strong>Refund Successful! (تم الاسترجاع بنجاح)</strong>
                <p>Refund ID: <?php echo htmlspecialchars($refundResult['data']['id']); ?></p>
                <p>Status: <?php echo htmlspecialchars($refundResult['data']['status']); ?></p>
                <pre><?php echo htmlspecialchars(json_encode($refundResult['data'], JSON_PRETTY_PRINT)); ?></pre>
            </div>
        <?php else: ?>
            <div class="error">
                <strong>Error creating refund:</strong>
                <ul>
                    <?php foreach ($refundResult['errors'] as $error): ?>
                        <li>[Code <?php echo htmlspecialchars($error['code']); ?>] <?php echo htmlspecialchars($error['description']); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="POST">
        <label for="charge_id">Charge ID (رقم عملية الدفع المراد استرجاعها):</label>
        <input type="text" id="charge_id" name="charge_id" placeholder="chg_xxxxxxxxx" required>

        <label for="amount">Refund Amount (المبلغ المسترجع):</label>
        <input type="number" step="0.001" id="amount" name="amount" value="5.000" required>

        <label for="currency">Currency (العملة):</label>
        <select id="currency" name="currency">
            <option value="KWD">KWD (Kuwaiti Dinar)</option>
            <option value="SAR">SAR (Saudi Riyal)</option>
            <option value="BHD">BHD (Bahraini Dinar)</option>
            <option value="AED">AED (UAE Dirham)</option>
            <option value="QAR">QAR (Qatari Riyal)</option>
            <option value="USD">USD (US Dollar)</option>
        </select>

        <label for="reason">Reason (السبب):</label>
        <input type="text" id="reason" name="reason" value="Customer Return (إرجاع من العميل)">

        <button type="submit">Issue Refund (طلب استرجاع)</button>
    </form>
</div>

</body>
</html>
