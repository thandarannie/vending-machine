<?php
/** @var string $title */
/** @var string $content  the page body */
/** @var string|null $flash  one-time message */
$me = Auth::user(); // logged-in user, or null
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title) ?> · Vending Machine</title>
    <link rel="stylesheet" href="<?= url('/assets/style.css') ?>">
</head>
<body>
<header class="nav">
    <div class="wrap">
        <a class="brand" href="<?= url('/products') ?>">🥤 Vending Machine</a>
        <a href="<?= url('/products') ?>">Products</a>

        <?php if (Auth::isAdmin()): ?>
            <a href="<?= url('/products/create') ?>">+ New product</a>
        <?php endif; ?>

        <?php if ($me): ?>
            <a href="<?= url('/transactions') ?>"><?= Auth::isAdmin() ? 'Transactions' : 'My purchases' ?></a>

            <span class="who">
                <?= htmlspecialchars($me['username']) ?>
                <span class="badge <?= $me['role'] ?>"><?= $me['role'] ?></span>
            </span>
            <form class="inline" method="post" action="<?= url('/logout') ?>">
                <button class="link" type="submit">Log out</button>
            </form>
        <?php else: ?>
            <a href="<?= url('/login') ?>">Log in</a>
        <?php endif; ?>
    </div>
</header>

<main class="wrap">
    <?php if ($flash): ?>
        <div class="flash"><?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>

    <?= $content ?>
</main>
</body>
</html>
