<?php
// src/models/Visitante.php

require_once __DIR__ . '/../config/db.php';

class Visitante {
    private $pdo;

    public function __construct() {
        $this->pdo = connectDB();
        if (!$this->pdo) {
            die("Error: No se pudo conectar a la base de datos para el modelo Visitante.");
        }
    }

    /**
     * Crea un nuevo registro de visitante.
     * @param array $data Array asociativo con los datos del visitante (nombre, numero_verificacion).
     * @return int|false El ID del registro insertado o false en caso de error.
     */
    public function createVisitante(array $data) {
        $sql = "INSERT INTO visitantes (
                    nombre,
                    numero_verificacion,
                    fecha_entrada,        
                    fecha_salida          
                ) VALUES (
                    :nombre,
                    :numero_verificacion,
                    :fecha_entrada,       
                    :fecha_salida
                )";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':numero_verificacion', $data['numero_verificacion']);
            $stmt->bindParam(':fecha_entrada', $data['fecha_entrada']); // AGREGAR ESTA LÍNEA
            $stmt->bindParam(':fecha_salida', $data['fecha_salida']);   // AGREGAR ESTA LÍNEA

            $stmt->execute();
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear visitante: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene todos los registros de visitantes.
     * @param array $filters Opcional: Array asociativo con filtros (ej. 'search_query').
     * @param int $limit Límite de registros a devolver.
     * @param int $offset Desplazamiento para la paginación.
     * @return array Array de registros de visitantes.
     */
    public function getAllVisitantes($filters = [], $limit = 10, $offset = 0) {
        $sql = "SELECT
                    v.id,
                    v.nombre,
                    v.numero_verificacion,
                    v.fecha_creacion,
                    v.fecha_entrada,
                    v.fecha_salida 
                FROM
                    visitantes v
                WHERE 1=1";

        $params = [];

        // Filtro de búsqueda general (nombre, numero_verificacion)
        if (!empty($filters['search_query'])) {
            $search_term = '%' . strtolower($filters['search_query']) . '%';
            $sql .= " AND (
                        LOWER(v.nombre) LIKE :search_term
                        OR LOWER(v.numero_verificacion) LIKE :search_term
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
            error_log("Error al obtener todos los visitantes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el número total de registros de visitantes con los filtros aplicados.
     * @param array $filters Opcional: Array asociativo con filtros.
     * @return int El número total de registros.
     */
    public function countAllVisitantes($filters = []) {
        $sql = "SELECT COUNT(*)
                FROM visitantes v
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search_query'])) {
            $search_term = '%' . strtolower($filters['search_query']) . '%';
            $sql .= " AND (
                        LOWER(v.nombre) LIKE :search_term
                        OR LOWER(v.numero_verificacion) LIKE :search_term
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
            error_log("Error al contar visitantes: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene un visitante por su ID.
     * @param int $id El ID del visitante.
     * @return array|false Los datos del visitante o false si no se encuentra.
     */
    public function getVisitanteById(int $id) {
        $sql = "SELECT
                    v.*
                FROM
                    visitantes v
                WHERE
                    v.id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener visitante por ID: " . $e->getMessage());
            return false;
        }
    }

    // Métodos para actualizar y eliminar (para Día 5)
    public function updateVisitante(int $id, array $data) {
        $sql = "UPDATE visitantes SET
                    nombre = :nombre,
                    numero_verificacion = :numero_verificacion,
                    fecha_entrada = :fecha_entrada,
                    fecha_salida = :fecha_salida 
                WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':numero_verificacion', $data['numero_verificacion']);
            $stmt->bindParam(':fecha_entrada', $data['fecha_entrada']); // AGREGAR ESTA LÍNEA
        $stmt->bindParam(':fecha_salida', $data['fecha_salida']); 
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar visitante: " . $e->getMessage());
            return false;
        }
    }

    public function deleteVisitante(int $id) {
        $sql = "DELETE FROM visitantes WHERE id = :id";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar visitante: " . $e->getMessage());
            return false;
        }
    }
}