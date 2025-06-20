<?php
// /includes/database.php (VERSIÓN CORREGIDA Y DEFINITIVA)

// Esta versión ya no necesita llamar a config.php, ya que el header.php se encarga.

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (\PDOException $e) {
    error_log("Error de conexión a la base de datos: " . $e->getMessage());
    die("Error de conexión a la base de datos. Por favor, revise el archivo de configuración.");
}
?>