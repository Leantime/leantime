# php-svg

[![Build Status](https://travis-ci.org/meyfa/php-svg.svg?branch=master)](https://travis-ci.org/meyfa/php-svg)
[![Code Climate](https://codeclimate.com/github/meyfa/php-svg/badges/gpa.svg)](https://codeclimate.com/github/meyfa/php-svg)

This is a vector graphics library for PHP, which surely is a broad
specification. That is due to the fact that the goal of this project is to
offer features in three different, big areas:

- Generating SVG images from PHP code and outputting them, either into XML
    strings or into files.
- Loading and parsing XML strings into document trees that can be easily
    modified and then also turned back into strings.
- Transforming parsed or generated document trees into raster graphics,
    like PNG.



## Contributing

These tasks will take a lot of time and effort, so you are welcome to contribute
if this is a project you are interested in.  
In case you decide to contribute, please honor these things:

1. External libraries shall not be used.
2. Please set your editor to use 4 spaces for indentation. In general, it would
    be good to follow the existing code style for consistency.
3. Source files must end with a newline character.
4. By contributing code, you agree to license that code under the MIT license to
    this project.



---



## Installation

### Composer (recommended)

This package is available through Composer/Packagist:

```
$ composer require meyfa/php-svg
```

### Manual

[Download](https://github.com/meyfa/php-svg/zipball/master) this repo,
or the [latest release](https://github.com/meyfa/php-svg/releases),
and put it somewhere in your project. Then do:

```php
<?php
require_once __DIR__.'/<path_where_you_put_it>/autoloader.php';
```

The rest works exactly the same as with Composer, just without things like nice
version management.



---



## Getting Started

### Creating an image

The following code generates an SVG with a blue square, sets the Content-Type
header and echoes it:

```php
<?php

use SVG\SVG;
use SVG\Nodes\Shapes\SVGRect;

// image with 100x100 viewport
$image = new SVG(100, 100);
$doc = $image->getDocument();

// blue 40x40 square at (0, 0)
$square = new SVGRect(0, 0, 40, 40);
$square->setStyle('fill', '#0000FF');
$doc->addChild($square);

header('Content-Type: image/svg+xml');
echo $image;
```

### Rasterizing

To convert an instance of `SVG` to a PHP/GD image resource, or in other words
convert it to a raster image, you simply call `toRasterImage($width, $height)`
on it. Example:

```php
<?php

use SVG\SVG;
use SVG\Nodes\Shapes\SVGCircle;

$image = new SVG(100, 100);
$doc = $image->getDocument();

// circle with radius 20 and green border, center at (50, 50)
$doc->addChild(
    (new SVGCircle(50, 50, 20))
        ->setStyle('fill', 'none')
        ->setStyle('stroke', '#0F0')
        ->setStyle('stroke-width', '2px')
);

// rasterize to a 200x200 image, i.e. the original SVG size scaled by 2
$rasterImage = $image->toRasterImage(200, 200);

header('Content-Type: image/png');
imagepng($rasterImage);
```

### Loading an SVG

You can load SVG images both from strings and from files. This example loads one
from a string, moves the contained rectangle and echoes the new SVG:

```php
<?php

use SVG\SVG;

$svg  = '<svg width="100" height="100">';
$svg .= '<rect width="50" height="50" fill="#00F" />';
$svg .= '</svg>';

$image = SVG::fromString($svg);
$doc = $image->getDocument();

$rect = $doc->getChild(0);
$rect->setX(25)->setY(25);

header('Content-Type: image/svg+xml');
echo $image;
```

For loading from a file instead, you would call `SVG::fromFile($file)`.
That function supports local file paths as well as URLs.

For additional documentation, see the [wiki](https://github.com/meyfa/php-svg/wiki).
