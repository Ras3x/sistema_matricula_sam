<?php

class AuthController {
    
    // Iniciar Sesión
    public function login() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $_SESSION['error'] = "Por favor, complete todos los campos.";
            header('Location: index.php?route=login');
            exit;
        }

        try {
            $db = Database::getConnection();
            // Consulta de usuario con contraseña en TEXTO PLANO
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ?");
            $stmt->execute([$username, $password]);
            $user = $stmt->fetch();

            if ($user) {
                // Verificar que la cuenta no esté inactiva
                if ($user['estado'] === 'Inactivo') {
                    $_SESSION['error'] = "Esta cuenta ha sido desactivada. Contacte a soporte.";
                    header('Location: index.php?route=login');
                    exit;
                }

                // Iniciar variables de sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nombres'] = $user['nombres'];
                $_SESSION['user_apellidos'] = $user['apellidos'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['codigo_matricula'] = $user['codigo_matricula'];
                $_SESSION['user_estado'] = $user['estado'];

                // Registrar log de auditoría
                Database::log($user['id'], 'Inicio de Sesión', 'El usuario ingresó al sistema.');

                // Redirigir al panel correspondiente
                $_SESSION['success'] = "¡Bienvenido al sistema, " . $user['nombres'] . "!";
                header('Location: index.php?route=dashboard');
                exit;
            } else {
                $_SESSION['error'] = "DNI, usuario o contraseña incorrectos.";
                header('Location: index.php?route=login');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error del sistema: " . $e->getMessage();
            header('Location: index.php?route=login');
            exit;
        }
    }

    // Registro de Postulantes (Admisión)
    public function register() {
        $dni = trim($_POST['dni'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $celular = trim($_POST['celular'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $programa_id = intval($_POST['programa_id'] ?? 0);
        $password = trim($_POST['password'] ?? '');

        // Validación de datos requeridos
        if (empty($dni) || empty($nombres) || empty($apellidos) || empty($email) || empty($password) || $programa_id === 0) {
            $_SESSION['error'] = "Por favor, complete todos los campos obligatorios y seleccione su carrera.";
            header('Location: index.php?route=register');
            exit;
        }

        // Validación de archivo de voucher
        if (!isset($_FILES['voucher_archivo']) || $_FILES['voucher_archivo']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Debe subir una foto o archivo legible del voucher de pago de derecho de admisión.";
            header('Location: index.php?route=register');
            exit;
        }

        try {
            $db = Database::getConnection();

            // Verificar si el DNI o usuario ya está registrado
            $stmt = $db->prepare("SELECT id FROM usuarios WHERE dni = ? OR username = ?");
            $stmt->execute([$dni, $dni]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = "El DNI ingresado ya se encuentra registrado en el sistema.";
                header('Location: index.php?route=register');
                exit;
            }

            // Iniciar transacción para asegurar consistencia
            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO usuarios (username, password, role, nombres, apellidos, email, celular, direccion, dni, programa_id, estado)
                VALUES (?, ?, 'Postulante', ?, ?, ?, ?, ?, ?, ?, 'Pago Pendiente')
                RETURNING id
            ");
            $stmt->execute([
                $dni,       
                $password,   
                $nombres,
                $apellidos,
                $email,
                $celular,
                $direccion,
                $dni,
                $programa_id
            ]);
            $user_id = $stmt->fetchColumn();

            // Procesar la subida del archivo del voucher de admisión
            $file = $_FILES['voucher_archivo'];
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'voucher_adm_' . $dni . '_' . time() . '.' . $ext;
            $upload_dir = __DIR__ . '/../uploads/vouchers/';
            $dest_path = $upload_dir . $filename;

            if (!move_uploaded_file($file['tmp_name'], $dest_path)) {
                $db->rollBack();
                $_SESSION['error'] = "Error al guardar la imagen del voucher. Inténtelo nuevamente.";
                header('Location: index.php?route=register');
                exit;
            }

            $relative_path = 'uploads/vouchers/' . $filename;

            // Registrar el voucher en la base de datos
            $stmt = $db->prepare("
                INSERT INTO vouchers (usuario_id, tipo, monto, numero_operacion, fecha_pago, archivo_path, estado)
                VALUES (?, 'Admision', ?, ?, ?, ?, 'Pendiente')
            ");
            $monto = floatval($_POST['voucher_monto'] ?? 150.00);
            $num_operacion = trim($_POST['voucher_operacion'] ?? 'OP-' . mt_rand(100000, 999999));
            $fecha_pago = $_POST['voucher_fecha'] ?? date('Y-m-d');

            $stmt->execute([
                $user_id,
                $monto,
                $num_operacion,
                $fecha_pago,
                $relative_path
            ]);

            // Registrar log de auditoría
            Database::log($user_id, 'Registro de Postulante', 'Se registró una nueva cuenta de postulante con voucher cargado.');

            // Confirmar transacción
            $db->commit();

            $_SESSION['success'] = "¡Registro exitoso! Tu cuenta ha sido creada y el voucher de pago fue enviado para revisión. Inicia sesión para seguir el estado de tu admisión.";
            header('Location: index.php?route=login');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = "Error durante el registro: " . $e->getMessage();
            header('Location: index.php?route=register');
            exit;
        }
    }

    // Cerrar Sesión
    public function logout() {
        if (isset($_SESSION['user_id'])) {
            Database::log($_SESSION['user_id'], 'Cierre de Sesión', 'El usuario cerró sesión.');
        }
        
        // Limpiar todas las variables de sesión
        $_SESSION = array();
        
        // Destruir la sesión física
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header('Location: index.php?route=login');
        exit;
    }
}
