<?php

namespace App\Core;

class View {
    public function render(string $viewPath, array $data = [], string $title = 'CED Portal'): void {
        extract($data);

        // Define BASE_URL for views if not defined (though it should be in env)
        if (!defined('BASE_URL')) {
            define('BASE_URL', getenv('BASE_URL') ?: '');
        }

        $contentView = __DIR__ . '/../../templates/pages/' . $viewPath . '.php';

        require_once __DIR__ . '/../../templates/layout/header.php';

        if (file_exists($contentView)) {
            require_once $contentView;
        } else {
            echo "<p>View file not found: " . htmlspecialchars($viewPath) . "</p>";
        }

        require_once __DIR__ . '/../../templates/layout/footer.php';
    }

    public static function h(string $v): string {
        return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
