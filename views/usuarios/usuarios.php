<?php
$pageTitle         = 'Gestión de Usuarios';
$pageCss           = 'usuarios.css';
$topbarTitulo      = 'Sistema de Administración';
$searchPlaceholder = 'Buscar en el sistema...';
$activeNav         = 'usuarios';
$pageJs            = ['js/usuarios.js'];

function inicialesUsuario(string $nombre): string
{
    $partes = preg_split('/\s+/', trim($nombre));
    $iniciales = mb_strtoupper(mb_substr($partes[0] ?? '', 0, 1));
    if (isset($partes[1])) {
        $iniciales .= mb_strtoupper(mb_substr($partes[1], 0, 1));
    }
    return $iniciales;
}

$tarjetasUsuarios = [
    ['icono' => 'bi-people', 'label' => 'Total Usuarios', 'valor' => $stats['total']],
    ['icono' => 'bi-person-check', 'label' => 'Usuarios Activos', 'valor' => $stats['activos']],
    ['icono' => 'bi-bookmark-check', 'label' => 'Con Préstamos Activos', 'valor' => $stats['conPrestamos']],
    ['icono' => 'bi-person-plus', 'label' => 'Nuevos Este Mes', 'valor' => $stats['nuevosEsteMes']],
];

require __DIR__ . '/../layout/header.php';
?>

<div class="pagina-encabezado">
  <div>
    <h1>Gestión de Usuarios</h1>
    <p>Administra el acceso y los permisos de los miembros de la biblioteca.</p>
  </div>
  <div class="pagina-acciones">
    <button class="btn btn-oscuro" id="btn-nuevo-usuario"><i class="bi bi-person-plus"></i> Registrar Usuario</button>
  </div>
</div>

<div class="fila-stats">
  <?php foreach ($tarjetasUsuarios as $tarjeta): ?>
    <div class="stat-card">
      <div class="stat-header"><i class="bi <?= htmlspecialchars($tarjeta['icono']) ?> stat-icono"></i></div>
      <p class="stat-label"><?= htmlspecialchars($tarjeta['label']) ?></p>
      <p class="stat-valor"><?= (int) $tarjeta['valor'] ?></p>
    </div>
  <?php endforeach; ?>
</div>

<div class="tabla-contenedor">
  <div class="tabla-filtros">
    <input type="search" id="buscar-usuario" placeholder="Filtrar por nombre...">
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
  <div class="tabla-scroll"><table class="tabla">
    <thead><tr><th>Usuario</th><th>Identificación</th><th>Correo Electrónico</th><th>Rol</th><th>Estado</th></tr></thead>
    <tbody id="tabla-usuarios">
      <?php foreach ($usuarios as $u): ?>
        <tr data-nombre="<?= htmlspecialchars(mb_strtolower($u['nombre_completo'])) ?>" data-rol="<?= htmlspecialchars($u['rol']) ?>" data-estado="<?= htmlspecialchars($u['estado']) ?>">
          <td>
            <div class="celda-usuario">
              <div class="avatar-tabla"><?= htmlspecialchars(inicialesUsuario($u['nombre_completo'])) ?></div>
              <div>
                <span class="nombre-usuario"><?= htmlspecialchars($u['nombre_completo']) ?></span><br>
                <span class="fecha-registro">Registrado el <?= htmlspecialchars((new DateTime($u['fecha_registro']))->format('d M, Y')) ?></span>
              </div>
            </div>
          </td>
          <td><?= htmlspecialchars($u['identificacion']) ?></td>
          <td><?= htmlspecialchars($u['correo']) ?></td>
          <td><span class="badge badge-oscuro"><?= $u['rol'] === 'admin' ? 'Administrador' : 'Lector' ?></span></td>
          <td>
            <span class="badge <?= $u['estado'] === 'activo' ? 'badge-exito' : 'badge-error' ?>"><i class="bi bi-circle-fill" style="font-size:0.4rem"></i> <?= $u['estado'] === 'activo' ? 'Activo' : 'Inactivo' ?></span>
            <button class="btn-icono" title="Editar" data-accion="editar"
              data-id="<?= (int) $u['usuario_id'] ?>"
              data-nombre="<?= htmlspecialchars($u['nombre_completo'], ENT_QUOTES) ?>"
              data-identificacion="<?= htmlspecialchars($u['identificacion'], ENT_QUOTES) ?>"
              data-correo="<?= htmlspecialchars($u['correo'], ENT_QUOTES) ?>"
              data-telefono="<?= htmlspecialchars($u['telefono'] ?? '', ENT_QUOTES) ?>"
              data-direccion="<?= htmlspecialchars($u['direccion'] ?? '', ENT_QUOTES) ?>"
              data-rol="<?= htmlspecialchars($u['rol'], ENT_QUOTES) ?>"
              data-estado="<?= htmlspecialchars($u['estado'], ENT_QUOTES) ?>"
            ><i class="bi bi-pencil"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <div class="paginacion-contenedor">
    <span id="info-paginacion-usuarios">Mostrando <?= count($usuarios) ?> de <?= count($usuarios) ?> usuarios</span>
  </div>
</div>

<!-- Modal Registrar/Editar Usuario -->
<div class="modal-overlay" id="modal-usuario">
  <div class="modal">
    <div class="modal-header">
      <h2 id="modal-usuario-titulo">Registrar Usuario</h2>
      <button class="modal-cerrar" data-cerrar-modal="modal-usuario"><i class="bi bi-x-lg"></i></button>
    </div>
    <form id="form-usuario" novalidate>
      <div class="modal-body">
        <div class="mensaje-error-servidor" id="mensaje-error-usuario" style="display:none"></div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="usuario-nombre">Nombre Completo</label>
            <input type="text" id="usuario-nombre" name="nombre_completo" placeholder="Nombre y apellidos">
          </div>
          <div class="form-grupo">
            <label for="usuario-identificacion">Identificación</label>
            <input type="text" id="usuario-identificacion" name="identificacion" placeholder="0-0000-0000">
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="usuario-correo">Correo Electrónico</label>
            <input type="email" id="usuario-correo" name="correo" placeholder="correo@ejemplo.com">
          </div>
          <div class="form-grupo">
            <label for="usuario-telefono">Teléfono</label>
            <input type="tel" id="usuario-telefono" name="telefono" placeholder="8888-8888">
          </div>
        </div>
        <div class="form-fila">
          <div class="form-grupo">
            <label for="usuario-rol">Rol</label>
            <select id="usuario-rol" name="rol">
              <option value="lector">Lector</option>
              <option value="admin">Administrador</option>
            </select>
          </div>
          <div class="form-grupo">
            <label for="usuario-direccion">Dirección</label>
            <input type="text" id="usuario-direccion" name="direccion" placeholder="Dirección completa">
          </div>
        </div>
        <div class="form-grupo" id="grupo-usuario-estado" style="display:none">
          <label for="usuario-estado">Estado</label>
          <select id="usuario-estado" name="estado">
            <option value="activo">Activo</option>
            <option value="inactivo">Inactivo</option>
          </select>
        </div>
        <div class="form-grupo" id="grupo-usuario-password">
          <label for="usuario-password">Contraseña Temporal</label>
          <input type="password" id="usuario-password" name="password" placeholder="Mínimo 8 caracteres">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" data-cerrar-modal="modal-usuario">Cancelar</button>
        <button type="submit" class="btn btn-primario">Guardar Usuario</button>
      </div>
    </form>
  </div>
</div>

<?php require __DIR__ . '/../layout/footer.php'; ?>
