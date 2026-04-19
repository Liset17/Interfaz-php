<?php
// =============================================================
// CONEXION A MYSQL
// -------------------------------------------------------------
// Crea una conexion PDO reutilizable. Todas las paginas la usan
// haciendo `require __DIR__ . '/../config/db.php'` (o similar).
//
// Por defecto usa la config de XAMPP (root/sin password, 127.0.0.1).
// Si existe `db.local.php` en este mismo directorio, se incluye y
// puede sobrescribir $DB_HOST/$DB_NAME/$DB_USER/$DB_PASS. Ese archivo
// NO se sube a git (esta en .gitignore).
// =============================================================

// Valores por defecto (XAMPP en local)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'academia_teatro';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

// Override opcional con credenciales reales
if (file_exists(__DIR__ . '/db.local.php')) {
    require __DIR__ . '/db.local.php';
}

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=$DB_CHARSET";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // En una app PHP tradicional mostramos un mensaje simple
    // (la pagina aun no tiene layout cargado)
    http_response_code(500);
    echo '<!doctype html><meta charset="utf-8"><title>Error</title>';
    echo '<h1>No se pudo conectar a la base de datos</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    exit;
}
