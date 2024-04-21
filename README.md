# Assign statuses to Eloquent models

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-model-status.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-status)
![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/spatie/laravel-model-status/run-tests.yml?branch=main&label=tests&style=flat-square)
![Check & fix styling](https://github.com/spatie/laravel-model-status/workflows/Check%20&%20fix%20styling/badge.svg)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-model-status.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-model-status)

Imagine you want to have an Eloquent model hold a status. It's easily solved by just adding a `status` field to that model and be done with it. But in case you need a history of status changes or need to store some extra info on why a status changed, just adding a single field won't cut it.

This package provides a `HasStatuses` trait that, once installed on a model, allows you to do things like this:

```php
// set a status
$model->setStatus(ModelStatus::PENDING, 'needs verification');

// set another status
$model->setStatus(ModelStatus::ACCEPTED);

// specify a reason
$model->setStatus(ModelStatus::REJECTED, 'My rejection reason');

// get the current status model
$model->status(); // returns an instance of \Spatie\ModelStatus\Status

//get the current status
$model->status; //returns an instance of the status enum

// get the previous status
$latestPendingStatus = $model->latestStatus(ModelStatus::PENDING);

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

Create The Enum that holds all of your available statuses for the model. \
The enum must be string backed

```php
enum ModelStatusEnum: string
{
    case PENDING = "pending";
    case APPROVED = "approved";
    case REJECTED = "rejected";
}
```

Add the `HasStatuses` trait to a model you like to use statuses on and implement the abstract function getStatusEnumClass. Inside, you must return the class name of the enum you desire to use with this model.

```php
use Spatie\ModelStatus\HasStatuses;

class YourEloquentModel extends Model
{
    use HasStatuses;

    public static function getStatusEnumClass(): string
    {
        return ModelStatusEnum::class;
    }
}
```

### Set a new status

You can set a new status like this:

```php
$model->setStatus(ModelStatusEnum::PENDING);
```

A reason for the status change can be passed as a second argument.

```php
$model->setStatus(ModelStatusEnum::APPROVED, 'optional reason');
```

### Retrieving statuses

You can get the current status of model:

```php
$model->status; // returns an enum instance of the latest status assigned to the model

$model->status(); // returns the latest instance of `Spatie\ModelStatus\Status`

$model->latestStatus(); // equivalent to `$model->status()`
```

You can also get latest status of a given value:

```php
$model->latestStatus(ModelStatusEnum::APPROVED); // returns an instance of `Spatie\ModelStatus\Status` that has the value `approved`
```


The following examples will return statusses of type APPROVED or REJECTED, whichever is latest.

```php
$lastStatus = $model->latestStatus([ModelStatusEnum::APPROVED, ModelStatusEnum::REJECTED]);

// or alternatively...
$lastStatus = $model->latestStatus(ModelStatusEnum::APPROVED, ModelStatusEnum::REJECTED);
```

All associated statuses of a model can be retrieved like this:

```php
$allStatuses = $model->statuses;
```
This will check if the model has status:

```php
$model->setStatus(ModelStatusEnum::APPROVED);

$isStatusExist = $model->hasStatus(ModelStatusEnum::APPROVED); // return true
$isStatusExist = $model->hasStatus(ModelStatusEnum::REJECTED); // return false
```
### Retrieving models with a given latest state

The `currentStatus` scope will return models that have a status with the given value.

```php
$allPendingModels = Model::currentStatus(ModelStatusEnum::APPROVED);

//or array of statuses
$allPendingModels = Model::currentStatus([ModelStatusEnum::APPROVED, ModelStatusEnum::PENDING]);
$allPendingModels = Model::currentStatus(ModelStatusEnum::APPROVED, ModelStatusEnum::PENDING);
```

### Retrieving models without a given status

The `otherCurrentStatus` scope will return all models that do not have a status with the given name, including any model that does not have any statuses associated with them.

```php
$allNonPendingModels = Model::otherCurrentStatus(ModelStatusEnum::PENDING);
```

You can also provide an array of status names to exclude from the query.
```php
$allNonPendingOrRejectedModels = Model::otherCurrentStatus([ModelStatusEnum::PENDING, ModelStatusEnum::REJECTED]);

// or alternatively...
$allNonPendingOrRejectedModels = Model::otherCurrentStatus(ModelStatusEnum::PENDING, ModelStatusEnum::REJECTED);
```

### Validating a status before setting it

You can add custom validation when setting a status by overwriting the `isValidStatus` method:

```php
public function isValidStatus($statusEnum, ?string $reason = null): bool
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
$model->forceSetStatus(ModelStatusEnum::PENDING);
```

### Check if status has been assigned

You can check if a specific status has been set on the model at any time by using the `hasEverHadStatus` method:

```php
$model->hasEverHadStatus(ModelStatusEnum::APPROVED);
```

### Delete status from model

You can delete any given status that has been set on the model at any time by using the `deleteStatus` method:

Delete single status from model:

```php
$model->deleteStatus(ModelStatusEnum::REJECTED);
```

Delete multiple statuses from model at once:

```php
$model->deleteStatus([ModelStatusEnum::PENDING, ModelStatusEnum::REJECTED]);
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
