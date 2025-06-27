<?php
// src/models/Consignatario.php

require_once __DIR__ . '/../config/db.php';

class Consignatario {
    private $pdo;

    public function __construct() {
        $this->pdo = connectDB();
        if (!$this->pdo) {
            die("Error: No se pudo conectar a la base de datos para el modelo Consignatario.");
        }
    }

    /**
     * Inserta un nuevo consignatario o devuelve su ID si ya existe.
     * @param array $data Datos del consignatario (nombre, domicilio, rfc, email, telefono).
     * @return int ID del consignatario.
     */
    public function findOrCreate(array $data) {
        // Intentar encontrar por nombre
        $stmt = $this->pdo->prepare("SELECT id FROM consignatarios WHERE nombre = :nombre");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->execute();
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            return $existing['id']; // Ya existe, devolver su ID
        }

        // Si no existe, insertar nuevo
        $stmt = $this->pdo->prepare("INSERT INTO consignatarios (nombre, domicilio, rfc, email, telefono) VALUES (:nombre, :domicilio, :rfc, :email, :telefono)");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':domicilio', $data['domicilio']);
        $stmt->bindParam(':rfc', $data['rfc']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':telefono', $data['telefono']);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }
}