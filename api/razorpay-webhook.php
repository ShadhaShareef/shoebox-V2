<?php
/**
 * Razorpay webhook handler (production).
 * Register URL in Razorpay Dashboard: https://yoursite.com/shoebox2/api/razorpay-webhook.php
 * Events: payment.captured
 */
require_once __DIR__ . '/../includes/init.php';

$rawBody = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

if (!razorpayVerifyWebhookSignature($rawBody, $signature)) {
    http_response_code(400);
    exit('Invalid signature');
}

$event = json_decode($rawBody, true);
if (!is_array($event)) {
    http_response_code(400);
    exit('Invalid payload');
}

// Log or process payment.captured events for async order reconciliation
if (($event['event'] ?? '') === 'payment.captured') {
    $payment = $event['payload']['payment']['entity'] ?? [];
    $orderId = $payment['order_id'] ?? null;
    $paymentId = $payment['id'] ?? null;

    if ($orderId && $paymentId) {
        try {
            db()->prepare(
                'UPDATE orders SET razorpay_payment_id = ? WHERE razorpay_order_id = ? AND razorpay_payment_id IS NULL'
            )->execute([$paymentId, $orderId]);
        } catch (PDOException $e) {
            logError('Webhook payment update failed: ' . $e->getMessage());
        }
    }
}

http_response_code(200);
echo json_encode(['status' => 'ok']);
