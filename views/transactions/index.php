<?php
/** @var string $title */
/** @var array $transactions */
$total = array_sum(array_column($transactions, 'total_price'));
?>
<div class="card">
    <h1><?= htmlspecialchars($title) ?></h1>

    <?php if (!$transactions): ?>
        <p class="muted">No purchases yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>#</th>
                <?php if (Auth::isAdmin()): ?><th>User</th><?php endif; ?>
                <th>Product</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Date</th>
            </tr>

            <?php foreach ($transactions as $t): ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <?php if (Auth::isAdmin()): ?><td><?= htmlspecialchars($t['username']) ?></td><?php endif; ?>
                <td><?= htmlspecialchars($t['product_name']) ?></td>
                <td><?= $t['quantity'] ?></td>
                <td>$<?= $t['total_price'] ?></td>
                <td><?= $t['created_at'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <p class="muted"><?= count($transactions) ?> purchases, total $<?= number_format($total, 3) ?></p>
    <?php endif; ?>
</div>
