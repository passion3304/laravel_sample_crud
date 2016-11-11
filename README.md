
# Backpack\CRUD

[![Latest Version on Packagist](https://img.shields.io/packagist/v/backpack/crud.svg?style=flat-square)](https://packagist.org/packages/backpack/crud)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/Laravel-Backpack/CRUD/master.svg?style=flat-square)](https://travis-ci.org/Laravel-Backpack/CRUD)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/laravel-backpack/crud.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-backpack/crud/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/laravel-backpack/crud.svg?style=flat-square)](https://scrutinizer-ci.com/g/laravel-backpack/crud)
[![Style CI](https://styleci.io/repos/53581270/shield)](https://styleci.io/repos/53581270)
[![Total Downloads](https://img.shields.io/packagist/dt/backpack/crud.svg?style=flat-square)](https://packagist.org/packages/backpack/crud)
[![Tasks Ready to be Done](https://badge.waffle.io/Laravel-Backpack/crud.png?label=ready&title=Ready)](https://waffle.io/Laravel-Backpack/crud)

Quickly build an admin interface for your Eloquent models, using Laravel 5. Erect a complete CMS at 10 minutes/model, max.

Features:
- 33+ field types
- 1-n relationships
- n-n relationships
- Table view with search, pagination, click column to sort by it
- Reordering (nested sortable)
- Back-end validation using Requests
- Translatable models (multi-language) // TODO
- Easily extend fields (customising a field type or adding a new one is as easy as creating a new view with a particular name)
- Easily overwrite functionality (customising how the create/update/delete/reorder process works is as easy as creating a new function with the proper name in your EntityCrudCrontroller)

> ### Security updates and breaking changes
> Please **[subscribe to the Backpack Newsletter](http://eepurl.com/bUEGjf)** so you can find out about any security updates, breaking changes or major features. We send an email every 1-2 months.

![List / table view for Backpack/CRUD](https://dl.dropboxusercontent.com/u/2431352/backpack_crud_list.png)

## Install

1) In your terminal:

``` bash
$ composer require backpack/crud
```

2) Add this to your config/app.php, under "providers":
```php
        Backpack\CRUD\CrudServiceProvider::class,
```

3) Run:
```bash
$ php artisan elfinder:publish #published elfinder assets
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="public" #publish CRUD assets
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="lang" #publish the lang files
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="config" #publish the config file
$ php artisan vendor:publish --provider="Backpack\CRUD\CrudServiceProvider" --tag="elfinder" #publish overwritten elFinder assets
```

4) Define an 'uploads' disk. In your config/filesystems.php add this disk:
```php
'uploads' => [
            'driver' => 'local',
            'root' => public_path('uploads'),
        ],
```

5) If you haven't already, go through [steps 3-5 from the Backpack\Base installation](https://github.com/Laravel-Backpack/Base#install) (it provides the general views for the admin panel - layout, menu, notification bubbles, etc).

6) [Optional] You can now the file manager to the menu, in `resources/views/vendor/backpack/base/inc/sidebar.blade.php` or `menu.blade.php`:
```html
<li><a href="{{ url(config('backpack.base.route_prefix').'/elfinder') }}"><i class="fa fa-files-o"></i> <span>File manager</span></a></li>
```

## Usage

Check out the documentation at https://laravelbackpack.com


In short:

1. Make your model use the CrudTrait.

2. Create a controller that extends CrudController.

3. Create a new resource route.

4. **(optional)** Define your validation rules in a Request files.


## **(Optional)** Enable Revisions

CRUD supports tracking and restoring Model change Revisions with the help of [VentureCraft/revisionable](https://github.com/VentureCraft/revisionable).

To enable revisions on your Model do the following:

1. Run:
```bash
$ php artisan migrate --path=vendor/venturecraft/revisionable/src/migrations #run revisionable migrations
```

2. Add the `\Venturecraft\Revisionable\RevisionableTrait` Trait to your Model. E.g:
```php
namespace MyApp\Models;

class Article extends Eloquent {
    use \Backpack\CRUD\CrudTrait, \Venturecraft\Revisionable\RevisionableTrait;

    // If you are using another bootable trait the be sure to override the boot method in your model
    public static function boot()
    {
        parent::boot();
    }
}
```

3. Enable access to Revisions in your CrudController with:
```php
$this->crud->allowAccess('revisions');
```

Head on over to the [VentureCraft/revisionable](https://github.com/VentureCraft/revisionable) GitHub repo to see the full documentation and extra configuration options.

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

Please **[subscribe to the Backpack Newsletter](http://eepurl.com/bUEGjf)** so you can find out about any security updates, breaking changes or major features. We send an email every 1-2 months.

## Credits

- [Cristian Tabacitu](http://tabacitu.ro) - architect, designer, manager, main coder, PR guy, customer service guy & chief honcho
- [Cristian Tone](http://updivision.com) - architecture improvements
- [Marius Constantin](http://updivision.com) - bug fixing & improvements
- [Federico Liva](https://github.com/fede91it) - bug fixing
- [All Contributors][link-contributors]

Special thanks go to:
- [John Skoumbourdis](http://www.grocerycrud.com/) - Grocery CRUD for CodeIgniter was the obvious inspiration for this package.
- [Jaroen Noten](https://github.com/JeroenNoten/Laravel-AdminLTE) - creator of AdminLTE


## License

Backpack is free for non-commercial use and $19/project for commercial use. Please see [License File](LICENSE.md) and [backpackforlaravel.com](https://backpackforlaravel.com/#pricing) for more information.

[ico-version]: https://img.shields.io/packagist/v/dick/crud.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/tabacitu/crud.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/backpack/crud
[link-downloads]: https://packagist.org/packages/backpack/crud
[link-author]: https://tabacitu.ro
[link-contributors]: ../../contributors
