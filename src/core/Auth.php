<?php
// src/core/Auth.php

// Asegúrate de que session_start() se llame al principio de cada script que use sesiones
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php'; // Asegúrate de que esta ruta sea correcta

class Auth {
    /**
     * Intenta autenticar un usuario.
     * @param string $username
     * @param string $password
     * @return bool True si la autenticación es exitosa, false de lo contrario.
     */
    public static function login(string $username, string $password): bool {
        $pdo = connectDB();
        if (!$pdo) {
            error_log("Auth Error: No se pudo conectar a la base de datos.");
            return false;
        }

        $stmt = $pdo->prepare("SELECT id, username, password_hash, role, email FROM users WHERE username = :username"); // Agrega 'email'
        $stmt->bindParam(':username', $username);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenerar ID de sesión para prevenir Session Fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['email'] = $user['email']; // Guardar email en sesión
            return true;
        }
        return false;
    }

    /**
     * Verifica si un usuario está logueado.
     * @return bool True si hay una sesión activa, false de lo contrario.
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']);
    }

    /**
     * Cierra la sesión del usuario.
     */
    public static function logout(): void {
        $_SESSION = array(); // Vacía todas las variables de sesión
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy(); // Destruye la sesión
    }

    /**
     * Obtiene el ID del usuario logueado.
     * @return int|null El ID del usuario o null si no hay sesión.
     */
    public static function getUserId(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Obtiene el nombre de usuario del usuario logueado.
     * @return string|null El nombre de usuario o null si no hay sesión.
     */
    public static function getUsername(): ?string {
        return $_SESSION['username'] ?? null;
    }

    /**
     * Obtiene el rol del usuario logueado.
     * @return string|null El rol del usuario o null si no hay sesión.
     */
    public static function getUserRole(): ?string {
        return $_SESSION['role'] ?? null;
    }
    
    /**
     * Obtiene el email del usuario logueado.
     * @return string|null El email del usuario o null si no hay sesión.
     */
    public static function getUserEmail(): ?string {
        return $_SESSION['email'] ?? null;
    }
}