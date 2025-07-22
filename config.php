<?php
// config.php (ACTUALIZADO CON RUTA RAÍZ)

// --- NUEVO: Definición de la ruta raíz del proyecto ---
// Esto hace que la inclusión de archivos sea robusta y a prueba de errores.
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// -- 1. CONFIGURACIÓN DE LA BASE DE DATOS --
// Las credenciales de la base de datos se han movido a un archivo separado.
require_once ROOT_PATH . '/admin/db_config.php';

// -- 2. CONFIGURACIÓN GENERAL DE LA APLICACIÓN --
define('BASE_URL', 'https://qdos.network/demos/certieducacion2/');
define('WHATSAPP_SUPPORT_NUMBER', '573204615527');

// -- 3. CONFIGURACIÓN DE ENVÍO DE CORREO (BREVO SMTP) --
define('BREVO_SMTP_HOST', '');
define('BREVO_SMTP_PORT', );
define('BREVO_SMTP_USER', '');
define('BREVO_SMTP_KEY', '');
define('SMTP_FROM_EMAIL', '');
define('SMTP_FROM_NAME', '');

// -- 4. CONFIGURACIÓN DE ENVÍO DE SMS (ALTIRIA) --
define('ALTIRIA_LOGIN', '');
define('ALTIRIA_PASSWORD', '');
define('ALTIRIA_SENDER_ID', '');

// -- Configuración Interna (No modificar) --
// -- 5. CONFIGURACIÓN DE SEGURIDAD --
// Iniciar sesión de forma segura
if (session_status() === PHP_SESSION_NONE) {
    // Configuración de cookies de sesión para mayor seguridad
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => $cookieParams['lifetime'],
        'path' => $cookieParams['path'],
        'domain' => '',
        'secure' => true, // Solo enviar cookies sobre HTTPS
        'httponly' => true, // Prevenir acceso a cookies desde JavaScript
        'samesite' => 'Lax' // Prevenir ataques CSRF
    ]);
    session_start();
}

// Headers de seguridad para prevenir ataques comunes
header("Content-Security-Policy: frame-ancestors 'self'"); // Previene Clickjacking
header("X-Content-Type-Options: nosniff"); // Previene que el navegador interprete archivos con un tipo MIME incorrecto
header("X-Frame-Options: SAMEORIGIN"); // Alternativa a CSP para prevenir Clickjacking
header("X-XSS-Protection: 1; mode=block"); // Activa el filtro XSS en navegadores antiguos

error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar errores en producción
ini_set('log_errors', 1); // Registrar errores en el log
date_default_timezone_set('America/Bogota');
?>