<?php
// src/models/Bitacora.php

require_once __DIR__ . '/../config/db.php'; // Asegúrate de que la ruta sea correcta

class Bitacora {
    private $pdo;

    public function __construct() {
        $this->pdo = connectDB();
        if (!$this->pdo) {
            die("Error: No se pudo conectar a la base de datos para el modelo Bitacora.");
        }
    }

    /**
     * Obtiene todos los registros de bitácora con información de consignatario y remitente.
     * @param array $filters Opcional: Array asociativo con filtros (ej. 'fecha_inicio', 'fecha_fin', 'search_query', 'tipo_operacion').
     * @param int $limit Límite de registros a devolver.
     * @param int $offset Desplazamiento para la paginación.
     * @return array Array de registros de bitácora.
     */
    public function getAllRegistros($filters = [], $limit = 10, $offset = 0) {
        $sql = "SELECT 
                    br.id, 
                    br.fecha_ingreso, 
                    br.tipo_operacion, 
                    br.num_conocimiento_embarque,
                    br.descripcion_mercancia,
                    br.peso_unidad_medida,
                    br.num_bultos,
                    c.nombre AS consignatario_nombre,
                    r.nombre AS remitente_nombre
                FROM 
                    bitacora_registros br
                LEFT JOIN 
                    consignatarios c ON br.consignatario_id = c.id
                LEFT JOIN 
                    remitentes r ON br.remitente_id = r.id
                WHERE 1=1"; // Cláusula WHERE base para añadir filtros

        $params = [];

        // Aplicar filtros
        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND br.fecha_ingreso >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'] . ' 00:00:00'; // Asegurar inicio del día
        }
        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND br.fecha_ingreso <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'] . ' 23:59:59'; // Asegurar fin del día
        }
        if (!empty($filters['tipo_operacion'])) {
            $sql .= " AND br.tipo_operacion = :tipo_operacion";
            $params[':tipo_operacion'] = $filters['tipo_operacion'];
        }
        if (!empty($filters['search_query'])) {
            $search_term = '%' . $filters['search_query'] . '%';
            $sql .= " AND (br.num_conocimiento_embarque LIKE :search_query 
                         OR br.num_registro_buque_vuelo_contenedor LIKE :search_query
                         OR br.descripcion_mercancia LIKE :search_query
                         OR c.nombre LIKE :search_query
                         OR r.nombre LIKE :search_query)";
            $params[':search_query'] = $search_term;
        }

        $sql .= " ORDER BY br.fecha_ingreso DESC"; // Ordenar por fecha más reciente

        // Añadir paginación
        $sql .= " LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener registros de bitácora: " . $e->getMessage());
            return []; // Devolver un array vacío en caso de error
        }
    }

    /**
     * Cuenta el total de registros de bitácora para la paginación.
     * @param array $filters Opcional: Array asociativo con filtros.
     * @return int Número total de registros.
     */
    public function countAllRegistros($filters = []) {
        $sql = "SELECT COUNT(br.id) 
                FROM bitacora_registros br
                LEFT JOIN consignatarios c ON br.consignatario_id = c.id
                LEFT JOIN remitentes r ON br.remitente_id = r.id
                WHERE 1=1";

        $params = [];

        // Aplicar los mismos filtros que en getAllRegistros
        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND br.fecha_ingreso >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND br.fecha_ingreso <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'] . ' 23:59:59';
        }
        if (!empty($filters['tipo_operacion'])) {
            $sql .= " AND br.tipo_operacion = :tipo_operacion";
            $params[':tipo_operacion'] = $filters['tipo_operacion'];
        }
        if (!empty($filters['search_query'])) {
            $search_term = '%' . $filters['search_query'] . '%';
            $sql .= " AND (br.num_conocimiento_embarque LIKE :search_query 
                         OR br.num_registro_buque_vuelo_contenedor LIKE :search_query
                         OR br.descripcion_mercancia LIKE :search_query
                         OR c.nombre LIKE :search_query
                         OR r.nombre LIKE :search_query)";
            $params[':search_query'] = $search_term;
        }

        try {
            $stmt = $this->pdo->prepare($sql);
            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar registros de bitácora: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene un registro de bitácora por su ID.
     * @param int $id El ID del registro.
     * @return array|null El registro o null si no se encuentra.
     */
    public function getRegistroById($id) {
        $sql = "SELECT 
                    br.*, 
                    c.nombre AS consignatario_nombre, c.domicilio AS consignatario_domicilio, c.rfc AS consignatario_rfc, c.email AS consignatario_email, c.telefono AS consignatario_telefono,
                    r.nombre AS remitente_nombre, r.domicilio AS remitente_domicilio, r.pais_origen AS remitente_pais_origen,
                    u.username AS registrado_por_username
                FROM 
                    bitacora_registros br
                LEFT JOIN 
                    consignatarios c ON br.consignatario_id = c.id
                LEFT JOIN 
                    remitentes r ON br.remitente_id = r.id
                LEFT JOIN
                    users u ON br.registrado_por_user_id = u.id
                WHERE br.id = :id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener registro por ID: " . $e->getMessage());
            return null;
        }
    }
}
?>