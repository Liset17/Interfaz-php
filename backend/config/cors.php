<?php
// =============================================================
// CORS + SESSION BOOTSTRAP
// -------------------------------------------------------------
// Se incluye al principio de CADA endpoint. Hace tres cosas:
//   1) Arranca la sesion PHP con cookie adecuada para dev
//   2) Manda cabeceras CORS que permiten llamadas desde Vite
//      (http://localhost:5173) y envio de cookies (credentials)
//   3) Responde 204 a los preflight OPTIONS
//
// En produccion (todo servido por Apache desde la misma origin)
// basta con dejar solo el session_start y las cabeceras de JSON.
// =============================================================

// --- Cookie de sesion: auto-detecta HTTPS ---
// En local (XAMPP) estamos en HTTP -> secure=false.
// En produccion (InfinityFree, etc.) estamos en HTTPS -> secure=true
// para que el navegador envie la cookie correctamente.
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (($_SERVER['SERVER_PORT'] ?? '') == 443);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '',
    'secure'   => $isHttps,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

// --- CORS ------------------------------------------------------
// Origen permitido: el dev server de Vite. Si llegaran otros, se
// ignoran. Para aceptar varios, se puede mantener una lista.
$allowedOrigins = [
    'http://localhost:5173',
    'http://127.0.0.1:5173',
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header('Vary: Origin');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
}

// Preflight: responder y salir sin ejecutar logica
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Todas las respuestas son JSON
header('Content-Type: application/json; charset=utf-8');
