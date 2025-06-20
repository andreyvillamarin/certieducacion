<?php
// admin/ajax_admin_handler.php (Archivo Nuevo)

require_once '../includes/database.php';
header('Content-Type: application/json');

// Seguridad: solo superadmin puede ejecutar estas acciones
if (!isset($_SESSION['admin_role']) || $_SESSION['admin_role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Acción no autorizada.']);
    exit;
}

function send_response($success, $message = '', $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'add_admin':
    case 'update_admin':
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $role = $_POST['role'];
        $admin_id = $_POST['admin_id'] ?? 0;

        if (empty($username) || empty($role) || ($action === 'add_admin' && empty($password))) {
            send_response(false, 'Nombre de usuario, contraseña y rol son requeridos.');
        }

        try {
            // Verificar duplicados de username
            $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ?");
            $stmt->execute([$username, $admin_id]);
            if ($stmt->fetch()) {
                send_response(false, 'El nombre de usuario ya existe.');
            }

            if ($action === 'add_admin') {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admins (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed_password, $role]);
                send_response(true, 'Administrador agregado correctamente.');
            } else {
                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE admins SET username = ?, password = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $hashed_password, $role, $admin_id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE admins SET username = ?, role = ? WHERE id = ?");
                    $stmt->execute([$username, $role, $admin_id]);
                }
                send_response(true, 'Administrador actualizado correctamente.');
            }
        } catch (PDOException $e) {
            send_response(false, 'Error en la base de datos.');
        }
        break;

    case 'get_admin':
        $admin_id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT id, username, role FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $admin = $stmt->fetch();
        send_response(true, '', $admin);
        break;

    case 'delete_admin':
        $admin_id = $_POST['admin_id'] ?? 0;
        if ($admin_id == $_SESSION['admin_id']) {
            send_response(false, 'No puedes eliminar tu propia cuenta.');
        }
        $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
        $stmt->execute([$admin_id]);
        $_SESSION['notification'] = ['type' => 'success', 'message' => 'Administrador eliminado correctamente.'];
        send_response(true);
        break;

    default:
        send_response(false, 'Acción no válida.');
        break;
}