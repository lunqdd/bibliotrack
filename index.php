<?php

session_start();

require_once 'config/database.php';

$controllerName = $_GET['controller'] ?? 'inicio';
$action         = $_GET['action'] ?? 'index';
$method         = $_SERVER['REQUEST_METHOD'];
$id             = isset($_GET['id']) ? (int) $_GET['id'] : null;

$controllerFile = __DIR__ . '/controllers/' . ucfirst($controllerName) . 'Controller.php';

if (!file_exists($controllerFile)) {
    http_response_code(404);
    die('<h2>404 - Controlador no encontrado.</h2>');
}



require_once $controllerFile;

$controllerClass = ucfirst($controllerName) . 'Controller';
$controller      = new $controllerClass();

switch ($action) {

    case 'index':
        $controller->index();
        break;

    case 'create':
        if ($method === 'GET') {
            $controller->create();
        }
        break;

    case 'store':
        if ($method === 'POST') {
            $controller->store();
        }
        break;

    case 'edit':
        if ($method === 'GET' && $id) {
            $controller->edit($id);
        }
        break;

    case 'update':
        if ($method === 'POST' && $id) {
            $controller->update($id);
        }
        break;

    case 'delete':
        if ($method === 'POST' && $id) {
            $controller->delete($id);
        }
        break;

    case 'login':
        if ($method === 'POST') {
            $controller->login();
        }
        break;

    case 'logout':
        $controller->logout();
        break;

    case 'explorar':
        // GET: catálogo de libros del lector
        if ($method === 'GET') {
            $controller->explorar();
        }
        break;

    case 'misPrestamos':
        // GET: préstamos activos e historial del lector
        if ($method === 'GET') {
            $controller->misPrestamos();
        }
        break;

    case 'perfil':
        // GET: perfil del lector
        if ($method === 'GET') {
            $controller->perfil();
        }
        break;

    case 'solicitar':
        // POST: solicitud de préstamo del lector
        if ($method === 'POST') {
            $controller->solicitar();
        }
        break;

    case 'actualizarPerfil':
        // POST: edición de datos personales del lector
        if ($method === 'POST') {
            $controller->actualizarPerfil();
        }
        break;

    case 'cambiarClave':
        // POST: cambio de contraseña del lector
        if ($method === 'POST') {
            $controller->cambiarClave();
        }
        break;

    case 'buscarCatalogo':
        // GET (AJAX): catálogo filtrado en JSON, sin recargar la página
        if ($method === 'GET') {
            $controller->buscarCatalogo();
        }
        break;

    case 'actividadReciente':
        // GET (AJAX): actividad reciente del dashboard en JSON
        if ($method === 'GET') {
            $controller->actividadJson();
        }
        break;

    case 'statsInicio':
        // GET (AJAX): totales públicos para la portada
        if ($method === 'GET') {
            $controller->statsJson();
        }
        break;

    default:
        http_response_code(404);
        die('<h2>404 - Acción no encontrada.</h2>');
}