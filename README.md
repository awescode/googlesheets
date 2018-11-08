# GoogleSheets

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Build Status][ico-travis]][link-travis]
[![StyleCI][ico-styleci]][link-styleci]

This is where your description should go. Take a look at [contributing.md](contributing.md) to see a to do list.

## Installation

Via Composer

``` bash
$ composer require awescode/googlesheets
```

The package will automatically register itself.

You can publish the migration with:

```bash
php artisan vendor:publish --provider="Awescode\GoogleSheets\Providers\GoogleSheetsServiceProvider" --tag="migrations"
```

After the migration has been published you can create the table for GoogleSheets by running the migrations:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider="Awescode\GoogleSheets\Providers\GoogleSheetsServiceProvider" --tag="config"
```


## Examples of use

```php
use Awescode\GoogleSheets\Facades\GoogleSheets;

GoogleSheets::lowerStr('Some String'); // 'some string'

GoogleSheets::count(); // 1
```

## Methods

#### example()

Description some example.

#### count()

Description some count.

#### validate(string $email)

Throws an `InvalidArgumentException` is email is invalid.

## Testing

You can run the tests with:

```bash
composer test
```

## Contributing

Please see [contributing.md](contributing.md) for details and a todolist.

## Security

If you discover any security related issues, please email :author_email instead of using the issue tracker.

## Credits

- [:author_name][link-author]
- [All Contributors][link-contributors]

## License

license. Please see the [license file](license.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/awescode/googlesheets.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/awescode/googlesheets.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/awescode/googlesheets/master.svg?style=flat-square
[ico-styleci]: https://styleci.io/repos/12345678/shield

[link-packagist]: https://packagist.org/packages/awescode/googlesheets
[link-downloads]: https://packagist.org/packages/awescode/googlesheets
[link-travis]: https://travis-ci.org/awescode/googlesheets
[link-styleci]: https://styleci.io/repos/12345678
[link-author]: https://github.com/awescode
[link-contributors]: ../../contributors]
