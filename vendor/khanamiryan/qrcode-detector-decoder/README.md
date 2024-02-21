# QR code decoder / reader for PHP
This is a PHP library to detect and decode QR-codes.<br />This is first and only QR code reader that works without extensions.<br />
Ported from [ZXing library](https://github.com/zxing/zxing)


## Installation
The recommended method of installing this library is via [Composer](https://getcomposer.org/).

Run the following command from your project root:

```bash
$ composer require khanamiryan/qrcode-detector-decoder
```


## Usage 
```php
require __DIR__ . "/vendor/autoload.php";
use Zxing\QrReader;
$qrcode = new QrReader('path/to_image');
$text = $qrcode->text(); //return decoded text from QR Code
```

## Requirements 
* PHP >= 5.6
* GD Library


## Contributing

You can help the project by adding features, cleaning the code, adding composer and other.

 
1. Fork it
2. Create your feature branch: `git checkout -b my-new-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin my-new-feature`
5. Submit a pull request
