<?php

function orderExistsForRazorpayPayment(string $paymentId): ?string
{
    if ($paymentId === '') {
        return null;
    }

    $stmt = db()->prepare('SELECT id FROM orders WHERE razorpay_payment_id = ? LIMIT 1');
    $stmt->execute([$paymentId]);
    $row = $stmt->fetch();

    return $row ? $row['id'] : null;
}

function prepareCheckoutCart(): ?string
{
    $syncError = syncCartFromDatabase();
    if ($syncError) {
        return $syncError;
    }

    if (empty(getCart())) {
        return 'Your cart is empty.';
    }

    return validateCartInventory();
}

function placeOrderFromCheckout(string $paymentMethod, ?string $razorpayOrderId = null, ?string $razorpayPaymentId = null): array
{
    $prepError = prepareCheckoutCart();
    if ($prepError) {
        return ['success' => false, 'message' => $prepError];
    }

    $cart = getCart();
    $delivery = $_SESSION['delivery'] ?? null;

    if (empty($cart) || !$delivery) {
        return ['success' => false, 'message' => 'Checkout session expired'];
    }

    if ($razorpayPaymentId) {
        $existing = orderExistsForRazorpayPayment($razorpayPaymentId);
        if ($existing) {
            return ['success' => true, 'order_id' => $existing, 'duplicate' => true];
        }
    }

    $subtotal = cartSubtotal();
    $shipping = shippingFee();
    $total = cartTotal();
    $orderId = generateOrderId();
    $user = currentUser();
    $email = $delivery['email'] ?: ($user['email'] ?? '');

    if ($email === '' || !validateEmail($email)) {
        return ['success' => false, 'message' => 'A valid email is required for order confirmation.'];
    }

    $pdo = db();
    $pdo->beginTransaction();

    try {
        $pdo->prepare(
            'INSERT INTO orders (id, user_id, full_name, email, phone, district, street_address, pincode,
             subtotal, shipping_fee, total_amount, status, courier, payment_method, razorpay_order_id, razorpay_payment_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        )->execute([
            $orderId,
            $user['id'] ?? null,
            $delivery['full_name'],
            normalizeEmail($email),
            sanitizePhone($delivery['phone']),
            $delivery['district'],
            $delivery['street_address'],
            sanitizePincode($delivery['pincode'] ?? ''),
            $subtotal,
            $shipping,
            $total,
            'placed',
            'Kerala Express Logistics (Dispatch Pending)',
            $paymentMethod,
            $razorpayOrderId,
            $razorpayPaymentId,
        ]);

        $itemStmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, product_id, name, brand, size_uk, quantity, unit_price, shoe_color, accent_color)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($cart as $item) {
            $itemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['name'],
                $item['brand'],
                $item['size'],
                $item['quantity'],
                $item['price'],
                $item['shoe_color'],
                $item['accent_color'],
            ]);
        }

        decrementCartStock($pdo);

        $now = new DateTime();
        $timelineDesc = match ($paymentMethod) {
            'upi' => 'UPI payment authenticated via Razorpay (Ref: ' . ($razorpayPaymentId ?? 'N/A') . ').',
            'cod' => 'Cash on Delivery order placed. Pay on delivery after authenticity inspection.',
            default => 'Order placed on Shoebox digital storefront.',
        };

        $pdo->prepare(
            'INSERT INTO order_timeline (order_id, status, event_date, event_time, description, location, sort_order)
             VALUES (?, ?, ?, ?, ?, ?, 1)'
        )->execute([
            $orderId,
            'placed',
            $now->format('F j, Y'),
            $now->format('h:i A'),
            $timelineDesc,
            $delivery['district'] . ', Kerala',
        ]);

        if ($user) {
            $points = (int) floor($total * 0.05);
            $pdo->prepare('UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?')
               ->execute([$points, $user['id']]);
        }

        $pdo->commit();
    } catch (Exception $e) {
        $pdo->rollBack();
        logError('Order placement failed: ' . $e->getMessage());
        return ['success' => false, 'message' => isDebug() ? $e->getMessage() : 'Could not save order. Please try again.'];
    }

    clearCart();
    $_SESSION['placed_order_id'] = $orderId;
    $_SESSION['checkout_step'] = 'done';
    unset($_SESSION['delivery'], $_SESSION['pending_razorpay_order']);

    return ['success' => true, 'order_id' => $orderId];
}

function canViewOrder(array $order, ?array $user, ?string $email = null): bool
{
    if ($user && (int) ($order['user_id'] ?? 0) === (int) $user['id']) {
        return true;
    }

    if ($email && validateEmail($email) && emailsMatch($email, $order['email'])) {
        return true;
    }

    return false;
}
