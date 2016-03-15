# Backpack\CRUD

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backpack/crud.svg?style=flat-square)](https://packagist.org/packages/backpack/crud)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/backpack/crud.svg?style=flat-square)](https://packagist.org/packages/backpack/crud)

Quickly build an admin interface for your Eloquent models, using Laravel 5. Erect a complete CMS at 10 minutes/model, max.

![List / table view for Backpack/CRUD](https://dl.dropboxusercontent.com/u/2431352/backpack_crud_list.png)

## Install

1) In your terminal:

``` bash
$ composer require backpack/crud
```

2) Add this to your config/app.php, under "providers":
```php
        Backpack\CRUD\CrudServiceProvider::class,
        'Collective\Html\HtmlServiceProvider',
        'Barryvdh\Elfinder\ElfinderServiceProvider',
```

3) Add this to your config/app.php, under "aliases":

```php
        'CRUD' => 'Backpack\CRUD\CrudServiceProvider',
        'Form' => 'Collective\Html\FormFacade',
        'Html' => 'Collective\Html\HtmlFacade',
```

4) Run:
```bash
$ php artisan elfinder:publish #published elfinder assets
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="public" #publish CRUD assets
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="elfinder" #publish overwritten elFinder assets
```

5) [Optional] You can now the file manager to the menu, in resources/views/vendor/backpack/base/inc/sidebar.blade.php or menu.blade.php:
```html
<li><a href="{{ url('admin/elfinder') }}"><i class="fa fa-files-o"></i> <span>File manager</span></a></li>
```

## Usage

Check out the documentation at https://laravel-backpack.readme.io/docs 

// TODO: create a documentation file base on Dick documentation

In short:

1. Create a controller that extends CrudController.

2. Make your model use the CrudTrait.

3. Create a new resource route.

4. **(optional)** Define your validation rules in a Request files.

## Screenshots

- List view pictured above.
- Create/update view:
![Create or update view for Backpack/CRUD](https://infinit.io/_/32czWa8.png)
- File manager (elFinder):
![File manager interface for Backpack/CRUD](https://dl.dropboxusercontent.com/u/2431352/backpack_crud_elfinder.png)

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email hello@tabacitu.ro instead of using the issue tracker.

## Credits

- [Cristian Tabacitu](http://tabacitu.ro)
- [All Contributors][link-contributors]

Special thanks go to:
- [John Skoumbourdis](http://www.grocerycrud.com/) - Grocery CRUD for CodeIgniter was the obvious inspiration for this package.
- [Jaroen Noten](https://github.com/JeroenNoten/Laravel-AdminLTE) - creator of AdminLTE


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/dick/crud.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/tabacitu/crud.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/backpack/crud
[link-downloads]: https://packagist.org/packages/backpack/crud
[link-author]: https://tabacitu.ro
[link-contributors]: ../../contributors
