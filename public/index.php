<?php
require_once __DIR__ . '/../vendor/autoload.php';
$config = require_once __DIR__ . '/../src/config/config.php';

// Import controllers
use App\Controllers\AuthController;
use App\Controllers\TicketController;
use App\Controllers\PageController;

// Initialize controllers
$authController = new AuthController($config['twig']);
$ticketController = new TicketController($config['twig']);
$pageController = new PageController($config['twig']);

// Route definitions
$routes = [
    'GET' => [
        '/' => fn() => $pageController->home(),
        '/auth/login' => fn() => $authController->login(),
        '/auth/signup' => fn() => $authController->signup(), // ADD THIS LINE
        '/auth/logout' => fn() => $authController->logout(),
        '/dashboard' => fn() => $pageController->dashboard(),
        '/tickets' => fn() => $ticketController->index(),
        '/tickets/create' => fn() => $ticketController->create(),
        '/tickets/edit' => function() use ($ticketController) {
            $id = $_GET['id'] ?? '';
            $ticketController->edit($id);
        }
    ],
    'POST' => [
        '/auth/login' => fn() => $authController->login(),
        '/auth/signup' => fn() => $authController->signup(), // ADD THIS LINE
        '/tickets/create' => fn() => $ticketController->create(),
        '/tickets/edit' => function() use ($ticketController) {
            $id = $_GET['id'] ?? '';
            $ticketController->edit($id);
        },
        '/tickets/delete' => function() use ($ticketController) {
            $id = $_GET['id'] ?? '';
            $ticketController->delete($id);
        }
    ]
];

// Execute route
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

if (isset($routes[$method][$path])) {
    $routes[$method][$path]();
} else {
    // 404 Not Found
    http_response_code(404);
    echo $config['twig']->render('errors/404.twig');
}