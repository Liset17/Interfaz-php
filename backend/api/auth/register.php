<?php
// =============================================================
// POST /api/auth/register.php
// Body JSON: { nombre, email, password }
// -------------------------------------------------------------
// Crea un nuevo profesor. No inicia sesion automaticamente
// (para eso el frontend puede llamar a /api/auth/login.php
// justo despues). Devuelve el usuario creado sin el hash.
// =============================================================

require __DIR__ . '/../../config/cors.php';
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/helpers.php';

require_method(['POST']);

$body     = read_json_body();
$nombre   = trim($body['nombre']   ?? '');
$email    = trim($body['email']    ?? '');
$password = (string)($body['password'] ?? '');

// Validaciones basicas
if ($nombre === '' || $email === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Nombre, email y contrasena son obligatorios'], 400);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    json_response(['ok' => false, 'error' => 'Email invalido'], 400);
}
if (strlen($password) < 6) {
    json_response(['ok' => false, 'error' => 'La contrasena debe tener al menos 6 caracteres'], 400);
}

try {
    // Evitamos duplicados: la BBDD ya tiene UNIQUE, pero damos
    // un mensaje mas amable antes de que salte el constraint.
    $stmt = $pdo->prepare('SELECT id FROM profesores WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        json_response(['ok' => false, 'error' => 'Ya existe un profesor con ese email'], 409);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $ins = $pdo->prepare(
        'INSERT INTO profesores (nombre, email, password_hash, rol)
         VALUES (?, ?, ?, "profesor")'
    );
    $ins->execute([$nombre, $email, $hash]);
    $id = (int)$pdo->lastInsertId();

    json_response([
        'ok'   => true,
        'user' => [
            'id'     => $id,
            'nombre' => $nombre,
            'email'  => $email,
            'rol'    => 'profesor',
        ],
    ], 201);
} catch (PDOException $e) {
    json_response(['ok' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
}
