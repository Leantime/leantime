# Contributing to Leantime

Thanks for considering contributing to Leantime.  While the current framework doesn't always comply with the following conventions we would prefer if all new pull requests did.  We will be modifying the previous code to adhere to these standards and thank you for doing the same.

## Table of Conventions

* [Contributing](#contributions)
* [Code](#conventions)

## Contributions

All contributions are welcome.  Think our typo, translation or sloppy code is too small for a pull request?  Think again.

### Committing
---

All Bug, feature and design commits should be tagged with the issue number:

`#69 - Add tests for MVC Model Metadata in core.`

Typo, translation and cleanup commits do not require an issue/issue number and should instead be tagged with **Clean**:

`Clean - Applied conventions to the core helper class.`

#### Bug Fixes

If this is a new bug please create a **bug** issue before submitting a pull request.

#### Features

If this is a new feature please create an **enhancement** issue before submitting.

#### Design

Create a **design** issue and discuss the desired changes before writing any code.  Describe the problem the design will attempt to solve.

#### Signatures

Then you just add a line to every git commit message:

Signed-off-by: Marcel Jensen <marcel.jensen@email.com>

**Tip:** `git commit -s`.

## Code

### Assignment
---
Expressions should not contain assignments.  This can be easily interpreted as a mistake.

```php
// Yes
$a = func();
if ( $x ) {
   // ...
}
```

```php
// No
if ( $x = func() ) {
    // ...
}
```

```php
// Yes
$result = $db->query( 'SELECT * FROM users' );
foreach ( $result as $user ) {
    // ...
}
```

```php
// No
$result = $db->query( 'SELECT * FROM users' );
while ( $user = $db->fetchObject( $result ) ) {
    // ...
}
```

### Naming
---
Functions and variables should use camelCase.

```php
public function getUser( $firstName, $lastName )
```

### Spacing
---

#### General

```php
// Yes
$x = $y + $z;
```

```php
// No
$x=$y+$z;
```

#### Control Structures

```php
// Yes
if ( func() ) {
	$x = 'happy';
}
```

```php
// No
if( func() )
{
	$x = 'sad';
}
```

#### Comments

```php
// Yes
//No
```

### Strings
---

#### String Literals

```php
// Yes
$name = 'Good';
```

```php
// No
$name = "Bad";
```

### Classes
---

Encapsulate your code in classes or add/extend functionality to existing classes; do not add new global functions or variables.

All new code should use proper modifiers, including public when it's appropriate, but do not add visibility to existing code without first checking, testing and refactoring as required. It's generally a good idea to avoid visibility changes unless you're making changes to the function which would break old uses of it anyway.
