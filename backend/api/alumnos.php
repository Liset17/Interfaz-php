<?php
// =============================================================
// /api/alumnos.php
// -------------------------------------------------------------
// CRUD completo sobre la tabla `alumnos`.
//
//   GET    /api/alumnos.php          -> listar todos
//   GET    /api/alumnos.php?id=1     -> obtener uno
//   POST   /api/alumnos.php          -> crear (body JSON)
//   PUT    /api/alumnos.php?id=1     -> actualizar (body JSON)
//   DELETE /api/alumnos.php?id=1     -> eliminar
//
// Requiere sesion iniciada (require_login).
// =============================================================

require __DIR__ . '/../config/cors.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/auth_check.php';

require_login();

$method = require_method(['GET', 'POST', 'PUT', 'DELETE']);
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    switch ($method) {
        // -------------------- LISTAR / VER --------------------
        case 'GET':
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'SELECT a.id, a.nombre, a.email, a.telefono,
                            a.grupo_id, g.nombre AS grupo
                       FROM alumnos a
                       LEFT JOIN grupos g ON g.id = a.grupo_id
                      WHERE a.id = ?'
                );
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if (!$row) {
                    json_response(['ok' => false, 'error' => 'Alumno no encontrado'], 404);
                }
                json_response(['ok' => true, 'data' => $row]);
            }
            $stmt = $pdo->query(
                'SELECT a.id, a.nombre, a.email, a.telefono,
                        a.grupo_id, g.nombre AS grupo
                   FROM alumnos a
                   LEFT JOIN grupos g ON g.id = a.grupo_id
                  ORDER BY a.id DESC'
            );
            json_response(['ok' => true, 'data' => $stmt->fetchAll()]);

        // -------------------------- CREAR ---------------------
        case 'POST':
            $b        = read_json_body();
            $nombre   = trim($b['nombre']   ?? '');
            $email    = trim($b['email']    ?? '') ?: null;
            $telefono = trim($b['telefono'] ?? '') ?: null;
            $grupo_id = isset($b['grupo_id']) && $b['grupo_id'] !== '' ? (int)$b['grupo_id'] : null;

            if ($nombre === '') {
                json_response(['ok' => false, 'error' => 'El nombre es obligatorio'], 400);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO alumnos (nombre, email, telefono, grupo_id)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$nombre, $email, $telefono, $grupo_id]);
            $newId = (int)$pdo->lastInsertId();

            json_response([
                'ok'   => true,
                'data' => [
                    'id'       => $newId,
                    'nombre'   => $nombre,
                    'email'    => $email,
                    'telefono' => $telefono,
                    'grupo_id' => $grupo_id,
                ],
            ], 201);

        // ----------------------- ACTUALIZAR -------------------
        case 'PUT':
            if ($id <= 0) {
                json_response(['ok' => false, 'error' => 'Falta ?id='], 400);
            }
            $b = read_json_body();

            // Solo actualizamos los campos que lleguen (patch parcial)
            $campos = [];
            $params = [];
            foreach (['nombre', 'email', 'telefono'] as $c) {
                if (array_key_exists($c, $b)) {
                    $campos[]  = "$c = ?";
                    $val       = trim((string)$b[$c]);
                    $params[]  = $val === '' ? null : $val;
                }
            }
            if (array_key_exists('grupo_id', $b)) {
                $campos[]  = 'grupo_id = ?';
                $params[]  = $b['grupo_id'] === '' || $b['grupo_id'] === null
                    ? null : (int)$b['grupo_id'];
            }
            if (empty($campos)) {
                json_response(['ok' => false, 'error' => 'Nada que actualizar'], 400);
            }
            $params[] = $id;

            $sql = 'UPDATE alumnos SET ' . implode(', ', $campos) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            json_response(['ok' => true, 'updated' => $stmt->rowCount()]);

        // ------------------------ ELIMINAR --------------------
        case 'DELETE':
            if ($id <= 0) {
                json_response(['ok' => false, 'error' => 'Falta ?id='], 400);
            }
            $stmt = $pdo->prepare('DELETE FROM alumnos WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['ok' => true, 'deleted' => $stmt->rowCount()]);
    }
} catch (PDOException $e) {
    json_response(['ok' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
}
