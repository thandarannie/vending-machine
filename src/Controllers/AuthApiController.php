<?php

class AuthApiController extends ApiController
{
    private UserRepository $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    // POST /api/login   body: {"username": "admin", "password": "admin123"}
    // gives back a token to send with the next requests
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
            'sub'      => (int) $user['id'],  // "subject" = who this token belongs to
            'username' => $user['username'],
            'role'     => $user['role'],
            'iat'      => time(),             // issued at
            'exp'      => time() + $ttl,      // expires at
        ], config('jwt_secret'));

        $this->json([
            'token'      => $token,
            'token_type' => 'Bearer',
            'expires_in' => $ttl,
            'user'       => ['id' => (int) $user['id'], 'username' => $user['username'], 'role' => $user['role']],
        ]);
    }

    // GET /api/me   -> who am I? (needs a token)
    public function me(): void
    {
        $user = $this->requireUser();

        $this->json(['id' => $user['sub'], 'username' => $user['username'], 'role' => $user['role']]);
    }
}
