<?php
// Vista de Dashboard de Estudiante - estudiante/dashboard.php

try {
    $db = Database::getConnection();
    $estudiante_id = $_SESSION['user_id'];

    // 1. Obtener perfil completo y nombre de carrera
    $stmt = $db->prepare("
        SELECT u.*, p.nombre as carrera_nombre 
        FROM usuarios u 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$estudiante_id]);
    $perfil = $stmt->fetch();

    // 2. Obtener resumen de notas
    $stmt = $db->prepare("SELECT COUNT(*) as total_cursos, AVG(promedio) as promedio_general FROM notas WHERE estudiante_id = ?");
    $stmt->execute([$estudiante_id]);
    $resumen_notas = $stmt->fetch();

    // 3. Obtener último voucher subido para ver estado
    $stmt = $db->prepare("SELECT * FROM vouchers WHERE usuario_id = ? AND tipo = 'Matricula' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$estudiante_id]);
    $ultimo_voucher = $stmt->fetch();

} catch (PDOException $e) {
    $error_msg = "Error al obtener datos estudiantiles: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Mi Portal Estudiantil</h1>
            <p class="text-muted mb-0">Revisa tu estado académico, calificaciones y comprueba tus pagos de matrícula.</p>
        </div>
        <div class="text-end text-muted small">
            <span class="badge bg-success rounded-pill px-3 py-2"><i class="fas fa-circle me-1 small"></i> Alumno Regular</span>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Ficha de Datos del Estudiante -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm sam-card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-id-card-alt me-2"></i>Información Personal y Académica</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <!-- Foto e Identificador Estudiantil -->
                        <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center bg-light text-primary border border-info border-3" style="width: 90px; height: 90px; font-size: 2.5rem;">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h5 class="fw-bold text-dark mt-3 mb-0"><?php echo htmlspecialchars($perfil['nombres'] . ' ' . $perfil['apellidos']); ?></h5>
                        <span class="badge bg-secondary mt-1"><?php echo htmlspecialchars($perfil['codigo_matricula']); ?></span>
                    </div>

                    <table class="table table-sm table-borderless align-middle" style="font-size: 0.9rem;">
                        <tr>
                            <td class="text-muted" style="width: 35%;">DNI:</td>
                            <td class="fw-bold"><?php echo htmlspecialchars($perfil['dni'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Carrera SAM:</td>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($perfil['carrera_nombre'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Correo Institucional:</td>
                            <td class="fw-bold"><a href="mailto:<?php echo htmlspecialchars($perfil['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($perfil['email']); ?></a></td>
                        </tr>
                        <tr>
                            <td class="text-muted">N° Celular:</td>
                            <td class="fw-bold"><?php echo htmlspecialchars($perfil['celular'] ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dirección:</td>
                            <td class="fw-bold text-muted" style="font-size: 0.85rem;"><?php echo htmlspecialchars($perfil['direccion'] ?? '-'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Estado de Matrícula y Notas -->
        <div class="col-lg-7 mb-4">
            <div class="row g-4 h-100">
                <!-- Estado Matrícula -->
                <div class="col-12">
                    <div class="card shadow-sm sam-card">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-clipboard-check me-2"></i>Estado de Matrícula - Ciclo Actual</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
                                    <i class="fas fa-check-double"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-success mb-1">Matrícula Formalizada</h5>
                                    <p class="text-muted small mb-0">Tu cuenta académica se encuentra activa para el presente semestre académico.</p>
                                </div>
                            </div>
                            
                            <?php if ($ultimo_voucher): ?>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                    <span>Último pago validado (Ref: <code><?php echo htmlspecialchars($ultimo_voucher['numero_operacion']); ?></code>)</span>
                                    <span class="badge bg-success">Aprobado</span>
                                </div>
                            <?php endif; ?>
                            
                            <hr class="mt-2">
                            <a href="index.php?route=estado_matricula" class="btn btn-sm btn-outline-info rounded-pill px-4">
                                <i class="fas fa-receipt me-1"></i> Ver Pagos / Subir Nuevo Voucher
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Resumen Académico -->
                <div class="col-12">
                    <div class="card shadow-sm sam-card mb-0">
                        <div class="card-header bg-white py-3">
                            <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-star me-2"></i>Rendimiento Académico Acumulado</h6>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center text-center">
                                <div class="col-sm-6 border-end">
                                    <span class="text-muted small d-block">Asignaturas con Calificación</span>
                                    <h2 class="fw-bold text-dark mb-0 mt-2"><i class="fas fa-book-reader me-2 text-info"></i><?php echo $resumen_notas['total_cursos'] ?? 0; ?></h2>
                                </div>
                                <div class="col-sm-6">
                                    <span class="text-muted small d-block">Promedio General Ponderado</span>
                                    <?php 
                                        $prom = floatval($resumen_notas['promedio_general'] ?? 0.00); 
                                        $color = ($prom >= 12.5) ? 'text-success' : 'text-danger';
                                    ?>
                                    <h2 class="fw-bold mb-0 mt-2 <?php echo $color; ?>"><i class="fas fa-award me-2"></i><?php echo number_format($prom, 2); ?></h2>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center">
                                <a href="index.php?route=mis_notas" class="btn btn-sm btn-outline-success rounded-pill px-4">
                                    <i class="fas fa-graduation-cap me-1"></i> Ver Historial de Calificaciones
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
