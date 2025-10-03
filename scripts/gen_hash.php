<?php
// Genera un hash de contraseña para usar en pruebas y emite la sentencia SQL
$password = isset($argv[1]) ? $argv[1] : 'Prueba123!';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "-- SQL UPDATE para establecer la contraseña del usuario admin\n";
echo "UPDATE users SET password_hash = '" . addslashes($hash) . "' WHERE username = 'admin';\n";
echo "-- Contraseña en texto claro: " . $password . "\n";
echo "-- Hash: " . $hash . "\n";

