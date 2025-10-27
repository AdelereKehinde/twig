<?php
namespace App\Controllers;

class TicketController {
    private $twig;
    
    public function __construct($twig) {
        $this->twig = $twig;
    }
    
    public function index() {
        $this->requireAuth();
        $tickets = $_SESSION['tickets'] ?? $this->getSampleTickets();
        
        echo $this->twig->render('tickets/index.twig', [
            'tickets' => $tickets
        ]);
    }
    
    public function create() {
        $this->requireAuth();
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? '';
            $priority = $_POST['priority'] ?? 'normal';
            
            // Validation
            if (empty($title)) $errors['title'] = 'Title is required';
            if (empty($status)) $errors['status'] = 'Status is required';
            if (!in_array($status, ['open', 'in_progress', 'closed'])) {
                $errors['status'] = 'Invalid status';
            }
            
            if (empty($errors)) {
                $ticket = [
                    'id' => uniqid(),
                    'title' => $title,
                    'description' => $description,
                    'status' => $status,
                    'priority' => $priority,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $_SESSION['user']['id']
                ];
                
                $_SESSION['tickets'][] = $ticket;
                header('Location: /tickets');
                exit;
            }
        }
        
        echo $this->twig->render('tickets/create.twig', [
            'errors' => $errors,
            'form_data' => $_POST
        ]);
    }
    
    public function edit($id) {
        $this->requireAuth();
        $tickets = $_SESSION['tickets'] ?? [];
        $ticket = $this->findTicket($id, $tickets);
        
        if (!$ticket) {
            header('Location: /tickets');
            exit;
        }
        
        $errors = [];
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';
            $status = $_POST['status'] ?? '';
            $priority = $_POST['priority'] ?? 'normal';
            
            // Validation
            if (empty($title)) $errors['title'] = 'Title is required';
            if (empty($status)) $errors['status'] = 'Status is required';
            if (!in_array($status, ['open', 'in_progress', 'closed'])) {
                $errors['status'] = 'Invalid status';
            }
            
            if (empty($errors)) {
                foreach ($_SESSION['tickets'] as &$t) {
                    if ($t['id'] === $id) {
                        $t['title'] = $title;
                        $t['description'] = $description;
                        $t['status'] = $status;
                        $t['priority'] = $priority;
                        break;
                    }
                }
                
                header('Location: /tickets');
                exit;
            }
        }
        
        echo $this->twig->render('tickets/edit.twig', [
            'ticket' => $ticket,
            'errors' => $errors
        ]);
    }
    
    public function delete($id) {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_SESSION['tickets'] = array_filter($_SESSION['tickets'] ?? [], function($ticket) use ($id) {
                return $ticket['id'] !== $id;
            });
        }
        
        header('Location: /tickets');
        exit;
    }
    
    private function requireAuth() {
        if (!isset($_SESSION['ticketapp_session']) || !$_SESSION['ticketapp_session']) {
            header('Location: /auth/login');
            exit;
        }
    }
    
    private function findTicket($id, $tickets) {
        foreach ($tickets as $ticket) {
            if ($ticket['id'] === $id) {
                return $ticket;
            }
        }
        return null;
    }
    
    private function getSampleTickets() {
        return [
            [
                'id' => '1',
                'title' => 'Website Login Issue',
                'description' => 'Users unable to login to the website',
                'status' => 'open',
                'priority' => 'high',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ],
            [
                'id' => '2',
                'title' => 'Mobile App Crash',
                'description' => 'App crashes on iOS when opening settings',
                'status' => 'in_progress',
                'priority' => 'high',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
            ]
        ];
    }
}