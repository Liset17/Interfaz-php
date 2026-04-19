<?php
// =============================================================
// CABECERA COMPARTIDA (layout)
// -------------------------------------------------------------
// Se incluye al principio de las paginas logueadas (home,
// alumnos, grupos, clases, asistencia). Las paginas de auth
// (login/register) NO la usan porque tienen un layout propio.
//
// Espera en $pageTitle el titulo a mostrar en el <title>.
// =============================================================

if (!isset($user)) {
    $user = current_user();
}
$pageTitle = $pageTitle ?? 'Academia de Teatro';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= e($pageTitle) ?> - Academia de Teatro</title>
<link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>" />
<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>

<nav>
  <div class="nav-content">
    <a href="<?= e(url('home.php')) ?>" class="nav-brand">
      <img class="mask" src="<?= e(url('assets/img/mask.webp')) ?>" alt="Mascara" />
    </a>
    <div class="text-group">
      <h1>Academia de Teatro</h1>
      <p>Profesores</p>
    </div>
    <div class="nav-user">
      <?php if ($user): ?>
        <span class="hola">Hola, <?= e($user['nombre']) ?></span>
        <form method="post" action="<?= e(url('logout.php')) ?>" class="nav-logout-form">
          <button type="submit" class="btn-logout">Cerrar sesion</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</nav>

<?php foreach (flash_get() as $f): ?>
  <div class="flash flash-<?= e($f['tipo']) ?>"><?= e($f['mensaje']) ?></div>
<?php endforeach; ?>
