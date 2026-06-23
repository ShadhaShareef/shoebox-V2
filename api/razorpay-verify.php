<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];

if (!verifyCsrf($input['csrf_token'] ?? '')) {
    jsonResponse(['error' => 'Invalid CSRF token'], 403);
}

if (!rateLimitAllow('razorpay_verify', 20, 900)) {
    jsonResponse(['error' => 'Too many payment attempts. Please wait.'], 429);
}

$razorpayOrderId = trim($input['razorpay_order_id'] ?? '');
$razorpayPaymentId = trim($input['razorpay_payment_id'] ?? '');
$razorpaySignature = trim($input['razorpay_signature'] ?? '');

$pending = $_SESSION['pending_razorpay_order'] ?? null;
$delivery = $_SESSION['delivery'] ?? null;

if (!$delivery) {
    jsonResponse(['error' => 'Checkout session expired'], 400);
}

$existingOrder = orderExistsForRazorpayPayment($razorpayPaymentId);
if ($existingOrder) {
    jsonResponse([
        'success'  => true,
        'orderId'  => $existingOrder,
        'redirect' => url('track.php?id=' . urlencode($existingOrder)),
    ]);
}

$isMock = $pending['is_mock'] ?? !razorpayHasLiveCredentials();

if ($isMock) {
    if ($razorpayOrderId === '') {
        $razorpayOrderId = $pending['id'] ?? ('order_mock_' . bin2hex(random_bytes(5)));
    }
    if ($razorpayPaymentId === '') {
        $razorpayPaymentId = 'pay_mock_' . bin2hex(random_bytes(5));
    }
    if ($razorpaySignature === '') {
        $razorpaySignature = 'mock_sig_' . bin2hex(random_bytes(8));
    }
} else {
    if ($razorpayOrderId === '' || $razorpayPaymentId === '' || $razorpaySignature === '') {
        jsonResponse(['error' => 'Missing payment credentials'], 400);
    }

    if (!razorpayVerifyPaymentSignature($razorpayOrderId, $razorpayPaymentId, $razorpaySignature)) {
        jsonResponse(['error' => 'Payment signature verification failed'], 402);
    }

    $payment = razorpayFetchPayment($razorpayPaymentId);
    if (!$payment || ($payment['status'] ?? '') !== 'captured') {
        jsonResponse(['error' => 'Payment not captured'], 402);
    }

    if ($pending && ($pending['id'] ?? '') !== $razorpayOrderId) {
        jsonResponse(['error' => 'Order ID mismatch'], 400);
    }

    $expectedPaise = (int) round(cartTotal() * 100);
    if (isset($payment['amount']) && (int) $payment['amount'] !== $expectedPaise) {
        jsonResponse(['error' => 'Payment amount mismatch'], 402);
    }
}

$result = placeOrderFromCheckout('upi', $razorpayOrderId, $razorpayPaymentId);

if (!$result['success']) {
    jsonResponse(['error' => $result['message']], 500);
}

unset($_SESSION['pending_razorpay_order']);

jsonResponse([
    'success'  => true,
    'orderId'  => $result['order_id'],
    'redirect' => url('track.php?id=' . urlencode($result['order_id'])),
]);
