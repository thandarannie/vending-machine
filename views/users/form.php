<?php
/** @var string $title */
/** @var string $action */
/** @var array $user */
/** @var array $errors */
/** @var bool $isNew */
?>
<div class="card narrow">
    <h1><?= htmlspecialchars($title) ?></h1>

    <form id="user-form" method="post" action="<?= url($action) ?>" novalidate>

        <div class="field">
            <label for="username">Username</label>
            <input id="username" name="username" type="text" required maxlength="50"
                   value="<?= htmlspecialchars($user['username']) ?>">
            <div class="error" id="username-error"><?= $errors['username'] ?? '' ?></div>
        </div>

        <div class="field">
            <label for="password">Password<?= $isNew ? '' : ' (leave blank to keep current)' ?></label>
            <input id="password" name="password" type="password" minlength="6" <?= $isNew ? 'required' : '' ?>>
            <div class="error" id="password-error"><?= $errors['password'] ?? '' ?></div>
        </div>

        <div class="field">
            <label for="role">Role</label>
            <select id="role" name="role">
                <option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
            <div class="error" id="role-error"><?= $errors['role'] ?? '' ?></div>
        </div>

        <button class="btn" type="submit">Save</button>
        <a class="btn grey" href="<?= url('/users') ?>">Cancel</a>
    </form>
</div>

<script>
    var isNew = <?= $isNew ? 'true' : 'false' ?>;

    var rules = {
        username: function (value) {
            if (value === '') return 'Username is required.';
            if (value.length > 50) return 'Username must be 50 characters or fewer.';
            return '';
        },
        password: function (value) {
            if (isNew && value === '') return 'Password is required.';
            if (value !== '' && value.length < 6) return 'Password must be at least 6 characters.';
            return '';
        }
    };

    function checkField(name) {
        var input   = document.getElementById(name);
        var error   = document.getElementById(name + '-error');
        var value   = name === 'password' ? input.value : input.value.trim();
        var message = rules[name](value);

        error.textContent = message;
        input.classList.toggle('input-error', message !== '');
        return message === '';
    }

    Object.keys(rules).forEach(function (name) {
        var input = document.getElementById(name);
        var error = document.getElementById(name + '-error');

        input.addEventListener('blur', function () {
            checkField(name);
        });

        input.addEventListener('input', function () {
            if (error.textContent !== '') {
                checkField(name);
            }
        });
    });

    document.getElementById('user-form').addEventListener('submit', function (event) {
        var allOk = true;

        Object.keys(rules).forEach(function (name) {
            if (!checkField(name)) {
                allOk = false;
            }
        });

        if (!allOk) {
            event.preventDefault();
            document.querySelector('.input-error').focus();
        }
    });
</script>
