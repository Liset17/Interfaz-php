<?php
// =============================================================
// AUTH CHECK
// -------------------------------------------------------------
// Los endpoints "protegidos" (alumnos, grupos, clases,
// asistencia, auth/me, auth/logout) llaman a require_login()
// al principio. Si no hay sesion devuelve 401.
// =============================================================

require_once __DIR__ . '/helpers.php';

/**
 * Devuelve el profesor logueado en forma de array, o null.
 */
function current_user(): ?array
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }
    return $_SESSION['user'];
}

/**
 * Bloquea la ejecucion si no hay sesion iniciada. Devuelve el
 * usuario actual para poder usarlo a continuacion.
 */
function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        json_response(['ok' => false, 'error' => 'No autenticado'], 401);
    }
    return $user;
}
