# Standalone Tap Payments PHP SDK / مكتبة الدفع Tap لبيئة PHP النقية

This directory contains a lightweight, pure PHP implementation of the **Tap Payments Gateway** (v2 API) to easily transition away from WooCommerce.

يحتوي هذا المجلد على مكتبة برمجية خفيفة الوزن ومكتوبة بلغة PHP نقية للربط مع بوابة الدفع **Tap Payments** (الإصدار الثاني)، للتحول بسهولة من بيئة الووكومرس إلى أي بيئة PHP أخرى.

---

## Directory Structure / هيكلية المجلد

- `src/Config.php`: Configuration manager for API credentials. / مدير الإعدادات والربط مع المفاتيح البرمجية.
- `src/TapClient.php`: Core API wrapper using PHP cURL. / المكتبة الأساسية للربط وإجراء الطلبات.
- `examples/config.php`: Load settings dynamically. / ملف تهيئة الإعدادات التجريبي.
- `examples/create_charge.php`: Web page to trigger direct charges. / صفحة نموذج الدفع المباشر والتحويل لبوابة الدفع.
- `examples/create_authorize.php`: Authorization hold payment. / صفحة تفويض وحجز مبلغ مالي.
- `examples/create_refund.php`: Web page to issue refunds. / نموذج طلب استرجاع مالي للعمليات المكتملة.
- `examples/webhook.php`: Webhook handler. / معالج الـ Webhook لتحديث حالة الدفع بالخلفية تلقائياً.
- `examples/success.php` & `examples/failure.php`: Redirection landing pages. / صفحات توجيه العميل بعد نجاح العملية أو فشلها.

---

## Configuration / إعدادات الربط

Settings are defined in `examples/config.php` dynamically:
يتم تكوين الإعدادات بشكل ديناميكي في الملف `examples/config.php`:

```php
$configOptions = [
    'secret_key'      => 'sk_test_' . 'XKokBfNWv6FIYuTMg5sLPjhJ',
    'publishable_key' => 'pk_test_ETGOZ4A626c06a0c0b0c0001',
    'merchant_id'     => '599424',
    'test_mode'       => true
];
```

---

## How to Test / كيفية البدء والتجربة

1. Start PHP built-in local development server:
   شغّل سيرفر PHP المحلي للتجربة:
   ```bash
   php -S localhost:8000
   ```
2. Navigate to: `http://localhost:8000/tap-pure-php/examples/create_charge.php` in your browser.
   افتح الرابط التالي في متصفحك: `http://localhost:8000/tap-pure-php/examples/create_charge.php`

---

## Test Credentials / بيانات الاختبار المقدمة

* **Test Merchant ID**: `599424`
* **Test Secret Key**: `sk_test_` + `XKokBfNWv6FIYuTMg5sLPjhJ`

### Test Card Numbers / أرقام بطاقات الاختبار

#### 1. Local Payment Methods / بطاقات الدفع المحلية

| Method | Card Number | Expiry Date | PIN | Status |
| :--- | :--- | :--- | :--- | :--- |
| **KNET** | `8888880000000001` | `09/30` | `1234` | **CAPTURED** |
| **KNET** | `8888880000000002` | `09/30` | `1234` | **CAPTURED** |
| **Benefit** | `4600410123456789` | `12/27` | `1234` | **CAPTURED** |
| **Naps/QPay**| `4215375500883243` | `12/25` | `944`  | **CAPTURED** (OTP: `1234`) |

#### 2. Credit & Debit Cards / بطاقات الائتمان والدفع

| Brand | Card Number | 3D Secure | Expiry Date Response |
| :--- | :--- | :--- | :--- |
| **MasterCard** | `5123450000000008` | Yes | `01/39` (APPROVED / مقبول) |
| **VISA** | `4508750015741019` | Yes | `05/22` (DECLINED / مرفوض) |
| **Mada** | `4464040000000007` | Yes | `04/27` (EXPIRED / منتهية) |
| **Amex** | `345678901234564`  | Yes | `08/28` (TIMED_OUT / انتهاء الوقت) |

---

## Response Status Codes / رموز الاستجابة والخطأ

### 1. Charges Codes / أكواد عمليات الشحن والدفع المباشر
- `000`: **Captured / تم الدفع والتقاط المبلغ بنجاح**
- `100`: **Initiated / بدأت العملية**
- `501`: **Declined / تم رفض العملية**
- `505`: **Declined, Insufficient Funds / رفض بسبب عدم كفاية الرصيد**

### 2. HTTP Status Codes / أكواد استجابة بروتوكول HTTP
- `200`: **OK / نجاح الطلب**
- `400`: **Bad Request / طلب غير صالح (برامترات مفقودة)**
- `401`: **Unauthorized / غير مصرح به (المفاتيح البرمجية غير صحيحة)**
- `404`: **Not Found / المورد غير موجود**
- `429`: **Too Many Requests / طلبات زائدة عن الحد**
