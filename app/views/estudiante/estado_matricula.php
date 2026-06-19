<?php
// Vista de Estado de Matrícula y carga de vouchers - estudiante/estado_matricula.php

try {
    $db = Database::getConnection();
    $estudiante_id = $_SESSION['user_id'];

    // 1. Obtener la lista de todos los vouchers de matrícula cargados por este estudiante
    $stmt = $db->prepare("
        SELECT * FROM vouchers 
        WHERE usuario_id = ? AND tipo = 'Matricula' 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$estudiante_id]);
    $vouchers = $stmt->fetchAll();

    // 2. Obtener los requisitos presentados (por si desea ver sus archivos validados)
    $stmt = $db->prepare("SELECT * FROM requisitos WHERE usuario_id = ? ORDER BY created_at DESC LIMIT 1");
    $stmt->execute([$estudiante_id]);
    $requisitos = $stmt->fetch();

} catch (PDOException $e) {
    $error_msg = "Error al obtener historial de pagos: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Estado de Matrícula y Pagos</h1>
            <p class="text-muted mb-0">Revisa tu historial de vouchers validados y sube tu pago del nuevo período semestral.</p>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Historial de Vouchers subidos -->
        <div class="col-lg-7 mb-4">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-history me-2"></i>Historial de Vouchers Semestrales</h6>
                </div>
                <div class="card-body">
                    <?php if (empty($vouchers)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Aún no se registran pagos de matrícula</h5>
                            <p class="text-muted small">Carga tu primer comprobante de derecho de matrícula en el formulario lateral.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha Registro</th>
                                        <th>Monto</th>
                                        <th>N° Operación</th>
                                        <th>Fecha Pago</th>
                                        <th class="text-center">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vouchers as $v): ?>
                                        <tr>
                                            <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($v['created_at'])); ?></td>
                                            <td class="fw-bold">S/. <?php echo number_format($v['monto'], 2); ?></td>
                                            <td><code><?php echo htmlspecialchars($v['numero_operacion']); ?></code></td>
                                            <td><?php echo date('d/m/Y', strtotime($v['fecha_pago'])); ?></td>
                                            <td class="text-center">
                                                <span class="badge <?php 
                                                    echo ($v['estado'] === 'Pendiente') ? 'badge-pendiente' : 
                                                        (($v['estado'] === 'Aprobado') ? 'badge-aprobado' : 'badge-rechazado'); 
                                                ?> px-3 py-1.5"><?php echo htmlspecialchars($v['estado']); ?></span>
                                                <?php if (!empty($v['observaciones'])): ?>
                                                    <div class="small text-muted mt-1" style="font-size: 0.75rem;">
                                                        Obs: <em><?php echo htmlspecialchars($v['observaciones']); ?></em>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($requisitos): ?>
                <!-- Panel de Requisitos Validados -->
                <div class="card shadow-sm sam-card mt-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="m-0 font-weight-bold text-success fw-bold"><i class="fas fa-folder-open me-2"></i>Mis Requisitos de Matrícula Históricos</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Documentación académica validada por Admisión al momento de tu ingreso:</p>
                        <div class="d-flex gap-3 flex-wrap">
                            <span class="badge bg-light text-dark p-2 border"><i class="fas fa-file-pdf text-danger me-1"></i> Certificado Aprobado</span>
                            <span class="badge bg-light text-dark p-2 border"><i class="fas fa-file-pdf text-danger me-1"></i> Partida Aprobado</span>
                            <span class="badge bg-light text-dark p-2 border"><i class="fas fa-id-card text-primary me-1"></i> Copia DNI Aprobado</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Formulario para subir nuevo voucher -->
        <div class="col-lg-5 mb-4">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-upload me-2"></i>Registrar Pago de Matrícula (Nuevo Semestre)</h6>
                </div>
                <div class="card-body">
                    <form action="index.php?route=estudiante_subir_voucher" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="monto" class="form-label fw-semibold text-secondary">Monto Depósito (S/.) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="monto" name="monto" value="200.00" required>
                            <div class="form-text"><small class="text-muted">El costo de matrícula semestral regular es de S/. 200.00.</small></div>
                        </div>

                        <div class="mb-3">
                            <label for="numero_operacion" class="form-label fw-semibold text-secondary">N° de Operación <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_operacion" name="numero_operacion" placeholder="Ej: OP-857412" required>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_pago" class="form-label fw-semibold text-secondary">Fecha de Depósito <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="voucher_archivo" class="form-label fw-semibold text-secondary">Adjuntar Imagen o PDF del Voucher <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="voucher_archivo" name="voucher_archivo" accept="image/*,application/pdf" required>
                            <div class="form-text"><small class="text-muted">Asegúrate de que los campos del comprobante sean claramente legibles.</small></div>
                        </div>

                        <button type="submit" class="btn sam-btn-primary w-100 py-2.5 shadow">
                            <i class="fas fa-paper-plane me-2"></i> Cargar Pago y Enviar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
