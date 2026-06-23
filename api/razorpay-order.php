<?php
require_once __DIR__ . '/../includes/init.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$csrf = $input['csrf_token'] ?? $_POST['csrf_token'] ?? '';

if (!verifyCsrf($csrf)) {
    jsonResponse(['error' => 'Invalid CSRF token'], 403);
}

$delivery = $_SESSION['delivery'] ?? null;
if (!$delivery) {
    jsonResponse(['error' => 'Complete delivery details first'], 400);
}

$prepError = prepareCheckoutCart();
if ($prepError) {
    jsonResponse(['error' => $prepError], 400);
}

$total = cartTotal();
$customerName = $delivery['full_name'] ?? 'Guest';
$receipt = 'shbx_' . time() . '_' . random_int(100, 999);

try {
    $order = razorpayCreateOrder($total, $receipt, [
        'customer' => $customerName,
        'email'    => $delivery['email'] ?? '',
        'phone'    => $delivery['phone'] ?? '',
    ]);

    $_SESSION['pending_razorpay_order'] = [
        'id'      => $order['id'],
        'amount'  => $total,
        'is_mock' => $order['is_mock'],
    ];

    jsonResponse([
        'id'          => $order['id'],
        'keyId'       => $order['key_id'],
        'amount'      => $total,
        'amountPaise' => $order['amount'],
        'currency'    => 'INR',
        'isMock'      => $order['is_mock'],
        'upiUri'      => razorpayBuildUpiUri($total, $order['id'], $customerName),
        'qrUrl'       => 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&color=0a0a0a&data='
            . rawurlencode(razorpayBuildUpiUri($total, $order['id'], $customerName)),
    ]);
} catch (RuntimeException $e) {
    logError('Razorpay order creation failed: ' . $e->getMessage());
    jsonResponse(['error' => 'Payment gateway unavailable'], 502);
}
