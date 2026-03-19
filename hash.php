<?php
// hash.php
// Ejecuta este archivo en tu navegador para obtener el hash de la contraseña
$hash = password_hash('admin123', PASSWORD_DEFAULT);
echo "Hash generado para 'admin123':<br><textarea cols='80' rows='2'>" . htmlspecialchars($hash) . "</textarea>";
?>