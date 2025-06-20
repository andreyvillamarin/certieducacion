<?php
// admin/history.php (NUEVO ARCHIVO)
$page_title = 'Historial de Certificados';
include 'includes/header.php';

$search_text = $_GET['search_text'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$sql = "SELECT c.id, c.course_name, c.issue_date, c.pdf_path, s.name as student_name, s.identification 
        FROM certificates c 
        JOIN students s ON c.student_id = s.id 
        WHERE 1=1";

$params = [];
if (!empty($search_text)) {
    $sql .= " AND (s.name LIKE ? OR s.identification LIKE ? OR c.course_name LIKE ?)";
    $params[] = "%$search_text%";
    $params[] = "%$search_text%";
    $params[] = "%$search_text%";
}
if (!empty($start_date)) {
    $sql .= " AND c.issue_date >= ?";
    $params[] = $start_date;
}
if (!empty($end_date)) {
    $sql .= " AND c.issue_date <= ?";
    $params[] = $end_date;
}
$sql .= " ORDER BY c.issue_date DESC, s.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$certificates = $stmt->fetchAll();
?>

<h1 class="mt-4">Historial de Certificados</h1>
<p>Busca y filtra todos los certificados generados en la plataforma.</p>

<div class="card shadow-sm mb-4">
    <div class="card-header"><h5 class="mb-0">Filtros de Búsqueda</h5></div>
    <div class="card-body">
        <form action="history.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-6"><label class="form-label">Buscar</label><input type="text" class="form-control" name="search_text" value="<?php echo htmlspecialchars($search_text); ?>"></div>
            <div class="col-md-2"><label class="form-label">Desde</label><input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>"></div>
            <div class="col-md-2"><label class="form-label">Hasta</label><input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Buscar</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><h5 class="mb-0">Resultados</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark"><tr><th>Estudiante</th><th>Identificación</th><th>Curso</th><th>Fecha Emisión</th><th>Acciones</th></tr></thead>
                <tbody>
                    <?php if (empty($certificates)): ?>
                        <tr><td colspan="5" class="text-center">No se encontraron certificados.</td></tr>
                    <?php else: foreach ($certificates as $cert): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cert['student_name']); ?></td>
                            <td><?php echo htmlspecialchars($cert['identification']); ?></td>
                            <td><?php echo htmlspecialchars($cert['course_name']); ?></td>
                            <td><?php echo date("d/m/Y", strtotime($cert['issue_date'])); ?></td>
                            <td><a href="../<?php echo htmlspecialchars($cert['pdf_path']); ?>" class="btn btn-sm btn-info" target="_blank" title="Ver PDF"><i class="fas fa-eye"></i></a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>