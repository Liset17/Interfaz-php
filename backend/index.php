<?php
// Punto de entrada del backend. Si alguien abre
// http://localhost/Interfaz-php/backend/ en el navegador,
// le damos una pista de que este directorio es una API.
header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'ok'       => true,
    'name'     => 'Academia de Teatro - API PHP',
    'version'  => '1.0.0',
    'endpoints' => [
        'POST /api/auth/register.php',
        'POST /api/auth/login.php',
        'POST /api/auth/logout.php',
        'GET  /api/auth/me.php',
        'GET|POST|PUT|DELETE /api/alumnos.php',
        'GET|POST|PUT|DELETE /api/grupos.php',
        'GET|POST|PUT|DELETE /api/clases.php',
        'GET|POST|PUT|DELETE /api/asistencia.php',
    ],
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
