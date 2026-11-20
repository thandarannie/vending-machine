<?php
/** @var array $products */
/** @var int $page */
/** @var int $totalPages */
/** @var int $total */
/** @var string $sort  column we sort by, e.g. 'price' */
/** @var string $dir   'asc' or 'desc' */

$listUrl = function (int $page, ?string $sortBy = null, ?string $direction = null) use ($sort, $dir): string {
    return url('/products?' . http_build_query([
        'sort' => $sortBy ?? $sort,
        'dir'  => $direction ?? $dir,
        'page' => $page,
    ]));
};

$sortHeader = function (string $column, string $label) use ($sort, $dir, $listUrl): string {
    $isCurrent = $sort === $column;
    $nextDir   = ($isCurrent && $dir === 'asc') ? 'desc' : 'asc';
    $arrow     = $isCurrent ? ($dir === 'asc' ? ' ▲' : ' ▼') : '';

    return '<a class="sort' . ($isCurrent ? ' active' : '') . '" href="' . $listUrl(1, $column, $nextDir) . '">'
         . $label . $arrow . '</a>';
};
?>
<div class="card">
    <h1>Products</h1>

    <table>
        <tr>
            <th><?= $sortHeader('id', 'ID') ?></th>
            <th><?= $sortHeader('name', 'Name') ?></th>
            <th><?= $sortHeader('price', 'Price') ?></th>
            <th><?= $sortHeader('quantity_available', 'Quantity') ?></th>
            <th>Actions</th>
        </tr>

        <?php foreach ($products as $product): ?>
        <tr>
            <td><?= $product['id'] ?></td>
            <td><?= htmlspecialchars($product['name']) ?></td>
            <td>$<?= $product['price'] ?></td>
            <td><?= $product['quantity_available'] ?></td>
            <td>
                <?php if ($product['quantity_available'] > 0): ?>
                    <a class="btn green" href="<?= purchase_url($product) ?>">Buy</a>
                <?php else: ?>
                    <span class="sold-out">Sold out</span>
                <?php endif; ?>

                <?php if (Auth::isAdmin()): ?>
                    <a class="btn" href="<?= url('/products/' . $product['id'] . '/edit') ?>">Edit</a>

                    <form class="inline" method="post" action="<?= url('/products/' . $product['id'] . '/delete') ?>"
                          onsubmit="return confirm('Delete this product?');">
                        <button class="btn red" type="submit">Delete</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <div class="pagination">
        <span class="muted">Page <?= $page ?> of <?= $totalPages ?> (<?= $total ?> products)</span>

        <?php if ($totalPages > 1): ?>
            <div class="pages">
                <?php if ($page > 1): ?>
                    <a href="<?= $listUrl($page - 1) ?>">« Prev</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= $listUrl($i) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= $listUrl($page + 1) ?>">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
