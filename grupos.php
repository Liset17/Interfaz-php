<?php
// =============================================================
// grupos.php - Gestion de grupos (PHP puro)
// -------------------------------------------------------------
// Incluye creacion, edicion, borrado y buscador sencillo por
// GET (?q=...). Cada tarjeta muestra una barra de progreso con
// el % de alumnos respecto al cupo maximo del grupo.
// =============================================================

require __DIR__ . '/includes/auth.php';

$user      = require_login();
$pageTitle = 'Gestion de Grupos';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'crear') {
            $nombre  = trim($_POST['nombre']  ?? '');
            $horario = trim($_POST['horario'] ?? '') ?: null;
            $cupo    = ($_POST['cupo'] ?? '') !== '' ? max(0, (int)$_POST['cupo']) : 20;

            if ($nombre === '') {
                flash_set('error', 'El nombre es obligatorio');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO grupos (nombre, horario, cupo, profesor_id)
                     VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$nombre, $horario, $cupo, (int)$user['id']]);
                flash_set('ok', 'Grupo creado');
            }
        } elseif ($accion === 'actualizar') {
            $id      = (int)($_POST['id'] ?? 0);
            $nombre  = trim($_POST['nombre']  ?? '');
            $horario = trim($_POST['horario'] ?? '') ?: null;
            $cupo    = max(0, (int)($_POST['cupo'] ?? 0));

            if ($id <= 0 || $nombre === '') {
                flash_set('error', 'Datos invalidos');
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE grupos
                        SET nombre = ?, horario = ?, cupo = ?
                      WHERE id = ?'
                );
                $stmt->execute([$nombre, $horario, $cupo, $id]);
                flash_set('ok', 'Grupo actualizado');
            }
        } elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM grupos WHERE id = ?');
                $stmt->execute([$id]);
                flash_set('ok', 'Grupo eliminado');
            }
        }
    } catch (PDOException $e) {
        flash_set('error', 'Error BBDD: ' . $e->getMessage());
    }

    // Mantenemos la busqueda si la habia
    $q = isset($_POST['q']) ? '?q=' . urlencode($_POST['q']) : '';
    redirect(url('grupos.php' . $q));
}

$busqueda = trim($_GET['q']      ?? '');
$editarId = isset($_GET['editar']) ? (int)$_GET['editar'] : 0;

$sql = 'SELECT g.id, g.nombre, g.horario, g.cupo, g.profesor_id,
               p.nombre AS profesor,
               (SELECT COUNT(*) FROM alumnos a WHERE a.grupo_id = g.id) AS total_alumnos
          FROM grupos g
          LEFT JOIN profesores p ON p.id = g.profesor_id';
$params = [];
if ($busqueda !== '') {
    $sql .= ' WHERE g.nombre LIKE ?';
    $params[] = '%' . $busqueda . '%';
}
$sql .= ' ORDER BY g.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$grupos = $stmt->fetchAll();

$totalAlumnos = 0;
foreach ($grupos as $g) {
    $totalAlumnos += (int)$g['total_alumnos'];
}

/** Devuelve % ocupacion (0-100) y clase CSS segun umbral. */
function porcentaje_grupo(array $g): int
{
    $cupo = max(1, (int)$g['cupo']);
    $act  = (int)$g['total_alumnos'];
    return min(100, (int)round(($act / $cupo) * 100));
}
function clase_progreso(int $p): string
{
    if ($p >= 90) return 'progreso-lleno';
    if ($p >= 70) return 'progreso-alto';
    return 'progreso-normal';
}

require __DIR__ . '/includes/header.php';
?>

<div class="view-container">
  <div class="view-header">
    <h1>&#128101; Gestion de Grupos</h1>
    <a class="btn-cerrar" href="<?= e(url('home.php')) ?>">&#10006; Cerrar</a>
  </div>

  <a class="btn-volver" href="<?= e(url('home.php')) ?>">&larr; Volver al panel principal</a>

  <div class="formulario">
    <h3>&#10133; Crear nuevo grupo</h3>
    <form method="post" action="<?= e(url('grupos.php')) ?>">
      <input type="hidden" name="accion" value="crear" />
      <input type="hidden" name="q" value="<?= e($busqueda) ?>" />
      <div class="form-group">
        <input type="text"   name="nombre"  placeholder="Nombre del grupo" required />
        <input type="text"   name="horario" placeholder="Horario (ej: Martes 19:00)" />
        <input type="number" name="cupo"    placeholder="Cupo maximo" min="0" />
        <button type="submit" class="btn-agregar">Crear grupo</button>
      </div>
    </form>
  </div>

  <div class="filtros">
    <form method="get" action="<?= e(url('grupos.php')) ?>" class="campo">
      <label>&#128269; Buscar grupo:</label>
      <input type="text" name="q" value="<?= e($busqueda) ?>" placeholder="Nombre del grupo..." />
      <button type="submit" class="btn-agregar">Buscar</button>
      <?php if ($busqueda !== ''): ?>
        <a class="btn-volver" style="margin:0" href="<?= e(url('grupos.php')) ?>">Limpiar</a>
      <?php endif; ?>
    </form>
    <div class="resumen">
      <strong>Total grupos:</strong> <?= count($grupos) ?> |
      <strong>Total alumnos:</strong> <?= (int)$totalAlumnos ?>
    </div>
  </div>

  <div class="grupos-grid">
    <?php if (empty($grupos)): ?>
      <div class="sin-datos">No hay grupos registrados todavia.</div>
    <?php endif; ?>

    <?php foreach ($grupos as $g):
        $p         = porcentaje_grupo($g);
        $claseProg = clase_progreso($p);
        $editando  = ($g['id'] == $editarId);
    ?>
      <div class="grupo-card">
        <?php if ($editando): ?>
          <form method="post" action="<?= e(url('grupos.php')) ?>">
            <input type="hidden" name="accion" value="actualizar" />
            <input type="hidden" name="id"     value="<?= (int)$g['id'] ?>" />
            <input type="hidden" name="q"      value="<?= e($busqueda) ?>" />

            <div class="card-header">
              <input class="edit-input edit-title" type="text" name="nombre"
                     value="<?= e($g['nombre']) ?>" required />
              <div class="acciones-card">
                <button type="submit" class="btn-guardar">&#128190;</button>
                <a class="btn-editar" href="<?= e(url('grupos.php')) ?>">X</a>
              </div>
            </div>
            <div class="card-body">
              <div class="info-grupo">
                <p><strong>&#128100; Profesor:</strong> <?= e($g['profesor'] ?: 'Sin asignar') ?></p>
                <p>
                  <strong>&#9200; Horario:</strong>
                  <input class="edit-input" type="text" name="horario"
                         value="<?= e($g['horario']) ?>" />
                </p>
                <p>
                  <strong>&#128202; Cupo maximo:</strong>
                  <input class="edit-input" type="number" name="cupo"
                         value="<?= (int)$g['cupo'] ?>" min="0" />
                </p>
                <p><strong>&#128101; Alumnos actuales:</strong>
                   <?= (int)$g['total_alumnos'] ?> / <?= (int)$g['cupo'] ?></p>
              </div>
              <div class="progress-bar">
                <div class="progress-fill <?= e($claseProg) ?>" style="width: <?= $p ?>%">
                  <?= $p ?>%
                </div>
              </div>
            </div>
          </form>
        <?php else: ?>
          <div class="card-header">
            <h2><?= e($g['nombre']) ?></h2>
            <div class="acciones-card">
              <a class="btn-editar"
                 href="<?= e(url('grupos.php?editar=' . $g['id'] . ($busqueda !== '' ? '&q=' . urlencode($busqueda) : ''))) ?>">
                 &#9999;&#65039;
              </a>
              <form method="post" action="<?= e(url('grupos.php')) ?>"
                    onsubmit="return confirm('Eliminar este grupo?');">
                <input type="hidden" name="accion" value="eliminar" />
                <input type="hidden" name="id"     value="<?= (int)$g['id'] ?>" />
                <input type="hidden" name="q"      value="<?= e($busqueda) ?>" />
                <button type="submit" class="btn-eliminar">&#128465;</button>
              </form>
            </div>
          </div>
          <div class="card-body">
            <div class="info-grupo">
              <p><strong>&#128100; Profesor:</strong> <?= e($g['profesor'] ?: 'Sin asignar') ?></p>
              <p><strong>&#9200; Horario:</strong> <?= e($g['horario'] ?: 'No definido') ?></p>
              <p><strong>&#128202; Cupo maximo:</strong> <?= (int)$g['cupo'] ?></p>
              <p><strong>&#128101; Alumnos actuales:</strong>
                 <?= (int)$g['total_alumnos'] ?> / <?= (int)$g['cupo'] ?></p>
            </div>
            <div class="progress-bar">
              <div class="progress-fill <?= e($claseProg) ?>" style="width: <?= $p ?>%">
                <?= $p ?>%
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
