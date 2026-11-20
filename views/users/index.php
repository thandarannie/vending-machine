<?php
/** @var array $users */
?>
<div class="card">
    <h1>Users</h1>

    <p><a class="btn" href="<?= url('/users/create') ?>">+ New user</a></p>

    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>

        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><span class="badge <?= $user['role'] ?>"><?= $user['role'] ?></span></td>
            <td><?= $user['created_at'] ?></td>
            <td>
                <a class="btn" href="<?= url('/users/' . $user['id'] . '/edit') ?>">Edit</a>

                <?php if ((int) $user['id'] !== Auth::id()): ?>
                    <form class="inline" method="post" action="<?= url('/users/' . $user['id'] . '/delete') ?>"
                          onsubmit="return confirm('Delete this user?');">
                        <button class="btn red" type="submit">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</div>
