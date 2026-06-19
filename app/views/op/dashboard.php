<?php
// Vista de Dashboard de OP - op/dashboard.php

try {
    $db = Database::getConnection();

    // 1. Estadísticas de Usuarios
    $total_users = $db->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
    $total_ops = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'OP'")->fetchColumn();
    $total_admins = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'Admin'")->fetchColumn();
    $total_estudiantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'Estudiante'")->fetchColumn();
    $total_postulantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'Postulante'")->fetchColumn();
    
    // 2. Vouchers y Logs
    $total_vouchers = $db->query("SELECT COUNT(*) FROM vouchers")->fetchColumn();
    $vouchers_pendientes = $db->query("SELECT COUNT(*) FROM vouchers WHERE estado = 'Pendiente'")->fetchColumn();
    $total_logs = $db->query("SELECT COUNT(*) FROM logs")->fetchColumn();

    // 3. Obtener últimos 10 logs de auditoría
    $stmt = $db->query("
        SELECT l.*, u.username, u.role as user_role 
        FROM logs l 
        LEFT JOIN usuarios u ON l.usuario_id = u.id 
        ORDER BY l.created_at DESC 
        LIMIT 10
    ");
    $recent_logs = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error al obtener estadísticas: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Panel de Control General (OP)</h1>
            <p class="text-muted mb-0">Vista general del rendimiento del sistema y actividades de auditoría.</p>
        </div>
        <div class="text-end text-muted small">
            <i class="far fa-clock me-1"></i> Servidor SAM: <strong><?php echo date('d/m/Y H:i:s'); ?></strong>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas -->
    <div class="row g-4 mb-4">
        <!-- Usuarios -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-primary shadow-sm h-100 py-2 sam-card border-0 border-start border-4 border-info">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1" style="font-size: 0.75rem;">Total Usuarios</div>
                            <div class="h5 mb-0 font-weight-bold text-dark fs-2 fw-bold"><?php echo $total_users; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300 opacity-50 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estudiantes -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-success shadow-sm h-100 py-2 sam-card border-0 border-start border-4 border-success">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.75rem;">Estudiantes Matriculados</div>
                            <div class="h5 mb-0 font-weight-bold text-dark fs-2 fw-bold"><?php echo $total_estudiantes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300 opacity-50 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Postulantes -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-warning shadow-sm h-100 py-2 sam-card border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" style="font-size: 0.75rem;">Postulantes Registrados</div>
                            <div class="h5 mb-0 font-weight-bold text-dark fs-2 fw-bold"><?php echo $total_postulantes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-clock fa-2x text-gray-300 opacity-50 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vouchers Pendientes -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-left-danger shadow-sm h-100 py-2 sam-card border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 0.75rem;">Vouchers Pendientes</div>
                            <div class="h5 mb-0 font-weight-bold text-dark fs-2 fw-bold">
                                <?php echo $vouchers_pendientes; ?> 
                                <small class="text-muted fs-6">/ <?php echo $total_vouchers; ?></small>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-300 opacity-50 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalle de Cuentas y Últimos Logs -->
    <div class="row">
        <!-- Gráfico o Resumen de Cuentas -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-chart-pie me-2"></i>Distribución de Roles</h6>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-2.5">
                            <span><i class="fas fa-user-shield me-2 text-danger"></i>OP (SuperAdmins)</span>
                            <span class="badge bg-danger rounded-pill"><?php echo $total_ops; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-2.5">
                            <span><i class="fas fa-user-tie me-2 text-primary"></i>Administradores</span>
                            <span class="badge bg-primary rounded-pill"><?php echo $total_admins; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-2.5">
                            <span><i class="fas fa-user-graduate me-2 text-success"></i>Estudiantes</span>
                            <span class="badge bg-success rounded-pill"><?php echo $total_estudiantes; ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center border-0 py-2.5">
                            <span><i class="fas fa-user-friends me-2 text-warning"></i>Postulantes</span>
                            <span class="badge bg-warning rounded-pill"><?php echo $total_postulantes; ?></span>
                        </li>
                    </ul>
                    <hr class="my-3">
                    <div class="text-center">
                        <a href="index.php?route=gestion_usuarios" class="btn btn-sm btn-outline-info rounded-pill px-4">
                            <i class="fas fa-users-cog me-1"></i> Administrar Usuarios
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visor de Logs del Sistema -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-history me-2"></i>Logs de Auditoría Recientes</h6>
                    <span class="badge bg-secondary">Total logs: <?php echo $total_logs; ?></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 15%;">Fecha/Hora</th>
                                    <th style="width: 15%;">Usuario</th>
                                    <th style="width: 12%;">Rol</th>
                                    <th style="width: 20%;">Acción</th>
                                    <th class="pe-3" style="width: 38%;">Detalles</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_logs)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No se registran eventos en el sistema.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($recent_logs as $log): ?>
                                        <tr>
                                            <td class="ps-3 text-muted"><?php echo date('d/m H:i:s', strtotime($log['created_at'])); ?></td>
                                            <td><strong><?php echo htmlspecialchars($log['username'] ?? 'Sistema/Anon'); ?></strong></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo ($log['user_role'] === 'OP') ? 'bg-danger' : 
                                                        (($log['user_role'] === 'Admin') ? 'bg-primary' : 'bg-secondary'); 
                                                ?>"><?php echo htmlspecialchars($log['user_role'] ?? '-'); ?></span>
                                            </td>
                                            <td class="text-primary font-weight-bold"><?php echo htmlspecialchars($log['accion']); ?></td>
                                            <td class="text-muted pe-3"><?php echo htmlspecialchars($log['detalles'] ?? ''); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
