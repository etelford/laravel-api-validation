# Laravel API Custom Validation

Lightweight package that allows for creating custom validation rules, mainly
to be used as an alternative to Form Requests or inline Validators for API
requests.

## System Requirements

Laravel 5.4+ and PHP >= 7.0.

<a name="install"/>
## Installation

Install through Composer.

```bash
composer require etelford/laravel-api-validation
```

<a name="start">
## Usage

First, import the `HandlesApiRequests` trait (usually in your base controller):

```php
use Etelford\LaravelValidation\HandlesApiRequests;
```

For custom validation using Laravel's built-in validators, make a validation
class:

```php
<?php

namespace App\Validation\User;

class Store extends \Etelford\LaravelValidation\BaseValidator
{
    public function rules() : array
    {
        return ['email' => 'required|email'];
    }
}
```

Then in your controller method, use the validation you just created:

```php
// UserController.php
public function store($request, $id)
{
    $this->validate($request, 'User::Store');
}
```

By default if validation fails, a `ApiValidationException` exception will be thrown.

If you wish to bypass this, you can pass a third argument to the `validate()` method:

```php
$validator = $this->validate($request, 'User::Store', $throwOnFailure = false);
```

From this you, you can get the `Validator` instance and access all of Laravel's built in Validation methods:

```
if ($validator->validator()->passes()) {
    return true;
}

return false;
```

If you need more specialized validation that can't be handled directly by
Laravel's Validator, you can create a Custom Rule and chain it to your validation call:

```php
class VerifyMinimum extends \Etelford\LaravelValidation\CustomRule
{
    public function passes() : bool
    {
        return $this->amount >= $this->minimum;
    }

    public function messageBag() : array
    {
        return ['amount' => 'Amount must be at least' . $this->minimum];
    }
}

$customRule = new VerifyMinimum(['amount' => 100000, 'minimum' => 50000]);
$validation = $class->validate($request, 'entity::bar')->attachRules($customRule);
```
