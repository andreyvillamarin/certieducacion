<?php
// admin/certificates.php (Versión Final Simplificada)
$page_title = 'Generación de Certificados';
$page_specific_js = 'js/certificates.js'; // Se define el JS para el footer
include 'includes/header.php';
require_once '../includes/database.php';

// Obtener la lista de todos los estudiantes para el selector
$stmt_students = $pdo->query("SELECT id, name, identification FROM students ORDER BY name ASC");
$all_students = $stmt_students->fetchAll();

$notification = '';
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}

$stmt_certs = $pdo->query("SELECT c.id, c.course_name, s.name as student_name, c.pdf_path FROM certificates c JOIN students s ON c.student_id = s.id ORDER BY c.id DESC LIMIT 20");
$generated_certificates = $stmt_certs->fetchAll();
?>

<h1 class="mt-4">Generación de Certificados</h1>
<p>Crea y administra los certificados para los estudiantes.</p>

<?php if (!empty($notification)): ?>
<div class="alert alert-<?php echo htmlspecialchars($notification['type']); ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($notification['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Columna para Generación -->
    <div class="col-lg-5 mb-4">
        <div class="card shadow-sm">
            <div class="card-header"><h5 class="mb-0"><i class="fas fa-award me-2"></i>Generar Certificados</h5></div>
            <div class="card-body">
                <form action="generate_certificate_handler.php" method="POST" id="generateCertForm">
                    <div class="mb-3">
                        <label for="course_name" class="form-label">Nombre del Curso / Programa</label>
                        <input type="text" class="form-control" id="course_name" name="course_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="duration" class="form-label">Intensidad Horaria</label>
                        <input type="number" class="form-control" id="duration" name="duration" placeholder="Ej: 40" required>
                    </div>
                    <div class="mb-3">
                        <label for="issue_date" class="form-label">Fecha de Emisión</label>
                        <input type="date" class="form-control" id="issue_date" name="issue_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <!-- El campo de nombre de director ha sido eliminado -->
                    <input type="hidden" name="signature_file" value="director.png">

                    <hr>
                    <div class="mb-3">
                        <label for="student_ids" class="form-label">Seleccionar Estudiantes</label>
                        <div class="mb-2"><input type="text" id="studentSearch" class="form-control" placeholder="Escribe para filtrar estudiantes..."></div>
                        <div class="d-flex gap-2 mb-2">
                            <button type="button" id="selectAllBtn" class="btn btn-outline-secondary btn-sm">Seleccionar Visibles</button>
                            <button type="button" id="deselectAllBtn" class="btn btn-outline-secondary btn-sm">Deseleccionar</button>
                        </div>
                        <select class="form-select" id="student_ids" name="student_ids[]" required multiple size="10">
                            <?php foreach ($all_students as $student): ?>
                                <option value="<?php echo $student['id']; ?>"><?php echo htmlspecialchars($student['name']) . ' (' . htmlspecialchars($student['identification']) . ')'; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="btn-generate">
                        <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                        Generar Certificados
                    </button>
                </form>
            </div>
        </div>
    </div>
    <!-- Columna para Certificados Recientes -->
    <div class="col-lg-7">
         <div class="card shadow-sm">
             <div class="card-header"><h5 class="mb-0"><i class="fas fa-history me-2"></i>Últimos Certificados Generados</h5></div>
             <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>Estudiante</th><th>Curso</th><th class="text-end">Acciones</th></tr></thead>
                        <tbody>
                        <?php if(empty($generated_certificates)): ?>
                            <tr><td colspan="3" class="text-center">No hay certificados generados.</td></tr>
                        <?php else: foreach($generated_certificates as $cert): ?>
                            <tr><td><?php echo htmlspecialchars($cert['student_name']); ?></td><td><?php echo htmlspecialchars($cert['course_name']); ?></td><td class="text-end"><a href="../<?php echo htmlspecialchars($cert['pdf_path']); ?>" class="btn btn-sm btn-info" target="_blank" title="Ver PDF"><i class="fas fa-eye"></i></a></td></tr>
                        <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>