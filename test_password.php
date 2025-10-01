<?php
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo 'Contraseña original: ' . $password . '<br>';
echo 'Contraseña hasheada: ' . $hashed_password . '<br><br>';

if (password_verify($password, $hashed_password)) {
    echo '¡La verificación de contraseña funciona correctamente!';
} else {
    echo 'Error: La verificación de contraseña falló.';
}
?>