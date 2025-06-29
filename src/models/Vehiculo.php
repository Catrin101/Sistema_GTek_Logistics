<?php
// src/models/Vehiculo.php

require_once __DIR__ . '/../config/db.php';

class Vehiculo {
    private $pdo;

    public function __construct() {
        $this->pdo = connectDB();
        if (!$this->pdo) {
            die("Error: No se pudo conectar a la base de datos para el modelo Vehiculo.");
        }
    }

    /**
     * Crea un nuevo registro de vehículo.
     * @param array $data Array asociativo con los datos del vehículo (nombre_conductor, usuario_del_sistema_id, placas, empresa, modelo).
     * @return int|false El ID del registro insertado o false en caso de error.
     */
    public function createVehiculo(array $data) {
        $sql = "INSERT INTO vehiculos (
                    nombre_conductor,
                    usuario_del_sistema_id,
                    placas,
                    empresa,
                    modelo
                ) VALUES (
                    :nombre_conductor,
                    :usuario_del_sistema_id,
                    :placas,
                    :empresa,
                    :modelo
                )";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nombre_conductor', $data['nombre_conductor']);
            $stmt->bindParam(':usuario_del_sistema_id', $data['usuario_del_sistema_id'], PDO::PARAM_INT);
            $stmt->bindParam(':placas', $data['placas']);
            $stmt->bindParam(':empresa', $data['empresa']);
            $stmt->bindParam(':modelo', $data['modelo']);

            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear vehículo: " . $e->getMessage());
            // En un entorno de desarrollo, podrías querer re-lanzar o mostrar el error para depuración
            // throw new Exception("Error al crear vehículo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los registros de vehículos, incluyendo el username del usuario que lo registró.
     * @param array $filters Opcional: Array asociativo con filtros (ej. 'search_query', 'empresa').
     * @param int $limit Límite de registros a devolver.
     * @param int $offset Desplazamiento para la paginación.
     * @return array Array de registros de vehículos.
     */
    public function getAllVehiculos($filters = [], $limit = 10, $offset = 0) {
        $sql = "SELECT
                    v.id,
                    v.nombre_conductor,
                    u.username AS usuario_del_sistema_username,
                    v.placas,
                    v.empresa,
                    v.modelo,
                    v.fecha_creacion
                FROM
                    vehiculos v
                LEFT JOIN -- Usamos LEFT JOIN porque usuario_del_sistema_id podría ser NULL
                    users u ON v.usuario_del_sistema_id = u.id
                WHERE 1=1";

        $params = [];

        // Filtro de búsqueda general (conductor, placas, empresa, modelo)
        if (!empty($filters['search_query'])) {
            $search_term = '%' . strtolower($filters['search_query']) . '%';
            $sql .= " AND (
                        LOWER(v.nombre_conductor) LIKE :search_term
                        OR LOWER(v.placas) LIKE :search_term
                        OR LOWER(v.empresa) LIKE :search_term
                        OR LOWER(v.modelo) LIKE :search_term
                        OR LOWER(u.username) LIKE :search_term -- Búsqueda también por nombre de usuario
                    )";
            $params[':search_term'] = $search_term;
        }

        $sql .= " ORDER BY v.fecha_creacion DESC"; // Ordenar por fecha de creación más reciente

        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindParam($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($key, $val);
                }
            }
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los vehículos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el número total de registros de vehículos con los filtros aplicados.
     * @param array $filters Opcional: Array asociativo con filtros.
     * @return int El número total de registros.
     */
    public function countAllVehiculos($filters = []) {
        $sql = "SELECT COUNT(*)
                FROM vehiculos v
                LEFT JOIN users u ON v.usuario_del_sistema_id = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search_query'])) {
            $search_term = '%' . strtolower($filters['search_query']) . '%';
            $sql .= " AND (
                        LOWER(v.nombre_conductor) LIKE :search_term
                        OR LOWER(v.placas) LIKE :search_term
                        OR LOWER(v.empresa) LIKE :search_term
                        OR LOWER(v.modelo) LIKE :search_term
                        OR LOWER(u.username) LIKE :search_term
                    )";
            $params[':search_term'] = $search_term;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar vehículos: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene un vehículo por su ID.
     * @param int $id El ID del vehículo.
     * @return array|false Los datos del vehículo o false si no se encuentra.
     */
    public function getVehiculoById(int $id) {
        $sql = "SELECT
                    v.*,
                    u.username AS usuario_del_sistema_username
                FROM
                    vehiculos v
                LEFT JOIN
                    users u ON v.usuario_del_sistema_id = u.id
                WHERE
                    v.id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener vehículo por ID: " . $e->getMessage());
            return false;
        }
    }

    // Métodos para actualizar y eliminar (para Día 5)
    public function updateVehiculo(int $id, array $data) {
        $sql = "UPDATE vehiculos SET
                    nombre_conductor = :nombre_conductor,
                    usuario_del_sistema_id = :usuario_del_sistema_id,
                    placas = :placas,
                    empresa = :empresa,
                    modelo = :modelo
                WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nombre_conductor', $data['nombre_conductor']);
            $stmt->bindParam(':usuario_del_sistema_id', $data['usuario_del_sistema_id'], PDO::PARAM_INT);
            $stmt->bindParam(':placas', $data['placas']);
            $stmt->bindParam(':empresa', $data['empresa']);
            $stmt->bindParam(':modelo', $data['modelo']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar vehículo: " . $e->getMessage());
            return false;
        }
    }

    public function deleteVehiculo(int $id) {
        $sql = "DELETE FROM vehiculos WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar vehículo: " . $e->getMessage());
            return false;
        }
    }
}