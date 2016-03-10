# Backpack\CRUD

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dick/crud.svg?style=flat-square)](https://packagist.org/packages/dick/crud)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/dick/crud.svg?style=flat-square)](https://packagist.org/packages/dick/crud)

Quickly build an admin interface for your Eloquent models, using Laravel 5. Erect a complete CMS at 10 minutes/model, max.

## Install

Via Composer

``` bash
$ composer require backpack/crud
```

Add this to your config/app.php, under "providers":
```php
        Backpack\CRUD\CrudServiceProvider::class,
        'Collective\Html\HtmlServiceProvider',
        'Barryvdh\Elfinder\ElfinderServiceProvider',
```

Add this to your config/app.php, under "aliases":

```php
        'CRUD' => 'Backpack\CRUD\CrudServiceProvider',
        'Form' => 'Collective\Html\FormFacade',
        'Html' => 'Collective\Html\HtmlFacade',
```

Run:
```bash
$ php artisan elfinder:publish #published elfinder assets
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="public" #publish CRUD assets
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="elfinder" #publish overwritten elFinder assets
```

## Usage

Check out the documentation at http://LaravelBackPack.com/docs 

// TODO: create a documentation file base on Dick documentation

In short:

1. Create a controller that extends CrudController.

2. Make your model use the CrudTrait.

3. Create a new resource route.

4. **(optional)** Define your validation rules in a Request files.

## Screenshots

See http://usedick.com

// TODO: create equivalent screenshots for all Dick screenshots

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@tabacitu.ro instead of using the issue tracker.

## Credits

- [Cristian Tabacitu][http://tabacitu.ro]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/dick/crud.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/tabacitu/crud.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/backpack/crud
[link-downloads]: https://packagist.org/packages/dick/crud
[link-author]: https://tabacitu.ro
[link-contributors]: ../../contributors
