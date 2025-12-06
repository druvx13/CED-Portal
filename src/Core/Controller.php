<?php
namespace Core;

class Controller {
    protected function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../Views/' . $view . '.php';
    }

    protected function redirect($path) {
        if (strpos($path, 'http') !== 0) {
            $path = rtrim(BASE_URL, '/') . $path;
        }
        header('Location: ' . $path);
        exit;
    }
}
