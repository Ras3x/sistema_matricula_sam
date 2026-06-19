<?php
session_start();


require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/EstudianteController.php';
require_once __DIR__ . '/controllers/PostulanteController.php';


$route = isset($_GET['route']) ? $_GET['route'] : 'home';
$authController = new AuthController();
$adminController = new AdminController();
$estudianteController = new EstudianteController();
$postulanteController = new PostulanteController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($route) {
        case 'login_submit':
            $authController->login();
            exit;
        case 'register_submit':
            $authController->register();
            exit;
        case 'op_save_user':
            $adminController->saveUserByOP();
            exit;
        case 'op_delete_user':
            $adminController->deleteUserByOP();
            exit;
        case 'admin_validar_voucher':
            $adminController->validarVoucher();
            exit;
        case 'admin_cambiar_estado':
            $adminController->cambiarEstadoPostulante();
            exit;
        case 'admin_matricular_ingresante':
            $adminController->matricularIngresante();
            exit;
        case 'admin_guardar_nota':
            $adminController->guardarNota();
            exit;
        case 'postulante_subir_voucher':
            $postulanteController->subirVoucher();
            exit;
        case 'postulante_subir_requisitos':
            $postulanteController->subirRequisitos();
            exit;
        case 'estudiante_subir_voucher':
            $estudianteController->subirVoucherMatricula();
            exit;
    }
}

if ($route === 'logout') {
    $authController->logout();
    exit;
}


$public_routes = ['home', 'login', 'register'];

if (in_array($route, $public_routes)) {
    if ($route === 'home') {
        include __DIR__ . '/views/portal.php';
    } elseif ($route === 'login') {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        include __DIR__ . '/views/auth/login.php';
    } elseif ($route === 'register') {
        if (isset($_SESSION['user_id'])) {
            header('Location: index.php?route=dashboard');
            exit;
        }
        include __DIR__ . '/views/auth/register.php';
    }
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?route=login');
    exit;
}

$user_role = $_SESSION['user_role'];

$permissions = [
    'dashboard' => ['OP', 'Admin', 'Estudiante', 'Postulante'],
    'gestion_usuarios' => ['OP'],
    'validacion_vouchers' => ['Admin'],
    'matriculas_estudiantes' => ['Admin'],
    'gestion_notas' => ['Admin'],
    'mis_notas' => ['Estudiante'],
    'estado_matricula' => ['Estudiante'],
    'subir_voucher' => ['Postulante'],
    'estado_admision' => ['Postulante'],
];

if (!isset($permissions[$route]) || !in_array($user_role, $permissions[$route])) {

    $_SESSION['error'] = "No tienes permisos para acceder a esta sección.";
    header('Location: index.php?route=dashboard');
    exit;
}


$page_titles = [
    'dashboard' => 'Panel de Control - SAM',
    'gestion_usuarios' => 'Gestión de Usuarios - SAM',
    'validacion_vouchers' => 'Validación de Vouchers - SAM',
    'matriculas_estudiantes' => 'Gestión de Matrículas y Admisión - SAM',
    'gestion_notas' => 'Gestión de Notas de Alumnos - SAM',
    'mis_notas' => 'Mis Calificaciones Académicas - SAM',
    'estado_matricula' => 'Estado de Matrícula Semestral - SAM',
    'subir_voucher' => 'Subir Voucher de Pago - SAM',
    'estado_admision' => 'Estado de Admisión y Requisitos - SAM',
];
$page_title = isset($page_titles[$route]) ? $page_titles[$route] : 'Sistema SAM';

include __DIR__ . '/views/layouts/header.php';
include __DIR__ . '/views/layouts/sidebar.php';


echo '<div id="content">';

echo '
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4 rounded shadow-sm d-flex justify-content-between px-3 py-2">
    <button type="button" id="sidebarToggle" class="btn btn-outline-secondary">
        <i class="fas fa-bars"></i>
    </button>
    <div class="d-flex align-items-center">
        <span class="navbar-text me-3 d-none d-sm-inline">
            Rol: <strong class="text-primary">' . htmlspecialchars($user_role) . '</strong> | 
            Bienvenido, <strong>' . htmlspecialchars($_SESSION['user_nombres'] . ' ' . $_SESSION['user_apellidos']) . '</strong>
        </span>
        <a href="index.php?route=logout" class="btn btn-sm btn-outline-danger shadow-sm">
            <i class="fas fa-sign-out-alt me-1"></i> Salir
        </a>
    </div>
</nav>';


if (isset($_SESSION['success'])) {
    echo '<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i>' . $_SESSION['success'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>' . $_SESSION['error'] . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error']);
}


switch ($route) {
    case 'dashboard':
        $role_lower = strtolower($user_role);
        include __DIR__ . "/views/{$role_lower}/dashboard.php";
        break;
    
    case 'gestion_usuarios':
        include __DIR__ . '/views/op/gestion_usuarios.php';
        break;
        
    case 'validacion_vouchers':
        include __DIR__ . '/views/admin/validacion_vouchers.php';
        break;
    case 'matriculas_estudiantes':
        include __DIR__ . '/views/admin/matriculas_estudiantes.php';
        break;
    case 'gestion_notas':
        include __DIR__ . '/views/admin/gestion_notas.php';
        break;
        
    case 'mis_notas':
        include __DIR__ . '/views/estudiante/mis_notas.php';
        break;
    case 'estado_matricula':
        include __DIR__ . '/views/estudiante/estado_matricula.php';
        break;
        
    case 'subir_voucher':
        include __DIR__ . '/views/postulante/subir_voucher.php';
        break;
    case 'estado_admision':
        include __DIR__ . '/views/postulante/estado_admision.php';
        break;
}

echo '</div>';

include __DIR__ . '/views/layouts/footer.php';
