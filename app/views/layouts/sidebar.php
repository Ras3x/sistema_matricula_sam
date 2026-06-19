<?php
// Sidebar dinámico según el rol del usuario logueado
$current_route = isset($_GET['route']) ? $_GET['route'] : 'dashboard';
$role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';
?>
<nav id="sidebar">
    <div class="sidebar-header text-center">
        <!-- Logo Institucional SAM -->
        <img src="assets/img/logo-sam.png" class="img-fluid sidebar-logo mb-2 rounded bg-white p-1" alt="Logo SAM">
        <h5 class="mb-0 fw-bold text-white tracking-wide">IESTP SAM</h5>
        <small class="text-info">Matrícula & Admisiones</small>
    </div>

    <div class="p-3 text-center border-bottom border-secondary" style="background-color: rgba(0, 0, 0, 0.1);">
        <i class="fas fa-user-circle fa-2x text-light mb-2"></i>
        <div class="small fw-semibold"><?php echo htmlspecialchars($_SESSION['user_nombres']); ?></div>
        <div class="text-muted" style="font-size: 0.8rem;"><?php echo htmlspecialchars($role); ?></div>
        <?php if (isset($_SESSION['codigo_matricula'])): ?>
            <span class="badge bg-info mt-1" style="font-size: 0.75rem;"><?php echo htmlspecialchars($_SESSION['codigo_matricula']); ?></span>
        <?php endif; ?>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo ($current_route == 'dashboard') ? 'active' : ''; ?>">
            <a href="index.php?route=dashboard">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
        </li>

        <?php if ($role === 'OP'): ?>
            <!-- Menú para Desarrollador/SuperAdmin -->
            <li class="<?php echo ($current_route == 'gestion_usuarios') ? 'active' : ''; ?>">
                <a href="index.php?route=gestion_usuarios">
                    <i class="fas fa-users-cog"></i> Gestión de Usuarios
                </a>
            </li>

        <?php elseif ($role === 'Admin'): ?>
            <!-- Menú para Administradores -->
            <li class="<?php echo ($current_route == 'validacion_vouchers') ? 'active' : ''; ?>">
                <a href="index.php?route=validacion_vouchers">
                    <i class="fas fa-file-invoice-dollar"></i> Validar Vouchers
                </a>
            </li>
            <li class="<?php echo ($current_route == 'matriculas_estudiantes') ? 'active' : ''; ?>">
                <a href="index.php?route=matriculas_estudiantes">
                    <i class="fas fa-user-graduate"></i> Matrículas e Ingresos
                </a>
            </li>
            <li class="<?php echo ($current_route == 'gestion_notas') ? 'active' : ''; ?>">
                <a href="index.php?route=gestion_notas">
                    <i class="fas fa-book-open"></i> Gestión de Notas
                </a>
            </li>

        <?php elseif ($role === 'Estudiante'): ?>
            <!-- Menú para Estudiantes -->
            <li class="<?php echo ($current_route == 'estado_matricula') ? 'active' : ''; ?>">
                <a href="index.php?route=estado_matricula">
                    <i class="fas fa-check-double"></i> Estado Matrícula
                </a>
            </li>
            <li class="<?php echo ($current_route == 'mis_notas') ? 'active' : ''; ?>">
                <a href="index.php?route=mis_notas">
                    <i class="fas fa-graduation-cap"></i> Mis Notas
                </a>
            </li>

        <?php elseif ($role === 'Postulante'): ?>
            <!-- Menú para Postulantes -->
            <li class="<?php echo ($current_route == 'subir_voucher') ? 'active' : ''; ?>">
                <a href="index.php?route=subir_voucher">
                    <i class="fas fa-upload"></i> Subir Voucher
                </a>
            </li>
            <li class="<?php echo ($current_route == 'estado_admision') ? 'active' : ''; ?>">
                <a href="index.php?route=estado_admision">
                    <i class="fas fa-id-card"></i> Admisión & Carnet
                </a>
            </li>
        <?php endif; ?>
    </ul>

    <div class="sidebar-footer px-3 text-center text-muted w-100" style="position: absolute; bottom: 20px; font-size: 0.8rem;">
        <hr class="border-secondary">
        &copy; 2026 IESTP SAM
    </div>
</nav>
