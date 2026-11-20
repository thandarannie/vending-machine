<?php

class AuthController extends Controller
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/products');
        }

        $this->render('auth/login', ['title' => 'Log in', 'username' => '', 'errors' => []]);
    }

    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];
        if ($username === '') {
            $errors['username'] = 'Username is required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if (!$errors) {
            $user = $this->users->findByUsername($username);

            if (!$user || !password_verify($password, $user['password'])) {

                $errors['password'] = 'Wrong username or password.';
            }
        }

        if ($errors) {
            $this->render('auth/login', ['title' => 'Log in', 'username' => $username, 'errors' => $errors]);
            return;
        }

        Auth::login($user);
        $this->flash("Welcome, {$user['username']}!");
        $this->redirect('/products');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->flash('You have been logged out.');
        $this->redirect('/login');
    }
}
