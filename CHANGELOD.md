### v0.0.3
##### Features:
- Set psalm level to 2.

### v0.0.2
##### Features:
- Removed `final` keyword from `__construct` method of [AbstractConstraintValidator](/src/AbstractConstraintValidator.php);
- Added ability to pass `ContainerInterface` instance to [Validator](/src/Validator.php) in order to write your
own constraints that require additional objects;
- Performed small refactoring;
- Fixed null path of invalid values;
- Added [ValidatedValueInterface](src/Model/ValidatedValueInterface.php);
- Added auto-release tag drafter;
- Upgraded PHP version to `8.3.*`.

### v0.0.1
##### Features:
- Base implementation.
