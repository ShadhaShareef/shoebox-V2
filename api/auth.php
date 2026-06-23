<?php

require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/admin.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
}

$action = $_POST['action'] ?? 'login';

function authRedirectTarget(string $default): string
{
    $target = trim((string) ($_POST['return_to'] ?? ''));
    if ($target === '') {
        return $default;
    }

    return preg_match('/^[a-z0-9._\/-]+(\?.*)?$/i', $target) ? $target : $default;
}

if ($action === 'logout') {
    logoutUser();
    flash('info', 'Signed Out', 'See you soon.');
    redirect('index.php');
}

if ($action === 'register') {
    if (!rateLimitAllow('auth_register', 5, 900)) {
        flash('warning', 'Too Many Attempts', 'Please wait before trying again.');
        redirect(authRedirectTarget('register.php'));
    }

    $email = normalizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!validateEmail($email)) {
        flash('warning', 'Invalid Email', 'Please enter a valid email address.');
        redirect(authRedirectTarget('register.php'));
    }

    $passwordError = validatePassword($password);
    if ($passwordError) {
        flash('warning', 'Weak Password', $passwordError);
        redirect(authRedirectTarget('register.php'));
    }

    $error = registerUser([
        'email'          => $email,
        'password'       => $password,
        'full_name'      => trim($_POST['full_name'] ?? ''),
        'phone'          => sanitizePhone(trim($_POST['phone'] ?? '')),
        'district'       => trim($_POST['district'] ?? 'Kochi (Ernakulam)'),
        'pincode'        => sanitizePincode(trim($_POST['pincode'] ?? '')),
        'street_address' => '',
    ]);

    if ($error) {
        flash('warning', 'Registration Failed', $error);
        redirect(authRedirectTarget('register.php'));
    }

    flash('success', 'Welcome to Shoebox', 'Your account has been created.');
    redirect('auth.php');
}

if ($action === 'login') {
    if (!rateLimitAllow('auth_login', 10, 900)) {
        flash('warning', 'Too Many Attempts', 'Please wait 15 minutes before trying again.');
        redirect(authRedirectTarget('login.php'));
    }

    $email = normalizeEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (adminLogin($email, $password)) {
        flash('success', 'Welcome Back', 'Signed in to the admin portal.');
        redirect('admin/');
    }

    if (loginUser($email, $password)) {
        flash('success', 'Welcome Back', 'Signed in successfully.');
        redirect('auth.php');
    }

    flash('warning', 'Login Failed', 'Invalid email or password.');
    redirect(authRedirectTarget('login.php'));
}

if ($action === 'update_profile' && currentUser()) {
    $user = currentUser();
    updateUserProfile((int) $user['id'], [
        'full_name'      => trim($_POST['full_name'] ?? ''),
        'phone'          => sanitizePhone(trim($_POST['phone'] ?? '')),
        'district'       => trim($_POST['district'] ?? ''),
        'street_address' => trim($_POST['street_address'] ?? ''),
        'pincode'        => sanitizePincode(trim($_POST['pincode'] ?? '')),
    ]);

    flash('success', 'Profile Updated', 'Your details have been saved.');
    redirect('auth.php');
}

if ($action === 'claim_loyalty' && currentUser()) {
    $type = $_POST['claim_type'] ?? '';

    if (!in_array($type, ['instagram_follow', 'phone_verify'], true)) {
        flash('warning', 'Invalid Claim', 'Unknown loyalty action.');
        redirect('auth.php');
    }

    $points = $type === 'instagram_follow' ? 50 : 100;
    claimLoyaltyPoints((int) currentUser()['id'], $type, $points);

    flash('success', 'Points Earned', "+{$points} loyalty points added.");
    redirect('auth.php');
}

redirect('auth.php');
