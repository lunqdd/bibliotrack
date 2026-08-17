<?php

require_once __DIR__ . '/../models/LibroModel.php';

class LibroController
{
    private LibroModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new LibroModel();
    }

    // GET ?controller=libro&action=index. Listado administrable del catálogo
    public function index(): void
    {
        [$busqueda, $generoId, $editorialId, $estado] = $this->filtros();

        $libros      = $this->model->getAdminListado($busqueda, $generoId, $editorialId, $estado);
        $generos     = $this->model->getGeneros();
        $editoriales = $this->model->getEditoriales();
        $stats       = $this->model->obtenerStats();

        require __DIR__ . '/../views/libros/libros.php';
    }

    // GET ?controller=libro&action=buscarLibros (AJAX). Listado filtrado en JSON
    public function buscarLibros(): void
    {
        [$busqueda, $generoId, $editorialId, $estado] = $this->filtros();

        header('Content-Type: application/json');
        echo json_encode(['libros' => $this->model->getAdminListado($busqueda, $generoId, $editorialId, $estado)]);
    }

    // GET ?controller=libro&action=edit&id= (AJAX). Datos de un libro para ver detalle o precargar edición
    public function edit(int $id): void
    {
        header('Content-Type: application/json');
        $libro = $this->model->getById($id);

        if (!$libro) {
            echo json_encode(['response' => '01', 'message' => 'Libro no encontrado.']);
            return;
        }

        echo json_encode(['response' => '00', 'libro' => $libro]);
    }

    // POST ?controller=libro&action=store (AJAX). Registra un libro nuevo con sus ejemplares iniciales
    public function store(): void
    {
        header('Content-Type: application/json');

        $data = $this->datosFormulario();

        if ($data['titulo'] === '' || $data['autor'] === '') {
            echo json_encode(['response' => '01', 'message' => 'El título y el autor son obligatorios.']);
            return;
        }

        $ejemplaresIniciales = max(1, (int) ($_POST['ejemplares'] ?? 1));

        try {
            $this->model->create($data, $ejemplaresIniciales);
        } catch (PDOException $e) {
            echo json_encode(['response' => '01', 'message' => 'No se pudo registrar el libro. Verifica que el ISBN no esté repetido.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Libro registrado.']);
    }

    // POST ?controller=libro&action=update&id= (AJAX). Actualiza los datos bibliográficos
    public function update(int $id): void
    {
        header('Content-Type: application/json');

        $data = $this->datosFormulario();

        if ($data['titulo'] === '' || $data['autor'] === '') {
            echo json_encode(['response' => '01', 'message' => 'El título y el autor son obligatorios.']);
            return;
        }

        try {
            $this->model->update($id, $data);
        } catch (PDOException $e) {
            echo json_encode(['response' => '01', 'message' => 'No se pudo actualizar el libro. Verifica que el ISBN no esté repetido.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Libro actualizado.']);
    }

    // POST ?controller=libro&action=delete&id= (AJAX). Elimina un libro sin ejemplares registrados
    public function delete(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $this->model->delete($id);
        } catch (PDOException $e) {
            echo json_encode(['response' => '01', 'message' => 'No se puede eliminar: el libro tiene ejemplares registrados en Inventario.']);
            return;
        }

        echo json_encode(['response' => '00', 'message' => 'Libro eliminado.']);
    }

    private function filtros(): array
    {
        return [
            trim($_GET['q'] ?? ''),
            isset($_GET['genero']) && $_GET['genero'] !== '' ? (int) $_GET['genero'] : null,
            isset($_GET['editorial']) && $_GET['editorial'] !== '' ? (int) $_GET['editorial'] : null,
            trim($_GET['estado'] ?? ''),
        ];
    }

    private function datosFormulario(): array
    {
        return [
            'titulo'           => trim($_POST['titulo'] ?? ''),
            'autor'            => trim($_POST['autor'] ?? ''),
            'isbn'             => trim($_POST['isbn'] ?? ''),
            'editorial_id'     => isset($_POST['editorial_id']) && $_POST['editorial_id'] !== '' ? (int) $_POST['editorial_id'] : null,
            'genero_id'        => isset($_POST['genero_id']) && $_POST['genero_id'] !== '' ? (int) $_POST['genero_id'] : null,
            'anio_publicacion' => (int) ($_POST['anio'] ?? 0) ?: null,
            'portada_url'      => trim($_POST['portada_url'] ?? ''),
        ];
    }

    private function requerirAdmin(): void
    {
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
            header('Location: index.php?controller=login&action=index');
            exit;
        }
    }
}
