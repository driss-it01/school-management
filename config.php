<?php
session_start();

// Paramètres base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_management');
define('DB_USER', 'root');
define('DB_PASS', '');
define('BASE_URL', 'http://localhost/school-management/');

// Autoload des classes
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Fonction helper de redirection
function redirect($url) {
    header('Location: ' . BASE_URL . $url);
    exit;
}