<?php
// =============================================================
// BOOTSTRAP
// -------------------------------------------------------------
// Se incluye al principio de CADA pagina .php. Hace tres cosas:
//   1) Arranca la sesion PHP con cookie adecuada (auto HTTPS)
//   2) Carga la conexion PDO en $pdo
//   3) Expone helpers utilizados en todas las vistas:
//      - e($s)      -> htmlspecialchars seguro
//      - flash_set(), flash_get()  -> mensajes entre redirecciones
//      - redirect($url)
// =============================================================

// Cookie de sesion: en local HTTP, en prod HTTPS
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (($_SERVER['SERVER_PORT'] ?? '') == 443);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/../config/db.php';

/** Escapa contenido para mostrar en HTML (evita XSS). */
function e($v): string
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Guarda un mensaje flash que se mostrara tras la siguiente redireccion. */
function flash_set(string $tipo, string $mensaje): void
{
    $_SESSION['flash'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/** Recupera y limpia los mensajes flash acumulados. */
function flash_get(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/** Redirige a otra pagina interna y corta la ejecucion. */
function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

/** Devuelve la URL base del proyecto (ej: /php-interfaz). */
function base_url(): string
{
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    // dirname de /php-interfaz/home.php -> /php-interfaz
    $dir = rtrim(str_replace('\\', '/', dirname($script)), '/');
    return $dir === '' ? '' : $dir;
}

/** Construye una URL interna a partir de una ruta relativa. */
function url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return base_url() . '/' . $path;
}
