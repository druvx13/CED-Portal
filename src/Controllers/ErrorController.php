<?php
namespace Controllers;

use Core\Controller;

class ErrorController extends Controller {
    public function notFound() {
        $this->view('layout/header', ['title' => 'Not Found']);
        $this->view('404');
        $this->view('layout/footer');
    }
}
