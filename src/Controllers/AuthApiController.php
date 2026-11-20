<?php

class AuthApiController extends ApiController
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    public function login(): void
    {
        $input    = $this->input();
        $username = trim((string) ($input['username'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($username === '' || $password === '') {
            $this->error('Username and password are required.', 422);
        }

        $user = $this->users->findByUsername($username);
        if (!$user || !password_verify($password, $user['password'])) {
            $this->error('Wrong username or password.', 401);
        }

        $ttl   = config('jwt_ttl');
        $token = JWT::encode([
            'sub'      => (int) $user['id'],
            'username' => $user['username'],
            'role'     => $user['role'],
            'iat'      => time(),
            'exp'      => time() + $ttl,
        ], config('jwt_secret'));

        $this->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'user'       => ['id' => (int) $user['id'], 'username' => $user['username'], 'role' => $user['role']],
        ]);
    }

    public function me(): void
    {
        $user = $this->requireUser();

        $this->json(['id' => $user['sub'], 'username' => $user['username'], 'role' => $user['role']]);
    }
}
