<?php
// Vista de Gestión de Usuarios - op/gestion_usuarios.php

try {
    $db = Database::getConnection();

    // 1. Obtener todos los usuarios con sus respectivas carreras (si tienen)
    $stmt = $db->query("
        SELECT u.*, p.nombre as carrera_nombre 
        FROM usuarios u 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id 
        ORDER BY u.role, u.apellidos, u.nombres
    ");
    $usuarios = $stmt->fetchAll();

    // 2. Obtener programas de estudio para el combo box
    $stmt = $db->query("SELECT * FROM programas_estudio ORDER BY nombre ASC");
    $programas = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error al conectar a la base de datos: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Usuarios</h1>
            <p class="text-muted mb-0">Crea, edita, desactiva y elimina cualquier usuario del sistema.</p>
        </div>
        <button class="btn sam-btn-primary shadow" data-bs-toggle="modal" data-bs-target="#userModal" onclick="clearForm()">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
        </button>
    </div>

    <!-- Tabla de Usuarios -->
    <div class="card shadow-sm sam-card">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-users me-2"></i>Listado de Cuentas de Acceso</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable w-100">
                    <thead class="table-light">
                        <tr>
                            <th>DNI / Usuario</th>
                            <th>Nombres y Apellidos</th>
                            <th>Rol</th>
                            <th>Carrera</th>
                            <th>Código/Info</th>
                            <th>Contraseña (Plana)</th>
                            <th>Estado</th>
                            <th class="text-center" style="width: 10%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></div>
                                    <small class="text-muted">DNI: <?php echo htmlspecialchars($u['dni'] ?? '-'); ?></small>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($u['apellidos'] . ', ' . $u['nombres']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($u['email'] ?? '-'); ?></small>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($u['role'] === 'OP') ? 'bg-danger' : 
                                            (($u['role'] === 'Admin') ? 'bg-primary' : 
                                            (($u['role'] === 'Estudiante') ? 'bg-success' : 'bg-warning text-dark')); 
                                    ?>">
                                        <?php echo htmlspecialchars($u['role']); ?>
                                    </span>
                                </td>
                                <td class="small"><?php echo htmlspecialchars($u['carrera_nombre'] ?? 'Ninguno'); ?></td>
                                <td>
                                    <?php if ($u['role'] === 'Estudiante'): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($u['codigo_matricula'] ?? 'Sin código'); ?></span>
                                    <?php elseif ($u['role'] === 'Postulante'): ?>
                                        <small class="text-muted">Estado Adm.: <br><strong><?php echo htmlspecialchars($u['estado']); ?></strong></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Contraseñas en texto plano mostradas directamente por requerimiento -->
                                    <code class="text-dark fw-bold"><?php echo htmlspecialchars($u['password']); ?></code>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($u['estado'] === 'Activo' || $u['estado'] === 'Matriculado' || $u['estado'] === 'Apto Examen' || $u['estado'] === 'Ingresante') ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo htmlspecialchars($u['estado'] ?? 'Activo'); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" 
                                                title="Editar" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#userModal" 
                                                onclick="loadUser(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="index.php?route=op_delete_user" method="POST" class="d-inline confirm-action" data-confirm-msg="¿Realmente desea eliminar esta cuenta? Esta acción borrará todas sus relaciones físicas de vouchers y notas.">
                                            <input type="hidden" name="id" value="<?php echo $u['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear / Editar Usuario -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="userModalLabel"><i class="fas fa-user-edit me-2"></i> Datos del Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=op_save_user" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="user_id" name="id" value="0">
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="username" class="form-label fw-bold">Nombre Usuario / DNI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_username" name="username" required>
                        </div>
                        <div class="col-md-4">
                            <label for="password" class="form-label fw-bold">Contraseña <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_password" name="password" placeholder="Asignar contraseña" required>
                            <small class="text-muted form-text" id="passHelp">Texto plano.</small>
                        </div>
                        <div class="col-md-4">
                            <label for="role" class="form-label fw-bold">Rol de Acceso <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_role" name="role" required onchange="toggleRoleFields()">
                                <option value="" disabled selected>Seleccione...</option>
                                <option value="OP">OP (SuperAdmin)</option>
                                <option value="Admin">Administrador</option>
                                <option value="Estudiante">Estudiante</option>
                                <option value="Postulante">Postulante</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombres" class="form-label fw-bold">Nombres <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_nombres" name="nombres" required>
                        </div>
                        <div class="col-md-6">
                            <label for="apellidos" class="form-label fw-bold">Apellidos <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="user_apellidos" name="apellidos" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="dni" class="form-label fw-bold">DNI</label>
                            <input type="text" class="form-control valida-dni" id="user_dni" name="dni">
                        </div>
                        <div class="col-md-4">
                            <label for="email" class="form-label fw-bold">Correo Electrónico</label>
                            <input type="email" class="form-control" id="user_email" name="email">
                        </div>
                        <div class="col-md-4">
                            <label for="celular" class="form-label fw-bold">Celular</label>
                            <input type="text" class="form-control" id="user_celular" name="celular">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="direccion" class="form-label fw-bold">Dirección</label>
                        <input type="text" class="form-control" id="user_direccion" name="direccion">
                    </div>

                    <div class="row mb-3" id="additional_fields" style="display: none;">
                        <div class="col-md-6">
                            <label for="programa_id" class="form-label fw-bold">Programa de Estudio</label>
                            <select class="form-select" id="user_programa_id" name="programa_id">
                                <option value="">Ninguno</option>
                                <?php foreach ($programas as $p): ?>
                                    <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="estado" class="form-label fw-bold">Estado</label>
                            <select class="form-select" id="user_estado" name="estado">
                                <option value="Activo">Activo (OP/Admin)</option>
                                <option value="Inactivo">Inactivo (Desactivado)</option>
                                <option value="Registrado">Registrado (Postulante)</option>
                                <option value="Pago Pendiente">Pago Pendiente (Postulante)</option>
                                <option value="Pago Rechazado">Pago Rechazado (Postulante)</option>
                                <option value="Apto Examen">Apto Examen (Postulante)</option>
                                <option value="Ingresante">Ingresante (Postulante)</option>
                                <option value="No Ingresó">No Ingresó (Postulante)</option>
                                <option value="Requisitos Subidos">Requisitos Subidos (Postulante)</option>
                                <option value="Matriculado">Matriculado (Estudiante)</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#userModal">Cerrar</button>
                    <button type="submit" class="btn sam-btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Función para limpiar campos al crear un nuevo usuario
function clearForm() {
    document.getElementById('user_id').value = '0';
    document.getElementById('user_username').value = '';
    document.getElementById('user_password').value = '';
    document.getElementById('user_password').required = true;
    document.getElementById('passHelp').textContent = "Contraseña en texto plano.";
    document.getElementById('user_role').value = '';
    document.getElementById('user_nombres').value = '';
    document.getElementById('user_apellidos').value = '';
    document.getElementById('user_dni').value = '';
    document.getElementById('user_email').value = '';
    document.getElementById('user_celular').value = '';
    document.getElementById('user_direccion').value = '';
    document.getElementById('user_programa_id').value = '';
    document.getElementById('user_estado').value = 'Activo';
    
    toggleRoleFields();
}

// Cargar datos del usuario en el formulario para editar
function loadUser(user) {
    document.getElementById('user_id').value = user.id;
    document.getElementById('user_username').value = user.username;
    document.getElementById('user_password').value = ''; // En edición no es obligatorio reescribir la contraseña
    document.getElementById('user_password').required = false;
    document.getElementById('passHelp').textContent = "Dejar en blanco para mantener la contraseña actual (" + user.password + ").";
    document.getElementById('user_role').value = user.role;
    document.getElementById('user_nombres').value = user.nombres;
    document.getElementById('user_apellidos').value = user.apellidos;
    document.getElementById('user_dni').value = user.dni || '';
    document.getElementById('user_email').value = user.email || '';
    document.getElementById('user_celular').value = user.celular || '';
    document.getElementById('user_direccion').value = user.direccion || '';
    document.getElementById('user_programa_id').value = user.programa_id || '';
    document.getElementById('user_estado').value = user.estado || 'Activo';

    toggleRoleFields();
}

// Muestra u oculta campos adicionales (como Carrera y Estados) si el rol lo requiere
function toggleRoleFields() {
    const role = document.getElementById('user_role').value;
    const additionalFields = document.getElementById('additional_fields');
    
    if (role === 'Estudiante' || role === 'Postulante') {
        additionalFields.style.display = 'flex';
    } else {
        additionalFields.style.display = 'none';
    }
}
</script>
