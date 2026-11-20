<?php
/** @var string $title */
/** @var string $action */
/** @var array $product */
/** @var array $errors */
?>
<div class="card narrow">
    <h1><?= htmlspecialchars($title) ?></h1>

    <!-- required / min / step = client-side validation rules -->
    <!-- novalidate = turn off the browser's popup, our JavaScript below shows the errors instead -->
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

    // 1. one check per field: return an error message, or '' if OK
    //    (same rules as validate() in ProductsController)
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
            if (!/^\d+$/.test(value)) return 'Quantity must be a whole number, 0 or more.'; // only digits, so no "1.5" or "-2"
            return '';
        }
    };

    // 2. check one field, show or clear its error, return true if OK
    function checkField(name) {
        var input   = document.getElementById(name);
        var error   = document.getElementById(name + '-error');
        var message = rules[name](input.value.trim());

        error.textContent = message;
        input.classList.toggle('input-error', message !== ''); // red border when wrong
        return message === '';
    }

    // 3. check a field when the user leaves it
    Object.keys(rules).forEach(function (name) {
        document.getElementById(name).addEventListener('blur', function () {
            checkField(name);
        });
    });

    // 4. on submit: check all fields, stop the form if any is wrong
    form.addEventListener('submit', function (event) {
        var allOk = true;

        Object.keys(rules).forEach(function (name) {
            if (!checkField(name)) {
                allOk = false;
            }
        });

        if (!allOk) {
            event.preventDefault();                          // do not send the form
            form.querySelector('.input-error').focus();      // jump to the first wrong field
        }
    });
</script>
