<?php

class PostulanteController {

    // Subir Voucher de Admisión adicional o corrección
    public function subirVoucher() {
        $usuario_id = $_SESSION['user_id'];
        $monto = floatval($_POST['monto'] ?? 150.00);
        $numero_operacion = trim($_POST['numero_operacion'] ?? '');
        $fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d');

        if (empty($numero_operacion)) {
            $_SESSION['error'] = "Debe registrar el número de operación.";
            header('Location: index.php?route=subir_voucher');
            exit;
        }

        if (!isset($_FILES['voucher_archivo']) || $_FILES['voucher_archivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Debe subir la foto del voucher.";
            header('Location: index.php?route=subir_voucher');
            exit;
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            // Guardar archivo
            $file = $_FILES['voucher_archivo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'voucher_corr_' . $_SESSION['username'] . '_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/vouchers/';
            
            if (!move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
                $db->rollBack();
                $_SESSION['error'] = "Error al guardar el voucher en el servidor.";
                header('Location: index.php?route=subir_voucher');
                exit;
            }

            $relative_path = 'uploads/vouchers/' . $filename;

            // Insertar voucher
            $stmt = $db->prepare("
                INSERT INTO vouchers (usuario_id, tipo, monto, numero_operacion, fecha_pago, archivo_path, estado)
                VALUES (?, 'Admision', ?, ?, ?, ?, 'Pendiente')
            ");
            $stmt->execute([$usuario_id, $monto, $numero_operacion, $fecha_pago, $relative_path]);

            // Actualizar estado de usuario de "Pago Rechazado" o "Registrado" a "Pago Pendiente"
            $stmt = $db->prepare("UPDATE usuarios SET estado = 'Pago Pendiente' WHERE id = ?");
            $stmt->execute([$usuario_id]);

            Database::log($usuario_id, 'Carga de Voucher Corrección', "Postulante cargó corrección de voucher de admisión.");
            
            $db->commit();
            $_SESSION['success'] = "Voucher cargado exitosamente. Se ha enviado para validación administrativa.";
            header('Location: index.php?route=dashboard');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = "Error al guardar el voucher: " . $e->getMessage();
            header('Location: index.php?route=subir_voucher');
            exit;
        }
    }

    // Subir Requisitos Físicos y Voucher de Matrícula (Para Ingresantes)
    public function subirRequisitos() {
        $usuario_id = $_SESSION['user_id'];
        $dni = $_SESSION['username'];

        // Archivos requeridos
        $req_files = ['certificado_estudios', 'partida_nacimiento', 'dni_copia'];
        foreach ($req_files as $field) {
            if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error'] = "Debe cargar todos los requisitos obligatorios en formato PDF o imagen.";
                header('Location: index.php?route=estado_admision');
                exit;
            }
        }

        // Voucher de matrícula
        if (!isset($_FILES['voucher_matricula']) || $_FILES['voucher_matricula']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Debe adjuntar el voucher de pago de matrícula semestral.";
            header('Location: index.php?route=estado_admision');
            exit;
        }

        $monto = floatval($_POST['voucher_monto'] ?? 200.00);
        $operacion = trim($_POST['voucher_operacion'] ?? '');
        $fecha = $_POST['voucher_fecha'] ?? date('Y-m-d');

        if (empty($operacion)) {
            $_SESSION['error'] = "Debe indicar el número de operación del voucher de matrícula.";
            header('Location: index.php?route=estado_admision');
            exit;
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            $upload_req_dir = __DIR__ . '/../uploads/requisitos/';
            $upload_voc_dir = __DIR__ . '/../uploads/vouchers/';
            
            // Guardar Requisitos Académicos
            // Certificado
            $file_cert = $_FILES['certificado_estudios'];
            $ext_cert = pathinfo($file_cert['name'], PATHINFO_EXTENSION);
            $name_cert = "req_cert_{$dni}_" . time() . ".{$ext_cert}";
            move_uploaded_file($file_cert['tmp_name'], $upload_req_dir . $name_cert);

            // Partida
            $file_part = $_FILES['partida_nacimiento'];
            $ext_part = pathinfo($file_part['name'], PATHINFO_EXTENSION);
            $name_part = "req_part_{$dni}_" . time() . ".{$ext_part}";
            move_uploaded_file($file_part['tmp_name'], $upload_req_dir . $name_part);

            // DNI Copia
            $file_dni = $_FILES['dni_copia'];
            $ext_dni = pathinfo($file_dni['name'], PATHINFO_EXTENSION);
            $name_dni = "req_dni_{$dni}_" . time() . ".{$ext_dni}";
            move_uploaded_file($file_dni['tmp_name'], $upload_req_dir . $name_dni);

            // Guardar en requisitos
            $stmt = $db->prepare("
                INSERT INTO requisitos (usuario_id, certificado_estudios_path, partida_nacimiento_path, dni_copia_path, estado)
                VALUES (?, ?, ?, ?, 'Pendiente')
            ");
            $stmt->execute([
                $usuario_id,
                'uploads/requisitos/' . $name_cert,
                'uploads/requisitos/' . $name_part,
                'uploads/requisitos/' . $name_dni
            ]);

            // Guardar Voucher de Matrícula
            $file_voc = $_FILES['voucher_matricula'];
            $ext_voc = pathinfo($file_voc['name'], PATHINFO_EXTENSION);
            $name_voc = "voucher_mat_{$dni}_" . time() . ".{$ext_voc}";
            move_uploaded_file($file_voc['tmp_name'], $upload_voc_dir . $name_voc);

            $stmt = $db->prepare("
                INSERT INTO vouchers (usuario_id, tipo, monto, numero_operacion, fecha_pago, archivo_path, estado)
                VALUES (?, 'Matricula', ?, ?, ?, ?, 'Pendiente')
            ");
            $stmt->execute([
                $usuario_id,
                $monto,
                $operacion,
                $fecha,
                'uploads/vouchers/' . $name_voc
            ]);

            // Actualizar estado del Postulante
            $stmt = $db->prepare("UPDATE usuarios SET estado = 'Requisitos Subidos' WHERE id = ?");
            $stmt->execute([$usuario_id]);

            Database::log($usuario_id, 'Carga de Requisitos', "El ingresante subió sus requisitos académicos y voucher de matrícula.");

            $db->commit();
            $_SESSION['success'] = "¡Requisitos y voucher de matrícula cargados correctamente! La administración revisará tus documentos para completar tu matriculación y activar tu cuenta estudiantil.";
            header('Location: index.php?route=dashboard');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = "Error al procesar la subida: " . $e->getMessage();
            header('Location: index.php?route=estado_admision');
            exit;
        }
    }
}
