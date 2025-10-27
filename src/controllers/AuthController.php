<?php
namespace App\Controllers;

use Twig\Environment;

class AuthController {
    private Environment $twig;

    public function __construct(Environment $twig) {
        $this->twig = $twig;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login() {
        $error = '';
        $form_data = $_POST ?? [];

        // If user already logged in, skip to dashboard
        if (!empty($_SESSION['ticketapp_session'])) {
            echo $this->twig->render('dashboard.twig', [
                'user' => $_SESSION['user'],
                'tickets' => $_SESSION['tickets'] ?? [],
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($email === '' || $password === '') {
                $error = 'Email and password are required.';
            } else {
                // Simulated login success
                $_SESSION['user'] = [
                    'id' => uniqid(),
                    'email' => $email,
                    'name' => explode('@', $email)[0],
                ];
                $_SESSION['ticketapp_session'] = true;
                $_SESSION['tickets'] = $this->getSampleTickets();

                // Redirect to dashboard
                header('Location: /dashboard');
                exit;
            }
        }

        echo $this->twig->render('auth/login.twig', [
            'error' => $error,
            'form_data' => $form_data,
        ]);
    }

    public function signup() {
        $error = '';
        $errors = [];
        $form_data = $_POST ?? [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Validation
            if ($name === '') {
                $errors['name'] = 'Full name is required';
            } elseif (strlen($name) < 2) {
                $errors['name'] = 'Name must be at least 2 characters';
            }

            if ($email === '') {
                $errors['email'] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Enter a valid email address';
            }

            if ($password === '') {
                $errors['password'] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors['password'] = 'Password must be at least 6 characters';
            }

            if ($password !== $confirm_password) {
                $errors['confirm_password'] = 'Passwords do not match';
            }

            // If no validation errors
            if (empty($errors)) {
                $_SESSION['user'] = [
                    'id' => uniqid(),
                    'name' => $name,
                    'email' => $email,
                ];
                $_SESSION['ticketapp_session'] = true;
                $_SESSION['tickets'] = $this->getSampleTickets();

                header('Location: /dashboard');
                exit;
            } else {
                $error = 'Please fix the errors below.';
            }
        }

        echo $this->twig->render('auth/signup.twig', [
            'error' => $error,
            'errors' => $errors,
            'form_data' => $form_data,
        ]);
    }

    public function dashboard() {
        if (empty($_SESSION['ticketapp_session'])) {
            header('Location: /auth/login');
            exit;
        }

        echo $this->twig->render('dashboard.twig', [
            'user' => $_SESSION['user'],
            'tickets' => $_SESSION['tickets'] ?? [],
        ]);
    }

    public function logout() {
        session_destroy();
        header('Location: /auth/login');
        exit;
    }

    private function getSampleTickets(): array {
        return [
            [
                'id' => '1',
                'title' => 'Welcome to TicketMaster! 🎉',
                'description' => 'This is your first ticket. You can edit, delete, or create new tickets to manage your support requests.',
                'status' => 'open',
                'priority' => 'normal',
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $_SESSION['user']['id'] ?? 'guest',
            ],
            [
                'id' => '2',
                'title' => 'Learn how to use the dashboard',
                'description' => 'Check out the dashboard to see your ticket statistics and quick actions.',
                'status' => 'in_progress',
                'priority' => 'low',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 hour')),
                'created_by' => $_SESSION['user']['id'] ?? 'guest',
            ],
        ];
    }
}
