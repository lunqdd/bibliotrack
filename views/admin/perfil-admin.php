<?php
$pageTitle         = 'Mi Perfil';
$pageCss           = 'perfil-admin.css';
$topbarTitulo      = 'Perfil Administrativo';
$searchPlaceholder = 'Buscar en el sistema...';
$activeNav         = 'perfil-admin';
$pageJs            = ['js/perfil-admin.js'];

$partesNombre = preg_split('/\s+/', trim($usuario['nombre_completo']));
$iniciales = mb_strtoupper(mb_substr($partesNombre[0] ?? '', 0, 1));
if (isset($partesNombre[1])) {
    $iniciales .= mb_strtoupper(mb_substr($partesNombre[1], 0, 1));
}

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div><h1>Mi Perfil</h1><p>Información de la cuenta administrativa activa.</p></div>
</div>

<div class="card" id="perfil-admin-contenido">
  <div class="perfil-encabezado">
    <div class="avatar perfil-avatar"><?= htmlspecialchars($iniciales) ?></div>
    <div>
      <h2 class="perfil-nombre"><?= htmlspecialchars($usuario['nombre_completo']) ?></h2>
      <p class="perfil-meta"><?= htmlspecialchars($usuario['correo']) ?></p>
      <span class="badge badge-oscuro perfil-admin-badge">Administradora</span>
    </div>
  </div>
  <div class="detalle-grid">
    <div class="detalle-campo"><div class="detalle-label">Correo Electrónico</div><div class="detalle-valor" id="perfil-admin-correo-valor"><?= htmlspecialchars($usuario['correo']) ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Teléfono</div><div class="detalle-valor" id="perfil-admin-telefono-valor"><?= htmlspecialchars($usuario['telefono'] ?? '') ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Dirección</div><div class="detalle-valor" id="perfil-admin-direccion-valor"><?= htmlspecialchars($usuario['direccion'] ?? '') ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Último acceso</div><div class="detalle-valor"><?= $usuario['ultimo_acceso'] ? htmlspecialchars((new DateTime($usuario['ultimo_acceso']))->format('d M Y')) : 'N/D' ?></div></div>
    <div class="detalle-campo"><div class="detalle-label">Permisos</div><div class="detalle-valor">Libros, usuarios, préstamos, inventario y reportes</div></div>
  </div>
  <div class="perfil-acciones">
    <button class="btn btn-primario" id="btn-editar-perfil-admin"><i class="bi bi-pencil"></i> Editar Perfil</button>
    <button class="btn btn-outline" id="btn-cambiar-clave-admin"><i class="bi bi-lock"></i> Cambiar Contraseña</button>
  </div>
</div>

<!-- Modal Editar Perfil -->
<div class="modal-overlay" id="modal-editar-perfil-admin">
  <div class="modal">
    <div class="modal-header">
      <h2>Editar Perfil</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-editar-perfil-admin"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="index.php?controller=dashboard&action=actualizarPerfil" id="form-perfil-admin" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-perfil-admin" style="display:none"></div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="perfil-admin-nombre">Nombre Completo</label>
            <input type="text" id="perfil-admin-nombre" name="nombre_completo" value="<?= htmlspecialchars($usuario['nombre_completo']) ?>">
          </div>
          <div class="form-grupo">
            <label for="perfil-admin-correo">Correo Electrónico</label>
            <input type="email" id="perfil-admin-correo" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>">
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="perfil-admin-telefono">Teléfono</label>
            <input type="tel" id="perfil-admin-telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>">
          </div>
          <div class="form-grupo">
            <label for="perfil-admin-direccion">Dirección</label>
            <input type="text" id="perfil-admin-direccion" name="direccion" value="<?= htmlspecialchars($usuario['direccion'] ?? '') ?>">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-editar-perfil-admin">Cancelar</button>
        <button type="submit" class="btn btn-primario">Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Cambiar Contraseña -->
<div class="modal-overlay" id="modal-cambiar-clave-admin">
  <div class="modal">
    <div class="modal-header">
      <h2>Cambiar Contraseña</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-cambiar-clave-admin"><i class="bi bi-x-lg"></i></button>
    </div>
    <form method="POST" action="index.php?controller=dashboard&action=cambiarClave" id="form-clave-admin" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-clave-admin" style="display:none"></div>
        <div class="form-grupo">
          <label for="clave-admin-actual">Contraseña Actual</label>
          <input type="password" id="clave-admin-actual" name="clave_actual" placeholder="••••••••">
          <span class="mensaje-error" id="error-clave-admin-actual"></span>
        </div>
        <div class="form-grupo">
          <label for="clave-admin-nueva">Nueva Contraseña</label>
          <input type="password" id="clave-admin-nueva" name="clave_nueva" placeholder="Mínimo 8 caracteres">
          <span class="mensaje-error" id="error-clave-admin-nueva"></span>
        </div>
        <div class="form-grupo">
          <label for="clave-admin-confirmar">Confirmar Nueva Contraseña</label>
          <input type="password" id="clave-admin-confirmar" name="clave_confirmar" placeholder="Repetir contraseña">
          <span class="mensaje-error" id="error-clave-admin-confirmar"></span>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-cambiar-clave-admin">Cancelar</button>
        <button type="submit" class="btn btn-primario">Actualizar Contraseña</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
