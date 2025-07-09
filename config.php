<?php
// config.php (ACTUALIZADO CON RUTA RAÍZ)

// --- NUEVO: Definición de la ruta raíz del proyecto ---
// Esto hace que la inclusión de archivos sea robusta y a prueba de errores.
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

// -- 1. CONFIGURACIÓN DE LA BASE DE DATOS --
define('DB_HOST', 'localhost');
define('DB_NAME', 'qdosnetw_certieducacion');
define('DB_USER', 'qdosnetw_webmaster');
define('DB_PASS', 'tRVy8pvXVAz8');
define('DB_CHARSET', 'utf8mb4');

// -- 2. CONFIGURACIÓN GENERAL DE LA APLICACIÓN --
define('BASE_URL', 'https://qdos.network/demos/certieducacion/');
define('WHATSAPP_SUPPORT_NUMBER', '573204615527');

// -- 3. CONFIGURACIÓN DE ENVÍO DE CORREO (BREVO SMTP) --
define('BREVO_SMTP_HOST', 'smtp-relay.brevo.com');
define('BREVO_SMTP_PORT', 587);
define('BREVO_SMTP_USER', '');
define('BREVO_SMTP_KEY', '');
define('SMTP_FROM_EMAIL', '');
define('SMTP_FROM_NAME', 'Comfamiliar');

// -- 4. CONFIGURACIÓN DE ENVÍO DE SMS (ALTIRIA) --
define('ALTIRIA_LOGIN', '');
define('ALTIRIA_PASSWORD', '');
define('ALTIRIA_SENDER_ID', 'Comfamiliar');

// -- Configuración Interna (No modificar) --
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Bogota');
?>