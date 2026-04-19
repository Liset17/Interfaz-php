<?php
// =============================================================
// POST /api/auth/logout.php
// -------------------------------------------------------------
// Cierra la sesion del profesor. Devuelve { ok: true } siempre,
// tanto si habia sesion como si no.
// =============================================================

require __DIR__ . '/../../config/cors.php';
require __DIR__ . '/../../includes/helpers.php';

require_method(['POST']);

// Vaciamos la superglobal y destruimos la cookie de sesion
$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

session_destroy();

json_response(['ok' => true]);
