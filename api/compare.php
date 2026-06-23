<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

$productId = (int) ($_POST['product_id'] ?? 0);
$mode = $_POST['mode'] ?? 'toggle';

if ($productId <= 0) {
    if (isset($_POST['ajax'])) {
        jsonResponse(['success' => false, 'error' => 'Invalid product'], 400);
    }
    redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');
}

if ($mode === 'primary') {
    $compare = setComparePrimary($productId);
    $message = 'Ready to compare';
} elseif ($mode === 'add') {
    $compare = addCompareSecondary($productId);
    $message = count($compare) >= 2 ? 'Compare updated' : 'Added to compare';
} else {
    $compare = toggleCompare($productId);
    $message = in_array($productId, $compare, true) ? 'Added to compare' : 'Removed from compare';
}

$product = getCompareProductById($productId);

if (isset($_POST['ajax'])) {
    jsonResponse([
        'success' => true,
        'compareIds' => $compare,
        'count' => count($compare),
        'product' => $product ? comparePayload($product) : null,
        'message' => $message,
    ]);
}

flash('success', 'Compare', $message);
redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');
