<?php
// Vista de Notas del Estudiante - estudiante/mis_notas.php

try {
    $db = Database::getConnection();
    $estudiante_id = $_SESSION['user_id'];

    // Obtener todas las notas del alumno
    $stmt = $db->prepare("SELECT * FROM notas WHERE estudiante_id = ? ORDER BY curso ASC");
    $stmt->execute([$estudiante_id]);
    $notas = $stmt->fetchAll();

    // Calcular estadísticas locales
    $total_cursos = count($notas);
    $aprobados = 0;
    $desaprobados = 0;
    $suma_promedios = 0;

    foreach ($notas as $n) {
        $suma_promedios += $n['promedio'];
        if ($n['promedio'] >= 12.5) {
            $aprobados++;
        } else {
            $desaprobados++;
        }
    }

    $promedio_general = ($total_cursos > 0) ? ($suma_promedios / $total_cursos) : 0;

} catch (PDOException $e) {
    $error_msg = "Error al obtener calificaciones: " . $e->getMessage();
}
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Mis Calificaciones Académicas</h1>
            <p class="text-muted mb-0">Visualiza tu récord de notas y promedios por cada asignatura en curso.</p>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="alert alert-danger shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <!-- Tarjetas de Resumen Rápido -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm sam-card border-0 border-start border-4 border-info py-2">
                <div class="card-body py-3">
                    <span class="text-muted small d-block uppercase">Cursos Matriculados</span>
                    <h3 class="fw-bold mt-1 text-dark"><i class="fas fa-book me-2 text-info"></i><?php echo $total_cursos; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm sam-card border-0 border-start border-4 border-success py-2">
                <div class="card-body py-3">
                    <span class="text-muted small d-block">Asignaturas Aprobadas</span>
                    <h3 class="fw-bold mt-1 text-success"><i class="fas fa-check-circle me-2 text-success"></i><?php echo $aprobados; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm sam-card border-0 border-start border-4 border-primary py-2">
                <div class="card-body py-3">
                    <span class="text-muted small d-block">Promedio General Ponderado</span>
                    <h3 class="fw-bold mt-1 <?php echo ($promedio_general >= 12.5) ? 'text-success' : 'text-danger'; ?>">
                        <i class="fas fa-chart-line me-2"></i><?php echo number_format($promedio_general, 2); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Boleta de Notas -->
    <div class="card shadow-sm sam-card">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-file-invoice me-2"></i>Boleta de Notas Digital - Semestre Vigente</h6>
        </div>
        <div class="card-body">
            <?php if (empty($notas)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aún no se registran calificaciones</h5>
                    <p class="text-muted small">Tu docente o el departamento administrativo cargará tus calificaciones pronto.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Asignatura / Unidad Didáctica</th>
                                <th class="text-center" style="width: 12%;">Evaluación 1</th>
                                <th class="text-center" style="width: 12%;">Evaluación 2</th>
                                <th class="text-center" style="width: 12%;">Evaluación 3</th>
                                <th class="text-center" style="width: 19%;">Promedio Final</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notas as $n): ?>
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        <i class="fas fa-circle-check text-primary me-2 small"></i>
                                        <?php echo htmlspecialchars($n['curso']); ?>
                                    </td>
                                    <td class="text-center"><?php echo number_format($n['nota1'], 2); ?></td>
                                    <td class="text-center"><?php echo number_format($n['nota2'], 2); ?></td>
                                    <td class="text-center"><?php echo number_format($n['nota3'], 2); ?></td>
                                    <td class="text-center">
                                        <span class="px-3 py-1.5 rounded fw-bold d-inline-block shadow-xs <?php 
                                            echo ($n['promedio'] >= 12.5) ? 'bg-success text-white' : 'bg-danger text-white'; 
                                        ?>" style="min-width: 70px;">
                                            <?php echo number_format($n['promedio'], 2); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-4 p-3 bg-light border rounded shadow-xs d-flex align-items-center">
                    <i class="fas fa-info-circle text-info me-2 fs-5"></i>
                    <span class="small text-muted">Nota: La nota mínima aprobatoria para las unidades didácticas del IESTP SAM es de **13.00** (representado como 12.50 redondeado). En caso de desaprobado, comunícate con tu docente para el examen de recuperación.</span>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
