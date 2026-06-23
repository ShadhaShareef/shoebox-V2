<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

if (!rateLimitAllow('newsletter', 10, 3600)) {
    flash('warning', 'Too Many Attempts', 'Please wait before subscribing again.');
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

$email = normalizeEmail($_POST['email'] ?? '');
if (!validateEmail($email)) {
    flash('warning', 'Invalid Email', 'Please enter a valid email address.');
    redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
}

try {
    db()->prepare('INSERT INTO newsletter_subscribers (email) VALUES (?)')->execute([$email]);
    flash('success', 'Subscribed', 'You will receive drop alerts and Kerala exclusives.');
} catch (PDOException $e) {
    flash('info', 'Already Subscribed', 'This email is already on our list.');
}
redirect($_SERVER['HTTP_REFERER'] ?? 'index.php');
