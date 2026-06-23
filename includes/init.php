<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/security.php';

configureSession();
session_start();
sendSecurityHeaders();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/razorpay.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/order.php';

if (!isset($_SESSION['flash'])) {
    $_SESSION['flash'] = [];
}
