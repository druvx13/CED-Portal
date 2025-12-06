<?php

namespace App\Core;

use App\Config\Config;

class View {
    public static function render(string $view, array $data = []): void {
        extract($data);

        // Define BASE_URL for views
        if (!defined('BASE_URL')) {
            define('BASE_URL', Config::get('BASE_URL', ''));
        }

        $viewFile = __DIR__ . '/../../templates/pages/' . $view . '.php';

        if (file_exists($viewFile)) {
            // Buffer the output
            ob_start();
            require $viewFile;
            $content = ob_get_clean();

            // Render the layout
            require __DIR__ . '/../../templates/layout/main.php';
        } else {
            throw new \Exception("View $view not found.");
        }
    }

    public static function renderPartial(string $view, array $data = []): void {
         extract($data);
         if (!defined('BASE_URL')) {
            define('BASE_URL', Config::get('BASE_URL', ''));
        }
        $viewFile = __DIR__ . '/../../templates/' . $view . '.php';
        if (file_exists($viewFile)) {
            require $viewFile;
        }
    }
}
