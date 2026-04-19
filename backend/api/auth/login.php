<?php
// =============================================================
// POST /api/auth/login.php
// Body JSON: { email, password }
// -------------------------------------------------------------
// Valida credenciales y, si son correctas, deja al profesor
// como $_SESSION['user']. A partir de ahi el resto de endpoints
// leen la sesion para autorizar.
// =============================================================

require __DIR__ . '/../../config/cors.php';
require __DIR__ . '/../../config/db.php';
require __DIR__ . '/../../includes/helpers.php';

require_method(['POST']);

$body     = read_json_body();
$email    = trim($body['email']    ?? '');
$password = (string)($body['password'] ?? '');

if ($email === '' || $password === '') {
    json_response(['ok' => false, 'error' => 'Email y contrasena son obligatorios'], 400);
}

try {
    $stmt = $pdo->prepare(
        'SELECT id, nombre, email, password_hash, rol
           FROM profesores
          WHERE email = ?
          LIMIT 1'
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Mismo mensaje para no filtrar si el email existe
        json_response(['ok' => false, 'error' => 'Credenciales incorrectas'], 401);
    }

    // Regeneramos el id de sesion tras login (buena practica)
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id'     => (int)$user['id'],
        'nombre' => $user['nombre'],
        'email'  => $user['email'],
        'rol'    => $user['rol'],
    ];

    json_response([
        'ok'   => true,
        'user' => $_SESSION['user'],
    ]);
} catch (PDOException $e) {
    json_response(['ok' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
}
