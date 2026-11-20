<?php
/** @var array $product */
/** @var mixed $quantity */
/** @var array $errors */
$stock = (int) $product['quantity_available'];
?>
<div class="card narrow">
    <h1>Buy <?= htmlspecialchars($product['name']) ?></h1>

    <p>Price: <strong>$<?= $product['price'] ?></strong></p>
    <p>In stock: <strong><?= $stock ?></strong></p>

    <?php if ($stock === 0): ?>
        <p class="error">Sorry, this product is sold out.</p>
        <a class="btn grey" href="<?= url('/products') ?>">Back to products</a>
    <?php else: ?>
        <form id="purchase-form" method="post" action="<?= purchase_url($product) ?>" novalidate>
            <div class="field">
                <label for="quantity">Quantity</label>
                <input id="quantity" name="quantity" type="number" required min="1" max="<?= $stock ?>" step="1"
                       value="<?= htmlspecialchars((string) $quantity) ?>">
                <div class="error" id="quantity-error"><?= $errors['quantity'] ?? '' ?></div>
            </div>

            <p>Total: <strong id="total"></strong></p>

            <button class="btn green" type="submit">Confirm</button>
            <a class="btn grey" href="<?= url('/products') ?>">Cancel</a>
        </form>

        <script>
            var price = <?= (float) $product['price'] ?>;
            var stock = <?= $stock ?>;

            var form  = document.getElementById('purchase-form');
            var input = document.getElementById('quantity');
            var error = document.getElementById('quantity-error');
            var total = document.getElementById('total');

            function checkQuantity() {
                var value = input.value.trim();

                if (value === '') {
                    return 'Quantity is required.';
                }
                if (!/^\d+$/.test(value)) {
                    return 'Quantity must be a whole number.';
                }
                if (Number(value) < 1) {
                    return 'Quantity must be at least 1.';
                }
                if (Number(value) > stock) {
                    return 'Only ' + stock + ' left in stock.';
                }
                return '';
            }

            function showTotal() {
                var qty = Number(input.value);
                total.textContent = checkQuantity() === '' ? '$' + (price * qty).toFixed(3) : '—';
            }

            input.addEventListener('input', function () {
                error.textContent = '';
                input.classList.remove('input-error');
                showTotal();
            });

            form.addEventListener('submit', function (event) {
                var message = checkQuantity();

                if (message !== '') {
                    event.preventDefault();
                    error.textContent = message;
                    input.classList.add('input-error');
                    input.focus();
                }
            });

            showTotal();
        </script>
    <?php endif; ?>
</div>
