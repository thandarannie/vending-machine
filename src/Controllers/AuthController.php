<?php

class AuthController extends Controller
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    // show the login form
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/products'); // already logged in
        }

        $this->render('auth/login', ['title' => 'Log in', 'username' => '', 'errors' => []]);
    }

    // check username + password
    public function login(): void
    {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // 1. both fields required
        $errors = [];
        if ($username === '') {
            $errors['username'] = 'Username is required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        // 2. find the user and compare the password with the saved hash
        if (!$errors) {
            $user = $this->users->findByUsername($username);

            if (!$user || !password_verify($password, $user['password'])) {
                // same message for both cases, so nobody can guess which usernames exist
                $errors['password'] = 'Wrong username or password.';
            }
        }

        if ($errors) {
            $this->render('auth/login', ['title' => 'Log in', 'username' => $username, 'errors' => $errors]);
            return;
        }

        // 3. correct: save the user in the session
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
