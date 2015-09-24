# Changelog

All Notable changes to `Dick CRUD` will be documented in this file

## NEXT - YYYY-MM-DD

### Added
- Nothing

### Deprecated
- Nothing

### Fixed
- Nothing

### Removed
- Nothing

### Security
- Nothing


## [0.8.9] - 2015-09-22

### Added
- added new column type: "model_function", that runs a certain function on the CRUD model;


## [0.8.8] - 2015-09-17

### Fixed
- bumped version;


## [0.8.7] - 2015-09-17

### Fixed
- update_fields and create_fields were being ignored because of the fake fields; now they're taken into consideration again, to allow different fields on the add/edit forms;

## [0.8.6] - 2015-09-11

### Fixed
- DateTime field type needed some magic to properly use the default value as stored in MySQL.

## [0.8.5] - 2015-09-11

### Fixed
- Fixed bug where reordering multi-language items didn't work through AJAX (route wasn't defined);


## [0.8.4] - 2015-09-10

### Added
- allTranslations() method on CrudTrait, to easily get all connected entities;


## [0.8.3] - 2015-09-10

### Added
- withFakes() method on CrudTrait, to easily get entities with fakes fields;

## [0.8.1] - 2015-09-09

### Added
- CRUD Alias for handling the routes. Now instead of defining a Route::resource() and a bunch of other routes if you need reorder/translations etc, you only define CRUD:resource() instead (same syntax) and the CrudServiceProvider will define all the routes you need. That, of course, if you define 'CRUD' => 'Dick\CRUD\CrudServiceProvider' in your config/app.php file, under 'aliases'.


## [0.8.0] - 2015-09-09

### Added
- CRUD Multi-language editing. If the EntityCrudController's "details_row" is set to true, by default the CRUD will output the translations for that entity's row. Tested and working add, edit, delete and reordering both for original rows and for translation rows.


## [0.7.9] - 2015-09-09

### Added
- CRUD Details Row functionality: if enabled, it will show a + sign for each row. When clicked, an AJAX call will return the showDetailsRow() method on the controller and place it in a row right below the current one; Currently that method just dumps the entry; But hey, it works.


## [0.7.8] - 2015-09-08

### Fixed
- In CRUD reordering, the leaf ID was outputted for debuging.


## [0.7.7] - 2015-09-08

### Added
- New field type: page_or_link; It's used in the MenuManager package, but can be used in any other model;


## [0.7.4] - 2015-09-08

### Added
- Actually started using CHANGELOG.md to track modifications.

### Fixed
- Reordering echo algorithm. It now takes account of leaf order.

