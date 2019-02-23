# Better Migrate Seed
A wrapper around the `artisan migrate:fresh --seed` command in Laravel with some added quality of life functionality to assist during development.

## Overview
This package allows you to create a snapshot of your database by generating a series of seeders (1 per table, plus a base seeder which calls all of the seeders) which sit within a named sub-directory in your `database/seeds` directory. You can then use these grouped seeders to populate your database from scratch after a fresh migrate - all within the same command.

> You're free to customize the generated seeders, but be aware that if you generate another snapshot with an identical name - the new ones will overwrite the old ones.

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

### Notes
The name you supply to the seed generator will have spaces stripped out and be pascal-cased before being used as the sub-folder name and seeder class prefix. If you don't provide a name, it will generate one based on the current UNIX timestamp.

## Requirements
- Laravel 5.3.8+

## Getting Started

You can install the package via composer:
```
composer require joeyrush/better-migrate-seed
```

Assuming you're on Laravel 5.5+ with auto-package discovery, you should be able to run the commands straight away. If you're rocking an earlier version, you'll have to add the service provider to the "providers" key in `config/app.php`

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
