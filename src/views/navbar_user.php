<?php
// src/views/navbar.php

// Asumo que Auth.php ya está incluido en los controladores que incluyen navbar.php
// Si no, descomentar: require_once __DIR__ . '/../src/core/Auth.php';
?>
<div class="navbar-container">
    <nav class="navbar">
        <ul class="navbar-menu">
            <a href="/bitacora.php">Consulta de Bitácora</a>
            <a href="/vehiculos.php">Consulta de Vehículos</a>
            <a href="/visitantes.php">Consulta de Visitantes</a>
        </ul>
    </nav>
</div>

<style>
/* src/views/navbar.php o en tu style.css */
/* Estilos básicos para el navbar y el dropdown */
.navbar-container {
    background-color: #004D40; /* Color primario de Gtek */
    color: white;
    padding: 10px 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    position: sticky;
    top: 0;
    z-index: 1000;
    /* Eliminar max-width y margin:auto si el contenedor debe ser de extremo a extremo y sin centrado */
    /* max-width: 1200px; */
    /* margin: 0 auto; */
}

.navbar {
    display: flex; /* Mantener flex para alinear marca y menú */
    justify-content: space-between;
    align-items: flex-start; /* Alinea los elementos al inicio (arriba) si el menú se vuelve columna */
    /* Eliminado: max-width para que el contenido se extienda */
    /* max-width: 1200px; */
    margin: 0 auto; /* Mantener si quieres que el contenido esté centrado pero el fondo sea completo */
    width: 100%; /* Asegurar que ocupe todo el ancho disponible */
    flex-direction: column; /* AGREGADO: Para que los elementos principales de la navbar se apilen verticalmente */
}

.navbar-brand a {
    color: white;
    text-decoration: none;
    font-size: 1.5em;
    font-weight: bold;
    margin-bottom: 10px; /* Espacio debajo de la marca si los elementos van a ser verticales */
}

.navbar-menu {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column; /* MODIFICADO: Para que los ítems del menú principal sean verticales */
    align-items: flex-start; /* Alinea los ítems a la izquierda */
    width: 100%; /* Asegura que el menú ocupe todo el ancho disponible */
}

.navbar-menu li {
    position: relative;
    margin-left: 0; /* Eliminado: margen lateral */
    margin-bottom: 5px; /* AGREGADO: Espacio entre ítems verticales */
    width: 100%; /* Asegura que cada ítem del menú ocupe todo el ancho */
}

.navbar-menu a {
    color: white;
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 4px;
    transition: background-color 0.3s ease;
    display: block; /* Asegura que el enlace ocupe todo el espacio del li */
    width: 100%; /* Asegura que el enlace ocupe todo el ancho del li */
    box-sizing: border-box; /* Incluye padding en el ancho total */
}

.navbar-menu a:hover, .dropbtn:hover {
    background-color: #00695C;
}

/* Dropdown */
.dropdown {
    position: relative;
    display: block; /* AGREGADO: Asegura que el dropdown en sí sea un bloque */
    width: 100%; /* Ocupa el ancho completo del li */
}

.dropdown-content {
    display: none;
    position: absolute;
    background-color: #f9f9f9;
    min-width: 180px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    border-radius: 4px;
    overflow: hidden;
    /* Ajuste de posición para un menú vertical */
    top: 0; /* Mantiene el dropdown a la altura del item principal */
    left: 100%; /* AGREGADO: Coloca el dropdown a la derecha del ítem principal */
    white-space: nowrap; /* Evita que los ítems del dropdown se rompan de línea */
}

.dropdown-content a {
    color: #333;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
    text-align: left;
}

.dropdown-content a:hover {
    background-color: #e0e0e0;
}

.dropdown:hover .dropdown-content {
    display: block;
}

/* Estilos para el usuario logueado */
.navbar-user {
    display: flex;
    align-items: center;
    /* margin-left: 30px;  Eliminado: margen lateral */
    margin-top: 15px; /* AGREGADO: Espacio superior para separarlo del menú */
    color: #E0F2F1;
    width: 100%; /* Asegura que el elemento de usuario ocupe todo el ancho */
    justify-content: center; /* Centra el botón de salir si no hay texto */
}

.navbar-user span {
    /* margin-right: 15px;  Eliminado junto con el texto */
    font-weight: bold;
    display: none; /* Oculta el span de "Hola, Admin" si solo quieres el botón Salir */
}

.btn-logout {
    background-color: #D32F2F;
    color: white;
    padding: 8px 15px;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.btn-logout:hover {
    background-color: #C62828;
}

/* Responsive adjustments (ejemplo básico) */
/* Los estilos responsive deberían ajustarse a la nueva estructura vertical si es una barra lateral */
/* Si la barra principal es vertical, esta sección @media podría ser menos relevante o necesitar un rediseño */
@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        align-items: flex-start;
    }

    .navbar-menu {
        flex-direction: column;
        width: 100%;
        margin-top: 10px;
    }

    .navbar-menu li {
        margin: 5px 0;
        width: 100%;
    }

    .navbar-menu a {
        display: block;
        width: 100%;
        text-align: center;
    }

    .dropdown-content {
        position: static;
        width: 100%;
        box-shadow: none;
        background-color: #f0f0f0;
    }

    .navbar-user {
        margin-top: 15px;
        margin-left: 0;
        width: 100%;
        justify-content: center;
    }
}
</style>