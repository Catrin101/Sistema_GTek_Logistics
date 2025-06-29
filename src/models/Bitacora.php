<?php
// src/models/Bitacora.php

require_once __DIR__ . '/../config/db.php'; // Asegúrate de que la ruta sea correcta
require_once __DIR__ . '/Consignatario.php'; // Incluir si se usa aquí directamente (aunque lo haremos en el controller)
require_once __DIR__ . '/Remitente.php';

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
    public function getRegistroById(int $id) {
        $sql = "SELECT 
                    br.*, 
                    c.nombre AS consignatario_nombre, c.domicilio AS consignatario_domicilio, c.rfc AS consignatario_rfc, c.email AS consignatario_email, c.telefono AS consignatario_telefono,
                    r.nombre AS remitente_nombre, r.domicilio AS remitente_domicilio, r.pais_origen AS remitente_pais_origen,
                    u.username AS registrado_por_username,
                    u.email AS registrado_por_email_usuario -- AGREGADO: Obtener el email del usuario
                FROM 
                    bitacora_registros br
                JOIN 
                    consignatarios c ON br.consignatario_id = c.id
                JOIN 
                    remitentes r ON br.remitente_id = r.id
                JOIN
                    users u ON br.registrado_por_user_id = u.id
                WHERE 
                    br.id = :id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener registro por ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Inserta un nuevo registro de bitácora.
     * @param array $data Datos del registro.
     * @return int|false El ID del nuevo registro o false si falla.
     */
    public function createRegistro(array $data) {
        $sql = "INSERT INTO bitacora_registros (
                    fecha_ingreso, 
                    tipo_operacion, 
                    num_conocimiento_embarque, 
                    num_registro_buque_vuelo_contenedor, 
                    dimension_tipo_sellos_candados, 
                    primer_puerto_terminal, 
                    descripcion_mercancia, 
                    peso_unidad_medida, 
                    num_bultos, 
                    valor_comercial, 
                    fecha_conclusion_descarga, 
                    consignatario_id, 
                    remitente_id, 
                    registrado_por_user_id
                ) VALUES (
                    :fecha_ingreso, 
                    :tipo_operacion, 
                    :num_conocimiento_embarque, 
                    :num_registro_buque_vuelo_contenedor, 
                    :dimension_tipo_sellos_candados, 
                    :primer_puerto_terminal, 
                    :descripcion_mercancia, 
                    :peso_unidad_medida, 
                    :num_bultos, 
                    :valor_comercial, 
                    :fecha_conclusion_descarga, 
                    :consignatario_id, 
                    :remitente_id, 
                    :registrado_por_user_id
                )";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            
            // Asigna los valores a los parámetros
            $stmt->bindParam(':fecha_ingreso', $data['fecha_ingreso']);
            $stmt->bindParam(':tipo_operacion', $data['tipo_operacion']);
            $stmt->bindParam(':num_conocimiento_embarque', $data['num_conocimiento_embarque']);
            $stmt->bindParam(':num_registro_buque_vuelo_contenedor', $data['num_registro_buque_vuelo_contenedor']);
            $stmt->bindParam(':dimension_tipo_sellos_candados', $data['dimension_tipo_sellos_candados']);
            $stmt->bindParam(':primer_puerto_terminal', $data['primer_puerto_terminal']);
            $stmt->bindParam(':descripcion_mercancia', $data['descripcion_mercancia']);
            $stmt->bindParam(':peso_unidad_medida', $data['peso_unidad_medida']);
            $stmt->bindParam(':num_bultos', $data['num_bultos']);
            $stmt->bindParam(':valor_comercial', $data['valor_comercial']);
            $stmt->bindParam(':fecha_conclusion_descarga', $data['fecha_conclusion_descarga']);
            $stmt->bindParam(':consignatario_id', $data['consignatario_id'], PDO::PARAM_INT);
            $stmt->bindParam(':remitente_id', $data['remitente_id'], PDO::PARAM_INT);
            $stmt->bindParam(':registrado_por_user_id', $data['registrado_por_user_id'], PDO::PARAM_INT);

            $stmt->execute();
            return $this->pdo->lastInsertId(); // Devuelve el ID del registro insertado
        } catch (PDOException $e) {
            error_log("Error al crear registro de bitácora: " . $e->getMessage());
            return false;
        }
    }
}
?>