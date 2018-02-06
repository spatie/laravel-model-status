# Assign statuses to Eloquent models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-model-status.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-status)
[![Build Status](https://img.shields.io/travis/spatie/laravel-model-status/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-model-status)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-model-status.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-model-status)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-model-status.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-status)

This package can be used when a status needs to be given to a specific model. 
The statuses are all saved so the model has a history which can using `statuses`.
Changing the status is as easy as `setStatus('pending')`.

Once the trait is installed on the model you can do things like this:

```php
$model = new Model();

$model->setStatus('pending', 'extra description');
$model->setStatus('declined');

$currentStatus = $model->status();

if($currentStatus === 'pending') {
    $lastDeclined = $model->latestStatus('declined');
}

$declinedReason = $lastDeclined->description;
```

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

Add `use HasStatuses` to the model you like to use statuses on.

```php
use Spatie\LaravelModelStatus\HasStatuses;

class YourEloquentModel extends Model
{
    use HasStatuses;
}
```

#### Set a new status

You can set a new status like this:

```php
$model->setStatus('status-name');
```

or with an optional description:

```php
$model->setStatus('status-name', 'optional desription');
```

#### Get the current status

You can get all the statuses:

```php
$allStatuses = $model->statuses;
```

You can get the current status like this:

```php
$currentStatus = $model->status();
```

or the last status:

```php
$lastStatus = $model->latestStatus();
```

You can get a status by name:

```php
$lastStatus = $model->latestStatus('status-name');
```

You can get the last set status from a few statuses:

```php
$lastStatus = $model->latestStatus('status 1', 'status 2', 'status 3');
```

#### Validating a status before setting it

You can add custom validation when setting a status by overwriting the `isValidStatus` method:

```php
public function isValidStatus(string $name, string $description = ''): bool
{
    if (condition) {
        return true;
    }

    return false;
}
```

### Custom model and migration

You can publish the config-file with:
```bash
php artisan vendor:publish --provider="Spatie\LaravelModelStatus\ModelStatusServiceProvider" --tag="config"
```

You can publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\LaravelModelStatus\ModelStatusServiceProvider" --tag="migrations"
```

Migrate after editing with: 
```bash
php artisan migrate
```

In the published config-file called `model-status.php` you can change the status_model.
Don't forget to extend the new model with `\Spatie\LaravelModelStatus\Models\Status` otherwise it will not work.

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

- [Thomas Verhelst](https://github.com/TVke)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie).
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
