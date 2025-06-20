<?php
// admin/settings_handler.php
require_once '../config.php';

// Seguridad: solo superadmin puede ejecutar esto
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superadmin') {
    die('Acceso denegado.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: settings.php');
    exit;
}

try {
    // Ruta al archivo de configuración
    $configFile = dirname(__DIR__) . '/config.php';
    
    // Leer el contenido actual del archivo
    $configContent = file_get_contents($configFile);
    if ($configContent === false) {
        throw new Exception("No se pudo leer el archivo de configuración.");
    }

    // Iterar sobre los datos del formulario y reemplazar los valores en el contenido
    foreach ($_POST as $key => $value) {
        // Escapar el valor para que sea seguro en la expresión regular
        $escapedValue = addslashes($value);
        // Expresión regular para encontrar la línea de la constante y reemplazar su valor
        $pattern = "/(define\s*\(\s*['\"]" . preg_quote($key, '/') . "['\"]\s*,\s*['\"])(.*?)['\"]\s*\);/";
        $replacement = "$1" . $escapedValue . "'\);";
        
        $configContent = preg_replace($pattern, $replacement, $configContent, 1);
    }
    
    // Escribir el nuevo contenido de vuelta al archivo
    if (file_put_contents($configFile, $configContent) === false) {
        throw new Exception("No se pudo escribir en el archivo de configuración. Verifica los permisos.");
    }

    $_SESSION['notification'] = ['type' => 'success', 'message' => 'La configuración ha sido guardada correctamente.'];

} catch (Exception $e) {
    $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Error al guardar la configuración: ' . $e->getMessage()];
}

header('Location: settings.php');
exit;
