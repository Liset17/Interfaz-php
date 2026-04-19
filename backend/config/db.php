<?php
// =============================================================
// CONEXION A MYSQL
// -------------------------------------------------------------
// Este archivo crea una conexion PDO reutilizable. Todas las APIs
// (alumnos, grupos, clases, asistencia, auth) hacen
// `require __DIR__ . '/../config/db.php'` para obtener $pdo.
//
// Por defecto usa la config de XAMPP (root/sin password, 127.0.0.1).
// Si existe `db.local.php` en este mismo directorio, se incluye y
// puede sobrescribir $DB_HOST/$DB_NAME/$DB_USER/$DB_PASS. Ese archivo
// NO se sube a git (esta en .gitignore) para no exponer credenciales
// en repositorios publicos.
// =============================================================

// Valores por defecto (XAMPP en local)
$DB_HOST = '127.0.0.1';
$DB_NAME = 'academia_teatro';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

// Override opcional con credenciales reales (produccion, InfinityFree...)
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
    // No exponemos detalles en produccion; en local ayuda para depurar
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'ok'    => false,
        'error' => 'No se pudo conectar a la base de datos: ' . $e->getMessage(),
    ]);
    exit;
}
