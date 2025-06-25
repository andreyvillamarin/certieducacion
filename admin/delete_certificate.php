<?php
// admin/delete_certificate.php
include 'includes/header.php'; // Assumes this handles session, auth, and $pdo

// Ensure only admin access (assuming header.php or a session check does this)
// if (!is_admin()) {
//     header('Location: login.php'); // Or some other appropriate action
//     exit;
// }

$deleted_count = 0;
$error_count = 0;
$messages = [];

// --- Function to delete a single certificate ---
function deleteSingleCertificate($pdo, $certificate_id) {
    global $deleted_count, $error_count;

    // Validate ID
    if (!filter_var($certificate_id, FILTER_VALIDATE_INT)) {
        $error_count++;
        return "ID de certificado inválido.";
    }
    $certificate_id = intval($certificate_id);

    try {
        $pdo->beginTransaction();

        // 1. Fetch pdf_path
        $stmt = $pdo->prepare("SELECT pdf_path FROM certificates WHERE id = ?");
        $stmt->execute([$certificate_id]);
        $certificate = $stmt->fetch();

        if (!$certificate) {
            $pdo->rollBack();
            $error_count++;
            return "Certificado no encontrado (ID: $certificate_id).";
        }

        $pdf_file_path = __DIR__ . '/../' . $certificate['pdf_path']; // Assumes pdf_path is relative to project root (parent of admin)

        // 2. Delete record from database
        $stmt_delete = $pdo->prepare("DELETE FROM certificates WHERE id = ?");
        $delete_success = $stmt_delete->execute([$certificate_id]);

        if ($delete_success) {
            // 3. Delete PDF file
            if (file_exists($pdf_file_path)) {
                if (unlink($pdf_file_path)) {
                    $deleted_count++;
                    $pdo->commit();
                    return "Certificado ID $certificate_id eliminado exitosamente.";
                } else {
                    $pdo->rollBack(); // Rollback if file deletion fails
                    $error_count++;
                    return "Error al eliminar el archivo PDF para el certificado ID $certificate_id. La base de datos no fue modificada.";
                }
            } else {
                // If PDF file doesn't exist, still consider DB deletion a success if chosen
                // For now, we'll count it as success as the record is gone.
                // Or, one might choose to log this as a warning.
                $deleted_count++;
                $pdo->commit();
                return "Certificado ID $certificate_id eliminado de la base de datos (el archivo PDF no se encontró).";
            }
        } else {
            $pdo->rollBack();
            $error_count++;
            return "Error al eliminar el certificado ID $certificate_id de la base de datos.";
        }
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error_count++;
        // Log error: error_log("Error deleting certificate ID $certificate_id: " . $e->getMessage());
        return "Error de base de datos al eliminar el certificado ID $certificate_id.";
    }
}

// --- Handle Single Deletion (GET request) ---
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $certificate_id = $_GET['id'];
    $message = deleteSingleCertificate($pdo, $certificate_id);
    if ($deleted_count > 0) {
        header('Location: history.php?message=' . urlencode($message) . '&type=success');
    } else {
        header('Location: history.php?message=' . urlencode($message) . '&type=error');
    }
    exit;
}

// --- Handle Bulk Deletion (POST request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_delete'])) {
    if (isset($_POST['certificate_ids']) && is_array($_POST['certificate_ids'])) {
        $certificate_ids = $_POST['certificate_ids'];
        if (empty($certificate_ids)) {
            header('Location: history.php?message=' . urlencode('No se seleccionaron certificados para eliminar.') . '&type=info');
            exit;
        }

        foreach ($certificate_ids as $id) {
            $result_message = deleteSingleCertificate($pdo, $id);
            // Store individual messages if needed, or just rely on counts
            // For simplicity, we'll just use counts for the final message
        }

        $final_message = "";
        if ($deleted_count > 0) {
            $final_message .= "$deleted_count certificado(s) eliminado(s) exitosamente. ";
        }
        if ($error_count > 0) {
            $final_message .= "$error_count error(es) al eliminar certificados.";
        }

        if ($deleted_count > 0 && $error_count == 0) {
            header('Location: history.php?message=' . urlencode($final_message) . '&type=success');
        } elseif ($deleted_count > 0 && $error_count > 0) {
            header('Location: history.php?message=' . urlencode($final_message) . '&type=warning'); // Partial success
        } elseif ($error_count > 0) {
            header('Location: history.php?message=' . urlencode($final_message ?: 'No se pudieron eliminar los certificados seleccionados.') . '&type=error');
        } else { // Should not happen if IDs were selected but nothing processed
             header('Location: history.php?message=' . urlencode('No se procesó ningún certificado.') . '&type=info');
        }
        exit;

    } else {
        header('Location: history.php?message=' . urlencode('No se seleccionaron certificados para eliminar.') . '&type=info');
        exit;
    }
}

// If accessed directly without proper parameters or method
header('Location: history.php?message=' . urlencode('Acción no válida.') . '&type=error');
exit;
?>
