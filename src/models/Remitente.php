<?php
// src/models/Remitente.php

require_once __DIR__ . '/../config/db.php';

class Remitente {
    private $pdo;

    public function __construct() {
        $this->pdo = connectDB();
        if (!$this->pdo) {
            die("Error: No se pudo conectar a la base de datos para el modelo Remitente.");
        }
    }

    /**
     * Inserta un nuevo remitente o devuelve su ID si ya existe.
     * @param array $data Datos del remitente (nombre, domicilio, pais_origen).
     * @return int ID del remitente.
     */
    public function findOrCreate(array $data) {
        // Intentar encontrar por nombre
        $stmt = $this->pdo->prepare("SELECT id FROM remitentes WHERE nombre = :nombre");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return $existing['id']; // Ya existe, devolver su ID
        }

        // Si no existe, insertar nuevo
        $stmt = $this->pdo->prepare("INSERT INTO remitentes (nombre, domicilio, pais_origen) VALUES (:nombre, :domicilio, :pais_origen)");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':domicilio', $data['domicilio']);
        $stmt->bindParam(':pais_origen', $data['pais_origen']);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }
}