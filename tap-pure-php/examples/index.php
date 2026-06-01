<?php

require_once __DIR__ . '/config.php';

// Route AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    // Retrieve credentials from POST inputs or fallback to config defaults
    $secretKey = filter_input(INPUT_POST, 'secret_key', FILTER_SANITIZE_SPECIAL_CHARS) ?: $tapConfig->getSecretKey();
    $publishableKey = filter_input(INPUT_POST, 'publishable_key', FILTER_SANITIZE_SPECIAL_CHARS) ?: $tapConfig->getPublishableKey();
    $merchantId = filter_input(INPUT_POST, 'merchant_id', FILTER_SANITIZE_SPECIAL_CHARS) ?: $tapConfig->getMerchantId();
    $testMode = filter_input(INPUT_POST, 'test_mode', FILTER_SANITIZE_SPECIAL_CHARS) === 'false' ? false : true;

    // Create a new client instance dynamically with the specified credentials
    $customConfig = new TapPayment\Config([
        'secret_key'      => $secretKey,
        'publishable_key' => $publishableKey,
        'merchant_id'     => $merchantId,
        'test_mode'       => $testMode
    ]);
    $customClient = new TapPayment\TapClient($customConfig);

    if ($action === 'test_connection') {
        $result = $customClient->getCharge('chg_non_existent_id_for_testing_connection');
        echo json_encode($result);
        exit;
    }

    if ($action === 'create_charge' || $action === 'create_authorize') {
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'KWD';
        $firstName = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'John';
        $lastName = filter_input(INPUT_POST, 'last_name', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Doe';
        $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL) ?: 'customer@example.com';
        $phoneCode = filter_input(INPUT_POST, 'phone_code', FILTER_SANITIZE_SPECIAL_CHARS) ?: '965';
        $phoneNumber = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_SPECIAL_CHARS) ?: '12345678';

        $payload = [
            "amount" => $amount,
            "currency" => $currency,
            "threeDsecure" => true,
            "save_card" => false,
            "description" => $action === 'create_charge' ? "Interactive Charge Test" : "Interactive Auth Hold Test",
            "statement_descriptor" => "Pure PHP SDK Dashboard",
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

        if ($action === 'create_charge') {
            $result = $customClient->createCharge($payload);
        } else {
            $result = $customClient->createAuthorize($payload);
        }
        echo json_encode($result);
        exit;
    }

    if ($action === 'create_refund') {
        $chargeId = filter_input(INPUT_POST, 'charge_id', FILTER_SANITIZE_SPECIAL_CHARS);
        $amount = filter_input(INPUT_POST, 'amount', FILTER_VALIDATE_FLOAT);
        $currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'KWD';
        $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Customer Request';

        $payload = [
            "charge_id" => $chargeId,
            "amount" => $amount,
            "currency" => $currency,
            "description" => "Interactive Refund Test",
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

        $result = $customClient->createRefund($payload);
        echo json_encode($result);
        exit;
    }

    if ($action === 'fetch_logs') {
        $logFile = __DIR__ . '/webhook_log.txt';
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            // Limit to last 2000 chars for performance
            if (strlen($logs) > 2000) {
                $logs = "..." . substr($logs, -2000);
            }
            echo json_encode(['success' => true, 'logs' => $logs]);
        } else {
            echo json_encode(['success' => true, 'logs' => 'No webhook logs found. Trigger a charge or post webhook payload to fill logs.']);
        }
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tap Payments PHP SDK Dashboard</title>
    <style>
        :root {
            --primary: #4b3ec4;
            --primary-hover: #3b2fb4;
            --success: #1e7e34;
            --error: #dc3545;
            --dark: #1e293b;
            --light-bg: #f8fafc;
            --border: #e2e8f0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--light-bg);
            margin: 0;
            padding: 0;
            color: #334155;
        }

        header {
            background: linear-gradient(135deg, #4b3ec4, #6366f1);
            color: white;
            padding: 24px 40px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        header p {
            margin: 8px 0 0 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .dashboard-container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 24px;
        }

        @media (max-width: 900px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }
        }

        .card {
            background: white;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card h2 {
            margin-top: 0;
            font-size: 18px;
            color: var(--dark);
            border-bottom: 2px solid var(--light-bg);
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: 600;
            font-size: 13px;
            color: #475569;
            margin-bottom: 4px;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
            color: #334155;
            transition: border-color 0.15s ease-in-out;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }

        .btn {
            background-color: var(--primary);
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.15s ease-in-out;
            margin-top: 16px;
        }

        .btn:hover {
            background-color: var(--primary-hover);
        }

        .btn-success {
            background-color: var(--success);
        }

        .btn-success:hover {
            background-color: #155724;
        }

        .btn-error {
            background-color: var(--error);
        }

        .btn-error:hover {
            background-color: #bd2130;
        }

        .btn-secondary {
            background-color: #64748b;
        }

        .btn-secondary:hover {
            background-color: #475569;
        }

        .result-panel {
            background-color: #0f172a;
            color: #38bdf8;
            padding: 16px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 13px;
            white-space: pre-wrap;
            word-break: break-all;
            max-height: 300px;
            overflow-y: auto;
            margin-top: 14px;
            border: 1px solid #1e293b;
        }

        .tab-buttons {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }

        .tab-btn {
            flex: 1;
            padding: 10px;
            background-color: #f1f5f9;
            border: 1px solid var(--border);
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            color: #64748b;
        }

        .tab-btn.active {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .row-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .log-console {
            background: #1e293b;
            color: #e2e8f0;
            padding: 16px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            height: 200px;
            overflow-y: auto;
            white-space: pre-wrap;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 700;
            border-radius: 4px;
            color: white;
        }

        .badge-test { background-color: #f59e0b; }
        .badge-live { background-color: #10b981; }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-top: 12px;
        }

        th, td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid var(--border);
        }

        th {
            background-color: var(--light-bg);
            color: #475569;
        }
    </style>
</head>
<body>

<header>
    <h1>Tap Payments PHP SDK Sandbox Dashboard</h1>
    <p>Interactively construct charges, authorizations, and refunds, and view live webhook payloads.</p>
</header>

<div class="dashboard-container">
    <!-- Configuration Column -->
    <div class="config-column">
        <div class="card">
            <h2>1. Credentials Options</h2>
            <form id="configForm">
                <label for="secret_key">Secret Key:</label>
                <input type="text" id="secret_key" name="secret_key" value="<?php echo htmlspecialchars($tapConfig->getSecretKey()); ?>">

                <label for="publishable_key">Publishable Key:</label>
                <input type="text" id="publishable_key" name="publishable_key" value="<?php echo htmlspecialchars($tapConfig->getPublishableKey()); ?>">

                <label for="merchant_id">Merchant ID:</label>
                <input type="text" id="merchant_id" name="merchant_id" value="<?php echo htmlspecialchars($tapConfig->getMerchantId()); ?>">

                <label for="test_mode">Environment Mode:</label>
                <select id="test_mode" name="test_mode">
                    <option value="true" <?php echo $tapConfig->isTestMode() ? 'selected' : ''; ?>>Sandbox (Test Mode)</option>
                    <option value="false" <?php echo !$tapConfig->isTestMode() ? 'selected' : ''; ?>>Production (Live Mode)</option>
                </select>

                <button type="button" class="btn btn-secondary" onclick="testConnection()">Test Connection</button>
            </form>
            <div id="connectionResult" class="result-panel" style="display:none;"></div>
        </div>

        <div class="card">
            <h2>2. Sandbox Cards Reference</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Method/Brand</th>
                            <th>Card Number</th>
                            <th>Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>KNET (Captured)</td>
                            <td><code>8888880000000001</code></td>
                            <td>09/30</td>
                        </tr>
                        <tr>
                            <td>Benefit (Captured)</td>
                            <td><code>4600410123456789</code></td>
                            <td>12/27</td>
                        </tr>
                        <tr>
                            <td>MasterCard (Approved)</td>
                            <td><code>5123450000000008</code></td>
                            <td>01/39</td>
                        </tr>
                        <tr>
                            <td>VISA (Declined)</td>
                            <td><code>4508750015741019</code></td>
                            <td>05/22</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Operations Column -->
    <div class="operations-column">
        <div class="card">
            <div class="tab-buttons">
                <button class="tab-btn active" onclick="switchTab('charge-tab', this)">Create Charge / Auth</button>
                <button class="tab-btn" onclick="switchTab('refund-tab', this)">Issue Refund</button>
            </div>

            <!-- Charge / Auth Tab -->
            <div id="charge-tab" class="tab-content">
                <h2>Create Charge or Hold Authorization</h2>
                <form id="chargeForm">
                    <div class="row-grid">
                        <div>
                            <label for="amount">Amount:</label>
                            <input type="number" step="0.001" id="amount" name="amount" value="5.000" required>
                        </div>
                        <div>
                            <label for="currency">Currency:</label>
                            <select id="currency" name="currency">
                                <option value="KWD">KWD</option>
                                <option value="SAR">SAR</option>
                                <option value="BHD">BHD</option>
                                <option value="AED">AED</option>
                                <option value="QAR">QAR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <div class="row-grid">
                        <div>
                            <label for="first_name">First Name:</label>
                            <input type="text" id="first_name" name="first_name" value="John">
                        </div>
                        <div>
                            <label for="last_name">Last Name:</label>
                            <input type="text" id="last_name" name="last_name" value="Doe">
                        </div>
                    </div>

                    <div class="row-grid">
                        <div>
                            <label for="email">Email Address:</label>
                            <input type="email" id="email" name="email" value="customer@example.com">
                        </div>
                        <div>
                            <label for="phone_number">Phone:</label>
                            <div style="display: flex; gap: 4px;">
                                <input type="text" id="phone_code" name="phone_code" value="965" style="width: 30%;">
                                <input type="text" id="phone_number" name="phone_number" value="12345678" style="width: 70%;">
                            </div>
                        </div>
                    </div>

                    <div class="row-grid">
                        <button type="button" class="btn" onclick="submitTransaction('create_charge')">Create Charge (Redirect)</button>
                        <button type="button" class="btn btn-success" onclick="submitTransaction('create_authorize')">Create Authorization (Hold)</button>
                    </div>
                </form>
                <div id="transactionResult" class="result-panel" style="display:none;"></div>
            </div>

            <!-- Refund Tab -->
            <div id="refund-tab" class="tab-content" style="display:none;">
                <h2>Issue Refund</h2>
                <form id="refundForm">
                    <label for="refund_charge_id">Charge ID / Payment ID:</label>
                    <input type="text" id="refund_charge_id" name="charge_id" placeholder="chg_xxxxxxxxxxxx" required>

                    <div class="row-grid">
                        <div>
                            <label for="refund_amount">Refund Amount:</label>
                            <input type="number" step="0.001" id="refund_amount" name="amount" value="5.000" required>
                        </div>
                        <div>
                            <label for="refund_currency">Currency:</label>
                            <select id="refund_currency" name="currency">
                                <option value="KWD">KWD</option>
                                <option value="SAR">SAR</option>
                                <option value="BHD">BHD</option>
                                <option value="AED">AED</option>
                                <option value="QAR">QAR</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>
                    </div>

                    <label for="refund_reason">Reason for Refund:</label>
                    <input type="text" id="refund_reason" name="reason" value="Customer Return">

                    <button type="button" class="btn btn-error" onclick="submitRefund()">Process Refund Request</button>
                </form>
                <div id="refundResultPanel" class="result-panel" style="display:none;"></div>
            </div>
        </div>

        <!-- Webhook Log Console -->
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--light-bg); padding-bottom: 12px; margin-bottom: 18px;">
                <h2 style="margin:0; border:none; padding:0;">3. Live Webhook Logs</h2>
                <button type="button" class="btn btn-secondary" style="margin:0; width:auto; padding:6px 12px; font-size:12px;" onclick="fetchLogs()">Refresh Logs</button>
            </div>
            <div id="logConsole" class="log-console">Loading webhook logs...</div>
        </div>
    </div>
</div>

<script>
    // Tab switcher
    function switchTab(tabId, btn) {
        document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
        document.getElementById(tabId).style.display = 'block';
        btn.classList.add('active');
    }

    // Combine config fields into a Form data helper
    function getConfigFormData() {
        const formData = new FormData();
        formData.append('secret_key', document.getElementById('secret_key').value);
        formData.append('publishable_key', document.getElementById('publishable_key').value);
        formData.append('merchant_id', document.getElementById('merchant_id').value);
        formData.append('test_mode', document.getElementById('test_mode').value);
        return formData;
    }

    // 1. Connection Test
    function testConnection() {
        const resultPanel = document.getElementById('connectionResult');
        resultPanel.style.display = 'block';
        resultPanel.innerText = 'Testing connectivity, please wait...';

        const formData = getConfigFormData();

        fetch('?action=test_connection', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            resultPanel.innerHTML = 'HTTP Code: ' + data.http_code + '\n\n' + JSON.stringify(data, null, 4);
        })
        .catch(err => {
            resultPanel.innerText = 'Network error testing credentials: ' + err;
        });
    }

    // 2. Submit charge / auth
    function submitTransaction(action) {
        const resultPanel = document.getElementById('transactionResult');
        resultPanel.style.display = 'block';
        resultPanel.innerText = 'Initializing payment, please wait...';

        const formData = getConfigFormData();
        formData.append('amount', document.getElementById('amount').value);
        formData.append('currency', document.getElementById('currency').value);
        formData.append('first_name', document.getElementById('first_name').value);
        formData.append('last_name', document.getElementById('last_name').value);
        formData.append('email', document.getElementById('email').value);
        formData.append('phone_code', document.getElementById('phone_code').value);
        formData.append('phone_number', document.getElementById('phone_number').value);

        fetch('?action=' + action, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.data && data.data.transaction && data.data.transaction.url) {
                const checkoutUrl = data.data.transaction.url;
                resultPanel.innerHTML = '<span style="color:#10b981;">[Success] Payment Link Created!</span>\n\nRedirecting in 3 seconds...\n\nIf redirect fails, click here: <a href="' + checkoutUrl + '" target="_blank" style="color:#38bdf8;">Open Sandbox Checkout Screen</a>\n\nFull Response details:\n' + JSON.stringify(data, null, 4);
                
                setTimeout(() => {
                    window.location.href = checkoutUrl;
                }, 3000);
            } else {
                resultPanel.innerHTML = '<span style="color:#ef4444;">[Failed] Error details returned:</span>\n\n' + JSON.stringify(data, null, 4);
            }
        })
        .catch(err => {
            resultPanel.innerText = 'Network error constructing payment request: ' + err;
        });
    }

    // 3. Submit refund
    function submitRefund() {
        const resultPanel = document.getElementById('refundResultPanel');
        resultPanel.style.display = 'block';
        resultPanel.innerText = 'Submitting refund request...';

        const formData = getConfigFormData();
        formData.append('charge_id', document.getElementById('refund_charge_id').value);
        formData.append('amount', document.getElementById('refund_amount').value);
        formData.append('currency', document.getElementById('refund_currency').value);
        formData.append('reason', document.getElementById('refund_reason').value);

        fetch('?action=create_refund', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                resultPanel.innerHTML = '<span style="color:#10b981;">[Success] Refund request processed. Details:</span>\n\n' + JSON.stringify(data, null, 4);
            } else {
                resultPanel.innerHTML = '<span style="color:#ef4444;">[Failed] Error details:</span>\n\n' + JSON.stringify(data, null, 4);
            }
        })
        .catch(err => {
            resultPanel.innerText = 'Network error: ' + err;
        });
    }

    // 4. Fetch logs
    function fetchLogs() {
        const consoleEl = document.getElementById('logConsole');
        fetch('?action=fetch_logs', {
            method: 'POST'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                consoleEl.innerText = data.logs;
                consoleEl.scrollTop = consoleEl.scrollHeight;
            } else {
                consoleEl.innerText = 'Failed to fetch logs.';
            }
        })
        .catch(err => {
            consoleEl.innerText = 'Network error fetching logs: ' + err;
        });
    }

    // Initial log load
    fetchLogs();
</script>

</body>
</html>
