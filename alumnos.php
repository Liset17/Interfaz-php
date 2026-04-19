<?php
// =============================================================
// alumnos.php - CRUD de alumnos (PHP puro)
// -------------------------------------------------------------
// Acciones por POST (self-submit):
//   - crear:      campos del formulario "Agregar"
//   - editar:     muestra la fila en modo edicion (GET ?editar=ID)
//   - actualizar: guarda cambios del alumno ?id=ID
//   - eliminar:   borra alumno ?id=ID
//
// Al terminar redirigimos (PRG: Post/Redirect/Get) para evitar
// reenvios y mensajes mostrados como flash.
// =============================================================

require __DIR__ . '/includes/auth.php';

$user      = require_login();
$pageTitle = 'Gestion de Alumnos';

// --- Acciones POST ------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'crear') {
            $nombre   = trim($_POST['nombre']   ?? '');
            $email    = trim($_POST['email']    ?? '') ?: null;
            $telefono = trim($_POST['telefono'] ?? '') ?: null;
            $grupoId  = ($_POST['grupo_id'] ?? '') !== '' ? (int)$_POST['grupo_id'] : null;

            if ($nombre === '') {
                flash_set('error', 'El nombre es obligatorio');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO alumnos (nombre, email, telefono, grupo_id)
                     VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$nombre, $email, $telefono, $grupoId]);
                flash_set('ok', 'Alumno agregado');
            }
        } elseif ($accion === 'actualizar') {
            $id       = (int)($_POST['id'] ?? 0);
            $nombre   = trim($_POST['nombre']   ?? '');
            $email    = trim($_POST['email']    ?? '') ?: null;
            $telefono = trim($_POST['telefono'] ?? '') ?: null;
            $grupoId  = ($_POST['grupo_id'] ?? '') !== '' ? (int)$_POST['grupo_id'] : null;

            if ($id <= 0 || $nombre === '') {
                flash_set('error', 'Datos invalidos');
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE alumnos
                        SET nombre = ?, email = ?, telefono = ?, grupo_id = ?
                      WHERE id = ?'
                );
                $stmt->execute([$nombre, $email, $telefono, $grupoId, $id]);
                flash_set('ok', 'Alumno actualizado');
            }
        } elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM alumnos WHERE id = ?');
                $stmt->execute([$id]);
                flash_set('ok', 'Alumno eliminado');
            }
        }
    } catch (PDOException $e) {
        flash_set('error', 'Error BBDD: ' . $e->getMessage());
    }

    redirect(url('alumnos.php'));
}

// --- Carga de datos (GET) ------------------------------------
$editarId = isset($_GET['editar']) ? (int)$_GET['editar'] : 0;

$grupos = $pdo->query('SELECT id, nombre FROM grupos ORDER BY nombre')->fetchAll();

$alumnos = $pdo->query(
    'SELECT a.id, a.nombre, a.email, a.telefono,
            a.grupo_id, g.nombre AS grupo
       FROM alumnos a
       LEFT JOIN grupos g ON g.id = a.grupo_id
      ORDER BY a.id DESC'
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="view-container">
  <div class="view-header">
    <h1>&#128203; Gestion de Alumnos</h1>
    <a class="btn-cerrar" href="<?= e(url('home.php')) ?>">&#10006; Cerrar</a>
  </div>

  <a class="btn-volver" href="<?= e(url('home.php')) ?>">&larr; Volver al panel principal</a>

  <div class="formulario">
    <h3>&#10133; Agregar nuevo alumno</h3>
    <form method="post" action="<?= e(url('alumnos.php')) ?>">
      <input type="hidden" name="accion" value="crear" />
      <div class="form-group">
        <input type="text"  name="nombre"   placeholder="Nombre completo" required />
        <input type="email" name="email"    placeholder="Email" />
        <input type="text"  name="telefono" placeholder="Telefono" />
        <select name="grupo_id">
          <option value="">Sin grupo</option>
          <?php foreach ($grupos as $g): ?>
            <option value="<?= (int)$g['id'] ?>"><?= e($g['nombre']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-agregar">Agregar alumno</button>
      </div>
    </form>
  </div>

  <div class="tabla-container">
    <table class="tabla-alumnos">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Email</th>
          <th>Telefono</th>
          <th>Grupo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($alumnos)): ?>
          <tr><td colspan="6" class="sin-datos">No hay alumnos registrados todavia.</td></tr>
        <?php endif; ?>

        <?php foreach ($alumnos as $a): $editando = ($a['id'] == $editarId); ?>
          <?php if ($editando): ?>
            <tr>
              <form method="post" action="<?= e(url('alumnos.php')) ?>">
                <input type="hidden" name="accion" value="actualizar" />
                <input type="hidden" name="id" value="<?= (int)$a['id'] ?>" />
                <td><?= (int)$a['id'] ?></td>
                <td><input class="edit-input" type="text"  name="nombre"   value="<?= e($a['nombre']) ?>" required /></td>
                <td><input class="edit-input" type="email" name="email"    value="<?= e($a['email']) ?>" /></td>
                <td><input class="edit-input" type="text"  name="telefono" value="<?= e($a['telefono']) ?>" /></td>
                <td>
                  <select class="edit-input" name="grupo_id">
                    <option value="">Sin grupo</option>
                    <?php foreach ($grupos as $g): ?>
                      <option value="<?= (int)$g['id'] ?>" <?= ($a['grupo_id'] == $g['id']) ? 'selected' : '' ?>>
                        <?= e($g['nombre']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </td>
                <td>
                  <div class="acciones-fila">
                    <button type="submit" class="btn-guardar">&#128190; Guardar</button>
                    <a class="btn-editar" href="<?= e(url('alumnos.php')) ?>">Cancelar</a>
                  </div>
                </td>
              </form>
            </tr>
          <?php else: ?>
            <tr>
              <td><?= (int)$a['id'] ?></td>
              <td><?= e($a['nombre']) ?></td>
              <td><?= e($a['email']) ?></td>
              <td><?= e($a['telefono']) ?></td>
              <td><?= e($a['grupo']) ?></td>
              <td>
                <div class="acciones-fila">
                  <a class="btn-editar" href="<?= e(url('alumnos.php?editar=' . $a['id'])) ?>">&#9999;&#65039; Editar</a>
                  <form method="post" action="<?= e(url('alumnos.php')) ?>"
                        onsubmit="return confirm('Eliminar este alumno?');">
                    <input type="hidden" name="accion" value="eliminar" />
                    <input type="hidden" name="id" value="<?= (int)$a['id'] ?>" />
                    <button type="submit" class="btn-eliminar">&#128465; Eliminar</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
