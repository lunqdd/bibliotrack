<?php

require_once __DIR__ . '/../models/LibroModel.php';

class LibrosController
{
    private LibroModel $model;

    public function __construct()
    {
        $this->requerirAdmin();
        $this->model = new LibroModel();
    }

    // GET ?controller=libros&action=index. Catálogo con existencias y filtros
    public function index(): void
    {
        $libros = $this->model->getAllAdmin();

        $totalEjemplares = array_sum(array_column($libros, 'ejemplares'));
        $disponibles     = array_sum(array_column($libros, 'disponibles'));
        $enReparacion    = 0;
        foreach ($libros as $libro) {
            $enReparacion += max(0, $libro['ejemplares'] - $libro['disponibles']);
        }

        $stats = [
            'titulos'          => count($libros),
            'totalEjemplares'  => $totalEjemplares,
            'disponibles'      => $disponibles,
        ];

        $generos     = $this->model->getGeneros();
        $editoriales = $this->model->getEditoriales();
        $autores     = $this->model->getAutores();

        require __DIR__ . '/../views/libros/libros.php';
    }

    // POST ?controller=libros&action=store (AJAX). Registro de un libro nuevo con sus primeros ejemplares
    public function store(): void
    {
        header('Content-Type: application/json');

        $data = $this->datosDelFormulario();
        if ($data === null) {
            echo json_encode(['response' => '01', 'message' => 'Título y autor son obligatorios.']);
            return;
        }

        $ejemplares = max(1, (int) ($_POST['ejemplares'] ?? 1));
        $this->model->create($data, $ejemplares);

        echo json_encode(['response' => '00', 'message' => 'Libro registrado.']);
    }

    // POST ?controller=libros&action=update&id=X (AJAX). Edita los datos bibliográficos, sin tocar ejemplares
    public function update(int $id): void
    {
        header('Content-Type: application/json');

        $data = $this->datosDelFormulario();
        if ($data === null) {
            echo json_encode(['response' => '01', 'message' => 'Título y autor son obligatorios.']);
            return;
        }

        $this->model->update($id, $data);

        echo json_encode(['response' => '00', 'message' => 'Libro actualizado.']);
    }

    // POST ?controller=libros&action=delete&id=X (AJAX). Falla si el libro tiene préstamos asociados
    public function delete(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $this->model->delete($id);
            echo json_encode(['response' => '00', 'message' => 'Libro eliminado.']);
        } catch (PDOException $e) {
            echo json_encode(['response' => '01', 'message' => 'No se puede eliminar: el libro tiene préstamos registrados.']);
        }
    }

    private function datosDelFormulario(): ?array
    {
        $titulo = trim($_POST['titulo'] ?? '');
        $autor  = trim($_POST['autor'] ?? '');

        if (empty($titulo) || empty($autor)) {
            return null;
        }

        return [
            'titulo'      => $titulo,
            'autor'       => $autor,
            'isbn'        => trim($_POST['isbn'] ?? ''),
            'editorial'   => trim($_POST['editorial'] ?? ''),
            'genero'      => trim($_POST['genero'] ?? ''),
            'anio'        => (int) ($_POST['anio'] ?? 0),
            'portada_url' => trim($_POST['portada_url'] ?? ''),
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
