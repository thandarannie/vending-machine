<?php

class UsersController extends Controller
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function index(): void
    {
        $this->render('users/index', [
            'title' => 'Users',
            'users' => $this->users->getAll(),
        ]);
    }

    public function create(): void
    {
        $this->showForm('Add User', '/users', ['username' => '', 'role' => 'user'], [], true);
    }

    public function store(): void
    {
        $data   = $this->getInput();
        $errors = $this->validate($data, null);

        if ($errors) {
            $this->showForm('Add User', '/users', $data, $errors, true);
            return;
        }

        $this->users->create($data['username'], $data['password'], $data['role']);
        $this->flash('User added.');
        $this->redirect('/users');
    }

    public function edit(int $id): void
    {
        $user = $this->findOrFail($id);
        $this->showForm('Edit User', "/users/$id", $user, [], false);
    }

    public function update(int $id): void
    {
        $this->findOrFail($id);

        $data   = $this->getInput();
        $errors = $this->validate($data, $id);

        if ($id === Auth::id() && $data['role'] !== 'admin') {
            $errors['role'] = "You can't remove your own admin role.";
        }

        if ($errors) {
            $this->showForm('Edit User', "/users/$id", $data, $errors, false);
            return;
        }

        $this->users->update($id, $data['username'], $data['role']);

        if ($data['password'] !== '') {
            $this->users->changePassword($id, $data['password']);
        }

        if ($id === Auth::id()) {
            Auth::login($this->users->find($id));
        }

        $this->flash('User updated.');
        $this->redirect('/users');
    }

    public function delete(int $id): void
    {
        $user = $this->findOrFail($id);

        if ($id === Auth::id()) {
            $this->flash("You can't delete your own account.");
            $this->redirect('/users');
        }

        try {
            $this->users->delete($id);
            $this->flash('User deleted.');
        } catch (PDOException $e) {
            $this->flash("Can't delete {$user['username']}: they have purchase history.");
        }

        $this->redirect('/users');
    }

    private function getInput(): array
    {
        return [
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'role'     => $_POST['role'] ?? '',
        ];
    }

    private function validate(array $data, ?int $id): array
    {
        $errors = [];
        $isNew  = $id === null;

        if ($data['username'] === '') {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($data['username']) > 50) {
            $errors['username'] = 'Username must be 50 characters or fewer.';
        } else {
            $existing = $this->users->findByUsername($data['username']);
            if ($existing && (int) $existing['id'] !== $id) {
                $errors['username'] = 'This username is already taken.';
            }
        }

        if ($isNew && $data['password'] === '') {
            $errors['password'] = 'Password is required.';
        } elseif ($data['password'] !== '' && strlen($data['password']) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if (!in_array($data['role'], ['admin', 'user'], true)) {
            $errors['role'] = 'Choose a role.';
        }

        return $errors;
    }

    private function findOrFail(int $id): array
    {
        $user = $this->users->find($id);

        if (!$user) {
            $this->notFound('User not found.');
        }

        return $user;
    }

    private function showForm(string $title, string $action, array $user, array $errors, bool $isNew): void
    {
        $this->render('users/form', compact('title', 'action', 'user', 'errors', 'isNew'));
    }
}
