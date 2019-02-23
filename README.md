# Better Migrate Seed
A wrapper around the `artisan migrate:fresh --seed` command in Laravel which provides some extra quality of life functionality to assist during development. 

## Overview
The command takes the signature `artisan seed:migrate`

Upon running the command, you'll be presented with the option to generate a complete set of seeders based on your current database. The goal here is to make it **super** easy to switch between different database states for local development.

**Example:** you've noticed a bug in your application based on some data you've setup through testing and you want to be able to easily recreate this scenario at a later date. You could either export the data or manually set up seeders, but both require some context switching. With this package, you can just run the same command that migrates and seeds your database to both create a set of seeders or run a previously generated batch.

### Available Commands
There are 3 commands available with this package:

A step in replacement for `artisan migrate:fresh --seed` with additional functionality to generate seeders and pick which seeders to use after re-migrating. You can pass the `--refresh` flag to use `migrate:refresh` instead of `migrate:fresh` behind the scenes:
```
artisan seed:migrate
```

To generate the seeders without having to re-migrate your DB, you can run the following command:
```
artisan seed:generate
```

To delete a set of seeders generated with any of the previous two commands (this will prompt before deletion):
```
artisan seed:delete
```

## Requirements
- Laravel 5.3.8+

## Getting Started

You can install the package via composer:
```
composer require joeyrush/better-migrate-seed
```

Assuming you're on Laravel 5.5+ with auto-package discovery, you should be able to run `php artisan migrate:seed`. If you're rocking an earlier version, you'll have to add the service provider to the "providers" key in `config/app.php`

```php
'providers' => [
    ...
    JoeyRush\BetterMigrateSeed\BetterMigrateSeedServiceProvider::class,
]
```

## Tests
Coming soon

## Credits
This package heavily relies on the use of [iSeed by OrangeHill](https://github.com/orangehill/iseed)
