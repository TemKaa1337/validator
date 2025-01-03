### v0.0.6
##### Features:
- Replaced `psalm` with `phpstan`;
- Added generics for some of the interfaces;
- Added code coverage test;
- Marked all classes with `@api` or `@internal` annotation.
##### BC Breaks:
- Changed namespace from `Temkaa\SimpleValidator` to `Temkaa\Validator`.

### v0.0.5
##### Features:
- Added infection to project.

### v0.0.4
##### Features:
- Set psalm level to 1;
- Small refactoring;
- Changed logic of validation by collecting all constraints first and then validate.

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
