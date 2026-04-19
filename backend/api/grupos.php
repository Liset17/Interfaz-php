<?php
// =============================================================
// /api/grupos.php
// -------------------------------------------------------------
// CRUD sobre la tabla `grupos`.
//   GET    ?            -> listar
//   GET    ?id=1        -> uno
//   POST   (body JSON)  -> crear
//   PUT    ?id=1 (body) -> actualizar parcial
//   DELETE ?id=1        -> eliminar
// =============================================================

require __DIR__ . '/../config/cors.php';
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/auth_check.php';

$me     = require_login();
$method = require_method(['GET', 'POST', 'PUT', 'DELETE']);
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    switch ($method) {
        case 'GET':
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'SELECT g.id, g.nombre, g.horario, g.cupo, g.profesor_id,
                            p.nombre AS profesor
                       FROM grupos g
                       LEFT JOIN profesores p ON p.id = g.profesor_id
                      WHERE g.id = ?'
                );
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if (!$row) json_response(['ok' => false, 'error' => 'Grupo no encontrado'], 404);
                json_response(['ok' => true, 'data' => $row]);
            }
            $stmt = $pdo->query(
                'SELECT g.id, g.nombre, g.horario, g.cupo, g.profesor_id,
                        p.nombre AS profesor,
                        (SELECT COUNT(*) FROM alumnos a WHERE a.grupo_id = g.id) AS total_alumnos
                   FROM grupos g
                   LEFT JOIN profesores p ON p.id = g.profesor_id
                  ORDER BY g.id DESC'
            );
            json_response(['ok' => true, 'data' => $stmt->fetchAll()]);

        case 'POST':
            $b          = read_json_body();
            $nombre     = trim($b['nombre']  ?? '');
            $horario    = trim($b['horario'] ?? '') ?: null;
            $cupo       = isset($b['cupo']) ? max(0, (int)$b['cupo']) : 20;
            // Si no se manda profesor_id, asociamos el grupo al profesor logueado
            $profesorId = isset($b['profesor_id']) && $b['profesor_id'] !== ''
                ? (int)$b['profesor_id']
                : (int)$me['id'];

            if ($nombre === '') {
                json_response(['ok' => false, 'error' => 'El nombre es obligatorio'], 400);
            }

            $stmt = $pdo->prepare(
                'INSERT INTO grupos (nombre, horario, cupo, profesor_id)
                 VALUES (?, ?, ?, ?)'
            );
            $stmt->execute([$nombre, $horario, $cupo, $profesorId]);
            $newId = (int)$pdo->lastInsertId();

            json_response([
                'ok'   => true,
                'data' => [
                    'id'          => $newId,
                    'nombre'      => $nombre,
                    'horario'     => $horario,
                    'cupo'        => $cupo,
                    'profesor_id' => $profesorId,
                ],
            ], 201);

        case 'PUT':
            if ($id <= 0) json_response(['ok' => false, 'error' => 'Falta ?id='], 400);
            $b = read_json_body();

            $campos = [];
            $params = [];
            foreach (['nombre', 'horario'] as $c) {
                if (array_key_exists($c, $b)) {
                    $campos[]  = "$c = ?";
                    $val       = trim((string)$b[$c]);
                    $params[]  = $val === '' ? null : $val;
                }
            }
            if (array_key_exists('cupo', $b)) {
                $campos[]  = 'cupo = ?';
                $params[]  = max(0, (int)$b['cupo']);
            }
            if (array_key_exists('profesor_id', $b)) {
                $campos[]  = 'profesor_id = ?';
                $params[]  = $b['profesor_id'] === '' || $b['profesor_id'] === null
                    ? null : (int)$b['profesor_id'];
            }
            if (empty($campos)) {
                json_response(['ok' => false, 'error' => 'Nada que actualizar'], 400);
            }
            $params[] = $id;

            $sql = 'UPDATE grupos SET ' . implode(', ', $campos) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            json_response(['ok' => true, 'updated' => $stmt->rowCount()]);

        case 'DELETE':
            if ($id <= 0) json_response(['ok' => false, 'error' => 'Falta ?id='], 400);
            $stmt = $pdo->prepare('DELETE FROM grupos WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['ok' => true, 'deleted' => $stmt->rowCount()]);
    }
} catch (PDOException $e) {
    json_response(['ok' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
}
