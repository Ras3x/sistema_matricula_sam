<?php

try {
    $db = Database::getConnection();

    // Estadísticas de Negocio
    $total_postulantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'Postulante'")->fetchColumn();
    $total_ingresantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'Postulante' AND estado = 'Ingresante'")->fetchColumn();
    $total_matriculados = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'Estudiante' AND estado = 'Matriculado'")->fetchColumn();
    
    // Vouchers
    $vouchers_pendientes = $db->query("SELECT COUNT(*) FROM vouchers WHERE estado = 'Pendiente'")->fetchColumn();
    $vouchers_aprobados = $db->query("SELECT COUNT(*) FROM vouchers WHERE estado = 'Aprobado'")->fetchColumn();
    
    // Conteo por carrera
    $stmt = $db->query("
        SELECT p.nombre, COUNT(u.id) as total 
        FROM programas_estudio p 
        LEFT JOIN usuarios u ON u.programa_id = p.id AND u.role = 'Estudiante'
        GROUP BY p.nombre
        ORDER BY total DESC
    ");
    $carreras_stats = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error al cargar información administrativa: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Panel de Administración</h1>
            <p class="text-muted mb-0">Gestión académica de admisiones, matrículas y carga de notas.</p>
        </div>
        <div class="text-end text-muted small">
            <i class="far fa-calendar-alt me-1"></i> Período Académico: <strong>2026 - I</strong>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Tarjetas Informativas -->
    <div class="row g-4 mb-4">
        <!-- Tarjeta Postulantes -->
        <div class="col-md-3">
            <div class="card shadow-sm sam-card border-0 border-start border-4 border-info h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1" style="font-size: 0.75rem;">Total Postulantes</div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo $total_postulantes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-friends fa-2x text-info opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta Ingresantes -->
        <div class="col-md-3">
            <div class="card shadow-sm sam-card border-0 border-start border-4 border-warning h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1" style="font-size: 0.75rem;">Ingresantes por Matricular</div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo $total_ingresantes; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-door-open fa-2x text-warning opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta Estudiantes Matriculados -->
        <div class="col-md-3">
            <div class="card shadow-sm sam-card border-0 border-start border-4 border-success h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1" style="font-size: 0.75rem;">Estudiantes Matriculados</div>
                            <div class="h3 mb-0 fw-bold text-dark"><?php echo $total_matriculados; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-graduation-cap fa-2x text-success opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta Vouchers por Revisar -->
        <div class="col-md-3">
            <div class="card shadow-sm sam-card border-0 border-start border-4 border-danger h-100 py-2">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1" style="font-size: 0.75rem;">Vouchers Pendientes</div>
                            <div class="h3 mb-0 fw-bold text-dark">
                                <?php echo $vouchers_pendientes; ?>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-danger opacity-40"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Acciones Directas (Atajos) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm sam-card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-rocket me-2"></i>Acciones Rápidas Académicas</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <a href="index.php?route=validacion_vouchers" class="card card-body text-center text-decoration-none shadow-sm hover-up border-light">
                                <div class="fs-1 text-danger mb-2"><i class="fas fa-wallet"></i></div>
                                <h6 class="fw-bold text-dark mb-1">Validar Vouchers</h6>
                                <span class="badge bg-danger rounded-pill w-50 mx-auto mt-1"><?php echo $vouchers_pendientes; ?> Pendientes</span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="index.php?route=matriculas_estudiantes" class="card card-body text-center text-decoration-none shadow-sm hover-up border-light">
                                <div class="fs-1 text-primary mb-2"><i class="fas fa-user-check"></i></div>
                                <h6 class="fw-bold text-dark mb-1">Matricular Alumnos</h6>
                                <span class="text-muted small mt-1">Admisiones y Requisitos</span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="index.php?route=gestion_notas" class="card card-body text-center text-decoration-none shadow-sm hover-up border-light">
                                <div class="fs-1 text-success mb-2"><i class="fas fa-clipboard-list"></i></div>
                                <h6 class="fw-bold text-dark mb-1">Cargar Calificaciones</h6>
                                <span class="text-muted small mt-1">Registrar Notas</span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <div class="card card-body text-center border-light bg-light">
                                <div class="fs-1 text-info mb-2"><i class="fas fa-landmark"></i></div>
                                <h6 class="fw-bold text-dark mb-0">IESTP SAM</h6>
                                <span class="text-muted small mt-1">Mesa de ayuda: soporte@sam.edu.pe</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Matrícula por Programa de Estudio -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm sam-card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-list-ol me-2"></i>Alumnos por Carrera (Programa de Estudio)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Programa de Estudio</th>
                                    <th class="text-end" style="width: 30%;">Estudiantes Matriculados</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($carreras_stats as $stat): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($stat['nombre']); ?></strong></td>
                                        <td class="text-end">
                                            <span class="badge bg-success rounded-pill px-3"><?php echo $stat['total']; ?> Alumnos</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
