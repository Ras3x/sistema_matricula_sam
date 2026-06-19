<?php

try {
    $db = Database::getConnection();

    // Obtener la lista de estudiantes para el registro de notas
    $stmt = $db->query("
        SELECT u.*, p.nombre as carrera_nombre 
        FROM usuarios u 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id 
        WHERE u.role = 'Estudiante' AND u.estado = 'Matriculado'
        ORDER BY u.apellidos, u.nombres
    ");
    $estudiantes = $stmt->fetchAll();

    // Obtener listado de todas las notas registradas
    $stmt = $db->query("
        SELECT n.*, u.nombres, u.apellidos, u.codigo_matricula, p.nombre as carrera_nombre 
        FROM notas n 
        JOIN usuarios u ON n.estudiante_id = u.id 
        LEFT JOIN programas_estudio p ON u.programa_id = p.id
        ORDER BY n.created_at DESC
    ");
    $notas_list = $stmt->fetchAll();

} catch (PDOException $e) {
    $error_msg = "Error al obtener información de notas: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Registro y Control de Notas</h1>
            <p class="text-muted mb-0">Registra y edita las calificaciones de los estudiantes del instituto.</p>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Pestañas de Notas -->
    <ul class="nav nav-pills mb-4 shadow-sm bg-white p-2 rounded" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-estudiantes-tab" data-bs-toggle="pill" data-bs-target="#pills-estudiantes" type="button" role="tab" aria-controls="pills-estudiantes" aria-selected="true">
                <i class="fas fa-edit me-1"></i> Subir Notas por Estudiante
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-registro-tab" data-bs-toggle="pill" data-bs-target="#pills-registro" type="button" role="tab" aria-controls="pills-registro" aria-selected="false">
                <i class="fas fa-book-open me-1"></i> Historial de Notas Registradas
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        
        <!-- PESTAÑA 1: SUBIR NOTAS POR ESTUDIANTE -->
        <div class="tab-pane fade show active animate-fade-in" id="pills-estudiantes" role="tabpanel" aria-labelledby="pills-estudiantes-tab">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-users-class me-2"></i>Selecciona un Estudiante para Calificar</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Cód. Estudiante</th>
                                    <th>DNI</th>
                                    <th>Nombres y Apellidos</th>
                                    <th>Programa de Estudio</th>
                                    <th class="text-center" style="width: 15%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantes as $e): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($e['codigo_matricula']); ?></span></td>
                                        <td><code><?php echo htmlspecialchars($e['dni']); ?></code></td>
                                        <td><strong><?php echo htmlspecialchars($e['apellidos'] . ', ' . $e['nombres']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($e['carrera_nombre']); ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-primary px-3 shadow-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#notaModal" 
                                                    onclick="setNotaModal(<?php echo $e['id']; ?>, '<?php echo htmlspecialchars($e['nombres'] . ' ' . $e['apellidos']); ?>')">
                                                <i class="fas fa-plus-circle me-1"></i> Calificar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- PESTAÑA 2: HISTORIAL DE NOTAS REGISTRADAS -->
        <div class="tab-pane fade animate-fade-in" id="pills-registro" role="tabpanel" aria-labelledby="pills-registro-tab">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-graduation-cap me-2"></i>Calificaciones por Curso Registradas</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle datatable w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Código</th>
                                    <th>Estudiante</th>
                                    <th>Carrera</th>
                                    <th>Asignatura / Curso</th>
                                    <th class="text-center">Nota 1</th>
                                    <th class="text-center">Nota 2</th>
                                    <th class="text-center">Nota 3</th>
                                    <th class="text-center">Promedio</th>
                                    <th class="text-center" style="width: 10%;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($notas_list as $n): ?>
                                    <tr>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($n['codigo_matricula']); ?></span></td>
                                        <td><strong><?php echo htmlspecialchars($n['apellidos'] . ', ' . $n['nombres']); ?></strong></td>
                                        <td class="small"><?php echo htmlspecialchars($n['carrera_nombre']); ?></td>
                                        <td class="text-primary fw-bold"><?php echo htmlspecialchars($n['curso']); ?></td>
                                        <td class="text-center"><?php echo number_format($n['nota1'], 2); ?></td>
                                        <td class="text-center"><?php echo number_format($n['nota2'], 2); ?></td>
                                        <td class="text-center"><?php echo number_format($n['nota3'], 2); ?></td>
                                        <td class="text-center fw-bold <?php echo ($n['promedio'] >= 12.5) ? 'text-success' : 'text-danger'; ?>">
                                            <?php echo number_format($n['promedio'], 2); ?>
                                        </td>
                                        <td class="text-center">
                                            <!-- Botón para editar directamente la nota usando los datos cargados -->
                                            <button class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#notaModal"
                                                    onclick="loadNotaData(<?php echo $n['estudiante_id']; ?>, '<?php echo htmlspecialchars($n['nombres'] . ' ' . $n['apellidos']); ?>', '<?php echo htmlspecialchars($n['curso']); ?>', <?php echo $n['nota1']; ?>, <?php echo $n['nota2']; ?>, <?php echo $n['nota3']; ?>)">
                                                <i class="fas fa-edit"></i> Editar
                                            </button>
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

<!-- Modal para Registrar/Editar Nota -->
<div class="modal fade" id="notaModal" tabindex="-1" aria-labelledby="notaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="notaModalLabel"><i class="fas fa-edit me-2"></i> Ingreso de Calificaciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="index.php?route=admin_guardar_nota" method="POST">
                <div class="modal-body">
                    <input type="hidden" id="modal_estudiante_id" name="estudiante_id" value="0">
                    
                    <div class="mb-3 border-bottom pb-2">
                        <span class="text-muted small">Estudiante a calificar:</span>
                        <h6 id="modal_nombres_estudiante" class="fw-bold text-dark mb-0"></h6>
                    </div>

                    <div class="mb-3">
                        <label for="curso" class="form-label fw-bold">Curso / Unidad Didáctica <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_curso" name="curso" placeholder="Ej: Programación Orientada a Objetos" required>
                    </div>

                    <div class="row mb-3">
                        <div class="col-4">
                            <label for="nota1" class="form-label fw-bold">Nota 1</label>
                            <input type="number" step="0.1" min="0" max="20" class="form-control text-center calc-prom" id="modal_nota1" name="nota1" value="0" required>
                        </div>
                        <div class="col-4">
                            <label for="nota2" class="form-label fw-bold">Nota 2</label>
                            <input type="number" step="0.1" min="0" max="20" class="form-control text-center calc-prom" id="modal_nota2" name="nota2" value="0" required>
                        </div>
                        <div class="col-4">
                            <label for="nota3" class="form-label fw-bold">Nota 3</label>
                            <input type="number" step="0.1" min="0" max="20" class="form-control text-center calc-prom" id="modal_nota3" name="nota3" value="0" required>
                        </div>
                    </div>

                    <div class="card p-3 border-light bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-secondary">Promedio Estimado:</span>
                            <span id="modal_promedio_label" class="fs-4 fw-bold text-danger">0.00</span>
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn sam-btn-primary">Guardar Notas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Cargar datos al modal para nuevo registro
function setNotaModal(id, nombre) {
    document.getElementById('modal_estudiante_id').value = id;
    document.getElementById('modal_nombres_estudiante').textContent = nombre;
    document.getElementById('modal_curso').value = '';
    document.getElementById('modal_curso').readOnly = false;
    document.getElementById('modal_nota1').value = '0';
    document.getElementById('modal_nota2').value = '0';
    document.getElementById('modal_nota3').value = '0';
    
    calcularPromedioRealTime();
}

// Cargar datos al modal para edición de notas existentes
function loadNotaData(estudianteId, nombre, curso, n1, n2, n3) {
    document.getElementById('modal_estudiante_id').value = estudianteId;
    document.getElementById('modal_nombres_estudiante').textContent = nombre;
    document.getElementById('modal_curso').value = curso;
    document.getElementById('modal_curso').readOnly = true; // Bloqueado para evitar cambiar llave
    document.getElementById('modal_nota1').value = n1;
    document.getElementById('modal_nota2').value = n2;
    document.getElementById('modal_nota3').value = n3;
    
    calcularPromedioRealTime();
}

// Cálculo dinámico en tiempo real usando JS
function calcularPromedioRealTime() {
    const n1 = parseFloat(document.getElementById('modal_nota1').value) || 0;
    const n2 = parseFloat(document.getElementById('modal_nota2').value) || 0;
    const n3 = parseFloat(document.getElementById('modal_nota3').value) || 0;
    
    const prom = (n1 + n2 + n3) / 3;
    const roundedProm = prom.toFixed(2);
    
    const promLabel = document.getElementById('modal_promedio_label');
    promLabel.textContent = roundedProm;
    
    if (prom >= 12.5) {
        promLabel.className = "fs-4 fw-bold text-success animate-fade-in";
    } else {
        promLabel.className = "fs-4 fw-bold text-danger animate-fade-in";
    }
}

// Vincular los eventos de escucha para calcular en cada pulsación
document.querySelectorAll('.calc-prom').forEach(input => {
    input.addEventListener('input', calcularPromedioRealTime);
});
</script>
