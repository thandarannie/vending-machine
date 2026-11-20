<?php
/** @var string $title */
/** @var string $action */
/** @var array $product */
/** @var array $errors */
?>
<div class="card narrow">
    <h1><?= htmlspecialchars($title) ?></h1>

    <form id="product-form" method="post" action="<?= url($action) ?>" novalidate>

        <div class="field">
            <label for="name">Name</label>
            <input id="name" name="name" type="text" required maxlength="100"
                   value="<?= htmlspecialchars($product['name']) ?>">
            <div class="error" id="name-error"><?= $errors['name'] ?? '' ?></div>
        </div>

        <div class="field">
            <label for="price">Price (USD)</label>
            <input id="price" name="price" type="number" required min="0.001" step="0.001"
                   value="<?= htmlspecialchars($product['price']) ?>">
            <div class="error" id="price-error"><?= $errors['price'] ?? '' ?></div>
        </div>

        <div class="field">
            <label for="quantity_available">Quantity Available</label>
            <input id="quantity_available" name="quantity_available" type="number" required min="0" step="1"
                   value="<?= htmlspecialchars($product['quantity_available']) ?>">
            <div class="error" id="quantity_available-error"><?= $errors['quantity_available'] ?? '' ?></div>
        </div>

        <button class="btn" type="submit">Save</button>
        <a class="btn grey" href="<?= url('/products') ?>">Cancel</a>
    </form>
</div>

<script>
    var form = document.getElementById('product-form');

    var rules = {
        name: function (value) {
            if (value === '') return 'Name is required.';
            return '';
        },
        price: function (value) {
            if (value === '') return 'Price is required.';
            if (isNaN(value) || Number(value) <= 0) return 'Price must be a positive number.';
            return '';
        },
        quantity_available: function (value) {
            if (value === '') return 'Quantity is required.';
            if (!/^\d+$/.test(value)) return 'Quantity must be a whole number, 0 or more.';
            return '';
        }
    };

    function checkField(name) {
        var input   = document.getElementById(name);
        var error   = document.getElementById(name + '-error');
        var message = rules[name](input.value.trim());

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

    form.addEventListener('submit', function (event) {
        var allOk = true;

        Object.keys(rules).forEach(function (name) {
            if (!checkField(name)) {
                allOk = false;
            }
        });

        if (!allOk) {
            event.preventDefault();
            form.querySelector('.input-error').focus();
        }
    });
</script>
