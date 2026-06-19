<?php

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            $host = getenv('DB_HOST') ?: 'localhost';
            $port = getenv('DB_PORT') ?: '5432';
            $dbname = getenv('DB_NAME') ?: 'sistema_sam';
            $user = getenv('DB_USER') ?: 'sam_user';
            $password = getenv('DB_PASSWORD') ?: 'sam_password';

            try {
                $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
                self::$connection = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                die("Error de conexión a la base de datos: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    public static function log($usuario_id, $accion, $detalles = null) {
        $db = self::getConnection();
        try {
            $stmt = $db->prepare("INSERT INTO logs (usuario_id, accion, detalles) VALUES (?, ?, ?)");
            $stmt->execute([$usuario_id, $accion, $detalles]);
        } catch (PDOException $e) {
        }
    }
}
