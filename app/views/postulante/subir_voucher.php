<?php
?>

<div class="container-fluid animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Actualizar Voucher de Admisión</h1>
            <p class="text-muted mb-0">Carga un voucher válido y legible en caso de que el anterior fuera rechazado o no registrado.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm sam-card">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 font-weight-bold text-primary fw-bold"><i class="fas fa-file-invoice-dollar me-2"></i>Comprobante de Pago Derecho de Admisión</h6>
                </div>
                <div class="card-body">
                    <form action="index.php?route=postulante_subir_voucher" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="monto" class="form-label fw-semibold text-secondary">Monto Depósito (S/.) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control" id="monto" name="monto" value="150.00" required>
                            <div class="form-text"><small class="text-muted">El derecho de examen de admisión ordinario es de S/. 150.00.</small></div>
                        </div>

                        <div class="mb-3">
                            <label for="numero_operacion" class="form-label fw-semibold text-secondary">N° de Operación Bancaria <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="numero_operacion" name="numero_operacion" placeholder="Ej: OP-987456" required>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_pago" class="form-label fw-semibold text-secondary">Fecha de Pago <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_pago" name="fecha_pago" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>

                        <div class="mb-4">
                            <label for="voucher_archivo" class="form-label fw-semibold text-secondary">Adjuntar Imagen del Voucher (JPG, PNG o PDF) <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="voucher_archivo" name="voucher_archivo" accept="image/*,application/pdf" required>
                            <div class="form-text text-muted"><small>Asegúrate de que la captura contenga el número de operación y la fecha de forma clara.</small></div>
                        </div>

                        <button type="submit" class="btn sam-btn-primary w-100 py-2.5 shadow">
                            <i class="fas fa-paper-plane me-2"></i> Cargar Pago y Enviar a Validación
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
