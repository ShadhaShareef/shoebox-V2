<?php

function razorpayHasLiveCredentials(): bool
{
    $keyId = RAZORPAY_KEY_ID;
    $secret = RAZORPAY_KEY_SECRET;

    if ($keyId === '' || $secret === '') {
        return false;
    }

    $mockMarkers = ['rzp_test_ShoeboxViteFullStack', 'mock_secret', 'your_key', 'placeholder'];
    foreach ($mockMarkers as $marker) {
        if (stripos($keyId, $marker) !== false || stripos($secret, $marker) !== false) {
            return false;
        }
    }

    return !RAZORPAY_MOCK_MODE;
}

function razorpayCreateOrder(float $amountInr, string $receipt, array $notes = []): array
{
    if (!razorpayHasLiveCredentials()) {
        return [
            'id'       => 'order_mock_' . bin2hex(random_bytes(7)),
            'amount'   => (int) round($amountInr * 100),
            'currency' => 'INR',
            'is_mock'  => true,
            'key_id'   => RAZORPAY_KEY_ID ?: 'rzp_test_ShoeboxSandbox',
        ];
    }

    $payload = [
        'amount'   => (int) round($amountInr * 100),
        'currency' => 'INR',
        'receipt'  => $receipt,
        'notes'    => $notes,
    ];

    $response = razorpayApiRequest('POST', 'orders', $payload);

    return [
        'id'       => $response['id'],
        'amount'   => (int) $response['amount'],
        'currency' => $response['currency'],
        'is_mock'  => false,
        'key_id'   => RAZORPAY_KEY_ID,
    ];
}

function razorpayVerifyPaymentSignature(string $orderId, string $paymentId, string $signature): bool
{
    if (!razorpayHasLiveCredentials()) {
        return $orderId !== '' && $paymentId !== '' && $signature !== '';
    }

    $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, RAZORPAY_KEY_SECRET);
    return hash_equals($expected, $signature);
}

function razorpayVerifyWebhookSignature(string $rawBody, string $signature): bool
{
    $secret = RAZORPAY_WEBHOOK_SECRET;
    if ($secret === '') {
        return false;
    }
    $expected = hash_hmac('sha256', $rawBody, $secret);
    return hash_equals($expected, $signature);
}

function razorpayFetchPayment(string $paymentId): ?array
{
    if (!razorpayHasLiveCredentials()) {
        return ['id' => $paymentId, 'status' => 'captured', 'method' => 'upi'];
    }

    try {
        return razorpayApiRequest('GET', 'payments/' . $paymentId);
    } catch (RuntimeException) {
        return null;
    }
}

function razorpayBuildUpiUri(float $amountInr, string $orderId, string $customerName): string
{
    $vpa = trim(RAZORPAY_UPI_VPA);
    if ($vpa === '' || !preg_match('/^[A-Za-z0-9._\-]+@[A-Za-z0-9._\-]+$/', $vpa)) {
        $vpa = 'shoebox@razorpay';
    }

    $name = 'Shoebox Kerala HQ';
    $amount = number_format($amountInr, 2, '.', '');
    $tn = rawurlencode("Shoebox Order {$orderId} - {$customerName}");

    return 'upi://pay?pa=' . rawurlencode($vpa)
        . '&pn=' . rawurlencode($name)
        . '&am=' . rawurlencode($amount)
        . '&tr=' . rawurlencode($orderId)
        . '&cu=INR&tn=' . $tn;
}

function razorpayApiRequest(string $method, string $endpoint, ?array $body = null): array
{
    $url = 'https://api.razorpay.com/v1/' . ltrim($endpoint, '/');
    $auth = base64_encode(RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Basic ' . $auth,
        ],
        CURLOPT_TIMEOUT => 30,
    ]);

    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body ?? []));
    }

    $raw = curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($raw === false) {
        throw new RuntimeException('Razorpay API unreachable: ' . $err);
    }

    $data = json_decode($raw, true);
    if ($code < 200 || $code >= 300) {
        $msg = is_array($data) ? ($data['error']['description'] ?? $raw) : $raw;
        throw new RuntimeException('Razorpay API error (' . $code . '): ' . $msg);
    }

    return is_array($data) ? $data : [];
}
