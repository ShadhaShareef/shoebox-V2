<?php

require_once __DIR__ . '/../includes/init.php';

$step = $_POST['step'] ?? '';

switch ($step) {
    case 'delivery':
        requireCsrf();

        if (empty(getCart())) {
            flash('warning', 'Cart Empty', 'Add items before checkout.');
            redirect('checkout.php');
        }

        $syncError = syncCartFromDatabase();
        if ($syncError) {
            flash('warning', 'Cart Updated', $syncError);
            redirect('checkout.php');
        }

        $_SESSION['checkout_step'] = 'delivery';
        redirect('checkout.php');

    case 'payment':
        requireCsrf();

        $required = ['full_name', 'phone', 'street_address', 'email'];
        foreach ($required as $field) {
            if (empty(trim($_POST[$field] ?? ''))) {
                flash('warning', 'Missing Details', 'Please fill in all required fields including email.');
                redirect('checkout.php');
            }
        }

        $email = normalizeEmail($_POST['email'] ?? '');
        if (!validateEmail($email)) {
            flash('warning', 'Invalid Email', 'Please enter a valid email address.');
            redirect('checkout.php');
        }

        $prepError = prepareCheckoutCart();
        if ($prepError) {
            flash('warning', 'Checkout Error', $prepError);
            redirect('checkout.php');
        }

        $_SESSION['delivery'] = [
            'full_name' => trim($_POST['full_name']),
            'phone' => sanitizePhone(trim($_POST['phone'])),
            'email' => $email,
            'district' => trim($_POST['district'] ?? 'Kochi (Ernakulam)'),
            'street_address' => trim($_POST['street_address']),
            'pincode' => sanitizePincode(trim($_POST['pincode'] ?? '')),
        ];
        unset($_SESSION['pending_razorpay_order']);
        $_SESSION['checkout_step'] = 'payment';
        redirect('checkout.php');

    case 'place':
        requireCsrf();

        $paymentMethod = $_POST['payment_method'] ?? 'cod';
        if ($paymentMethod === 'upi') {
            flash('warning', 'Use UPI Checkout', 'Complete payment via the Razorpay UPI panel above.');
            redirect('checkout.php');
        }

        if ($paymentMethod !== 'cod') {
            flash('warning', 'Payment Error', 'Select Cash on Delivery or pay via UPI.');
            redirect('checkout.php');
        }

        $result = placeOrderFromCheckout('cod');
        if (!$result['success']) {
            flash('warning', 'Order Failed', $result['message']);
            redirect('checkout.php');
        }

        flash('success', 'Order Placed', 'Tracking ID: ' . $result['order_id']);
        redirect('checkout.php');

    default:
        redirect('checkout.php');
}
