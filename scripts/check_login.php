<?php
require_once __DIR__ . '/../config/database.php';
$db = Database::getInstance();
$user = $db->getUserByUsername('admin');
if (!$user) {
    echo "NO_USER\n";
    exit;
}
echo "HASH_LEN=" . strlen($user['password_hash']) . "\n";
echo "HASH=" . $user['password_hash'] . "\n";
$ok = password_verify('Prueba123!', $user['password_hash']);
echo "VERIFY=" . ($ok ? 'TRUE' : 'FALSE') . "\n";
