<?php
try {
    $db = Database::getConnection();
    $stmt = $db->query("SELECT * FROM programas_estudio ORDER BY nombre ASC");
    $programas = $stmt->fetchAll();
} catch (PDOException $e) {
    $programas = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceso de Admisión (Pre-Registro) - IESTP SAM</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Estilos Personalizados -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="auth-bg py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                
                <!-- Alertas locales de sesión -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card auth-card border-0 p-4 shadow">
                    <div class="text-center mb-4">
                        <img src="assets/img/logo-sam.png" alt="Logo SAM" class="img-fluid mb-2" style="max-height: 75px;">
                        <h3 class="fw-bold text-dark mb-1">Ficha de Inscripción de Admisión</h3>
                        <p class="text-muted">Completa tus datos personales y adjunta el voucher por derecho de examen.</p>
                        <hr class="w-25 mx-auto border-info border-2">
                    </div>

                    <form action="index.php?route=register_submit" method="POST" enctype="multipart/form-data">
                        
                        <!-- Datos Personales -->
                        <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i>1. Datos Personales</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="dni" class="form-label fw-semibold text-secondary">DNI (Usuario) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control valida-dni" id="dni" name="dni" placeholder="Ingrese 8 dígitos" required autocomplete="username">
                            </div>
                            <div class="col-md-4">
                                <label for="nombres" class="form-label fw-semibold text-secondary">Nombres <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombres" name="nombres" placeholder="Nombres completos" required>
                            </div>
                            <div class="col-md-4">
                                <label for="apellidos" class="form-label fw-semibold text-secondary">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="apellidos" name="apellidos" placeholder="Apellidos paterno y materno" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold text-secondary">Correo Electrónico <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="ejemplo@correo.com" required>
                            </div>
                            <div class="col-md-6">
                                <label for="celular" class="form-label fw-semibold text-secondary">N° Celular <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="celular" name="celular" placeholder="Celular de contacto" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="direccion" class="form-label fw-semibold text-secondary">Dirección de Residencia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="direccion" name="direccion" placeholder="Dirección completa, Distrito, Provincia" required>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="programa_id" class="form-label fw-semibold text-secondary">Programa de Estudio (Carrera) <span class="text-danger">*</span></label>
                                <select class="form-select" id="programa_id" name="programa_id" required>
                                    <option value="" disabled selected>Seleccione una carrera...</option>
                                    <?php foreach ($programas as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label fw-semibold text-secondary">Asignar Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Mínimo 6 caracteres" required autocomplete="new-password">
                                <div class="form-text"><small class="text-muted">Las contraseñas no se encriptarán para la demo local.</small></div>
                            </div>
                        </div>

                        <!-- Carga de Voucher de Admisión -->
                        <h5 class="text-primary border-bottom pb-2 mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>2. Voucher de Pago de Admisión</h5>
                        
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label for="voucher_monto" class="form-label fw-semibold text-secondary">Monto del Derecho (S/.) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" id="voucher_monto" name="voucher_monto" value="150.00" required>
                            </div>
                            <div class="col-md-4">
                                <label for="voucher_operacion" class="form-label fw-semibold text-secondary">N° de Operación Bancaria <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="voucher_operacion" name="voucher_operacion" placeholder="Ej: OP-123456" required>
                            </div>
                            <div class="col-md-4">
                                <label for="voucher_fecha" class="form-label fw-semibold text-secondary">Fecha de Pago <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="voucher_fecha" name="voucher_fecha" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="voucher_archivo" class="form-label fw-semibold text-secondary">Adjuntar Imagen del Voucher (JPG, PNG o PDF) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="voucher_archivo" name="voucher_archivo" accept="image/*,application/pdf" required>
                            <div class="form-text text-muted"><small>Asegúrate de que la captura del voucher sea nítida y legible.</small></div>
                        </div>

                        <button type="submit" class="btn sam-btn-primary w-100 py-3 shadow fs-5">
                            <i class="fas fa-paper-plane me-2"></i> Registrar Postulación y Enviar Voucher
                        </button>

                        <div class="text-center mt-3">
                            <hr class="text-muted">
                            <p class="mb-0 text-muted small">¿Ya tienes una cuenta?</p>
                            <a href="index.php?route=login" class="btn btn-link text-info fw-bold text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión aquí
                            </a>
                        </div>
                    </form>
                </div>

                <div class="text-center mt-3 mb-5">
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
