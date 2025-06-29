<?php
// src/models/Bitacora.php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/Consignatario.php';
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
     * Obtiene todos los registros de bitácora con información de consignatario y remitente,
     * aplicando filtros, paginación y ordenando por fecha de ingreso más reciente.
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
                    c.nombre AS consignatario_nombre
                FROM
                    bitacora_registros br
                JOIN
                    consignatarios c ON br.consignatario_id = c.id
                WHERE 1=1"; // Cláusula WHERE base para facilitar la adición de filtros

        $params = [];

        // Filtro por rango de fechas
        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND br.fecha_ingreso >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'] . ' 00:00:00'; // Incluye el inicio del día
        }
        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND br.fecha_ingreso <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'] . ' 23:59:59'; // Incluye el fin del día
        }

        // Filtro por tipo de operación
        if (!empty($filters['tipo_operacion'])) {
            $sql .= " AND br.tipo_operacion = :tipo_operacion";
            $params[':tipo_operacion'] = $filters['tipo_operacion'];
        }

        // Filtro de búsqueda general mejorado (más flexible)
        if (!empty($filters['search_query'])) {
            // Limpiar y preparar el término de búsqueda
            $search_clean = trim($filters['search_query']);
            $search_words = explode(' ', $search_clean); // Dividir en palabras
            
            $search_conditions = [];
            foreach ($search_words as $index => $word) {
                if (!empty(trim($word))) {
                    $param_key = ":search_word_" . $index;
                    $search_term = '%' . strtolower(trim($word)) . '%';
                    $params[$param_key] = $search_term;
                    
                    // Buscar cada palabra en todos los campos relevantes
                    $search_conditions[] = "(
                        LOWER(br.num_conocimiento_embarque) LIKE {$param_key}
                        OR LOWER(br.descripcion_mercancia) LIKE {$param_key}
                        OR LOWER(c.nombre) LIKE {$param_key}
                    )";
                }
            }
            
            if (!empty($search_conditions)) {
                // Todas las palabras deben encontrarse (AND) para una búsqueda más precisa
                $sql .= " AND (" . implode(' AND ', $search_conditions) . ")";
            }
        }

        // Ordenar por fecha de ingreso más reciente primero (MAS RECIENTES ARRIBA)
        $sql .= " ORDER BY br.fecha_ingreso DESC";

        // Paginación
        $sql .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;

        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($params as $key => &$val) {
                // Determine tipo de parámetro para bindParam
                if ($key === ':limit' || $key === ':offset') {
                    $stmt->bindParam($key, $val, PDO::PARAM_INT);
                } else {
                    $stmt->bindParam($key, $val);
                }
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener todos los registros: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Cuenta el número total de registros de bitácora con los filtros aplicados.
     * Esto es útil para la paginación.
     * @param array $filters Opcional: Array asociativo con filtros.
     * @return int El número total de registros.
     */
    public function countAllRegistros($filters = []) {
        $sql = "SELECT COUNT(*)
                FROM bitacora_registros br
                JOIN consignatarios c ON br.consignatario_id = c.id
                WHERE 1=1";

        $params = [];

        // Filtro por rango de fechas
        if (!empty($filters['fecha_inicio'])) {
            $sql .= " AND br.fecha_ingreso >= :fecha_inicio";
            $params[':fecha_inicio'] = $filters['fecha_inicio'] . ' 00:00:00';
        }
        if (!empty($filters['fecha_fin'])) {
            $sql .= " AND br.fecha_ingreso <= :fecha_fin";
            $params[':fecha_fin'] = $filters['fecha_fin'] . ' 23:59:59';
        }

        // Filtro por tipo de operación
        if (!empty($filters['tipo_operacion'])) {
            $sql .= " AND br.tipo_operacion = :tipo_operacion";
            $params[':tipo_operacion'] = $filters['tipo_operacion'];
        }

        // Filtro de búsqueda general mejorado (mismo que arriba)
        if (!empty($filters['search_query'])) {
            $search_clean = trim($filters['search_query']);
            $search_words = explode(' ', $search_clean);
            
            $search_conditions = [];
            foreach ($search_words as $index => $word) {
                if (!empty(trim($word))) {
                    $param_key = ":search_word_" . $index;
                    $search_term = '%' . strtolower(trim($word)) . '%';
                    $params[$param_key] = $search_term;
                    
                    $search_conditions[] = "(
                        LOWER(br.num_conocimiento_embarque) LIKE {$param_key}
                        OR LOWER(br.descripcion_mercancia) LIKE {$param_key}
                        OR LOWER(c.nombre) LIKE {$param_key}
                    )";
                }
            }
            
            if (!empty($search_conditions)) {
                $sql .= " AND (" . implode(' AND ', $search_conditions) . ")";
            }
        }

        try {
            $stmt = $this->pdo->prepare($sql);

            foreach ($params as $key => &$val) {
                $stmt->bindParam($key, $val);
            }
            
            $stmt->execute();
            return $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log("Error al contar todos los registros: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Obtiene un registro de bitácora por su ID, incluyendo datos de consignatario, remitente, nombre de usuario y email.
     * @param int $id El ID del registro.
     * @return array|false Los datos del registro o false si no se encuentra.
     */
    public function getRegistroById(int $id) {
        $sql = "SELECT
                    br.*,
                    c.nombre AS consignatario_nombre, c.domicilio AS consignatario_domicilio, c.rfc AS consignatario_rfc, c.email AS consignatario_email, c.telefono AS consignatario_telefono,
                    r.nombre AS remitente_nombre, r.domicilio AS remitente_domicilio, r.pais_origen AS remitente_pais_origen,
                    u.username AS registrado_por_username,
                    u.email AS registrado_por_email_usuario
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
     * Crea un nuevo registro en la bitácora.
     * @param array $data Array asociativo con los datos del registro.
     * @return int|false El ID del registro insertado o false en caso de error.
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
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error al crear registro: " . $e->getMessage());
            return false;
        }
    }
}