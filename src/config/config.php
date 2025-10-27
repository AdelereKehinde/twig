<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session configuration
session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'] ?? 'localhost',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Application configuration
define('APP_NAME', 'TicketMaster');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));

// Twig configuration
$loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../views');
$twig = new \Twig\Environment($loader, [
    'cache' => false, // Set to 'cache' directory in production
    'debug' => true,
]);

// Add this to your config after session_start()
$current_route = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Add global variables to Twig
$twig->addGlobal('app_name', APP_NAME);
$twig->addGlobal('base_url', BASE_URL);
$twig->addGlobal('is_authenticated', isset($_SESSION['ticketapp_session']) && $_SESSION['ticketapp_session']);
$twig->addGlobal('current_user', $_SESSION['user'] ?? null);
$twig->addGlobal('current_route', $current_route); // ADD THIS LINE

// Add custom filters/functions
$twig->addFilter(new \Twig\TwigFilter('format_date', function($date) {
    return date('M j, Y', strtotime($date));
}));

return [
    'twig' => $twig,
    'db' => null // We'll use sessions for data storage
];