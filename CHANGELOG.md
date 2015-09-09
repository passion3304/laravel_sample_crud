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

