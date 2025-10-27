<?php
namespace App\Controllers;

class PageController {
    private $twig;
    
    public function __construct($twig) {
        $this->twig = $twig;
    }
    
    public function home() {
        echo $this->twig->render('home.twig');
    }
    
    public function dashboard() {
    $this->requireAuth();
    
    $tickets = $_SESSION['tickets'] ?? [];
    $stats = [
        'total' => count($tickets),
        'open' => count(array_filter($tickets, fn($t) => $t['status'] === 'open')),
        'in_progress' => count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress')),
        'closed' => count(array_filter($tickets, fn($t) => $t['status'] === 'closed'))
    ];
    
    echo $this->twig->render('dashboard.twig', [
        'stats' => $stats,
        'tickets' => $tickets
    ]);
}

private function requireAuth() {
    if (!isset($_SESSION['ticketapp_session']) || empty($_SESSION['ticketapp_session'])) {
        header('Location: /auth/login');
        exit;
    }
}
};