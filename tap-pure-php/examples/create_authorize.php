<?php

require_once __DIR__ . '/config.php';

$authResult = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
    $currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'KWD';
    $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'John';
    $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Doe';
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: 'customer@example.com';
    $phoneCode = filter_input(INPUT_POST, 'phone_code', FILTER_SANITIZE_SPECIAL_CHARS) ?: '965';
    $phoneNumber = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_SPECIAL_CHARS) ?: '12345678';
    
    $authPayload = [
        "amount" => $amount,
        "currency" => $currency,
        "threeDsecure" => true,
        "save_card" => false,
        "description" => "Custom PHP Authorization",
        "statement_descriptor" => "Pure PHP SDK",
        "reference" => [
            "transaction" => "txn_" . time(),
            "order" => "order_" . time()
        ],
        "customer" => [
            "first_name" => $firstName,
            "last_name" => $lastName,
            "email" => $email,
            "phone" => [
                "country_code" => $phoneCode,
                "number" => $phoneNumber
            ]
        ],
        "source" => [
            "id" => "src_all"
        ],
        "redirect" => [
            "url" => REDIRECT_URL
        ],
        "post" => [
            "url" => POST_URL
        ]
    ];

    $authResult = $tapClient->createAuthorize($authPayload);

    if ($authResult['success'] && isset($authResult['data']['transaction']['url'])) {
        header("Location: " . $authResult['data']['transaction']['url']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Tap Authorization (Hold)</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; background-color: #f7f9fc; padding: 40px; color: #333; }
        .container { max-width: 600px; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin: 0 auto; }
        h2 { margin-top: 0; color: #007bff; }
        label { display: block; margin-top: 15px; font-weight: bold; margin-bottom: 5px; }
        input, select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px; box-sizing: border-box; }
        button { background-color: #007bff; color: white; padding: 12px 20px; border: none; border-radius: 6px; font-size: 16px; margin-top: 20px; cursor: pointer; width: 100%; transition: background-color 0.2s; }
        button:hover { background-color: #0056b3; }
        .error { background: #ffebeb; border-left: 4px solid #ff4d4d; color: #d00; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Authorization (حجز مبلغ تفويض)</h2>

    <?php if ($authResult && !$authResult['success']): ?>
        <div class="error">
            <strong>Error creating authorization hold:</strong>
            <ul>
                <?php foreach ($authResult['errors'] as $error): ?>
                    <li>[Code <?php echo htmlspecialchars($error['code']); ?>] <?php echo htmlspecialchars($error['description']); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label for="amount">Amount (المبلغ):</label>
        <input type="number" step="0.001" id="amount" name="amount" value="10.000" required>

        <label for="currency">Currency (العملة):</label>
        <select id="currency" name="currency">
            <option value="KWD">KWD (Kuwaiti Dinar)</option>
            <option value="SAR">SAR (Saudi Riyal)</option>
            <option value="BHD">BHD (Bahraini Dinar)</option>
            <option value="AED">AED (UAE Dirham)</option>
            <option value="QAR">QAR (Qatari Riyal)</option>
            <option value="USD">USD (US Dollar)</option>
        </select>

        <label for="first_name">First Name (الاسم الأول):</label>
        <input type="text" id="first_name" name="first_name" value="Jane">

        <label for="last_name">Last Name (الاسم الأخير):</label>
        <input type="text" id="last_name" name="last_name" value="Doe">

        <label for="email">Email (البريد الإلكتروني):</label>
        <input type="email" id="email" name="email" value="customer@example.com">

        <label for="phone_code">Country Code (رمز الدولة):</label>
        <input type="text" id="phone_code" name="phone_code" value="966">

        <label for="phone_number">Phone Number (رقم الهاتف):</label>
        <input type="text" id="phone_number" name="phone_number" value="548220713">

        <button type="submit">Authorize Card (تفويض حجز مبلغ)</button>
    </form>
</div>

</body>
</html>
