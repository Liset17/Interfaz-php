<?php
// =============================================================
// home.php
// -------------------------------------------------------------
// Panel principal tras iniciar sesion. Cuatro tarjetas que
// llevan a los CRUD: alumnos, asistencia, clases, grupos.
// =============================================================

require __DIR__ . '/includes/auth.php';

$user      = require_login();
$pageTitle = 'Panel principal';

require __DIR__ . '/includes/header.php';
?>

<main>
  <h1>Selecciona una seccion</h1>

  <div class="grid">
    <a class="card" href="<?= e(url('alumnos.php')) ?>">
      <i class="fas fa-users"></i>
      <h2>Alumnos</h2>
      <p>Gestion de alumnos</p>
      <span class="btn">Acceder</span>
    </a>

    <a class="card" href="<?= e(url('asistencia.php')) ?>">
      <i class="fas fa-calendar-check"></i>
      <h2>Asistencia</h2>
      <p>Control de asistencia</p>
      <span class="btn">Acceder</span>
    </a>

    <a class="card" href="<?= e(url('clases.php')) ?>">
      <i class="fas fa-chalkboard"></i>
      <h2>Clases</h2>
      <p>Gestion de clases</p>
      <span class="btn">Acceder</span>
    </a>

    <a class="card" href="<?= e(url('grupos.php')) ?>">
      <i class="fas fa-layer-group"></i>
      <h2>Grupos</h2>
      <p>Organizacion de grupos</p>
      <span class="btn">Acceder</span>
    </a>
  </div>
</main>

<?php require __DIR__ . '/includes/footer.php'; ?>
