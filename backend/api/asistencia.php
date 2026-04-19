<?php
// =============================================================
// /api/asistencia.php
// -------------------------------------------------------------
// CRUD sobre `asistencia`. Un registro = un alumno y una fecha.
// Hay UNIQUE (alumno_id, fecha), asi que si se inserta el mismo
// alumno dos veces en la misma fecha, usamos "upsert" con
// ON DUPLICATE KEY UPDATE.
//
//   GET    ?fecha=YYYY-MM-DD      -> lista del dia
//   GET    ?alumno_id=1           -> historico de un alumno
//   GET    ?id=1                  -> uno concreto
//   POST   (body)                 -> crear o actualizar (upsert)
//   PUT    ?id=1 (body)           -> editar uno
//   DELETE ?id=1                  -> eliminar
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
        case 'GET':
            if ($id > 0) {
                $stmt = $pdo->prepare(
                    'SELECT a.id, a.alumno_id, a.grupo_id, a.fecha,
                            a.presente, a.observacion,
                            al.nombre AS alumno,
                            g.nombre  AS grupo
                       FROM asistencia a
                       LEFT JOIN alumnos al ON al.id = a.alumno_id
                       LEFT JOIN grupos  g  ON g.id  = a.grupo_id
                      WHERE a.id = ?'
                );
                $stmt->execute([$id]);
                $row = $stmt->fetch();
                if (!$row) json_response(['ok' => false, 'error' => 'Registro no encontrado'], 404);
                json_response(['ok' => true, 'data' => $row]);
            }

            $where  = [];
            $params = [];
            if (!empty($_GET['fecha'])) {
                $where[]  = 'a.fecha = ?';
                $params[] = $_GET['fecha'];
            }
            if (!empty($_GET['alumno_id'])) {
                $where[]  = 'a.alumno_id = ?';
                $params[] = (int)$_GET['alumno_id'];
            }
            if (!empty($_GET['grupo_id'])) {
                $where[]  = 'a.grupo_id = ?';
                $params[] = (int)$_GET['grupo_id'];
            }
            $sql = 'SELECT a.id, a.alumno_id, a.grupo_id, a.fecha,
                           a.presente, a.observacion,
                           al.nombre AS alumno,
                           g.nombre  AS grupo
                      FROM asistencia a
                      LEFT JOIN alumnos al ON al.id = a.alumno_id
                      LEFT JOIN grupos  g  ON g.id  = a.grupo_id';
            if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
            $sql .= ' ORDER BY a.fecha DESC, al.nombre ASC';

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            json_response(['ok' => true, 'data' => $stmt->fetchAll()]);

        case 'POST':
            $b        = read_json_body();
            $alumno   = (int)($b['alumno_id'] ?? 0);
            $grupo    = isset($b['grupo_id']) && $b['grupo_id'] !== '' ? (int)$b['grupo_id'] : null;
            $fecha    = trim($b['fecha'] ?? '');
            $presente = !empty($b['presente']) ? 1 : 0;
            $obs      = trim($b['observacion'] ?? '') ?: null;

            if ($alumno <= 0 || $fecha === '') {
                json_response(['ok' => false, 'error' => 'alumno_id y fecha son obligatorios'], 400);
            }

            // UPSERT: si ya existe para (alumno, fecha) actualiza
            $stmt = $pdo->prepare(
                'INSERT INTO asistencia (alumno_id, grupo_id, fecha, presente, observacion)
                 VALUES (?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE
                   grupo_id    = VALUES(grupo_id),
                   presente    = VALUES(presente),
                   observacion = VALUES(observacion)'
            );
            $stmt->execute([$alumno, $grupo, $fecha, $presente, $obs]);

            // lastInsertId devuelve 0 si solo fue UPDATE; en ese caso lo buscamos
            $newId = (int)$pdo->lastInsertId();
            if ($newId === 0) {
                $q = $pdo->prepare('SELECT id FROM asistencia WHERE alumno_id = ? AND fecha = ?');
                $q->execute([$alumno, $fecha]);
                $newId = (int)($q->fetchColumn() ?: 0);
            }

            json_response([
                'ok'   => true,
                'data' => [
                    'id'          => $newId,
                    'alumno_id'   => $alumno,
                    'grupo_id'    => $grupo,
                    'fecha'       => $fecha,
                    'presente'    => $presente,
                    'observacion' => $obs,
                ],
            ], 201);

        case 'PUT':
            if ($id <= 0) json_response(['ok' => false, 'error' => 'Falta ?id='], 400);
            $b = read_json_body();

            $campos = [];
            $params = [];
            if (array_key_exists('presente', $b)) {
                $campos[]  = 'presente = ?';
                $params[]  = !empty($b['presente']) ? 1 : 0;
            }
            if (array_key_exists('observacion', $b)) {
                $campos[]  = 'observacion = ?';
                $val       = trim((string)$b['observacion']);
                $params[]  = $val === '' ? null : $val;
            }
            if (array_key_exists('grupo_id', $b)) {
                $campos[]  = 'grupo_id = ?';
                $params[]  = $b['grupo_id'] === '' || $b['grupo_id'] === null
                    ? null : (int)$b['grupo_id'];
            }
            if (array_key_exists('fecha', $b)) {
                $campos[]  = 'fecha = ?';
                $params[]  = $b['fecha'];
            }
            if (empty($campos)) {
                json_response(['ok' => false, 'error' => 'Nada que actualizar'], 400);
            }
            $params[] = $id;

            $sql = 'UPDATE asistencia SET ' . implode(', ', $campos) . ' WHERE id = ?';
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            json_response(['ok' => true, 'updated' => $stmt->rowCount()]);

        case 'DELETE':
            if ($id <= 0) json_response(['ok' => false, 'error' => 'Falta ?id='], 400);
            $stmt = $pdo->prepare('DELETE FROM asistencia WHERE id = ?');
            $stmt->execute([$id]);
            json_response(['ok' => true, 'deleted' => $stmt->rowCount()]);
    }
} catch (PDOException $e) {
    json_response(['ok' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()], 500);
}
