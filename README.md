
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Assign statuses to Eloquent models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-model-status.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-status)
![GitHub Workflow Status](https://img.shields.io/github/workflow/status/spatie/laravel-model-status/run-tests?label=tests)
![Check & fix styling](https://github.com/spatie/laravel-model-status/workflows/Check%20&%20fix%20styling/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-model-status.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-status)

Imagine you want to have an Eloquent model hold a status. It's easily solved by just adding a `status` field to that model and be done with it. But in case you need a history of status changes or need to store some extra info on why a status changed, just adding a single field won't cut it.

This package provides a `HasStatuses` trait that, once installed on a model, allows you to do things like this:

```php
// set a status
$model->setStatus('pending', 'needs verification');

// set another status
$model->setStatus('accepted');

// specify a reason
$model->setStatus('rejected', 'My rejection reason');

// get the current status
$model->status(); // returns an instance of \Spatie\ModelStatus\Status

// get the previous status
$latestPendingStatus = $model->latestStatus('pending');

$latestPendingStatus->reason; // returns 'needs verification'
```

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/laravel-model-status.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/laravel-model-status)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-model-status
```

You must publish the migration with:
```bash
php artisan vendor:publish --provider="Spatie\ModelStatus\ModelStatusServiceProvider" --tag="migrations"
```

Migrate the `statuses` table:

```bash
php artisan migrate
```

Optionally you can publish the config-file with:
```bash
php artisan vendor:publish --provider="Spatie\ModelStatus\ModelStatusServiceProvider" --tag="config"
```

This is the contents of the file which will be published at `config/model-status.php`

```php
return [

    /*
     * The class name of the status model that holds all statuses.
     *
     * The model must be or extend `Spatie\ModelStatus\Status`.
     */
    'status_model' => Spatie\ModelStatus\Status::class,

    /*
     * The name of the column which holds the ID of the model related to the statuses.
     *
     * You can change this value if you have set a different name in the migration for the statuses table.
     */
    'model_primary_key_attribute' => 'model_id',

];
```

## Usage

Add the `HasStatuses` trait to a model you like to use statuses on.

```php
use Spatie\ModelStatus\HasStatuses;

class YourEloquentModel extends Model
{
    use HasStatuses;
}
```

### Set a new status

You can set a new status like this:

```php
$model->setStatus('status-name');
```

A reason for the status change can be passed as a second argument.

```php
$model->setStatus('status-name', 'optional reason');
```

### Retrieving statuses

You can get the current status of model:

```php
$model->status; // returns a string with the name of the latest status

$model->status(); // returns the latest instance of `Spatie\ModelStatus\Status`

$model->latestStatus(); // equivalent to `$model->status()`
```

You can also get latest status of a given name:

```php
$model->latestStatus('pending'); // returns an instance of `Spatie\ModelStatus\Status` that has the name `pending`
```

The following examples will return statusses of type `status 1` or `status 2`, whichever is latest.

```php
$lastStatus = $model->latestStatus(['status 1', 'status 2']);

// or alternatively...
$lastStatus = $model->latestStatus('status 1', 'status 2');
```

All associated statuses of a model can be retrieved like this:

```php
$allStatuses = $model->statuses;
```

### Retrieving models with a given latest state

The `currentStatus` scope will return models that have a status with the given name.

```php
$allPendingModels = Model::currentStatus('pending');

//or array of statuses
$allPendingModels = Model::currentStatus(['pending', 'initiated']);
$allPendingModels = Model::currentStatus('pending', 'initiated');
```

### Retrieving models without a given state

The `otherCurrentStatus` scope will return all models that do not have a status with the given name, including any model that does not have any statuses associated with them.

```php
$allNonPendingModels = Model::otherCurrentStatus('pending');
```

You can also provide an array of status names to exclude from the query.
```php
$allNonInitiatedOrPendingModels = Model::otherCurrentStatus(['initiated', 'pending']);

// or alternatively...
$allNonInitiatedOrPendingModels = Model::otherCurrentStatus('initiated', 'pending');
```

### Validating a status before setting it

You can add custom validation when setting a status by overwriting the `isValidStatus` method:

```php
public function isValidStatus(string $name, ?string $reason = null): bool
{
    ...

    if (! $condition) {
        return false;
    }

    return true;
}
```

If `isValidStatus` returns `false` a `Spatie\ModelStatus\Exceptions\InvalidStatus` exception will be thrown.

You may bypass validation with the `forceSetStatus` method:

```php
$model->forceSetStatus('invalid-status-name');
```

### Check if status has been assigned

You can check if a specific status has been set on the model at any time by using the `hasEverHadStatus` method:

```php
$model->hasEverHadStatus('status 1');
```

### Delete status from model

You can delete any given status that has been set on the model at any time by using the `deleteStatus` method:

Delete single status from model:

```php
$model->deleteStatus('status 1');
```

Delete multiple statuses from model at once:

```php
$model->deleteStatus(['status 1', 'status 2']);
```

### Events

The`Spatie\ModelStatus\Events\StatusUpdated`  event will be dispatched when the status is updated.

```php
namespace Spatie\ModelStatus\Events;

use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStatus\Status;

class StatusUpdated
{
    /** @var \Spatie\ModelStatus\Status|null */
    public $oldStatus;

    /** @var \Spatie\ModelStatus\Status */
    public $newStatus;

    /** @var \Illuminate\Database\Eloquent\Model */
    public $model;

    public function __construct(?Status $oldStatus, Status $newStatus, Model $model)
    {
        $this->oldStatus = $oldStatus;

        $this->newStatus = $newStatus;

        $this->model = $model;
    }
}
```

### Custom model and migration

You can change the model used by specifying a class name in the `status_model` key of the `model-status` config file.

You can change the column name used in the status table (`model_id` by default) when using a custom migration where you changed
that. In that case, simply change the `model_primary_key_attribute` key of the `model-status` config file.

### Testing

This package contains integration tests that are powered by [orchestral/testbench](https://github.com/orchestral/testbench).

You can run all tests with:

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

### Security

If you've found a bug regarding security please mail [security@spatie.be](mailto:security@spatie.be) instead of using the issue tracker.

## Credits

- [Thomas Verhelst](https://github.com/TVke)
- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
