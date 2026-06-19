<?php

try {
    $db = Database::getConnection();

    // Obtener todos los vouchers con la información de los usuarios correspondientes
    $stmt = $db->query("
        SELECT v.*, u.nombres, u.apellidos, u.dni, u.role as user_role, p.nombre as carrera_nombre
        FROM vouchers v 
        JOIN usuarios u ON v.usuario_id = u.id 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id
        ORDER BY CASE WHEN v.estado = 'Pendiente' THEN 1 ELSE 2 END, v.created_at DESC
    ");
    $vouchers = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error al obtener vouchers: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Validación de Vouchers de Pago</h1>
            <p class="text-muted mb-0">Revisa los comprobantes subidos por postulantes (admisión) y estudiantes (matrícula).</p>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Pestañas para filtrar rápidamente -->
    <div class="card shadow-sm sam-card">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-clipboard-check me-2"></i>Comprobantes Recibidos</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle datatable w-100">
                    <thead class="table-light">
                        <tr>
                            <th>Fecha Registro</th>
                            <th>Postulante / Estudiante</th>
                            <th>Tipo de Pago</th>
                            <th>Monto</th>
                            <th>N° Operación</th>
                            <th>Fecha Pago</th>
                            <th>Comprobante</th>
                            <th>Estado</th>
                            <th class="text-center" style="width: 15%;">Revisión</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vouchers as $v): ?>
                            <tr>
                                <td class="small text-muted"><?php echo date('d/m/Y H:i', strtotime($v['created_at'])); ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($v['apellidos'] . ', ' . $v['nombres']); ?></strong>
                                    <div class="text-muted small">DNI: <?php echo htmlspecialchars($v['dni'] ?? '-'); ?> | Rol: <span class="badge bg-secondary"><?php echo htmlspecialchars($v['user_role']); ?></span></div>
                                    <div class="small text-info"><?php echo htmlspecialchars($v['carrera_nombre'] ?? ''); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo ($v['tipo'] === 'Admision') ? 'bg-info' : 'bg-primary'; ?>">
                                        <?php echo ($v['tipo'] === 'Admision') ? 'Examen Admisión' : 'Matrícula Semestre'; ?>
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">S/. <?php echo number_format($v['monto'], 2); ?></td>
                                <td><code><?php echo htmlspecialchars($v['numero_operacion']); ?></code></td>
                                <td class="small"><?php echo date('d/m/Y', strtotime($v['fecha_pago'])); ?></td>
                                <td>
                                    <!-- Enlace para abrir imagen del voucher -->
                                    <a href="<?php echo htmlspecialchars($v['archivo_path']); ?>" target="_blank" class="btn btn-xs btn-outline-secondary py-1 px-2 text-decoration-none shadow-sm rounded-pill" style="font-size: 0.8rem;">
                                        <i class="fas fa-image me-1"></i> Ver Imagen
                                    </a>
                                </td>
                                <td>
                                    <span class="badge <?php 
                                        echo ($v['estado'] === 'Pendiente') ? 'badge-pendiente' : 
                                            (($v['estado'] === 'Aprobado') ? 'badge-aprobado' : 'badge-rechazado'); 
                                    ?>">
                                        <?php echo htmlspecialchars($v['estado']); ?>
                                    </span>
                                    <?php if (!empty($v['observaciones'])): ?>
                                        <div class="text-muted small mt-1" style="max-width: 180px; font-size: 0.75rem;">
                                            Obs: <em><?php echo htmlspecialchars($v['observaciones']); ?></em>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($v['estado'] === 'Pendiente'): ?>
                                        <!-- Formulario rápido de validación -->
                                        <button class="btn btn-sm sam-btn-primary py-1 px-3 shadow-sm rounded" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#validaModal" 
                                                onclick="setVoucherId(<?php echo $v['id']; ?>, '<?php echo htmlspecialchars($v['nombres'] . ' ' . $v['apellidos']); ?>', '<?php echo $v['tipo']; ?>', <?php echo $v['monto']; ?>, '<?php echo htmlspecialchars($v['numero_operacion']); ?>', '<?php echo htmlspecialchars($v['archivo_path']); ?>')">
                                            <i class="fas fa-gavel me-1"></i> Evaluar
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted small"><i class="fas fa-lock me-1"></i> Evaluado</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Validar Voucher -->
<div class="modal fade" id="validaModal" tabindex="-1" aria-labelledby="validaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="validaModalLabel"><i class="fas fa-gavel me-2"></i> Evaluación del Comprobante</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=admin_validar_voucher" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="modal_voucher_id" name="voucher_id" value="0">
                    
                    <div class="row">
                        <!-- Detalles del Comprobante -->
                        <div class="col-md-5 border-end">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-info-circle me-1"></i>Detalles del Depósito</h6>
                            <table class="table table-sm table-borderless align-middle" style="font-size: 0.85rem;">
                                <tr>
                                    <td><strong>Usuario:</strong></td>
                                    <td><span id="modal_usuario_nombre"></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Trámite:</strong></td>
                                    <td><span id="modal_tipo_voucher" class="badge bg-info"></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Monto:</strong></td>
                                    <td><strong>S/. <span id="modal_monto"></span></strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Operación:</strong></td>
                                    <td><code id="modal_operacion"></code></td>
                                </tr>
                            </table>
                            
                            <hr>
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold">Decisión <span class="text-danger">*</span></label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="estado" id="estado_aprobar" value="Aprobado" checked>
                                        <label class="form-check-label text-success fw-bold" for="estado_aprobar">
                                            <i class="fas fa-check-circle me-1"></i> Aprobar Pago
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="estado" id="estado_rechazar" value="Rechazado">
                                        <label class="form-check-label text-danger fw-bold" for="estado_rechazar">
                                            <i class="fas fa-times-circle me-1"></i> Rechazar Pago
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="observaciones" class="form-label fw-bold">Observaciones / Motivo Rechazo</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3" placeholder="Indicar observaciones si se rechaza el voucher..."></textarea>
                            </div>
                        </div>

                        <!-- Imagen del Comprobante -->
                        <div class="col-md-7 text-center">
                            <h6 class="text-primary fw-bold mb-3">
                                <i class="fas fa-file-image me-1"></i>Vista del Documento Cargado
                                <a id="modal_voucher_link" href="" target="_blank" class="btn btn-xs btn-outline-primary ms-2 py-0 px-2 rounded-pill text-decoration-none" style="font-size: 0.75rem;">
                                    <i class="fas fa-external-link-alt"></i> Ver completo
                                </a>
                            </h6>
                            <div id="modal_voucher_container" class="border rounded p-2 bg-light d-flex align-items-center justify-content-center" style="min-height: 250px; max-height: 350px; overflow: auto;">
                                <img id="modal_voucher_img" src="" alt="Voucher" class="img-fluid rounded shadow-sm" style="max-height: 330px;">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn sam-btn-primary">Guardar Evaluación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cargar datos seleccionados en el modal de evaluación
function setVoucherId(id, usuarioNombre, tipo, monto, operacion, archivoPath) {
    document.getElementById('modal_voucher_id').value = id;
    document.getElementById('modal_usuario_nombre').textContent = usuarioNombre;
    document.getElementById('modal_monto').textContent = Number(monto).toFixed(2);
    document.getElementById('modal_operacion').textContent = operacion;
    document.getElementById('observaciones').value = '';
    
    // Tipo de voucher badge
    const badge = document.getElementById('modal_tipo_voucher');
    badge.textContent = tipo === 'Admision' ? 'Examen Admisión' : 'Matrícula';
    badge.className = tipo === 'Admision' ? 'badge bg-info' : 'badge bg-primary';

    // Setear link externo
    document.getElementById('modal_voucher_link').href = archivoPath;

    // Documento del voucher (Imagen o PDF)
    const container = document.getElementById('modal_voucher_container');
    const isPdf = archivoPath.toLowerCase().endsWith('.pdf');
    if (isPdf) {
        container.innerHTML = `<iframe src="${archivoPath}" width="100%" height="330px" style="border: none;"></iframe>`;
    } else {
        container.innerHTML = `<img id="modal_voucher_img" src="${archivoPath}" alt="Voucher" class="img-fluid rounded shadow-sm" style="max-height: 330px;">`;
    }
}
</script>
