<?php
// Vistas que incluyen este layout deben definir antes:
// $pageTitle, $pageCss, $topbarTitulo y $activeNav.
// Opcional: $searchPlaceholder.

$usuarioSesion   = $_SESSION['usuario'] ?? null;
$rolSesion       = $usuarioSesion['rol'] ?? 'lector';
$nombreCompleto  = $usuarioSesion['nombre_completo'] ?? 'Invitado';
$rolLabel        = $rolSesion === 'admin' ? 'Administradora' : 'Lector';
$searchPlaceholder = $searchPlaceholder ?? 'Buscar...';

$partesNombre = preg_split('/\s+/', trim($nombreCompleto));
$iniciales = mb_strtoupper(mb_substr($partesNombre[0] ?? '', 0, 1));
if (isset($partesNombre[1])) {
    $iniciales .= mb_strtoupper(mb_substr($partesNombre[1], 0, 1));
}

$navAdmin = [
    ['key' => 'dashboard',    'href' => 'index.php?controller=dashboard&action=index', 'icon' => 'bi-grid-1x2',        'label' => 'Panel Principal'],
    ['key' => 'libros',       'href' => 'index.php?controller=libro&action=index',     'icon' => 'bi-book',            'label' => 'Gestión de Libros'],
    ['key' => 'usuarios',     'href' => 'index.php?controller=usuario&action=index',                               'icon' => 'bi-people',          'label' => 'Gestión de Usuarios'],
    ['key' => 'prestamos',    'href' => 'prestamos.html',                              'icon' => 'bi-arrow-left-right','label' => 'Préstamos y Devoluciones'],
    ['key' => 'inventario',   'href' => 'index.php?controller=inventario&action=index','icon' => 'bi-box-seam',        'label' => 'Inventario'],
    ['key' => 'reportes',     'href' => 'reportes.html',                               'icon' => 'bi-bar-chart-line',  'label' => 'Reportes y Estadísticas'],
    ['key' => 'perfil-admin', 'href' => 'index.php?controller=perfiladmin&action=index',  'icon' => 'bi-person', 'label' => 'Mi Perfil'],
];

$navLector = [
    ['key' => 'lector',       'href' => 'index.php?controller=lector&action=index',       'icon' => 'bi-grid-1x2',   'label' => 'Panel del Lector'],
    ['key' => 'explorar',     'href' => 'index.php?controller=lector&action=explorar',    'icon' => 'bi-book',       'label' => 'Explorar Libros'],
    ['key' => 'misPrestamos', 'href' => 'index.php?controller=lector&action=misPrestamos','icon' => 'bi-journal-text','label' => 'Mis Préstamos'],
    ['key' => 'perfil',       'href' => 'index.php?controller=lector&action=perfil',      'icon' => 'bi-person',     'label' => 'Mi Perfil'],
];

$navLinks   = $rolSesion === 'admin' ? $navAdmin : $navLector;
$perfilHref = $rolSesion === 'admin' ? 'perfil-admin.html' : 'index.php?controller=lector&action=perfil';
?>
<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | BiblioTrack</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/<?= htmlspecialchars($pageCss) ?>">
    <script src="https://code.jquery.com/jquery-4.0.0.min.js"></script>
  </head>
  <body>

    <aside class="sidebar" id="menu-principal">
      <div class="sidebar-logo">
        <img src="img/logo-blanco.png" alt="BiblioTrack">
      </div>
      <nav class="sidebar-nav">
        <?php foreach ($navLinks as $link): ?>
          <a href="<?= htmlspecialchars($link['href']) ?>" class="<?= $link['key'] === $activeNav ? 'activo' : '' ?>">
            <i class="bi <?= htmlspecialchars($link['icon']) ?>"></i> <?= htmlspecialchars($link['label']) ?>
          </a>
        <?php endforeach; ?>
      </nav>
      <div class="sidebar-usuario">
        <div class="avatar-sm"><?= htmlspecialchars($iniciales) ?></div>
        <div class="detalle">
          <?= htmlspecialchars($nombreCompleto) ?>
          <span><?= htmlspecialchars($rolLabel) ?></span>
        </div>
      </div>
    </aside>

    <div class="sidebar-overlay"></div>

    <header class="topbar">
      <button class="btn-menu-hamburguesa" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu-principal"><i class="bi bi-list"></i></button>
      <span class="topbar-titulo"><?= htmlspecialchars($topbarTitulo) ?></span>
      <div class="topbar-busqueda">
        <i class="bi bi-search"></i>
        <input type="search" placeholder="<?= htmlspecialchars($searchPlaceholder) ?>">
      </div>
      <div class="topbar-acciones">
        <div class="topbar-usuario" tabindex="0">
          <div class="info-usuario">
            <strong><?= htmlspecialchars($nombreCompleto) ?></strong>
            <span><?= htmlspecialchars($rolLabel) ?></span>
          </div>
          <div class="avatar"><?= htmlspecialchars($iniciales) ?></div>
          <div class="menu-usuario">
            <a href="<?= htmlspecialchars($perfilHref) ?>"><i class="bi bi-person"></i> Ver perfil</a>
            <a href="index.php?controller=login&action=logout" id="btn-logout"><i class="bi bi-arrow-return-left"></i> Cerrar sesión</a>
          </div>
        </div>
      </div>
    </header>

    <main class="contenido-principal">