# PHP Enum implementation inspired from SplEnum

[![GitHub Actions][GA Image]][GA Link]
[![Latest Stable Version](https://poser.pugx.org/myclabs/php-enum/version.png)](https://packagist.org/packages/myclabs/php-enum)
[![Total Downloads](https://poser.pugx.org/myclabs/php-enum/downloads.png)](https://packagist.org/packages/myclabs/php-enum)
[![Psalm Shepherd][Shepherd Image]][Shepherd Link]

Maintenance for this project is [supported via Tidelift](https://tidelift.com/subscription/pkg/packagist-myclabs-php-enum?utm_source=packagist-myclabs-php-enum&utm_medium=referral&utm_campaign=readme).

## Why?

First, and mainly, `SplEnum` is not integrated to PHP, you have to install the extension separately.

Using an enum instead of class constants provides the following advantages:

- You can use an enum as a parameter type: `function setAction(Action $action) {`
- You can use an enum as a return type: `function getAction() : Action {`
- You can enrich the enum with methods (e.g. `format`, `parse`, …)
- You can extend the enum to add new values (make your enum `final` to prevent it)
- You can get a list of all the possible values (see below)

This Enum class is not intended to replace class constants, but only to be used when it makes sense.

## Installation

```
composer require myclabs/php-enum
```

## Declaration

```php
use MyCLabs\Enum\Enum;

/**
 * Action enum
 */
final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
```

## Usage

```php
$action = Action::VIEW();

// or with a dynamic key:
$action = Action::$key();
// or with a dynamic value:
$action = Action::from($value);
// or
$action = new Action($value);
```

As you can see, static methods are automatically implemented to provide quick access to an enum value.

One advantage over using class constants is to be able to use an enum as a parameter type:

```php
function setAction(Action $action) {
    // ...
}
```

## Documentation

- `__construct()` The constructor checks that the value exist in the enum
- `__toString()` You can `echo $myValue`, it will display the enum value (value of the constant)
- `getValue()` Returns the current value of the enum
- `getKey()` Returns the key of the current value on Enum
- `equals()` Tests whether enum instances are equal (returns `true` if enum values are equal, `false` otherwise)

Static methods:

- `from()` Creates an Enum instance, checking that the value exist in the enum
- `toArray()` method Returns all possible values as an array (constant name in key, constant value in value)
- `keys()` Returns the names (keys) of all constants in the Enum class
- `values()` Returns instances of the Enum class of all Enum constants (constant name in key, Enum instance in value)
- `isValid()` Check if tested value is valid on enum set
- `isValidKey()` Check if tested key is valid on enum set
- `assertValidValue()` Assert the value is valid on enum set, throwing exception otherwise
- `search()` Return key for searched value

### Static methods

```php
final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}

// Static method:
$action = Action::VIEW();
$action = Action::EDIT();
```

Static method helpers are implemented using [`__callStatic()`](http://www.php.net/manual/en/language.oop5.overloading.php#object.callstatic).

If you care about IDE autocompletion, you can either implement the static methods yourself:

```php
final class Action extends Enum
{
    private const VIEW = 'view';

    /**
     * @return Action
     */
    public static function VIEW() {
        return new Action(self::VIEW);
    }
}
```

or you can use phpdoc (this is supported in PhpStorm for example):

```php
/**
 * @method static Action VIEW()
 * @method static Action EDIT()
 */
final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
```

## Native enums and migration
Native enum arrived to PHP in version 8.1: https://www.php.net/enumerations  
If your project is running PHP 8.1+ or your library has it as a minimum requirement you should use it instead of this library.

When migrating from `myclabs/php-enum`, the effort should be small if the usage was in the recommended way:
- private constants
- final classes
- no method overridden

Changes for migration:
- Class definition should be changed from
```php
/**
 * @method static Action VIEW()
 * @method static Action EDIT()
 */
final class Action extends Enum
{
    private const VIEW = 'view';
    private const EDIT = 'edit';
}
```
 to
```php
enum Action: string
{
    case VIEW = 'view';
    case EDIT = 'edit';
}
```
All places where the class was used as a type will continue to work.

Usages and the change needed:

| Operation                                                      | myclabs/php-enum                                                           | native enum                                                                                                                                                                                                                              |
|----------------------------------------------------------------|----------------------------------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| Obtain an instance will change from                            | `$enumCase = Action::VIEW()`                                               | `$enumCase = Action::VIEW`                                                                                                                                                                                                               |
| Create an enum from a backed value                             | `$enumCase = new Action('view')`                                           | `$enumCase = Action::from('view')`                                                                                                                                                                                                       |
| Get the backed value of the enum instance                      | `$enumCase->getValue()`                                                    | `$enumCase->value`                                                                                                                                                                                                                       |
| Compare two enum instances                                     | `$enumCase1 == $enumCase2` <br/> or <br/> `$enumCase1->equals($enumCase2)` | `$enumCase1 === $enumCase2`                                                                                                                                                                                                              |
| Get the key/name of the enum instance                          | `$enumCase->getKey()`                                                      | `$enumCase->name`                                                                                                                                                                                                                        |
| Get a list of all the possible instances of the enum           | `Action::values()`                                                         | `Action::cases()`                                                                                                                                                                                                                        |
| Get a map of possible instances of the enum mapped by name     | `Action::values()`                                                         | `array_combine(array_map(fn($case) => $case->name, Action::cases()), Action::cases())` <br/> or <br/> `(new ReflectionEnum(Action::class))->getConstants()`                                                                              |
| Get a list of all possible names of the enum                   | `Action::keys()`                                                           | `array_map(fn($case) => $case->name, Action::cases())`                                                                                                                                                                                   |
| Get a list of all possible backed values of the enum           | `Action::toArray()`                                                        | `array_map(fn($case) => $case->value, Action::cases())`                                                                                                                                                                                  |
| Get a map of possible backed values of the enum mapped by name | `Action::toArray()`                                                        | `array_combine(array_map(fn($case) => $case->name, Action::cases()), array_map(fn($case) => $case->value, Action::cases()))` <br/> or <br/> `array_map(fn($case) => $case->value, (new ReflectionEnum(Action::class))->getConstants()))` |

## Related projects

- [PHP 8.1+ native enum](https://www.php.net/enumerations)
- [Doctrine enum mapping](https://github.com/acelaya/doctrine-enum-type)
- [Symfony ParamConverter integration](https://github.com/Ex3v/MyCLabsEnumParamConverter)
- [PHPStan integration](https://github.com/timeweb/phpstan-enum)


[GA Image]: https://github.com/myclabs/php-enum/workflows/CI/badge.svg

[GA Link]: https://github.com/myclabs/php-enum/actions?query=workflow%3A%22CI%22+branch%3Amaster

[Shepherd Image]: https://shepherd.dev/github/myclabs/php-enum/coverage.svg

[Shepherd Link]: https://shepherd.dev/github/myclabs/php-enum
