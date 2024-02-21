# Generate avatars with initials
Ever seen those avatars (basically everywhere) that has your initials — mine would be LR; Lasse Rafn — well this package allows you to generate those, in a simple manner.

<p align="center">
<img src="https://ui-avatars.com/assets/promo-new.png" alt="Banner" />
</p>
 
<p align="center"> 
<a href="https://github.com/LasseRafn/php-initial-avatar-generator/actions/workflows/test.yml"><img src="https://github.com/LasseRafn/php-initial-avatar-generator/actions/workflows/test.yml/badge.svg" alt="Build Status"></a>
<a href="https://otterwise.app/github/lasserafn/php-initial-avatar-generator"><img src="https://img.shields.io/endpoint?url=https%3A%2F%2Fotterwise.app%2Fbadge%2Fgithub%2Flasserafn%2Fphp-initial-avatar-generator%2F6db6fcbb-95a6-446a-b5a1-15d7b1809dca" alt="OtterWise Coverage"></a>
<a href="https://styleci.io/repos/78973710"><img src="https://styleci.io/repos/78973710/shield?branch=master" alt="StyleCI Status"></a>
<a href="https://packagist.org/packages/LasseRafn/php-initial-avatar-generator"><img src="https://img.shields.io/packagist/dt/LasseRafn/php-initial-avatar-generator.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/LasseRafn/php-initial-avatar-generator"><img src="https://img.shields.io/packagist/v/LasseRafn/php-initial-avatar-generator.svg?style=flat-square" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/LasseRafn/php-initial-avatar-generator"><img src="https://img.shields.io/packagist/l/LasseRafn/php-initial-avatar-generator.svg?style=flat-square" alt="License"></a>
</p>

## Backers

Thank you to all our backers! 🙏 [[Become a backer](https://opencollective.com/ui-avatars#backer)]

<a href="https://opencollective.com/ui-avatars#backers" target="_blank"><img src="https://opencollective.com/ui-avatars/backers.svg?width=890"></a>

## Sponsors

Support this project by becoming a sponsor. Your logo will show up here with a link to your website. [[Become a sponsor](https://opencollective.com/ui-avatars#sponsor)]

<a href="https://opencollective.com/ui-avatars/sponsor/0/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/0/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/1/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/1/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/2/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/2/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/3/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/3/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/4/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/4/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/5/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/5/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/6/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/6/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/7/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/7/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/8/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/8/avatar.svg"></a>
<a href="https://opencollective.com/ui-avatars/sponsor/9/website" target="_blank"><img src="https://opencollective.com/ui-avatars/sponsor/9/avatar.svg"></a>

**There's also a api you can use: [https://ui-avatars.com](https://ui-avatars.com)**

## Installation
You just require using composer and you're good to go!
````bash
composer require lasserafn/php-initial-avatar-generator
````
Rad, *and long*, package name.. huh? Sorry. I'm not very good with names.

## Usage
As with installation, usage is quite simple. Generating a image is done by running:
````php
$avatar = new LasseRafn\InitialAvatarGenerator\InitialAvatar();

$image = $avatar->name('Lasse Rafn')->generate();
````

Thats it! The method will return a instance of [Image from Intervention](https://github.com/Intervention/image) so you can stream, download or even encode the image:
````php
return $image->stream('png', 100);
````
You can also just pass along the initials, and it will use those. Should you just include a first name, it will use the first two letters of it.

## SVG generation
````php
$avatar = new LasseRafn\InitialAvatarGenerator\InitialAvatar();

echo $avatar->name('Lasse Rafn')->generateSvg()->toXMLString(); // returns SVG XML string
````

## Supported methods and parameters
Of cause, passing a name is not the only thing this sweet thing does!

### Name (initials) - default: JD
````php
$image = $avatar->name('Albert Magnum')->generate();
````

### AutoFont - default: false

Will detect language script (using [lasserafn/php-string-script-language](https://github.com/lasserafn/php-string-script-language)) and use a font that supports it.

````php
$image = $avatar->autoFont()->generate();
````

### Width - default: 48
````php
// will be 96 pixels wide.
$image = $avatar->width(96)->generate();
````

### Height - default: 48
````php
// will be 96 pixels tall.
$image = $avatar->height(96)->generate();
````

### Size - default: 48 (proxy for `$avatar->width(X)->height(X)`)
````php
// will be 96x96 pixels.
$image = $avatar->size(96)->generate();
````

### Background color - default: #f0e9e9
````php
// will be red
$image = $avatar->background('#ff0000')->generate();
````

### Font color - default: #8b5d5d
````php
// will be red
$image = $avatar->color('#ff0000')->generate();
````

### Auto Color
````php
// Will choose a background color based on `name` and a contrasting font color. The color for a specific name will always be the same.
$image = $avatar->autoColor()->generate();
````

### Font file - default: /fonts/OpenSans-Regular.ttf
Two fonts with two variants are included:
* /fonts/OpenSans-Regular.ttf
* /fonts/OpenSans-Semibold.ttf
* /fonts/NotoSans-Bold.ttf
* /fonts/NotoSans-Regular.ttf

The method will look for the font, if none found it will append `__DIR__` and try again, and if not it will default to the first GD Internal Font.
If you input an integer between 1 and 5, it will use a GD Internal font as per that number.

````php
// will be Semibold
$image = $avatar->font('/fonts/OpenSans-Semibold.ttf')->generate();
````

### Font name (for SVGs) - default: Open Sans, sans-serif

````php
$image = $avatar->fontName('Arial, Helvetica, sans-serif')->generate();
````

### Length - default: 2
````php
$image = $avatar->name('John Doe Johnson')->length(3)->generate(); // 3 letters = JDJ
````

### Switching driver - default: gd
````php
$image = $avatar->gd()->generate(); // Uses GD driver
$image = $avatar->imagick()->generate(); // Uses Imagick driver
````

### Rounded - default: false
````php
$image = $avatar->rounded()->generate();
````

### Smooth - default: false

Makes rounding smoother with a resizing hack. Could be slower.

````php
$image = $avatar->rounded()->smooth()->generate();
````

If you are going to use `rounded()`, you want to use `smooth()` to avoid pixelated edges. Disabled by default because it _COULD_ be slower.
I would recommend just rounding with CSS.

### Font Size - default: 0.5
````php
$image = $avatar->fontSize(0.25)->generate(); // Font will be 25% of image size.
````
If the Image size is 50px and fontSize is 0.5, the font size will be 25px.

## Chaining it all together
We will not use the ->font() method in this example; as I like the regular one.

````php
return $avatar->name('Lasse Rafn')
              ->length(2)
              ->fontSize(0.5)
              ->size(96) // 48 * 2
              ->background('#8BC34A')
              ->color('#fff')
              ->generate()
              ->stream('png', 100);
````

Now, using that in a image (sized 48x48 pixels for retina):
````html
<img src="url-for-avatar-generation" width="48" height="48" style="border-radius: 100%" />
````
Will yield:

<img src="https://raw.githubusercontent.com/LasseRafn/php-initial-avatar-generator/master/demo_result.png" width="48" height="48" alt="Result" style="border-radius: 100%" />

*Rounded for appearance; the actual avatar is a filled square*

## Font Awesome Support

The package supports FontAwesome (v5) and already distributes the free version as `otf` format (see `/fonts` folder).

However, when using FontAwesome you may want to display one specific icon instead of the user's initials. This package, therefore, provides a handy `glyph($code)` method to be used along with FontAwesome.

First, you need to "find" the respective unicode for the glyph you want to insert. For example, you may want to display a typical "user" icon (unicode: `f007`). The unicode is located near the name of the icon (e.g., see here the user icon as an example here: [https://fontawesome.com/icons/user](https://fontawesome.com/icons/user) ).

An example for rendering a red avatar with a white "user" glyph would look like this:

```php
// note that we
// 1) use glyph() instead of name
// 2) change the font to FontAwesome!
return $avatar->glyph('f007')
              ->font('/fonts/FontAwesome5Free-Regular-400.otf')
              ->color('#fff')
              ->background('#ff0000')
              ->generate()
              ->stream('png', 100);
```

## Requirements
* PHP +7.0
* Fileinfo Extension (from intervention/image)

## Script/Language support
Some letters are not supported by the default font files, so I added some fonts to add support. You must use `autoFont()` to enable this feature. Supported are:

* Arabic
* Armenian
* Bengali
* Georgian
* Hebrew
* Mongolian
* Chinese
* Thai
* Tibetan

## [Contributors](https://github.com/LasseRafn/php-initial-avatar-generator/graphs/contributors)
Open Source is best when supported by a community. Any size of contribution is **very** appreciated.

## Supported Image Libraries (from intervention/image)
* GD Library (>=2.0)
* Imagick PHP extension (>=6.5.7)
