# Laravel status addition 

Assign statuses to Eloquent Models

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-model-status
```

Migrate the statuses table:

```php
php artisan migrate
```

## Usage

Add  ``` use HasStatuses``` to the model you like to use statuses on.

```php
<?php

namespace App;

use Spatie\LaravelStatus\HasStatuses;

class YourEloquentModel extends Model{
    use HasStatuses;
}
```

#### Setting

You can set a status like this:

```php
$model->setStatus('status-name', 'explenation-of-the-status');
```

#### Getting

getting all the statuses:

```php
$allStatuses = $model->statuses;
```

You can get the last status like this:

```php
$currentStatus = $model->getStatus();
```

You can get the a status by name:

```php
$currentStatus = $model->getStatusByName("status_name");
```

#### Validation

You can set custom validation to the status:

```php
public function isValidStatus($status_name, $status_explanation)
    {
        if (condition) {
            return true;
        }
        return false;
    }
```

#### Callback when a new status is set

You can execute a closure every time a new status is set. 

```php
$user->setCallbackOnSetStatus(
    function ($latest_name, $latest_explenation) 
    {
        // callback functionality
    }
);
```

### Testing

This package contains integration tests that are powered by [orchestral/testbench](https://github.com/orchestral/testbench).

You can run all tests with:

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Thomas](https://github.com/TVke)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
