<?php
// =============================================================
// AUTENTICACION
// -------------------------------------------------------------
// Helpers para paginas protegidas:
//   - current_user(): devuelve el profesor en sesion (o null)
//   - require_login(): redirige a login.php si no hay sesion
//   - require_guest(): redirige al panel si ya hay sesion
//
// Depende de includes/bootstrap.php (session, url()).
// =============================================================

require_once __DIR__ . '/bootstrap.php';

function current_user(): ?array
{
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        return null;
    }
    return $_SESSION['user'];
}

function require_login(): array
{
    $user = current_user();
    if ($user === null) {
        redirect(url('login.php'));
    }
    return $user;
}

function require_guest(): void
{
    if (current_user() !== null) {
        redirect(url('home.php'));
    }
}
