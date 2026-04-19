<?php
// =============================================================
// clases.php - CRUD de clases (PHP puro)
// -------------------------------------------------------------
// Mismo patron que alumnos.php: acciones via POST y redireccion
// (PRG). Cada clase queda asociada al profesor logueado al
// crearla (profesor_id = $user['id']).
// =============================================================

require __DIR__ . '/includes/auth.php';

$user      = require_login();
$pageTitle = 'Gestion de Clases';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'crear') {
            $nombre  = trim($_POST['nombre']  ?? '');
            $horario = trim($_POST['horario'] ?? '') ?: null;
            $cupo    = max(0, (int)($_POST['cupo'] ?? 20));

            if ($nombre === '' || $horario === null) {
                flash_set('error', 'Nombre y horario son obligatorios');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO clases (nombre, horario, cupo, profesor_id)
                     VALUES (?, ?, ?, ?)'
                );
                $stmt->execute([$nombre, $horario, $cupo, (int)$user['id']]);
                flash_set('ok', 'Clase agregada');
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
                    'UPDATE clases
                        SET nombre = ?, horario = ?, cupo = ?
                      WHERE id = ?'
                );
                $stmt->execute([$nombre, $horario, $cupo, $id]);
                flash_set('ok', 'Clase actualizada');
            }
        } elseif ($accion === 'eliminar') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare('DELETE FROM clases WHERE id = ?');
                $stmt->execute([$id]);
                flash_set('ok', 'Clase eliminada');
            }
        }
    } catch (PDOException $e) {
        flash_set('error', 'Error BBDD: ' . $e->getMessage());
    }

    redirect(url('clases.php'));
}

$editarId = isset($_GET['editar']) ? (int)$_GET['editar'] : 0;

$clases = $pdo->query(
    'SELECT c.id, c.nombre, c.horario, c.cupo, c.profesor_id,
            p.nombre AS profesor
       FROM clases c
       LEFT JOIN profesores p ON p.id = c.profesor_id
      ORDER BY c.id DESC'
)->fetchAll();

require __DIR__ . '/includes/header.php';
?>

<div class="view-container">
  <div class="view-header">
    <h1>&#128218; Gestion de Clases</h1>
    <a class="btn-cerrar" href="<?= e(url('home.php')) ?>">&#10006; Cerrar</a>
  </div>

  <a class="btn-volver" href="<?= e(url('home.php')) ?>">&larr; Volver al panel principal</a>

  <div class="formulario">
    <h3>&#10133; Agregar nueva clase</h3>
    <form method="post" action="<?= e(url('clases.php')) ?>">
      <input type="hidden" name="accion" value="crear" />
      <div class="form-group">
        <input type="text"   name="nombre"  placeholder="Nombre de la clase" required />
        <input type="text"   name="horario" placeholder="Horario (ej: Lunes 18:00)" required />
        <input type="number" name="cupo"    placeholder="Cupo maximo" min="0" />
        <button type="submit" class="btn-agregar">Agregar clase</button>
      </div>
    </form>
  </div>

  <div class="tabla-container">
    <table class="tabla-clases">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Horario</th>
          <th>Profesor</th>
          <th>Cupo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($clases)): ?>
          <tr><td colspan="6" class="sin-datos">No hay clases registradas todavia.</td></tr>
        <?php endif; ?>

        <?php foreach ($clases as $c): $editando = ($c['id'] == $editarId); ?>
          <?php if ($editando): ?>
            <tr>
              <form method="post" action="<?= e(url('clases.php')) ?>">
                <input type="hidden" name="accion" value="actualizar" />
                <input type="hidden" name="id" value="<?= (int)$c['id'] ?>" />
                <td><?= (int)$c['id'] ?></td>
                <td><input class="edit-input" type="text" name="nombre"  value="<?= e($c['nombre']) ?>" required /></td>
                <td><input class="edit-input" type="text" name="horario" value="<?= e($c['horario']) ?>" /></td>
                <td><?= e($c['profesor']) ?></td>
                <td><input class="edit-input" type="number" name="cupo" value="<?= (int)$c['cupo'] ?>" min="0" /></td>
                <td>
                  <div class="acciones-fila">
                    <button type="submit" class="btn-guardar">&#128190; Guardar</button>
                    <a class="btn-editar" href="<?= e(url('clases.php')) ?>">Cancelar</a>
                  </div>
                </td>
              </form>
            </tr>
          <?php else: ?>
            <tr>
              <td><?= (int)$c['id'] ?></td>
              <td><?= e($c['nombre']) ?></td>
              <td><?= e($c['horario']) ?></td>
              <td><?= e($c['profesor']) ?></td>
              <td><?= (int)$c['cupo'] ?></td>
              <td>
                <div class="acciones-fila">
                  <a class="btn-editar" href="<?= e(url('clases.php?editar=' . $c['id'])) ?>">&#9999;&#65039; Editar</a>
                  <form method="post" action="<?= e(url('clases.php')) ?>"
                        onsubmit="return confirm('Eliminar esta clase?');">
                    <input type="hidden" name="accion" value="eliminar" />
                    <input type="hidden" name="id" value="<?= (int)$c['id'] ?>" />
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
