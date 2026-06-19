<?php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar al Sistema - IESTP SAM</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Estilos Personalizados -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-bg">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                
                <!-- Alertas locales de sesión -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card auth-card border-0 p-4">
                    <div class="text-center mb-4">
                        <img src="assets/img/logo-sam.png" alt="Logo SAM" class="img-fluid mb-2" style="max-height: 80px;">
                        <h4 class="fw-bold text-dark mb-1">IESTP SAM</h4>
                        <p class="text-muted small">Plataforma Digital de Matrícula</p>
                        <h5 class="sam-gradient-text mt-3">Ingreso al Sistema</h5>
                    </div>

                    <form action="index.php?route=login_submit" method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label fw-semibold text-secondary">Usuario / DNI</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-id-card"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" id="username" name="username" placeholder="Ingrese su DNI o usuario OP" required autocomplete="username">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold text-secondary">Contraseña</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-key"></i></span>
                                <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" placeholder="Ingrese su contraseña" required autocomplete="current-password">
                </div>
                        </div>

                        <button type="submit" class="btn sam-btn-primary w-100 py-2.5 mb-3 shadow">
                            <i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión
                        </button>

                        <div class="text-center">
                            <hr class="text-muted">
                            <p class="mb-0 text-muted small">¿Eres un nuevo postulante?</p>
                            <a href="index.php?route=register" class="btn btn-link text-info fw-bold text-decoration-none">
                                <i class="fas fa-user-plus me-1"></i> Regístrate e Inscríbete Aquí
                            </a>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-3">
                    <a href="index.php" class="text-white small text-decoration-none opacity-75 hover-opacity-100">
                        <i class="fas fa-arrow-left me-1"></i> Volver al Portal de Inicio
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
