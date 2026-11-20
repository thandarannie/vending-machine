<?php
/** @var string $username */
/** @var array $errors */
?>
<div class="card narrow">
    <h1>Log in</h1>

    <form id="login-form" method="post" action="<?= url('/login') ?>" novalidate>
        <div class="field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required autofocus
                   value="<?= htmlspecialchars($username) ?>">
            <div class="error" id="username-error"><?= $errors['username'] ?? '' ?></div>
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" name="password" type="password" required>
            <div class="error" id="password-error"><?= $errors['password'] ?? '' ?></div>
        </div>

        <button class="btn" type="submit">Log in</button>
    </form>

    <p class="muted">Demo: <code>admin / admin123</code> or <code>user / user123</code></p>
</div>

<script>
    document.getElementById('login-form').addEventListener('submit', function (event) {
        var ok = true;

        ['username', 'password'].forEach(function (name) {
            var input = document.getElementById(name);
            var error = document.getElementById(name + '-error');
            var empty = input.value.trim() === '';
            var label = name.charAt(0).toUpperCase() + name.slice(1);

            error.textContent = empty ? label + ' is required.' : '';
            input.classList.toggle('input-error', empty);
            if (empty) ok = false;
        });

        if (!ok) {
            event.preventDefault();
        }
    });
</script>
