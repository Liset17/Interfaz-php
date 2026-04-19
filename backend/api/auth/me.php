<?php
// =============================================================
// GET /api/auth/me.php
// -------------------------------------------------------------
// Devuelve el usuario actual si hay sesion, o 401 si no.
// El frontend lo llama al arrancar para saber si ya hay un
// profesor logueado y poder redirigir a /login o al panel.
// =============================================================

require __DIR__ . '/../../config/cors.php';
require __DIR__ . '/../../includes/helpers.php';
require __DIR__ . '/../../includes/auth_check.php';

require_method(['GET']);

$user = current_user();
if ($user === null) {
    // Devolvemos 200 con user=null para que el frontend
    // no tenga que tratar el 401 como caso especial al
    // comprobar sesion al iniciar.
    json_response(['ok' => true, 'user' => null]);
}

json_response(['ok' => true, 'user' => $user]);
