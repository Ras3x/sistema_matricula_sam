<?php
// Vista de Estado de Admisión y Carnet de Postulante - postulante/estado_admision.php

try {
    $db = Database::getConnection();
    $postulante_id = $_SESSION['user_id'];

    // Obtener información del postulante y carrera
    $stmt = $db->prepare("
        SELECT u.*, p.nombre as carrera_nombre 
        FROM usuarios u 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$postulante_id]);
    $perfil = $stmt->fetch();

} catch (PDOException $e) {
    $error_msg = "Error de base de datos: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Trámites y Documentos de Admisión</h1>
            <p class="text-muted mb-0">Descarga tu carnet de postulante o formaliza tus requisitos académicos de ingreso.</p>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="row">
        
        <!-- SECCIÓN 1: CARNET DE POSTULANTE (Visible para Apto Examen, Ingresante, Requisitos Subidos) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm sam-card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-id-card me-2"></i>Carnet del Postulante Virtual</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $estados_carnet = ['Apto Examen', 'Ingresante', 'Requisitos Subidos', 'Matriculado'];
                    if (!in_array($perfil['estado'], $estados_carnet)): 
                    ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-lock fa-3x mb-3 text-secondary"></i>
                            <h5>Carnet no disponible</h5>
                            <p class="small">Tu carnet se generará de forma automática una vez que la administración apruebe tu voucher de admisión.</p>
                        </div>
                    <?php else: ?>
                        <p class="small text-muted mb-4">Imprime tu carnet. Deberás presentarlo el día del examen junto con tu DNI físico original.</p>
                        
                        <!-- Diseño del Carnet de Postulante Virtual -->
                        <div class="carnet-box mb-4 animate-fade-in shadow" id="carnetPrintable">
                            <div class="carnet-header text-center">
                                <img src="assets/img/logo-sam.png" alt="Logo SAM" height="40" class="mb-1 bg-white p-0.5 rounded">
                                <h6 class="fw-bold text-dark mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">IESTP SANTIAGO ANTÚNEZ DE MAYOLO</h6>
                                <span class="badge bg-info text-white fw-bold uppercase py-1 px-3 mt-1" style="font-size: 0.7rem;">CARNET DE POSTULANTE</span>
                            </div>
                            
                            <div class="row align-items-center mt-3">
                                <div class="col-4 d-flex justify-content-center">
                                    <!-- Foto del postulante (Avatar con clase foto) -->
                                    <div class="carnet-photo text-secondary bg-light">
                                        <i class="fas fa-user fa-4x opacity-50"></i>
                                    </div>
                                </div>
                                <div class="col-8">
                                    <table class="table table-sm table-borderless small mb-0" style="font-size: 0.75rem; line-height: 1.2;">
                                        <tr>
                                            <td class="text-muted py-0.5" style="width: 30%;">Nombres:</td>
                                            <td class="fw-bold text-dark py-0.5"><?php echo htmlspecialchars($perfil['nombres']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0.5">Apellidos:</td>
                                            <td class="fw-bold text-dark py-0.5"><?php echo htmlspecialchars($perfil['apellidos']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0.5">DNI:</td>
                                            <td class="fw-bold text-dark py-0.5"><code><?php echo htmlspecialchars($perfil['dni']); ?></code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0.5">Carrera:</td>
                                            <td class="fw-bold text-primary py-0.5"><?php echo htmlspecialchars($perfil['carrera_nombre']); ?></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0.5">Examen:</td>
                                            <td class="fw-bold text-danger py-0.5">28/06/2026</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="text-center mt-3 border-top border-secondary border-dashed pt-2 small text-muted" style="font-size: 0.65rem;">
                                Presentar este carnet firmado el día del examen.
                            </div>
                        </div>

                        <div class="text-center">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill px-4 shadow-sm" onclick="printCarnet()">
                                <i class="fas fa-print me-1"></i> Imprimir Carnet Virtual
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- SECCIÓN 2: CARGA DE REQUISITOS (Visible para Ingresantes) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm sam-card h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-file-upload me-2"></i>Requisitos Académicos e Inscripción Final</h6>
                </div>
                <div class="card-body">
                    
                    <?php if ($perfil['estado'] === 'Ingresante'): ?>
                        <!-- Formulario para subir requisitos -->
                        <div class="alert alert-success py-2 small shadow-xs">
                            <i class="fas fa-award me-1"></i> ¡Obtuviste vacante! Adjunta tus documentos y voucher de matrícula para registrarte como estudiante oficial.
                        </div>
                        
                        <form action="index.php?route=postulante_subir_requisitos" method="POST" enctype="multipart/form-data">
                            
                            <!-- Documentos Académicos -->
                            <h6 class="text-primary border-bottom pb-1 mb-3"><i class="fas fa-folder-open me-1"></i>1. Documentación (PDF o Imagen)</h6>
                            
                            <div class="mb-3">
                                <label for="certificado_estudios" class="form-label fw-bold small text-muted">Certificado Oficial de Estudios Secundarios <span class="text-danger">*</span></label>
                                <input type="file" class="form-control form-control-sm" id="certificado_estudios" name="certificado_estudios" accept="image/*,application/pdf" required>
                            </div>

                            <div class="mb-3">
                                <label for="partida_nacimiento" class="form-label fw-bold small text-muted">Partida de Nacimiento Original <span class="text-danger">*</span></label>
                                <input type="file" class="form-control form-control-sm" id="partida_nacimiento" name="partida_nacimiento" accept="image/*,application/pdf" required>
                            </div>

                            <div class="mb-3">
                                <label for="dni_copia" class="form-label fw-bold small text-muted">Copia Simple de DNI Legible <span class="text-danger">*</span></label>
                                <input type="file" class="form-control form-control-sm" id="dni_copia" name="dni_copia" accept="image/*,application/pdf" required>
                            </div>

                            <!-- Voucher de matrícula -->
                            <h6 class="text-primary border-bottom pb-1 mt-4 mb-3"><i class="fas fa-receipt me-1"></i>2. Pago de Matrícula Semestral</h6>
                            
                            <div class="row mb-3">
                                <div class="col-6">
                                    <label for="voucher_monto" class="form-label fw-bold small text-muted">Monto Pago (S/.)</label>
                                    <input type="number" step="0.01" class="form-control form-control-sm" id="voucher_monto" name="voucher_monto" value="200.00" required>
                                </div>
                                <div class="col-6">
                                    <label for="voucher_operacion" class="form-label fw-bold small text-muted">N° Operación</label>
                                    <input type="text" class="form-control form-control-sm" id="voucher_operacion" name="voucher_operacion" placeholder="Ej: OP-748596" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="voucher_fecha" class="form-label fw-bold small text-muted">Fecha de Pago</label>
                                <input type="date" class="form-control form-control-sm" id="voucher_fecha" name="voucher_fecha" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>

                            <div class="mb-4">
                                <label for="voucher_matricula" class="form-label fw-bold small text-muted">Adjuntar Captura del Voucher de Matrícula <span class="text-danger">*</span></label>
                                <input type="file" class="form-control form-control-sm" id="voucher_matricula" name="voucher_matricula" accept="image/*,application/pdf" required>
                            </div>

                            <button type="submit" class="btn btn-success w-100 py-2.5 shadow-sm">
                                <i class="fas fa-upload me-1"></i> Subir Documentos y Confirmar Pago
                            </button>
                        </form>

                    <?php elseif ($perfil['estado'] === 'Requisitos Subidos'): ?>
                        <!-- Requisitos enviados, en espera -->
                        <div class="text-center py-5">
                            <div class="text-primary fs-1 mb-3"><i class="fas fa-file-signature animate-pulse"></i></div>
                            <h5 class="fw-bold">Requisitos en revisión</h5>
                            <p class="text-muted small">Tus documentos académicos y el voucher de matrícula semestral ya fueron cargados y están en evaluación de secretaría general. Te avisaremos en cuanto tu código sea asignado.</p>
                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <span class="badge bg-light text-dark border p-2"><i class="fas fa-check text-success me-1"></i> Certificado Cargado</span>
                                <span class="badge bg-light text-dark border p-2"><i class="fas fa-check text-success me-1"></i> Partida Cargado</span>
                                <span class="badge bg-light text-dark border p-2"><i class="fas fa-check text-success me-1"></i> Copia DNI Cargado</span>
                            </div>
                        </div>

                    <?php elseif ($perfil['estado'] === 'Matriculado'): ?>
                        <!-- Ya matriculado -->
                        <div class="text-center py-5">
                            <div class="text-success fs-1 mb-3"><i class="fas fa-graduation-cap"></i></div>
                            <h5 class="fw-bold text-success">¡Matrícula Completada con Éxito!</h5>
                            <p class="text-muted small">Ya eres formalmente un estudiante de nuestra casa de estudios. En tu próximo login verás el menú de notas e historial semestral.</p>
                            <span class="badge bg-success px-4 py-2 mt-2">Código de Estudiante Activo</span>
                        </div>

                    <?php else: ?>
                        <!-- Aún no ingresante -->
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-folder-closed fa-3x mb-3 text-secondary"></i>
                            <h5>Trámite no habilitado</h5>
                            <p class="small">Esta sección se activará una vez que apruebes el examen de admisión y obtengas la vacante correspondiente.</p>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
// Imprimir carnet virtual
function printCarnet() {
    const carnetContent = document.getElementById('carnetPrintable').innerHTML;
    const originalContent = document.body.innerHTML;
    
    // Crear una ventana o reemplazar contenido temporalmente para la impresión
    document.body.innerHTML = `
        <div style="display: flex; justify-content: center; align-items: center; min-height: 100vh;">
            <div style="border: 2px solid #0088cc; padding: 20px; border-radius: 10px; max-width: 400px; width: 100%;">
                ${carnetContent}
            </div>
        </div>
    `;
    
    window.print();
    
    // Restaurar contenido original
    document.body.innerHTML = originalContent;
    window.location.reload(); // Recarga para volver a enlazar los JS de Bootstrap
}
</script>
