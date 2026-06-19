<?php

try {
    $db = Database::getConnection();

    // Obtener la lista de postulantes (todos los estados de admisión)
    $stmt = $db->query("
        SELECT u.*, p.nombre as carrera_nombre 
        FROM usuarios u 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id 
        WHERE u.role = 'Postulante' 
        ORDER BY u.estado, u.apellidos, u.nombres
    ");
    $postulantes = $stmt->fetchAll();

    // Obtener lista de estudiantes matriculados oficialmente
    $stmt = $db->query("
        SELECT u.*, p.nombre as carrera_nombre 
        FROM usuarios u 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id 
        WHERE u.role = 'Estudiante' AND u.estado = 'Matriculado'
        ORDER BY p.nombre, u.apellidos, u.nombres
    ");
    $estudiantes = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error al obtener información académica: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Gestión de Matrículas e Ingresos</h1>
            <p class="text-muted mb-0">Controla el estado del examen de admisión y formaliza las matrículas de ingresantes.</p>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Pestañas de Navegación Local -->
    <ul class="nav nav-pills mb-4 shadow-sm bg-white p-2 rounded" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-admision-tab" data-bs-toggle="pill" data-bs-target="#pills-admision" type="button" role="tab" aria-controls="pills-admision" aria-selected="true">
                <i class="fas fa-user-clock me-1"></i> Control de Admisiones
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-estudiantes-tab" data-bs-toggle="pill" data-bs-target="#pills-estudiantes" type="button" role="tab" aria-controls="pills-estudiantes" aria-selected="false">
                <i class="fas fa-user-graduate me-1"></i> Alumnos Matriculados
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        
        <!-- PESTAÑA 1: CONTROL DE ADMISIONES (POSTULANTES) -->
        <div class="tab-pane fade show active animate-fade-in" id="pills-admision" role="tabpanel" aria-labelledby="pills-admision-tab">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-user-clock me-2"></i>Postulantes en Proceso de Admisión</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>DNI</th>
                                    <th>Postulante</th>
                                    <th>Carrera Postulada</th>
                                    <th>Celular / Correo</th>
                                    <th>Estado de Pago / Admisión</th>
                                    <th class="text-center" style="width: 20%;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($postulantes as $p): ?>
                                    <?php 
                                        $req_cert = '';
                                        $req_part = '';
                                        $req_dni = '';
                                        $voc_mat = '';
                                        if ($p['estado'] === 'Requisitos Subidos') {
                                            try {
                                                // Obtener requisitos del postulante
                                                $stmtReq = $db->prepare("SELECT * FROM requisitos WHERE usuario_id = ? ORDER BY id DESC LIMIT 1");
                                                $stmtReq->execute([$p['id']]);
                                                $req = $stmtReq->fetch();
                                                if ($req) {
                                                    $req_cert = $req['certificado_estudios_path'];
                                                    $req_part = $req['partida_nacimiento_path'];
                                                    $req_dni = $req['dni_copia_path'];
                                                }
                                                // Obtener voucher de matrícula del postulante
                                                $stmtVoc = $db->prepare("SELECT * FROM vouchers WHERE usuario_id = ? AND tipo = 'Matricula' ORDER BY id DESC LIMIT 1");
                                                $stmtVoc->execute([$p['id']]);
                                                $voc = $stmtVoc->fetch();
                                                if ($voc) {
                                                    $voc_mat = $voc['archivo_path'];
                                                }
                                            } catch (PDOException $e) {
                                                // Ignorar error de base de datos de forma segura
                                            }
                                        }
                                    ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($p['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($p['apellidos'] . ', ' . $p['nombres']); ?></td>
                                        <td><span class="text-primary fw-semibold"><?php echo htmlspecialchars($p['carrera_nombre']); ?></span></td>
                                        <td>
                                            <small><?php echo htmlspecialchars($p['celular']); ?></small><br>
                                            <small class="text-muted"><?php echo htmlspecialchars($p['email']); ?></small>
                                        </td>
                                        <td>
                                            <!-- Badge adaptado al estado actual -->
                                            <?php 
                                                $badge_class = 'badge-pendiente';
                                                if ($p['estado'] === 'Apto Examen') $badge_class = 'bg-info';
                                                elseif ($p['estado'] === 'Ingresante') $badge_class = 'bg-success';
                                                elseif ($p['estado'] === 'Requisitos Subidos') $badge_class = 'bg-purple text-white'; // Requisitos cargados
                                                elseif ($p['estado'] === 'No Ingresó' || $p['estado'] === 'Pago Rechazado') $badge_class = 'badge-rechazado';
                                                
                                                // Mostrar estilo personalizado
                                                if ($p['estado'] === 'Requisitos Subidos') {
                                                    echo '<span class="badge bg-primary"><i class="fas fa-file-upload me-1"></i> Requisitos Cargados</span>';
                                                } else {
                                                    echo '<span class="badge ' . $badge_class . '">' . htmlspecialchars($p['estado']) . '</span>';
                                                }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <?php if ($p['estado'] === 'Apto Examen'): ?>
                                                    <!-- Botones para calificar ingreso -->
                                                    <form action="index.php?route=admin_cambiar_estado" method="POST" class="d-inline confirm-action" data-confirm-msg="¿Confirmar que el postulante aprobó el examen de admisión y es INGRESANTE?">
                                                        <input type="hidden" name="usuario_id" value="<?php echo $p['id']; ?>">
                                                        <input type="hidden" name="nuevo_estado" value="Ingresante">
                                                        <button type="submit" class="btn btn-sm btn-success px-2 py-1" title="Aprobar Ingreso">
                                                            <i class="fas fa-check"></i> Ingresó
                                                        </button>
                                                    </form>
                                                    <form action="index.php?route=admin_cambiar_estado" method="POST" class="d-inline confirm-action" data-confirm-msg="¿Confirmar que el postulante NO ingresó?">
                                                        <input type="hidden" name="usuario_id" value="<?php echo $p['id']; ?>">
                                                        <input type="hidden" name="nuevo_estado" value="No Ingresó">
                                                        <button type="submit" class="btn btn-sm btn-danger px-2 py-1" title="Marcar No Ingreso">
                                                            <i class="fas fa-times"></i> No Ingresó
                                                        </button>
                                                    </form>
                                                <?php elseif ($p['estado'] === 'Requisitos Subidos'): ?>
                                                    <!-- Botón para matricular -->
                                                    <button class="btn btn-sm btn-primary px-3 shadow-sm"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#matriculaModal"
                                                            onclick="setMatriculaModal(
                                                                <?php echo $p['id']; ?>, 
                                                                '<?php echo htmlspecialchars($p['nombres'] . ' ' . $p['apellidos'], ENT_QUOTES); ?>', 
                                                                '<?php echo htmlspecialchars($p['carrera_nombre'], ENT_QUOTES); ?>', 
                                                                '<?php echo htmlspecialchars($p['dni'], ENT_QUOTES); ?>',
                                                                '<?php echo htmlspecialchars($req_cert, ENT_QUOTES); ?>',
                                                                '<?php echo htmlspecialchars($req_part, ENT_QUOTES); ?>',
                                                                '<?php echo htmlspecialchars($req_dni, ENT_QUOTES); ?>',
                                                                '<?php echo htmlspecialchars($voc_mat, ENT_QUOTES); ?>'
                                                            )">
                                                        <i class="fas fa-user-check me-1"></i> Matricular
                                                    </button>
                                                <?php else: ?>
                                                    <span class="text-muted small">Revisión previa requ.</span>
                                                <?php endif; ?>
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

        <!-- PESTAÑA 2: ALUMNOS MATRICULADOS OFICIALMENTE -->
        <div class="tab-pane fade animate-fade-in" id="pills-estudiantes" role="tabpanel" aria-labelledby="pills-estudiantes-tab">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-user-graduate me-2"></i>Estudiantes con Matrícula Activa</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <!-- DataTable para búsqueda y filtros rápidos -->
                        <table class="table table-hover align-middle datatable w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Cód. Matrícula</th>
                                    <th>DNI</th>
                                    <th>Estudiante</th>
                                    <th>Correo Institucional</th>
                                    <th>Programa de Estudio</th>
                                    <th>Dirección / Teléfono</th>
                                    <th>Fecha Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $e): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary py-2 px-3 fw-bold fs-7"><?php echo htmlspecialchars($e['codigo_matricula']); ?></span></td>
                                        <td><code><?php echo htmlspecialchars($e['dni']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($e['apellidos'] . ', ' . $e['nombres']); ?></strong></td>
                                        <td><a href="mailto:<?php echo htmlspecialchars($e['email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($e['email']); ?></a></td>
                                        <td><span class="text-primary fw-semibold"><?php echo htmlspecialchars($e['carrera_nombre']); ?></span></td>
                                        <td>
                                            <div class="small">Cel: <?php echo htmlspecialchars($e['celular'] ?? '-'); ?></div>
                                            <div class="small text-muted">Dir: <?php echo htmlspecialchars($e['direccion'] ?? '-'); ?></div>
                                        </td>
                                        <td class="small text-muted"><?php echo date('d/m/Y', strtotime($e['created_at'])); ?></td>
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

<!-- Modal para Validar Requisitos y Ejecutar Matrícula Oficial -->
<div class="modal fade" id="matriculaModal" tabindex="-1" aria-labelledby="matriculaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="matriculaModalLabel"><i class="fas fa-file-signature me-2"></i> Revisión de Requisitos de Matrícula</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=admin_matricular_ingresante" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="modal_ingresante_id" name="ingresante_id" value="0">
                    
                    <div class="alert alert-info py-2 shadow-sm d-flex align-items-center">
                        <i class="fas fa-info-circle me-2 fs-5"></i> 
                        <span>Formalizarás la matrícula del ingresante. Esto generará su **Código de Matrícula** y su **Correo Institucional (@sam.edu.pe)**.</span>
                    </div>

                    <div class="row mb-3 border-bottom pb-3">
                        <div class="col-md-6">
                            <span class="text-muted d-block small">Ingresante:</span>
                            <h5 id="modal_nombres_ingresante" class="fw-bold text-dark mb-0"></h5>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <span class="text-muted d-block small">Carrera:</span>
                            <strong id="modal_carrera_ingresante" class="text-primary"></strong>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Requisitos subidos -->
                        <div class="col-md-6 border-end">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-folder-open me-1"></i>Requisitos Académicos</h6>
                            <ul class="list-group list-group-flush mb-3">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light py-2">
                                    <span><i class="far fa-file-pdf me-2 text-danger"></i>Certificado de Estudios</span>
                                    <!-- Requisitos paths dinámicos cargados por JS -->
                                    <a id="lnk_req_cert" href="#" target="_blank" class="btn btn-xs btn-outline-secondary py-1 px-2 rounded-pill small">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light py-2">
                                    <span><i class="far fa-file-pdf me-2 text-danger"></i>Partida de Nacimiento</span>
                                    <a id="lnk_req_part" href="#" target="_blank" class="btn btn-xs btn-outline-secondary py-1 px-2 rounded-pill small">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 border-light py-2">
                                    <span><i class="far fa-id-card me-2 text-info"></i>Copia de DNI</span>
                                    <a id="lnk_req_dni" href="#" target="_blank" class="btn btn-xs btn-outline-secondary py-1 px-2 rounded-pill small">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Voucher de matrícula -->
                        <div class="col-md-6">
                            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-receipt me-1"></i>Comprobante Matrícula</h6>
                            
                            <div class="card p-3 border-light bg-light mb-3">
                                <span class="d-block small text-muted">Voucher de Matrícula:</span>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <strong>S/. 200.00</strong>
                                    <a id="lnk_voucher_mat" href="#" target="_blank" class="btn btn-sm btn-outline-info py-1 px-3 shadow-sm">
                                        <i class="fas fa-receipt me-1"></i> Ver Comprobante
                                    </a>
                                </div>
                            </div>
                            
                            <p class="small text-muted mb-0"><i class="fas fa-exclamation-triangle text-warning me-1"></i> Al dar click en **Matricular e Inscribir**, los comprobantes y requisitos se marcarán como **Aprobados** automáticamente.</p>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn sam-btn-primary">
                        <i class="fas fa-user-check me-1"></i> Confirmar Matrícula Oficial
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cargar datos en el modal de formalización de matrícula
function setMatriculaModal(id, nombre, carrera, dni, certPath, partPath, dniPath, voucherPath) {
    document.getElementById('modal_ingresante_id').value = id;
    document.getElementById('modal_nombres_ingresante').textContent = nombre;
    document.getElementById('modal_carrera_ingresante').textContent = carrera;
    
    // Setear links dinámicos apuntando a los archivos reales, con fallback en caso estén vacíos
    document.getElementById('lnk_req_cert').href = certPath || 'uploads/requisitos/requisito_demo.pdf';
    document.getElementById('lnk_req_part').href = partPath || 'uploads/requisitos/requisito_demo.pdf';
    document.getElementById('lnk_req_dni').href = dniPath || 'uploads/requisitos/requisito_demo.pdf';
    document.getElementById('lnk_voucher_mat').href = voucherPath || 'uploads/vouchers/voucher_demo_carlos.jpg';
}
</script>
