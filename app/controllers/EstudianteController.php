<?php

class EstudianteController {

    // Subir Voucher de Matrícula (para estudiantes regulares)
    public function subirVoucherMatricula() {
        $usuario_id = $_SESSION['user_id'];
        $monto = floatval($_POST['monto'] ?? 0.00);
        $numero_operacion = trim($_POST['numero_operacion'] ?? '');
        $fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d');

        if ($monto <= 0 || empty($numero_operacion) || empty($fecha_pago)) {
            $_SESSION['error'] = "Por favor, complete todos los campos obligatorios del voucher.";
            header('Location: index.php?route=estado_matricula');
            exit;
        }

        if (!isset($_FILES['voucher_archivo']) || $_FILES['voucher_archivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Debe adjuntar la imagen o documento legible del voucher.";
            header('Location: index.php?route=estado_matricula');
            exit;
        }

        try {
            $db = Database::getConnection();

            // Guardar el archivo físicamente
            $file = $_FILES['voucher_archivo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'voucher_mat_' . $_SESSION['username'] . '_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/vouchers/';
            
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $_SESSION['error'] = "Error al guardar el archivo en el servidor. Inténtelo nuevamente.";
                header('Location: index.php?route=estado_matricula');
                exit;
            }

            $relative_path = 'uploads/vouchers/' . $filename;

            // Registrar en base de datos
            $stmt = $db->prepare("
                INSERT INTO vouchers (usuario_id, tipo, monto, numero_operacion, fecha_pago, archivo_path, estado)
                VALUES (?, 'Matricula', ?, ?, ?, ?, 'Pendiente')
            ");
            $stmt->execute([
                $usuario_id,
                $monto,
                $numero_operacion,
                $fecha_pago,
                $relative_path
            ]);

            // Registrar log de auditoría
            Database::log($usuario_id, 'Carga de Voucher Matrícula', "El estudiante cargó el voucher de matrícula N°: $numero_operacion.");

            $_SESSION['success'] = "Voucher de matrícula subido exitosamente. Esperando validación por el departamento de Administración.";
            header('Location: index.php?route=estado_matricula');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error de base de datos al subir voucher: " . $e->getMessage();
            header('Location: index.php?route=estado_matricula');
            exit;
        }
    }
}
