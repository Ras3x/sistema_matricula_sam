<?php

class AdminController {

    // --- ACCIONES DE SUPERADMIN ---

    public function saveUserByOP() {
        $id = intval($_POST['id'] ?? 0);
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $nombres = trim($_POST['nombres'] ?? '');
        $apellidos = trim($_POST['apellidos'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $celular = trim($_POST['celular'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '');
        $dni = trim($_POST['dni'] ?? '');
        $programa_id = !empty($_POST['programa_id']) ? intval($_POST['programa_id']) : null;
        $estado = trim($_POST['estado'] ?? 'Activo');

        if (empty($username) || empty($role) || empty($nombres) || empty($apellidos)) {
            $_SESSION['error'] = "Por favor, complete todos los campos obligatorios.";
            header('Location: index.php?route=gestion_usuarios');
            exit;
        }

        try {
            $db = Database::getConnection();

            if ($id > 0) {
                $stmt = $db->prepare("SELECT id FROM usuarios WHERE (username = ? OR (dni IS NOT NULL AND dni = ?)) AND id != ?");
                $stmt->execute([$username, $dni, $id]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = "El nombre de usuario o DNI ya está siendo utilizado por otra cuenta.";
                    header('Location: index.php?route=gestion_usuarios');
                    exit;
                }

                if (empty($password)) {
                    $stmt = $db->prepare("
                        UPDATE usuarios 
                        SET username = ?, role = ?, nombres = ?, apellidos = ?, email = ?, celular = ?, direccion = ?, dni = ?, programa_id = ?, estado = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $role, $nombres, $apellidos, $email, $celular, $direccion, $dni, $programa_id, $estado, $id]);
                } else {
                    $stmt = $db->prepare("
                        UPDATE usuarios 
                        SET username = ?, password = ?, role = ?, nombres = ?, apellidos = ?, email = ?, celular = ?, direccion = ?, dni = ?, programa_id = ?, estado = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$username, $password, $role, $nombres, $apellidos, $email, $celular, $direccion, $dni, $programa_id, $estado, $id]);
                }

                Database::log($_SESSION['user_id'], 'Edición de Usuario', "Se modificó el usuario ID: $id ($username).");
                $_SESSION['success'] = "Usuario actualizado correctamente.";

            } else {
                // Modo Creación
                if (empty($password)) {
                    $_SESSION['error'] = "Debe asignar una contraseña al nuevo usuario.";
                    header('Location: index.php?route=gestion_usuarios');
                    exit;
                }

                // Verificar si existe el username o DNI
                $stmt = $db->prepare("SELECT id FROM usuarios WHERE username = ? OR (dni IS NOT NULL AND dni = ?)");
                $stmt->execute([$username, $dni]);
                if ($stmt->fetch()) {
                    $_SESSION['error'] = "El nombre de usuario o DNI ya se encuentra registrado.";
                    header('Location: index.php?route=gestion_usuarios');
                    exit;
                }

                $stmt = $db->prepare("
                    INSERT INTO usuarios (username, password, role, nombres, apellidos, email, celular, direccion, dni, programa_id, estado)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$username, $password, $role, $nombres, $apellidos, $email, $celular, $direccion, $dni, $programa_id, $estado]);
                
                $new_id = $db->lastInsertId();
                Database::log($_SESSION['user_id'], 'Creación de Usuario', "Se creó el usuario ID: $new_id ($username) con rol: $role.");
                $_SESSION['success'] = "Usuario creado exitosamente.";
            }

            header('Location: index.php?route=gestion_usuarios');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al guardar usuario: " . $e->getMessage();
            header('Location: index.php?route=gestion_usuarios');
            exit;
        }
    }

    // Eliminar Usuario 
    public function deleteUserByOP() {
        $id = intval($_POST['id'] ?? 0);

        if ($id === intval($_SESSION['user_id'])) {
            $_SESSION['error'] = "No puedes eliminar tu propio usuario actual.";
            header('Location: index.php?route=gestion_usuarios');
            exit;
        }

        try {
            $db = Database::getConnection();
            
            // Obtener username antes de borrar para el log
            $stmt = $db->prepare("SELECT username FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $username = $stmt->fetchColumn();

            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);

            Database::log($_SESSION['user_id'], 'Eliminación de Usuario', "Se eliminó el usuario $username (ID: $id).");
            $_SESSION['success'] = "Usuario eliminado correctamente.";
            header('Location: index.php?route=gestion_usuarios');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al eliminar usuario: " . $e->getMessage();
            header('Location: index.php?route=gestion_usuarios');
            exit;
        }
    }


    // --- ACCIONES DE ADMINISTRADOR ---

    // Validar Voucher de Pago (Aprobar/Rechazar)
    public function validarVoucher() {
        $voucher_id = intval($_POST['voucher_id'] ?? 0);
        $estado_voucher = trim($_POST['estado'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        if ($voucher_id === 0 || !in_array($estado_voucher, ['Aprobado', 'Rechazado'])) {
            $_SESSION['error'] = "Parámetros de validación incorrectos.";
            header('Location: index.php?route=validacion_vouchers');
            exit;
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            //Obtener detalles del voucher
            $stmt = $db->prepare("SELECT v.*, u.role, u.estado as user_estado, u.dni FROM vouchers v JOIN usuarios u ON v.usuario_id = u.id WHERE v.id = ?");
            $stmt->execute([$voucher_id]);
            $voucher = $stmt->fetch();

            if (!$voucher) {
                $db->rollBack();
                $_SESSION['error'] = "El voucher especificado no existe.";
                header('Location: index.php?route=validacion_vouchers');
                exit;
            }

            //Actualizar estado del voucher
            $stmt = $db->prepare("UPDATE vouchers SET estado = ?, observaciones = ? WHERE id = ?");
            $stmt->execute([$estado_voucher, $observaciones, $voucher_id]);

            //Modificar el estado del usuario de acuerdo al tipo de voucher
            $usuario_id = $voucher['usuario_id'];
            if ($voucher['tipo'] === 'Admision') {
                if ($estado_voucher === 'Aprobado') {
                    $stmt = $db->prepare("UPDATE usuarios SET estado = 'Apto Examen' WHERE id = ?");
                    $stmt->execute([$usuario_id]);
                    Database::log($_SESSION['user_id'], 'Aprobación de Voucher', "Voucher de Admisión aprobado para el usuario ID: $usuario_id. Estado actualizado a Apto Examen.");
                } else {
                    $stmt = $db->prepare("UPDATE usuarios SET estado = 'Pago Rechazado' WHERE id = ?");
                    $stmt->execute([$usuario_id]);
                    Database::log($_SESSION['user_id'], 'Rechazo de Voucher', "Voucher de Admisión rechazado para el usuario ID: $usuario_id. Estado: Pago Rechazado.");
                }
            } elseif ($voucher['tipo'] === 'Matricula') {
                if ($estado_voucher === 'Aprobado') {
                    Database::log($_SESSION['user_id'], 'Aprobación de Voucher', "Voucher de Matrícula aprobado para el estudiante ID: $usuario_id.");
                } else {
                    Database::log($_SESSION['user_id'], 'Rechazo de Voucher', "Voucher de Matrícula rechazado para el estudiante ID: $usuario_id.");
                }
            }

            $db->commit();
            $_SESSION['success'] = "El voucher de pago ha sido " . ($estado_voucher === 'Aprobado' ? 'APROBADO' : 'RECHAZADO') . " exitosamente.";
            header('Location: index.php?route=validacion_vouchers');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = "Error al validar el voucher: " . $e->getMessage();
            header('Location: index.php?route=validacion_vouchers');
            exit;
        }
    }

    // Cambiar Estado de Postulante
    public function cambiarEstadoPostulante() {
        $usuario_id = intval($_POST['usuario_id'] ?? 0);
        $nuevo_estado = trim($_POST['nuevo_estado'] ?? '');
        $estados_validos = ['Ingresante', 'No Ingresó', 'Apto Examen'];

        if ($usuario_id === 0 || !in_array($nuevo_estado, $estados_validos)) {
            $_SESSION['error'] = "Parámetros inválidos.";
            header('Location: index.php?route=matriculas_estudiantes');
            exit;
        }

        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("UPDATE usuarios SET estado = ? WHERE id = ? AND role = 'Postulante'");
            $stmt->execute([$nuevo_estado, $usuario_id]);

            Database::log($_SESSION['user_id'], 'Cambio de Estado Admisión', "Postulante ID: $usuario_id actualizado a estado: $nuevo_estado.");
            
            $_SESSION['success'] = "Estado del postulante actualizado a '$nuevo_estado'.";
            header('Location: index.php?route=matriculas_estudiantes');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al actualizar estado: " . $e->getMessage();
            header('Location: index.php?route=matriculas_estudiantes');
            exit;
        }
    }

    // Matricular de forma Oficial (Promoción a Estudiante, generación de Código e Email institucional)
    // Se ejecuta cuando el Admin valida y aprueba la matrícula de un Ingresante que ya subió requisitos
    public function matricularIngresante() {
        $ingresante_id = intval($_POST['ingresante_id'] ?? 0);

        if ($ingresante_id === 0) {
            $_SESSION['error'] = "Ingresante inválido.";
            header('Location: index.php?route=matriculas_estudiantes');
            exit;
        }

        try {
            $db = Database::getConnection();
            $db->beginTransaction();

            //Obtener datos del postulante/ingresante
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ? AND role = 'Postulante'");
            $stmt->execute([$ingresante_id]);
            $user = $stmt->fetch();

            if (!$user || $user['estado'] !== 'Requisitos Subidos') {
                $db->rollBack();
                $_SESSION['error'] = "El usuario no cuenta con el estado 'Requisitos Subidos'.";
                header('Location: index.php?route=matriculas_estudiantes');
                exit;
            }

            //Generar el código de matrícula
            $count_estudiantes = $db->query("SELECT COUNT(*) FROM usuarios WHERE role = 'Estudiante'")->fetchColumn();
            $next_seq = str_pad($count_estudiantes + 1, 4, '0', STR_PAD_LEFT);
            $codigo_matricula = "SAM-2026-" . $next_seq;

            //Generar el correo institucional
            // [primera_letra_nombre][primer_apellido]@sam.edu.pe
            $nombre_limpio = strtolower(substr(preg_replace('/[^a-zA-Z]/', '', $user['nombres']), 0, 1));
            $apellidos_arr = explode(' ', trim($user['apellidos']));
            $primer_apellido = strtolower(preg_replace('/[^a-zA-Z]/', '', $apellidos_arr[0]));
            $email_institucional = $nombre_limpio . $primer_apellido . "@sam.edu.pe";

            // 4. Promover el rol del usuario a 'Estudiante' y estado a 'Matriculado'
            $stmt = $db->prepare("
                UPDATE usuarios 
                SET role = 'Estudiante', estado = 'Matriculado', codigo_matricula = ?, email = ?
                WHERE id = ?
            ");
            $stmt->execute([$codigo_matricula, $email_institucional, $ingresante_id]);

            //Validar y aprobar requisitos y vouchers de matrícula asociados
            $stmt = $db->prepare("UPDATE requisitos SET estado = 'Aprobado' WHERE usuario_id = ? AND estado = 'Pendiente'");
            $stmt->execute([$ingresante_id]);

            $stmt = $db->prepare("UPDATE vouchers SET estado = 'Aprobado' WHERE usuario_id = ? AND tipo = 'Matricula' AND estado = 'Pendiente'");
            $stmt->execute([$ingresante_id]);

            //Registrar log de auditoría
            Database::log($_SESSION['user_id'], 'Matrícula Oficial', "Matriculó al ingresante ID: $ingresante_id como Estudiante. Código: $codigo_matricula. Correo: $email_institucional.");

            $db->commit();
            $_SESSION['success'] = "¡Matrícula formalizada exitosamente! El usuario " . htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) . " ahora es oficialmente un Estudiante con código $codigo_matricula y correo $email_institucional.";
            header('Location: index.php?route=matriculas_estudiantes');
            exit;

        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['error'] = "Error al formalizar la matrícula: " . $e->getMessage();
            header('Location: index.php?route=matriculas_estudiantes');
            exit;
        }
    }

    // Guardar / Registrar Nota de Estudiante
    public function guardarNota() {
        $estudiante_id = intval($_POST['estudiante_id'] ?? 0);
        $curso = trim($_POST['curso'] ?? '');
        $nota1 = floatval($_POST['nota1'] ?? 0);
        $nota2 = floatval($_POST['nota2'] ?? 0);
        $nota3 = floatval($_POST['nota3'] ?? 0);

        if ($estudiante_id === 0 || empty($curso)) {
            $_SESSION['error'] = "Datos de nota incompletos.";
            header('Location: index.php?route=gestion_notas');
            exit;
        }

        // Validar rango de notas (0 a 20)
        if ($nota1 < 0 || $nota1 > 20 || $nota2 < 0 || $nota2 > 20 || $nota3 < 0 || $nota3 > 20) {
            $_SESSION['error'] = "Las calificaciones deben estar en el rango de 0 a 20.";
            header('Location: index.php?route=gestion_notas');
            exit;
        }

        // Calcular promedio de las 3 notas
        $promedio = round(($nota1 + $nota2 + $nota3) / 3, 2);

        try {
            $db = Database::getConnection();

            // Verificar si el curso ya está registrado para el estudiante
            $stmt = $db->prepare("SELECT id FROM notas WHERE estudiante_id = ? AND curso = ?");
            $stmt->execute([$estudiante_id, $curso]);
            $nota_existente = $stmt->fetch();

            if ($nota_existente) {
                // Actualizar notas
                $stmt = $db->prepare("
                    UPDATE notas 
                    SET nota1 = ?, nota2 = ?, nota3 = ?, promedio = ?
                    WHERE id = ?
                ");
                $stmt->execute([$nota1, $nota2, $nota3, $promedio, $nota_existente['id']]);
                Database::log($_SESSION['user_id'], 'Actualización de Nota', "Actualizó notas del curso '$curso' para estudiante ID: $estudiante_id. Promedio: $promedio");
                $_SESSION['success'] = "Notas actualizadas correctamente.";
            } else {
                // Registrar nuevas notas
                $stmt = $db->prepare("
                    INSERT INTO notas (estudiante_id, curso, nota1, nota2, nota3, promedio)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$estudiante_id, $curso, $nota1, $nota2, $nota3, $promedio]);
                Database::log($_SESSION['user_id'], 'Registro de Nota', "Registró notas del curso '$curso' para estudiante ID: $estudiante_id. Promedio: $promedio");
                $_SESSION['success'] = "Notas guardadas exitosamente.";
            }

            header('Location: index.php?route=gestion_notas');
            exit;

        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al guardar notas: " . $e->getMessage();
            header('Location: index.php?route=gestion_notas');
            exit;
        }
    }
}
