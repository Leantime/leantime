# QR Code

*By [endroid](https://endroid.nl/)*

[![Latest Stable Version](http://img.shields.io/packagist/v/endroid/qr-code.svg)](https://packagist.org/packages/endroid/qr-code)
[![Build Status](https://github.com/endroid/qr-code/workflows/CI/badge.svg)](https://github.com/endroid/qr-code/actions)
[![Total Downloads](http://img.shields.io/packagist/dt/endroid/qr-code.svg)](https://packagist.org/packages/endroid/qr-code)
[![Monthly Downloads](http://img.shields.io/packagist/dm/endroid/qr-code.svg)](https://packagist.org/packages/endroid/qr-code)
[![License](http://img.shields.io/packagist/l/endroid/qr-code.svg)](https://packagist.org/packages/endroid/qr-code)

This library helps you generate QR codes in a jiffy. Makes use of [bacon/bacon-qr-code](https://github.com/Bacon/BaconQrCode)
to generate the matrix and [khanamiryan/qrcode-detector-decoder](https://github.com/khanamiryan/php-qrcode-detector-decoder)
for validating generated QR codes. Further extended with Twig extensions, generation routes, a factory and a
Symfony bundle for easy installation and configuration.

Different writers are provided to generate the QR code as PNG, SVG, EPS, PDF or in binary format.

## Installation

Use [Composer](https://getcomposer.org/) to install the library.

``` bash
$ composer require endroid/qr-code
```

## Basic usage

```php
use Endroid\QrCode\QrCode;

$qrCode = new QrCode('Life is too short to be generating QR codes');

header('Content-Type: '.$qrCode->getContentType());
echo $qrCode->writeString();
```

## Advanced usage

```php
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\LabelAlignment;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Response\QrCodeResponse;

// Create a basic QR code
$qrCode = new QrCode('Life is too short to be generating QR codes');
$qrCode->setSize(300);
$qrCode->setMargin(10); 

// Set advanced options
$qrCode->setWriterByName('png');
$qrCode->setEncoding('UTF-8');
$qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
$qrCode->setForegroundColor(['r' => 0, 'g' => 0, 'b' => 0, 'a' => 0]);
$qrCode->setBackgroundColor(['r' => 255, 'g' => 255, 'b' => 255, 'a' => 0]);
$qrCode->setLabel('Scan the code', 16, __DIR__.'/../assets/fonts/noto_sans.otf', LabelAlignment::CENTER());
$qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
$qrCode->setLogoSize(150, 200);
$qrCode->setValidateResult(false);

// Round block sizes to improve readability and make the blocks sharper in pixel based outputs (like png).
// There are three approaches:
$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_MARGIN); // The size of the qr code is shrinked, if necessary, but the size of the final image remains unchanged due to additional margin being added (default)
$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE); // The size of the qr code and the final image is enlarged, if necessary
$qrCode->setRoundBlockSize(true, QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK); // The size of the qr code and the final image is shrinked, if necessary

// Set additional writer options (SvgWriter example)
$qrCode->setWriterOptions(['exclude_xml_declaration' => true]);

// Directly output the QR code
header('Content-Type: '.$qrCode->getContentType());
echo $qrCode->writeString();

// Save it to a file
$qrCode->writeFile(__DIR__.'/qrcode.png');

// Generate a data URI to include image data inline (i.e. inside an <img> tag)
$dataUri = $qrCode->writeDataUri();
```

![QR Code](https://endroid.nl/qr-code/Life%20is%20too%20short%20to%20be%20generating%20QR%20codes.png)

### Encoding
You can pick one of these values for encoding:

`ISO-8859-1`, `ISO-8859-2`, `ISO-8859-3`, `ISO-8859-4`, `ISO-8859-5`, `ISO-8859-6`, `ISO-8859-7`, `ISO-8859-8`, `ISO-8859-9`, `ISO-8859-10`, `ISO-8859-11`, `ISO-8859-12`, `ISO-8859-13`, `ISO-8859-14`, `ISO-8859-15`, `ISO-8859-16`, `Shift_JIS`, `windows-1250`, `windows-1251`, `windows-1252`, `windows-1256`, `UTF-16BE`, `UTF-8`, `US-ASCII`, `GBK` `EUC-KR`

If you use a barcode scanner you can have some troubles while reading the generated QR codes. Depending on the encoding you chose you will have an extra amount of data corresponding to the ECI block. Some barcode scanner are not programmed to interpret this block of information. For exemple the ECI block for `UTF-8` is `000026` so the above exemple will produce : `\000026Life is too short to be generating QR codes`. To ensure a maximum compatibility you can use the `ISO-8859-1` encoding that is the default encoding used by barcode scanners.

## Readability

The readability of a QR code is primarily determined by the size, the input
length, the error correction level and any possible logo over the image so you
can tweak these parameters if you are looking for optimal results. You can also
check $qrCode->getRoundBlockSize() value to see if block dimensions are rounded
so that the image is more sharp and readable. Please note that rounding block
size can result in additional padding to compensate for the rounding difference.

## Built-in validation reader

You can enable the built-in validation reader (disabled by default) by calling
setValidateResult(true). This validation reader does not guarantee that the QR
code will be readable by all readers but it helps you provide a minimum level
of quality.

Take note that the validator can consume quite amount of additional resources.

## Symfony integration

The [endroid/qr-code-bundle](https://github.com/endroid/qr-code-bundle)
integrates the QR code library in Symfony for an even better experience.

* Configure your defaults (like image size, default writer etc.)
* Generate QR codes quickly from anywhere via the factory service
* Generate QR codes directly by typing an URL like /qr-code/\<text>.png?size=300
* Generate QR codes or URLs directly from Twig using dedicated functions
 
Read the [bundle documentation](https://github.com/endroid/qr-code-bundle)
for more information.

## Versioning

Version numbers follow the MAJOR.MINOR.PATCH scheme. Backwards compatibility
breaking changes will be kept to a minimum but be aware that these can occur.
Lock your dependencies for production and test your code when upgrading.

## License

This bundle is under the MIT license. For the full copyright and license
information please view the LICENSE file that was distributed with this source code.
