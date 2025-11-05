<?php

require __DIR__ . '/config.php';

$admin_email = 'admin@moonheritage.com';
$new_password = 'Admin@123';

try {
    $db = getDB();


    $stmt = $db->prepare("SELECT id, email, role FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$admin_email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "No user found with email {$admin_email}\n";
        exit(1);
    }

    echo "Found user id={$user['id']} role={$user['role']}\n";

    $hash = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

    $update = $db->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
    $update->execute([$hash, $user['id']]);

    if ($update->rowCount()) {
        echo "Password updated for {$admin_email}. New password: {$new_password}\n";
        echo "IMPORTANT: Delete this file after use (rm reset_admin.php)\n";
        exit(0);
    } else {
        echo "Nothing changed — maybe the same hash already present.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}