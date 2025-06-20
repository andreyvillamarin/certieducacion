<?php
// admin/generate_certificate_handler.php (ACTUALIZADO CON RUTA DEFINITIVA)
require_once '../config.php';
require_once ROOT_PATH . '/includes/database.php';
require_once ROOT_PATH . '/libs/PHPQRCode/qrlib.php';
require_once ROOT_PATH . '/libs/TCPDF/tcpdf.php';

if (!isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['student_ids'])) {
    $_SESSION['notification'] = ['type' => 'danger', 'message' => 'No se seleccionó ningún estudiante.'];
    header('Location: certificates.php');
    exit;
}

$student_ids = $_POST['student_ids'];
$course_name = trim($_POST['course_name'] ?? '');
$duration = trim($_POST['duration'] ?? 'XX');
$issue_date_str = $_POST['issue_date'] ?? null;
// El nombre del director ahora es estático en la plantilla.
$signature_file = $_POST['signature_file'] ?? 'director.png';

$success_count = 0; $error_count = 0;

// ... (Clase MYPDF no cambia) ...
class MYPDF extends TCPDF {
    public $backgroundImage;
    public function Header() {
        if ($this->backgroundImage) {
            $this->Image($this->backgroundImage, 0, 0, $this->w, $this->h, '', '', '', false, 300, '', false, false, 0);
        }
    }
}


foreach ($student_ids as $student_id) {
    try {
        $stmt = $pdo->prepare("SELECT name, identification FROM students WHERE id = ?");
        $stmt->execute([$student_id]);
        $student = $stmt->fetch();
        if (!$student) continue;

        $validation_code = 'CERT-' . strtoupper(uniqid()) . '-' . date('Y');
        $pdf_filename = 'certificado-' . $student['identification'] . '-' . time() . '.pdf';
        $qr_temp_path = '../uploads/qr_' . $validation_code . '.png';
        $pdf_save_path = '../certificates_generated/' . $pdf_filename;
        $pdf_url_path = 'certificates_generated/' . $pdf_filename;
        $base_server_path = dirname(__DIR__);
        
        QRcode::png($validation_code, $qr_temp_path, QR_ECLEVEL_L, 3);
        $template_html = file_get_contents('certificate_template.php');
        
        $date = new DateTime($issue_date_str, new DateTimeZone('America/Bogota'));
        $months_es = ["","enero","febrero","marzo","abril","mayo","junio","julio","agosto","septiembre","octubre","noviembre","diciembre"];
        $issue_date_formatted = $date->format('d') . " días del mes de " . $months_es[(int)$date->format('n')] . " de " . $date->format('Y');
        
        $replacements = [
            '{{student_name}}' => htmlspecialchars($student['name']),
            '{{student_identification}}' => number_format($student['identification'], 0, ',', '.'),
            '{{course_name}}' => htmlspecialchars($course_name),
            '{{duration}}' => htmlspecialchars($duration),
            '{{issue_date}}' => $issue_date_formatted,
            '{{validation_code}}' => $validation_code,
            // El nombre del director se elimina de los reemplazos dinámicos.
            // Se puede dejar un espacio en blanco o un nombre fijo si se quiere en la plantilla
            '{{director_name}}' => '&nbsp;', // Espacio para que la línea no se colapse
            '{{qr_code_path}}' => $qr_temp_path,
            '{{logo_path}}' => $base_server_path . '/assets/img/logo_comfamiliar.png',
            '{{vigilado_logo_path}}' => $base_server_path . '/assets/img/logo_supersubsidio.png',
            '{{signature_path}}' => $base_server_path . '/assets/img/signatures/' . $signature_file
        ];
        $final_html = str_replace(array_keys($replacements), array_values($replacements), $template_html);

        $pdf = new TCPDF('P', 'px', 'A4', true, 'UTF-8', false);
        $pdf->SetMargins(0, 0, 0, true);
        $pdf->SetAutoPageBreak(false, 0);
        $pdf->setPrintHeader(false); $pdf->setPrintFooter(false);
        $pdf->AddPage();
        $pdf->writeHTML($final_html, true, false, true, false, '');
        $pdf->Output($pdf_save_path, 'F');
        
        $sql = "INSERT INTO certificates (student_id, course_name, issue_date, validation_code, pdf_path) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $pdo->prepare($sql);
        $stmt_insert->execute([$student_id, $course_name, $issue_date_str, $validation_code, $pdf_url_path]);
        
        unlink($qr_temp_path);
        $success_count++;

    } catch (Exception $e) {
        $error_count++;
        error_log("Error al generar certificado masivo: " . $e->getMessage());
    }
}
$message = "Proceso completado. Certificados generados: $success_count. Errores: $error_count.";
$_SESSION['notification'] = ['type' => $error_count > 0 ? 'warning' : 'success', 'message' => $message];
header('Location: certificates.php');
exit;