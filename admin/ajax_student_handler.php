<?php
// admin/ajax_student_handler.php
require_once '../includes/database.php';
header('Content-Type: application/json');

// Función para unificar respuestas
function send_response($success, $message = '', $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
    exit;
}

if (!isset($_POST['action'])) {
    send_response(false, 'Acción no definida.');
}

$action = $_POST['action'];

switch ($action) {
    case 'add_student':
    case 'update_student':
        // Validación de datos
        $name = trim($_POST['name'] ?? '');
        $id_num = trim($_POST['identification'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($name) || empty($id_num)) {
            send_response(false, 'Nombre e identificación son requeridos.');
        }

        try {
            // Verificar duplicados por identificación
            $stmt = $pdo->prepare("SELECT id FROM students WHERE identification = ? AND id != ?");
            $student_id = ($action === 'update_student') ? ($_POST['student_id'] ?? 0) : 0;
            $stmt->execute([$id_num, $student_id]);
            if ($stmt->fetch()) {
                send_response(false, 'La identificación ingresada ya pertenece a otro estudiante.');
            }

            if ($action === 'add_student') {
                $sql = "INSERT INTO students (name, identification, phone, email) VALUES (?, ?, ?, ?)";
                $params = [$name, $id_num, $phone, $email];
                $msg = 'Estudiante agregado con éxito.';
            } else {
                $sql = "UPDATE students SET name = ?, identification = ?, phone = ?, email = ? WHERE id = ?";
                $params = [$name, $id_num, $phone, $email, $student_id];
                $msg = 'Estudiante actualizado con éxito.';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            send_response(true, $msg);

        } catch (PDOException $e) {
            error_log("Error en $action: " . $e->getMessage());
            send_response(false, 'Error de base de datos.');
        }
        break;

    case 'get_student':
        if (empty($_POST['id'])) {
            send_response(false, 'ID de estudiante no proporcionado.');
        }
        $stmt = $pdo->prepare("SELECT id, name, identification, phone, email FROM students WHERE id = ?");
        $stmt->execute([$_POST['id']]);
        $student = $stmt->fetch();
        if ($student) {
            send_response(true, '', $student);
        } else {
            send_response(false, 'Estudiante no encontrado.');
        }
        break;
    
    case 'upload_csv':
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
            $file = $_FILES['csv_file']['tmp_name'];
            $handle = fopen($file, "r");
            
            // Validar cabeceras
            $header = fgetcsv($handle, 1000, ",");
            if ($header !== ['nombre', 'identificacion', 'telefono', 'email']) {
                send_response(false, 'Las cabeceras del CSV son incorrectas. Deben ser: nombre,identificacion,telefono,email');
            }

            $added = 0;
            $skipped = 0;
            $pdo->beginTransaction();
            try {
                $stmt_check = $pdo->prepare("SELECT id FROM students WHERE identification = ?");
                $stmt_insert = $pdo->prepare("INSERT INTO students (name, identification, phone, email) VALUES (?, ?, ?, ?)");

                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    // Verificar que la identificación no esté vacía
                    if (empty($data[1])) {
                        $skipped++;
                        continue;
                    }
                    
                    // Verificar si ya existe
                    $stmt_check->execute([$data[1]]);
                    if ($stmt_check->fetch()) {
                        $skipped++;
                        continue;
                    }

                    // Insertar
                    $stmt_insert->execute($data);
                    $added++;
                }
                $pdo->commit();
                send_response(true, "Proceso completado. Estudiantes agregados: $added. Duplicados/omitidos: $skipped.");
            } catch (Exception $e) {
                $pdo->rollBack();
                error_log("Error en carga CSV: " . $e->getMessage());
                send_response(false, 'Ocurrió un error durante la carga masiva.');
            }
            fclose($handle);
        } else {
            send_response(false, 'Error al subir el archivo o archivo no seleccionado.');
        }
        break;
}

?>
