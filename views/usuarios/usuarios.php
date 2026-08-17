<?php
$pageTitle         = 'Gestión de Usuarios';
$pageCss           = 'usuarios.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar usuario...';
$activeNav         = 'usuarios';
$pageJs            = ['js/usuarios.js'];

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Gestión de Usuarios</h1>
    <p>Administra el acceso y los permisos de los miembros de la biblioteca.</p>
  </div>
  <div class="pagina-acciones">
    <button type="button" class="btn btn-oscuro" id="btn-nuevo-usuario"><i class="bi bi-person-plus"></i> Registrar Usuario</button>
  </div>
</div>

<div class="fila-stats">
  <div class="stat-card">
    <p class="stat-label">Total Usuarios</p>
    <p class="stat-valor" id="stat-total-usuarios"><?= (int) $stats['total'] ?></p>
  </div>
  <div class="stat-card">
    <p class="stat-label">Usuarios Activos</p>
    <p class="stat-valor" id="stat-activos"><?= (int) $stats['activos'] ?></p>
    <span class="stat-trend" style="color:var(--exito)"><?= $stats['total'] > 0 ? round($stats['activos'] / $stats['total'] * 100) : 0 ?>%</span>
  </div>
  <div class="stat-card">
    <p class="stat-label">Con Préstamos</p>
    <p class="stat-valor" id="stat-con-prestamos"><?= (int) $stats['conPrestamos'] ?></p>
    <span class="stat-trend">Lectores activos</span>
  </div>
  <div class="stat-card">
    <p class="stat-label">Nuevos Este Mes</p>
    <p class="stat-valor" id="stat-nuevos"><?= (int) $stats['nuevosEsteMes'] ?></p>
  </div>
</div>

<div class="tabla-contenedor">
  <div class="tabla-filtros">
    <input type="search" id="buscar-usuario" placeholder="Buscar por nombre, correo o identificación...">
    <select id="filtro-rol">
      <option value="">Todos los Roles</option>
      <option value="admin">Administrador</option>
      <option value="lector">Lector</option>
    </select>
    <select id="filtro-estado-usuario">
      <option value="">Cualquier Estado</option>
      <option value="activo">Activo</option>
      <option value="inactivo">Inactivo</option>
    </select>
  </div>
  <div class="tabla-scroll">
    <table class="tabla">
      <thead><tr><th>Usuario</th><th>Identificación</th><th>Correo Electrónico</th><th>Rol</th><th>Estado</th></tr></thead>
      <tbody id="tabla-usuarios">
        <?php if (empty($usuarios)): ?>
          <tr><td colspan="5">No se encontraron usuarios con ese criterio.</td></tr>
        <?php endif; ?>
        <?php foreach ($usuarios as $usuario): ?>
          <tr>
            <td>
              <div class="celda-usuario">
                <div class="avatar-tabla" style="background-color:<?= htmlspecialchars($usuario['color']) ?>"><?= htmlspecialchars($usuario['iniciales']) ?></div>
                <div>
                  <span class="nombre-usuario"><?= htmlspecialchars($usuario['nombre_completo']) ?></span><br>
                  <span class="fecha-registro"><?= htmlspecialchars($usuario['fecha_registro_texto']) ?></span>
                </div>
              </div>
            </td>
            <td><?= htmlspecialchars($usuario['identificacion']) ?></td>
            <td><?= htmlspecialchars($usuario['correo']) ?></td>
            <td><span class="badge badge-oscuro"><?= $usuario['rol'] === 'admin' ? 'Administrador' : 'Lector' ?></span></td>
            <td class="acciones-celda">
              <span class="badge <?= $usuario['estado'] === 'activo' ? 'badge-exito' : 'badge-error' ?>"><i class="bi bi-circle-fill" style="font-size:0.4rem"></i> <?= $usuario['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?></span>
              <button class="btn-icono" title="Ver" data-accion="ver" data-id="<?= (int) $usuario['usuario_id'] ?>"><i class="bi bi-eye"></i></button>
              <button class="btn-icono" title="Editar" data-accion="editar" data-id="<?= (int) $usuario['usuario_id'] ?>"><i class="bi bi-pencil"></i></button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="paginacion-contenedor">
    <span id="info-paginacion-usuarios"><?= count($usuarios) ?> usuario<?= count($usuarios) === 1 ? '' : 's' ?> encontrados</span>
    <div class="paginacion" id="paginacion-usuarios"></div>
  </div>
</div>

<!-- Modal Registrar/Editar Usuario -->
<div class="modal-overlay" id="modal-usuario">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modal-usuario-titulo">Registrar Usuario</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-usuario"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body">
      <div class="mensaje-error-servidor" id="mensaje-error-usuario" style="display:none"></div>
      <p class="form-nota" id="nota-password-usuario">La contraseña temporal será el número de identificación; el usuario podrá cambiarla luego desde su perfil.</p>
      <form id="form-usuario" novalidate>
        <input type="hidden" id="usuario-db-id" value="">
        <div class="form-fila">
          <div class="form-grupo">
            <label for="usuario-nombre">Nombre Completo</label>
            <input type="text" id="usuario-nombre" placeholder="Nombre y apellidos">
          </div>
          <div class="form-grupo">
            <label for="usuario-identificacion">Identificación</label>
            <input type="text" id="usuario-identificacion" placeholder="1-2345-6789">
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="usuario-correo">Correo Electrónico</label>
            <input type="email" id="usuario-correo" placeholder="correo@ejemplo.com">
          </div>
          <div class="form-grupo">
            <label for="usuario-telefono">Teléfono</label>
            <input type="tel" id="usuario-telefono" placeholder="8888-8888">
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="usuario-rol">Rol</label>
            <select id="usuario-rol">
              <option value="lector">Lector</option>
              <option value="admin">Administrador</option>
            </select>
          </div>
          <div class="form-grupo">
            <label for="usuario-direccion">Dirección</label>
            <input type="text" id="usuario-direccion" placeholder="Dirección completa">
          </div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-cerrar-modal="modal-usuario">Cancelar</button>
      <button class="btn btn-primario" id="btn-guardar-usuario">Guardar Usuario</button>
    </div>
  </div>
</div>

<!-- Modal Detalle Usuario -->
<div class="modal-overlay" id="modal-detalle-usuario">
  <div class="modal">
    <div class="modal-header">
      <h2>Detalle del Usuario</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-detalle-usuario"><i class="bi bi-x-lg"></i></button>
    </div>
    <div class="modal-body" id="detalle-usuario-contenido"></div>
    <div class="modal-footer">
      <button class="btn btn-outline" data-cerrar-modal="modal-detalle-usuario">Cerrar</button>
    </div>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>