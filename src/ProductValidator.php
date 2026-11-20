<?php

// Product input rules (Task 12), shared by the web form AND the API.
class ProductValidator
{
    // take only the 3 fields we need, as trimmed strings
    // (the API may send numbers like 6.885, the form sends strings like "6.885")
    public static function clean(array $input): array
    {
        return [
            'name'               => trim((string) ($input['name'] ?? '')),
            'price'              => trim((string) ($input['price'] ?? '')),
            'quantity_available' => trim((string) ($input['quantity_available'] ?? '')),
        ];
    }

    // all fields required, price > 0, quantity >= 0
    // returns ['field' => 'message'], or [] when everything is OK
    public static function validate(array $data): array
    {
        $errors = [];

        if ($data['name'] === '') {
            $errors['name'] = 'Name is required.';
        }

        if ($data['price'] === '') {
            $errors['price'] = 'Price is required.';
        } elseif (!is_numeric($data['price']) || $data['price'] <= 0) {
            $errors['price'] = 'Price must be a positive number.';
        }

        if ($data['quantity_available'] === '') {
            $errors['quantity_available'] = 'Quantity is required.';
        } elseif (filter_var($data['quantity_available'], FILTER_VALIDATE_INT) === false || $data['quantity_available'] < 0) {
            $errors['quantity_available'] = 'Quantity must be a whole number, 0 or more.';
        }

        return $errors;
    }
}
