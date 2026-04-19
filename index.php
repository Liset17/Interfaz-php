<?php
// =============================================================
// index.php - punto de entrada
// -------------------------------------------------------------
// Si hay sesion iniciada -> home.php, si no -> login.php.
// =============================================================

require __DIR__ . '/includes/auth.php';

if (current_user() !== null) {
    redirect(url('home.php'));
}
redirect(url('login.php'));
