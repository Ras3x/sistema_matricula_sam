<?php
// Vista de Dashboard de Postulante - postulante/dashboard.php

try {
    $db = Database::getConnection();
    $postulante_id = $_SESSION['user_id'];

    // 1. Obtener perfil completo y carrera
    $stmt = $db->prepare("
        SELECT u.*, p.nombre as carrera_nombre 
        FROM usuarios u 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$postulante_id]);
    $perfil = $stmt->fetch();

    // 2. Obtener último voucher de admisión cargado
    $stmt = $db->prepare("
        SELECT * FROM vouchers 
        WHERE usuario_id = ? AND tipo = 'Admision' 
        ORDER BY created_at DESC LIMIT 1
    ");
    $stmt->execute([$postulante_id]);
    $ultimo_voucher = $stmt->fetch();

} catch (PDOException $e) {
    $error_msg = "Error al conectar a la base de datos: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Mi Proceso de Admisión</h1>
            <p class="text-muted mb-0">Portal temporal para postulantes del IESTP SAM.</p>
        </div>
        <div class="text-end">
            <span class="badge bg-secondary p-2">Postulante SAM</span>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Resumen del Proceso según Estado -->
        <div class="col-lg-8 mb-4">
            
            <?php if ($perfil['estado'] === 'Pago Pendiente'): ?>
                <!-- Caso 1: Voucher pendiente de revisión -->
                <div class="card border-start border-4 border-warning shadow-sm sam-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-warning text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px; font-size: 1.8rem;">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-warning mb-1">Pago en Revisión</h4>
                                <p class="text-muted mb-0">Hemos recibido tu voucher de depósito. La oficina de tesorería y admisión está verificando la transacción bancaria.</p>
                            </div>
                        </div>
                        <hr>
                        <table class="table table-sm table-borderless align-middle small mb-0 text-muted">
                            <tr>
                                <td style="width: 25%;">Trámite:</td>
                                <td><strong>Derecho de Examen de Admisión</strong></td>
                            </tr>
                            <tr>
                                <td>Depósito:</td>
                                <td>S/. <?php echo number_format($ultimo_voucher['monto'] ?? 150.00, 2); ?> | N° Operación: <code><?php echo htmlspecialchars($ultimo_voucher['numero_operacion'] ?? '-'); ?></code></td>
                            </tr>
                            <tr>
                                <td>Fecha de Carga:</td>
                                <td><?php echo date('d/m/Y H:i', strtotime($ultimo_voucher['created_at'] ?? 'now')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

            <?php elseif ($perfil['estado'] === 'Pago Rechazado'): ?>
                <!-- Caso 2: Voucher rechazado -->
                <div class="card border-start border-4 border-danger shadow-sm sam-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px; font-size: 1.8rem;">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-danger mb-1">Comprobante de Pago Rechazado</h4>
                                <p class="text-muted mb-0">Tu voucher no pudo ser validado por la administración.</p>
                            </div>
                        </div>
                        <?php if (!empty($ultimo_voucher['observaciones'])): ?>
                            <div class="alert alert-danger py-2 px-3 small">
                                <i class="fas fa-info-circle me-1"></i> Motivo: <strong><?php echo htmlspecialchars($ultimo_voucher['observaciones']); ?></strong>
                            </div>
                        <?php endif; ?>
                        <hr>
                        <p class="small text-muted mb-3">Por favor, vuelve a subir la captura o el voucher legible utilizando un depósito válido.</p>
                        <a href="index.php?route=subir_voucher" class="btn btn-danger btn-sm shadow-sm rounded-pill px-4">
                            <i class="fas fa-upload me-1"></i> Volver a Subir Voucher
                        </a>
                    </div>
                </div>

            <?php elseif ($perfil['estado'] === 'Apto Examen'): ?>
                <!-- Caso 3: Aprobado / Listo para el examen -->
                <div class="card border-start border-4 border-info shadow-sm sam-card mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px; font-size: 1.8rem;">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-info mb-1">¡Inscripción Aprobada! Estado: Apto para Examen</h4>
                                <p class="text-muted mb-0">Tu pago ha sido validado correctamente. Ya estás registrado en el padrón de postulantes.</p>
                            </div>
                        </div>
                        <hr>
                        <p class="mb-3 small">Ya puedes visualizar y descargar tu **Carnet de Postulante** oficial para presentarte al Examen de Admisión.</p>
                        <a href="index.php?route=estado_admision" class="btn btn-info text-white btn-sm shadow-sm rounded-pill px-4" style="background-color: var(--sam-celeste); border-color: var(--sam-celeste);">
                            <i class="fas fa-id-card me-1"></i> Ver Carnet de Postulante
                        </a>
                    </div>
                </div>

                <!-- Info del examen -->
                <div class="card shadow-sm sam-card">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-dark fw-bold"><i class="fas fa-calendar-alt me-2 text-primary"></i>Cronograma del Examen de Admisión</h6>
                    </div>
                    <div class="card-body py-3">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between py-2 border-light">
                                <span><i class="fas fa-calendar-day me-2 text-muted"></i>Fecha de Examen:</span>
                                <strong>Domingo, 28 de Junio de 2026</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2 border-light">
                                <span><i class="fas fa-clock me-2 text-muted"></i>Hora de Ingreso:</span>
                                <strong>07:30 AM - 08:30 AM</strong>
                            </li>
                            <li class="list-group-item d-flex justify-content-between py-2 border-light">
                                <span><i class="fas fa-map-marker-alt me-2 text-muted"></i>Lugar:</span>
                                <strong>Campus Principal IESTP SAM, Palian - Huancayo</strong>
                            </li>
                        </ul>
                    </div>
                </div>

            <?php elseif ($perfil['estado'] === 'Ingresante'): ?>
                <!-- Caso 4: Ingresó al instituto (Pendiente de Requisitos) -->
                <div class="card border-start border-4 border-success shadow-sm sam-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px; font-size: 1.8rem;">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-success mb-1">¡Felicitaciones! Has Ingresado</h4>
                                <p class="text-muted mb-0">Has obtenido una vacante en el programa de estudio de **<?php echo htmlspecialchars($perfil['carrera_nombre']); ?>**.</p>
                            </div>
                        </div>
                        <hr>
                        <p class="mb-3 text-dark">Para formalizar tu matrícula y activar tu código de estudiante oficial, debes subir tus requisitos físicos obligatorios y el voucher de depósito por derecho de matrícula semestral.</p>
                        <a href="index.php?route=estado_admision" class="btn btn-success btn-sm shadow-sm rounded-pill px-4">
                            <i class="fas fa-file-upload me-1"></i> Subir Requisitos y Voucher Matrícula
                        </a>
                    </div>
                </div>

            <?php elseif ($perfil['estado'] === 'Requisitos Subidos'): ?>
                <!-- Caso 5: Requisitos subidos, en proceso de formalización -->
                <div class="card border-start border-4 border-primary shadow-sm sam-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px; font-size: 1.8rem;">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-primary mb-1">Requisitos en Proceso de Matrícula</h4>
                                <p class="text-muted mb-0">Tus documentos académicos (certificado, partida, DNI) y voucher de matrícula semestral están siendo validados por la oficina de secretaría general.</p>
                            </div>
                        </div>
                        <hr>
                        <p class="small text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Tan pronto como la administración apruebe tus documentos, tu cuenta será promovida al rol de **Estudiante**, se te asignará un código y un correo institucional `@sam.edu.pe`. Recibirás un aviso en tu próximo inicio de sesión.</p>
                    </div>
                </div>

            <?php elseif ($perfil['estado'] === 'No Ingresó'): ?>
                <!-- Caso 6: No ingresó -->
                <div class="card border-start border-4 border-secondary shadow-sm sam-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 55px; height: 55px; font-size: 1.8rem;">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <div>
                                <h4 class="fw-bold text-secondary mb-1">Resultados del Examen de Admisión</h4>
                                <p class="text-muted mb-0">Lamentamos informarte que en esta oportunidad no obtuviste una vacante para la carrera de **<?php echo htmlspecialchars($perfil['carrera_nombre']); ?>**.</p>
                            </div>
                        </div>
                        <hr>
                        <p class="small text-muted mb-0">Te agradecemos por tu participación en este proceso de admisión. Te alentamos a seguir preparándote y postular en nuestra próxima convocatoria.</p>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Ficha del Postulante -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-user me-2"></i>Ficha del Postulante</h6>
                </div>
                <div class="card-body py-3">
                    <table class="table table-sm table-borderless align-middle small mb-0">
                        <tr>
                            <td class="text-muted" style="width: 35%;">Postulante:</td>
                            <td class="fw-bold"><?php echo htmlspecialchars($perfil['nombres'] . ' ' . $perfil['apellidos']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">DNI:</td>
                            <td class="fw-bold"><code><?php echo htmlspecialchars($perfil['dni']); ?></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Carrera:</td>
                            <td class="fw-bold text-primary"><?php echo htmlspecialchars($perfil['carrera_nombre']); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Contacto:</td>
                            <td class="fw-bold"><?php echo htmlspecialchars($perfil['celular']); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
