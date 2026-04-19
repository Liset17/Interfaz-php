<?php
// =============================================================
// asistencia.php - Control de asistencia (PHP puro)
// -------------------------------------------------------------
// - Filtros: fecha (por defecto hoy) y grupo.
// - Al guardar: hace un INSERT ... ON DUPLICATE KEY UPDATE por
//   cada alumno (un registro por alumno+fecha).
// - Botones "marcar todos presentes/ausentes" solo marcan los
//   checkboxes via JS sencillo.
//
// La ruta siempre usa metodo POST para guardar y GET para
// visualizar los filtros.
// =============================================================

require __DIR__ . '/includes/auth.php';

$user      = require_login();
$pageTitle = 'Control de Asistencia';

$fecha   = $_POST['fecha']    ?? $_GET['fecha']    ?? date('Y-m-d');
$grupoId = (int)($_POST['grupo_id'] ?? $_GET['grupo_id'] ?? 0);

// Validamos el formato de la fecha: si no es YYYY-MM-DD, caemos a hoy.
// Aunque las queries usan prepared statements (no hay SQL injection),
// asi evitamos guardar basura en la columna DATE.
$dt = DateTime::createFromFormat('Y-m-d', $fecha);
if (!$dt || $dt->format('Y-m-d') !== $fecha) {
    $fecha = date('Y-m-d');
}

// --- Guardado (POST accion=guardar) --------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'guardar') {
    $filas = $_POST['filas'] ?? [];  // array de alumno_id => [grupo_id, obs, presente]
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO asistencia (alumno_id, grupo_id, fecha, presente, observacion)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
               grupo_id    = VALUES(grupo_id),
               presente    = VALUES(presente),
               observacion = VALUES(observacion)'
        );

        foreach ($filas as $alumnoId => $datos) {
            $alumnoId = (int)$alumnoId;
            if ($alumnoId <= 0) continue;

            $gid      = isset($datos['grupo_id']) && $datos['grupo_id'] !== '' ? (int)$datos['grupo_id'] : null;
            $presente = !empty($datos['presente']) ? 1 : 0;
            $obs      = trim($datos['observacion'] ?? '') ?: null;

            $stmt->execute([$alumnoId, $gid, $fecha, $presente, $obs]);
        }
        flash_set('ok', 'Asistencia guardada');
    } catch (PDOException $e) {
        flash_set('error', 'Error BBDD: ' . $e->getMessage());
    }

    redirect(url('asistencia.php?fecha=' . urlencode($fecha) . '&grupo_id=' . $grupoId));
}

// --- Carga de datos ------------------------------------------
$grupos = $pdo->query('SELECT id, nombre FROM grupos ORDER BY nombre')->fetchAll();

$sqlAl = 'SELECT a.id, a.nombre, a.grupo_id, g.nombre AS grupo
            FROM alumnos a
            LEFT JOIN grupos g ON g.id = a.grupo_id';
$paramsAl = [];
if ($grupoId > 0) {
    $sqlAl .= ' WHERE a.grupo_id = ?';
    $paramsAl[] = $grupoId;
}
$sqlAl .= ' ORDER BY a.nombre';
$stmtAl = $pdo->prepare($sqlAl);
$stmtAl->execute($paramsAl);
$alumnos = $stmtAl->fetchAll();

// Registros ya guardados para esa fecha
$stmtAs = $pdo->prepare(
    'SELECT alumno_id, presente, observacion
       FROM asistencia
      WHERE fecha = ?'
);
$stmtAs->execute([$fecha]);
$registros = [];
foreach ($stmtAs->fetchAll() as $r) {
    $registros[(int)$r['alumno_id']] = $r;
}

// Calcular estadisticas
$total     = count($alumnos);
$presentes = 0;
foreach ($alumnos as $a) {
    $reg = $registros[(int)$a['id']] ?? null;
    if ($reg && (int)$reg['presente'] === 1) $presentes++;
}
$porc = $total > 0 ? (int)round(($presentes / $total) * 100) : 0;

require __DIR__ . '/includes/header.php';
?>

<div class="view-container">
  <div class="view-header">
    <h1>&#9989; Control de Asistencia</h1>
    <a class="btn-cerrar" href="<?= e(url('home.php')) ?>">&#10006; Cerrar</a>
  </div>

  <a class="btn-volver" href="<?= e(url('home.php')) ?>">&larr; Volver al panel principal</a>

  <!-- Filtros (GET) -->
  <form method="get" action="<?= e(url('asistencia.php')) ?>" class="filtros">
    <div class="campo">
      <label>&#128197; Fecha:</label>
      <input type="date" name="fecha" value="<?= e($fecha) ?>" onchange="this.form.submit()" />
    </div>
    <div class="campo">
      <label>&#128101; Grupo:</label>
      <select name="grupo_id" onchange="this.form.submit()">
        <option value="0">Todos los grupos</option>
        <?php foreach ($grupos as $g): ?>
          <option value="<?= (int)$g['id'] ?>" <?= ($grupoId === (int)$g['id']) ? 'selected' : '' ?>>
            <?= e($g['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="resumen">
      <strong>Presentes:</strong> <?= $presentes ?> / <?= $total ?> (<?= $porc ?>%)
    </div>
  </form>

  <!-- Guardado (POST) -->
  <form method="post" action="<?= e(url('asistencia.php')) ?>" id="form-asistencia">
    <input type="hidden" name="accion"   value="guardar" />
    <input type="hidden" name="fecha"    value="<?= e($fecha) ?>" />
    <input type="hidden" name="grupo_id" value="<?= (int)$grupoId ?>" />

    <div class="tabla-container">
      <table class="tabla-asistencia">
        <thead>
          <tr>
            <th>Alumno</th>
            <th>Grupo</th>
            <th>Presente</th>
            <th>Observacion</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($alumnos)): ?>
            <tr><td colspan="4" class="sin-datos">No hay alumnos para este filtro.</td></tr>
          <?php endif; ?>

          <?php foreach ($alumnos as $a):
              $reg      = $registros[(int)$a['id']] ?? null;
              $presente = $reg ? (int)$reg['presente'] : 0;
              $obs      = $reg ? ($reg['observacion'] ?? '') : '';
          ?>
            <tr>
              <td>
                <?= e($a['nombre']) ?>
                <input type="hidden"
                       name="filas[<?= (int)$a['id'] ?>][grupo_id]"
                       value="<?= e($a['grupo_id']) ?>" />
              </td>
              <td><?= e($a['grupo'] ?: '-') ?></td>
              <td class="checkbox-cell">
                <!-- hidden + checkbox: asegura que si esta desmarcado llegue 0 -->
                <input type="hidden" name="filas[<?= (int)$a['id'] ?>][presente]" value="0" />
                <input type="checkbox"
                       class="chk-presente"
                       name="filas[<?= (int)$a['id'] ?>][presente]"
                       value="1"
                       <?= $presente ? 'checked' : '' ?> />
              </td>
              <td>
                <input type="text"
                       class="observacion-input"
                       name="filas[<?= (int)$a['id'] ?>][observacion]"
                       value="<?= e($obs) ?>"
                       placeholder="Ej: Llego tarde" />
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <?php if (!empty($alumnos)): ?>
    <div class="acciones">
      <button type="button" class="btn-marcar-todos"   onclick="marcarTodos(true)">
        &#9989; Marcar todos presentes
      </button>
      <button type="button" class="btn-marcar-ninguno" onclick="marcarTodos(false)">
        &#10060; Marcar todos ausentes
      </button>
      <button type="submit" class="btn-guardar-asist">&#128190; Guardar asistencia</button>
    </div>
    <?php endif; ?>
  </form>
</div>

<script>
// JS minimo: solo para los botones "marcar todos". Toda la
// logica de persistencia va por el submit normal del form.
function marcarTodos(estado) {
    document.querySelectorAll('.chk-presente').forEach(function (c) {
        c.checked = estado;
    });
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
