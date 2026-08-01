<?php
$pageTitle         = 'Mi Perfil';
$pageCss           = 'perfil.css';
$topbarTitulo      = 'Mi Perfil';
$searchPlaceholder = 'Buscar...';
$activeNav         = 'perfil';
$pageJs            = ['js/perfil.js'];

$partesNombre = preg_split('/\s+/', trim($usuario['nombre_completo']));
$iniciales = mb_strtoupper(mb_substr($partesNombre[0] ?? '', 0, 1));
if (isset($partesNombre[1])) {
    $iniciales .= mb_strtoupper(mb_substr($partesNombre[1], 0, 1));
}

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div><h1>Mi Perfil</h1><p>Administra tu información personal y preferencias.</p></div>
</div>

<div class="card" id="perfil-contenido">
  <div class="perfil-encabezado">
    <div class="avatar perfil-avatar"><?= htmlspecialchars($iniciales) ?></div>
    <div>
      <h2 class="perfil-nombre"><?= htmlspecialchars($usuario['nombre_completo']) ?></h2>
      <p class="perfil-meta">Socio #<?= (int) $usuario['usuario_id'] ?> &middot; Miembro desde <?= htmlspecialchars((new DateTime($usuario['fecha_registro']))->format('d M Y')) ?></p>
    </div>
  </div>
  <div class="detalle-grid">
    <div class="detalle-campo"><div class="detalle-label">Correo Electrónico</div><div class="detalle-valor" id="perfil-correo-valor"><?= htmlspecialchars($usuario['correo']) ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Teléfono</div><div class="detalle-valor" id="perfil-telefono-valor"><?= htmlspecialchars($usuario['telefono'] ?? '') ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Dirección</div><div class="detalle-valor" id="perfil-direccion-valor"><?= htmlspecialchars($usuario['direccion'] ?? '') ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Total Préstamos</div><div class="detalle-valor"><?= (int) $totalPrestamos ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Devoluciones</div><div class="detalle-valor"><?= (int) $devoluciones ?></div></div>
  </div>
  <div class="perfil-acciones">
    <button class="btn btn-primario" id="btn-editar-perfil"><i class="bi bi-pencil"></i> Editar Perfil</button>
    <button class="btn btn-outline" id="btn-cambiar-clave"><i class="bi bi-lock"></i> Cambiar Contraseña</button>
  </div>
</div>

<!-- Modal Editar Perfil -->
<div class="modal-overlay" id="modal-editar-perfil">
  <div class="modal">
    <div class="modal-header">
      <h2>Editar Perfil</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-editar-perfil"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="index.php?controller=lector&action=actualizarPerfil" id="form-perfil" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-perfil" style="display:none"></div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="perfil-nombre">Nombre Completo</label>
            <input type="text" id="perfil-nombre" name="nombre_completo" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>">
          </div>
          <div class="form-grupo">
            <label for="perfil-correo">Correo Electrónico</label>
            <input type="email" id="perfil-correo" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>">
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="perfil-telefono">Teléfono</label>
            <input type="tel" id="perfil-telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
          </div>
          <div class="form-grupo">
            <label for="perfil-direccion">Dirección</label>
            <input type="text" id="perfil-direccion" name="direccion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-editar-perfil">Cancelar</button>
        <button type="submit" class="btn btn-primario">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal-overlay" id="modal-cambiar-clave">
  <div class="modal">
    <div class="modal-header">
      <h2>Cambiar Contraseña</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-cambiar-clave"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="index.php?controller=lector&action=cambiarClave" id="form-clave" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-clave" style="display:none"></div>
        <div class="form-grupo">
          <label for="clave-actual">Contraseña Actual</label>
          <input type="password" id="clave-actual" name="clave_actual" placeholder="••••••••">
          <span class="mensaje-error" id="error-clave-actual"></span>
        </div>
        <div class="form-grupo">
          <label for="clave-nueva">Nueva Contraseña</label>
          <input type="password" id="clave-nueva" name="clave_nueva" placeholder="Mínimo 8 caracteres">
          <span class="mensaje-error" id="error-clave-nueva"></span>
        </div>
        <div class="form-grupo">
          <label for="clave-confirmar">Confirmar Nueva Contraseña</label>
          <input type="password" id="clave-confirmar" name="clave_confirmar" placeholder="Repetir contraseña">
          <span class="mensaje-error" id="error-clave-confirmar"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-cambiar-clave">Cancelar</button>
        <button type="submit" class="btn btn-primario">Actualizar Contraseña</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
