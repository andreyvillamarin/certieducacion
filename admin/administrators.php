<?php
// admin/administrators.php (Versi�n Corregida UTF-8)
$page_title = 'Gesti�n de Administradores';
$page_specific_js = 'js/administrators.js';
include 'includes/header.php';

// Solo superadmin puede ver esta p�gina
if ($admin_role !== 'superadmin') {
    echo '<div class="alert alert-danger">Acceso denegado. Esta secci�n es solo para superadministradores.</div>';
    include 'includes/footer.php';
    exit;
}

// Procesar eliminaci�n de administrador
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_admin') {
    $admin_id_to_delete = $_POST['admin_id'];
    if ($admin_id_to_delete != $_SESSION['admin_id']) {
        try {
            $stmt = $pdo->prepare("DELETE FROM admins WHERE id = ?");
            $stmt->execute([$admin_id_to_delete]);
            $_SESSION['notification'] = ['type' => 'success', 'message' => 'Administrador eliminado correctamente.'];
        } catch (PDOException $e) {
            $_SESSION['notification'] = ['type' => 'danger', 'message' => 'Error al eliminar el administrador.'];
        }
    } else {
        $_SESSION['notification'] = ['type' => 'danger', 'message' => 'No puedes eliminar tu propia cuenta.'];
    }
    header("Location: administrators.php");
    exit;
}

// Obtener lista de administradores
$stmt = $pdo->query("SELECT id, username, role FROM admins ORDER BY username ASC");
$admins = $stmt->fetchAll();

$notification = '';
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}
?>

<h1 class="mt-4">Gesti�n de Administradores</h1>
<p>Crear, editar y eliminar cuentas de administrador.</p>

<?php if ($notification): ?>
<div class="alert alert-<?php echo htmlspecialchars($notification['type']); ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($notification['message']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Cuentas</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i class="fas fa-plus me-1"></i> Agregar Administrador
        </button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th class="text-end">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admins as $admin_user): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($admin_user['username']); ?></td>
                        <td>
                            <span class="badge bg-<?php echo $admin_user['role'] === 'superadmin' ? 'success' : 'secondary'; ?>">
                                <?php echo htmlspecialchars($admin_user['role']); ?>
                            </span>
                        </td>
                        <td class="text-end">
                            <?php if ($_SESSION['admin_id'] != $admin_user['id']): ?>
                                <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#editAdminModal" 
                                        data-id="<?php echo $admin_user['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteAdminModal" 
                                        data-id="<?php echo $admin_user['id']; ?>" 
                                        data-username="<?php echo htmlspecialchars($admin_user['username']); ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-muted">Tu cuenta</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Agregar Administrador -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addAdminForm" action="ajax_admin_handler.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="add_admin">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Contrase�a</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar Administrador -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editAdminForm" action="ajax_admin_handler.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="action" value="update_admin">
                    <input type="hidden" id="edit_admin_id" name="admin_id">
                    <div class="mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="edit_username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nueva Contrase�a</label>
                        <input type="password" class="form-control" name="password" 
                               placeholder="Dejar en blanco para no cambiar">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rol</label>
                        <select class="form-select" id="edit_role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="superadmin">Superadmin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Confirmar Eliminaci�n -->
<div class="modal fade" id="deleteAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminaci�n</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                �Est�s seguro de que deseas eliminar a <strong id="admin-username-to-delete"></strong>?
            </div>
            <div class="modal-footer">
                <form action="administrators.php" method="POST">
                    <input type="hidden" name="action" value="delete_admin">
                    <input type="hidden" id="admin_id_to_delete" name="admin_id">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">S�, Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>