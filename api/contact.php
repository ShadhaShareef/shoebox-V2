<?php
require_once __DIR__ . '/../includes/init.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

if (!rateLimitAllow('contact_form', 5, 3600)) {
    flash('warning', 'Too Many Messages', 'Please wait before sending another inquiry.');
    redirect('about.php');
}

if (isHoneypotFilled($_POST['website'] ?? null)) {
    redirect('about.php');
}

$required = ['full_name', 'email', 'message'];
foreach ($required as $field) {
    if (empty(trim($_POST[$field] ?? ''))) {
        flash('warning', 'Form Incomplete', 'Please fill in name, email, and message.');
        redirect('about.php');
    }
}

$email = normalizeEmail($_POST['email'] ?? '');
if (!validateEmail($email)) {
    flash('warning', 'Invalid Email', 'Please enter a valid email address.');
    redirect('about.php');
}

$message = trim($_POST['message'] ?? '');
if (strlen($message) > 5000) {
    flash('warning', 'Message Too Long', 'Please keep your message under 5000 characters.');
    redirect('about.php');
}

db()->prepare(
    'INSERT INTO contact_messages (full_name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)'
)->execute([
    trim($_POST['full_name']),
    $email,
    sanitizePhone(trim($_POST['phone'] ?? '')),
    trim($_POST['subject'] ?? 'General Inquiry'),
    $message,
]);

flash('success', 'Inquiry Sent', 'Our Kerala boutique concierge will respond shortly.');
redirect('about.php');
