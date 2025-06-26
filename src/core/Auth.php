<?php
// src/core/Auth.php

require_once __DIR__ . '/../config/db.php'; // Asegúrate de que la ruta sea correcta

class Auth {
    private $pdo;

    public function __construct() {
        $this->pdo = connectDB(); // Obtener la conexión PDO
        if (!$this->pdo) {
            // Manejar el error de conexión a la BD si es necesario
            die("Error: No se pudo conectar a la base de datos para autenticación.");
        }
    }

    /**
     * Intenta autenticar un usuario.
     * @param string $username
     * @param string $password
     * @return bool True si la autenticación es exitosa, false en caso contrario.
     */
    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT id, username, password, role FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Autenticación exitosa, iniciar sesión
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            session_regenerate_id(true); // Regenerar ID de sesión para seguridad
            return true;
        }
        return false;
    }

    /**
     * Verifica si un usuario está logueado.
     * @return bool True si el usuario está logueado, false en caso contrario.
     */
    public static function isLoggedIn() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    /**
     * Cierra la sesión del usuario.
     */
    public static function logout() {
        session_start();
        session_unset();     // Elimina todas las variables de sesión
        session_destroy();   // Destruye la sesión
        // Eliminar la cookie de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
    }

    /**
     * Obtiene el ID del usuario logueado.
     * @return int|null ID del usuario o null si no está logueado.
     */
    public static function getUserId() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtiene el nombre de usuario logueado.
     * @return string|null Nombre de usuario o null si no está logueado.
     */
    public static function getUsername() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['username'] ?? null;
    }

    /**
     * Obtiene el rol del usuario logueado.
     * @return string|null Rol del usuario o null si no está logueado.
     */
    public static function getUserRole() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['user_role'] ?? null;
    }

    /**
     * Genera un hash de contraseña. Útil para crear el usuario admin inicial.
     * @param string $password La contraseña en texto plano.
     * @return string El hash de la contraseña.
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT);
    }
}
?>