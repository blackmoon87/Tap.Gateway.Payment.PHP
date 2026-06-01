# Standalone Tap Payments PHP SDK / مكتبة الدفع Tap لبيئة PHP النقية

This repository contains a lightweight, modern, and standalone PHP implementation of the **Tap Payments Gateway** (v2 API). It has been completely migrated and decoupled from WordPress/WooCommerce dependencies to allow integration into any PHP environment or framework.

يحتوي هذا المستودع على مكتبة برمجية خفيفة الوزن ومكتوبة بلغة PHP نقية للربط مع بوابة الدفع **Tap Payments** (الإصدار الثاني)، بعد أن تم فصلها بالكامل عن بيئة ووكومرس والووردبريس لتسهيل ربطها مع أي نظام أو إطار عمل PHP.

---

## 📁 Repository Structure / هيكلية المجلدات

```text
├── tap-pure-php/
│   ├── src/
│   │   ├── Config.php        # Credentials manager & fallback to environment variables
│   │   └── TapClient.php     # Core API wrapper (cURL requests & Webhook decoder)
│   ├── tests/
│   │   └── RunAllTests.php   # Automated test suite (30 functional & mock test assertions)
│   └── examples/
│       ├── index.php         # Interactive GUI Sandbox Dashboard (HTML, JS, CSS, PHP)
│       ├── config.php        # Dynamic settings loader
│       ├── create_charge.php    # Standalone charge redirect script
│       ├── create_authorize.php # Standalone authorization hold script
│       ├── create_refund.php    # Standalone refund initiator
│       ├── success.php       # Payment successful redirect landing page
│       ├── failure.php       # Payment failed/declined landing page
│       ├── webhook.php       # Webhook callback log generator
│       └── webhook_log.txt   # File storing received webhook payloads
└── README.md                 # Project documentation
```

---

## ⚙️ Configuration / إعدادات الربط

Credentials are loaded dynamically in `tap-pure-php/examples/config.php` using config arrays or environment variables fallback:
يتم قراءة بيانات الربط ديناميكياً من خلال المصفوفة البرمجية أو عبر متغيرات البيئة (Environment Variables):

```php
$configOptions = [
    'secret_key'      => 'sk_test_' . 'XKokBfNWv6FIYuTMg5sLPjhJ', // Test Secret Key
    'publishable_key' => 'pk_test_ETGOZ4A626c06a0c0b0c0001', // Test Publishable Key
    'merchant_id'     => '599424',                           // Test Merchant ID
    'test_mode'       => true                                // Test Sandbox Mode
];
```

---

## 🖥️ Interactive Web GUI Sandbox Dashboard / لوحة التحكم التفاعلية

We have designed an interactive Web GUI Dashboard at `tap-pure-php/examples/index.php`. It provides:
لقد قمنا بتصميم لوحة تحكم ويب تفاعلية متكاملة لتسهيل تجربة المطورين وتوفر الميزات التالية:

1. **Credentials Manager**: Update Keys/Merchant ID on the fly.
2. **Payment Form Creator**: Generate charge checkout links or authorization holds dynamically with custom amount, currency, and customer parameters.
3. **Saudi & Kuwait Sandbox Flow**: Test Saudi local payment cards (Mada) via SAR currency and Kuwaiti cards (KNET) via KWD currency.
4. **Refund Console**: Process refunds instantly using Charge ID and amount.
5. **Real-time Webhook Log Viewer**: Inspect payloads sent from Tap API.

### How to access the Dashboard:
1. Start the PHP development server:
   ```bash
   php -S localhost:8080
   ```
2. Navigate to: `http://localhost:8080/tap-pure-php/examples/index.php` in your browser.

---

## 🧪 Automated Testing / حزمة الاختبارات التلقائية

The project features a full PHP test runner covering all functions, configurations, route parameters, response integrity, and network checks.
تأتي المكتبة مزودة بحزمة اختبارات برمجية شاملة للتحقق من الاتصال وسلامة الأكواد ومطابقة المسارات والاستجابات.

### How to Run Tests:
Execute the testing suite in your terminal:
```bash
php tap-pure-php/tests/RunAllTests.php
```

### Validation Scope (30 Assertions):
- **Config Defaults & Overrides**: Standard initialization verification.
- **Environment Variables**: Verifying dynamic fallback variables (`TAP_SECRET_KEY`, etc.).
- **Endpoint Routing Paths**: Correct routes matching `/charges`, `/authorize`, and `/refunds` with correct HTTP verbs.
- **Mock Success & Error Handlers**: Evaluating JSON decoding, HTTP code returns, and error payload structures.
- **Webhook Handlers**: Empty or corrupt payload validation tests.
- **Live Sandbox Connections**: Executing actual test requests directly to the Tap API Sandbox endpoint to verify cURL SSL certificates and network status.

---

## 📚 SDK Methods Reference / جدول الدوال البرمجية

Below is the list of available methods in the `TapPayment\TapClient` class:

| Method (الدالة) | Arguments (المعاملات) | Return (الاسترجاع) | Description (الوصف) |
| :--- | :--- | :--- | :--- |
| `__construct` | `Config $config` | `void` | Initializes the SDK Client using configuration properties. / تهيئة الكائن البرمجي باستخدام الإعدادات. |
| `createCharge` | `array $params` | `array` | Creates a direct charge checkout session. / إنشاء عملية شحن/دفع مباشر. |
| `getCharge` | `string $chargeId` | `array` | Fetches transaction parameters for a Charge ID. / جلب تفاصيل عملية الشحن بالرمز التعريفي. |
| `createAuthorize` | `array $params` | `array` | Creates a pre-authorization hold transaction. / تفويض وحجز مبلغ مالي من بطاقة العميل. |
| `getAuthorize` | `string $authId` | `array` | Fetches transaction details for an Auth ID. / جلب تفاصيل عملية الحجز والتفويض المالي. |
| `createRefund` | `array $params` | `array` | Issues a full or partial refund. / طلب استرجاع مالي كلي أو جزئي. |
| `getRefund` | `string $refundId` | `array` | Fetches transaction details for a Refund ID. / استدعاء تفاصيل عملية الاسترداد المالي. |
| `handleWebhook` | *None* | `array` | Decodes and returns the raw POST webhook payload. / استقبال ومعالجة البيانات الواردة في الـ Webhook تلقائياً. |
| `makeRequest` | `string $endpoint, string $method, array $data` | `array` | Internal cURL low-level request wrapper. / دالة داخلية لإجراء طلبات HTTP عبر بروتوكول cURL. |

---

## 💳 Test Card Numbers / أرقام بطاقات الاختبار

### 1. Local Payment Methods / بطاقات الدفع المحلية

| Method | Card Number | Expiry Date | PIN | Status |
| :--- | :--- | :--- | :--- | :--- |
| **KNET** | `8888880000000001` | `09/30` | `1234` | **CAPTURED** |
| **KNET** | `8888880000000002` | `09/30` | `1234` | **CAPTURED** |
| **Benefit** | `4600410123456789` | `12/27` | `1234` | **CAPTURED** |
| **Naps/QPay**| `4215375500883243` | `12/25` | `944`  | **CAPTURED** (OTP: `1234`) |

### 2. Credit & Debit Cards / بطاقات الائتمان والدفع

| Brand | Card Number | 3D Secure | Expiry Date Response |
| :--- | :--- | :--- | :--- |
| **MasterCard** | `5123450000000008` | Yes | `01/39` (APPROVED / مقبول) |
| **VISA** | `4508750015741019` | Yes | `05/22` (DECLINED / مرفوض) |
| **Mada** | `4464040000000007` | Yes | `04/27` (EXPIRED / منتهية) |
| **Amex** | `345678901234564`  | Yes | `08/28` (TIMED_OUT / انتهاء الوقت) |
