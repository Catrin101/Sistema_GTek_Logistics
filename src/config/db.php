<?php
// src/config/db.php

// Define las constantes para la conexión a la base de datos
// **IMPORTANTE**: Reemplaza estos valores con los de tu cuenta de InfinityFree
define('DB_HOST', 'sql102.infinityfree.com'); // Tu host de DB en InfinityFree (ej. sql123.epizy.com)
define('DB_USER', 'if0_39332004'); // Tu nombre de usuario de DB
define('DB_PASS', 'qgQehnHYR3XoU'); // Tu contraseña de DB
define('DB_NAME', 'if0_39332004_gtek'); // Tu nombre de base de datos

/**
 * Función para establecer la conexión a la base de datos.
 * @return PDO|null Objeto PDO si la conexión es exitosa, null en caso contrario.
 */
function connectDB() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Lanzar excepciones en caso de errores
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devolver filas como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactivar emulación para sentencias preparadas
        ];
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
    } catch (PDOException $e) {
        // En un entorno de desarrollo, podrías mostrar el error:
        // echo "Error de conexión a la base de datos: " . $e->getMessage();
        // Para producción, es mejor registrarlo y mostrar un mensaje genérico.
        error_log("Error de conexión a la base de datos: " . $e->getMessage());
        return null;
    }
}

// Opcional: Probar la conexión al incluir este archivo (solo para depuración)
/*
$conn = connectDB();
if ($conn) {
    echo "¡Conexión a la base de datos exitosa!";
} else {
    echo "Fallo la conexión a la base de datos.";
}
*/
?>