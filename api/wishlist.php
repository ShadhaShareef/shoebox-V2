<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

$productId = (int) ($_POST['product_id'] ?? 0);
if ($productId > 0) {
    $added = toggleWishlist($productId);
    flash($added ? 'success' : 'info', $added ? 'Added to Wishlist' : 'Removed from Wishlist');
}
redirect($_SERVER['HTTP_REFERER'] ?? 'shop.php');
