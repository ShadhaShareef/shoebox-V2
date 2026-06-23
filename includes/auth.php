<?php

function currentUser(): ?array
{
    if (!isset($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare(
        'SELECT u.*, lt.name AS tier_name
         FROM users u
         JOIN loyalty_tiers lt ON lt.id = u.member_tier_id
         WHERE u.id = ?'
    );
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch() ?: null;
    if ($user) {
        $tier = resolveMemberTier((int) $user['loyalty_points']);
        if ((int) $tier['id'] !== (int) $user['member_tier_id']) {
            db()->prepare('UPDATE users SET member_tier_id = ? WHERE id = ?')
               ->execute([$tier['id'], $user['id']]);
            $user['member_tier_id'] = $tier['id'];
            $user['tier_name'] = $tier['name'];
        }
    }
    return $user;
}

function loginUser(string $email, string $password): bool
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if (function_exists('logoutAdmin')) {
            logoutAdmin();
        }
        regenerateSession();
        $_SESSION['user_id'] = $user['id'];
        return true;
    }
    return false;
}

function registerUser(array $data): ?string
{
    $check = db()->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$data['email']]);
    if ($check->fetch()) {
        return 'An account with this email already exists.';
    }

    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $stmt = db()->prepare(
        'INSERT INTO users (email, password_hash, full_name, phone, district, pincode, street_address)
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $data['email'],
        $hash,
        $data['full_name'],
        $data['phone'] ?? '',
        $data['district'] ?? 'Kochi (Ernakulam)',
        $data['pincode'] ?? '',
        $data['street_address'] ?? '',
    ]);

    $_SESSION['user_id'] = (int) db()->lastInsertId();
    regenerateSession();
    return null;
}

function logoutUser(): void
{
    unset($_SESSION['user_id']);
}

function updateUserProfile(int $userId, array $data): void
{
    db()->prepare(
        'UPDATE users SET full_name = ?, phone = ?, district = ?, street_address = ?, pincode = ? WHERE id = ?'
    )->execute([
        $data['full_name'],
        $data['phone'],
        $data['district'],
        $data['street_address'],
        $data['pincode'],
        $userId,
    ]);
}

function hasLoyaltyClaim(int $userId, string $type): bool
{
    $stmt = db()->prepare('SELECT 1 FROM loyalty_claims WHERE user_id = ? AND claim_type = ?');
    $stmt->execute([$userId, $type]);
    return (bool) $stmt->fetch();
}

function claimLoyaltyPoints(int $userId, string $type, int $points): void
{
    if (hasLoyaltyClaim($userId, $type)) {
        return;
    }
    db()->prepare('INSERT INTO loyalty_claims (user_id, claim_type) VALUES (?, ?)')->execute([$userId, $type]);
    db()->prepare('UPDATE users SET loyalty_points = loyalty_points + ? WHERE id = ?')->execute([$points, $userId]);
}
