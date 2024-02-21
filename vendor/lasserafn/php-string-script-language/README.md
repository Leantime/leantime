# Determine Language Script of Strings

Detect if a string contains different language scripts with a simple API. 
 
<p align="center"> 
<a href="https://travis-ci.org/LasseRafn/php-string-script-language"><img src="https://img.shields.io/travis/LasseRafn/php-string-script-language.svg?style=flat-square" alt="Build Status"></a>
<a href="https://coveralls.io/github/LasseRafn/php-string-script-language"><img src="https://img.shields.io/coveralls/LasseRafn/php-string-script-language.svg?style=flat-square" alt="Coverage"></a>
<a href="https://styleci.io/repos/105565993"><img src="https://styleci.io/repos/105565993/shield?branch=master" alt="StyleCI Status"></a>
<a href="https://packagist.org/packages/LasseRafn/php-string-script-language"><img src="https://img.shields.io/packagist/dt/LasseRafn/php-string-script-language.svg?style=flat-square" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/LasseRafn/php-string-script-language"><img src="https://img.shields.io/packagist/v/LasseRafn/php-string-script-language.svg?style=flat-square" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/LasseRafn/php-string-script-language"><img src="https://img.shields.io/packagist/l/LasseRafn/php-string-script-language.svg?style=flat-square" alt="License"></a>
</p>

## Installation

You just require using composer and you're good to go!
```bash
composer require lasserafn/php-string-script-language
```

## Usage

As with installation, usage is quite simple:

```php
use LasseRafn\StringScript;

StringScript::isThai('Hello world.'); // false
StringScript::isChinese('你好世界。'); // true
```

All methods return a boolean value.

## Supported scripts

* Common
* Arabic
* Armenian
* Bengali
* Bopomofo
* Braille
* Buhid
* Canadian Aboriginal
* Cherokee
* Cyrillic
* Devanagari
* Ethiopic
* Georgian
* Greek
* Gujarati
* Gurmukhi
* Han
* Hangul
* Hanunoo
* Hebrew
* Hiragana
* Inherited
* Kannada
* Katakana
* Khmer
* Lao
* Latin
* Limbu
* Malayalam
* Mongolian
* Myanmar
* Ogham
* Oriya
* Runic
* Sinhala
* Syriac
* Tagalog
* Tagbanwa
* TaiLe
* Tamil
* Telugu
* Thaana
* Thai
* Tibetan
* Yi
* Chinese (Helper for Han)
* Japanese (Helper for Hiragana or Katakana)

## All methods

```php
StringScript::isCommon($string);
```

```php
StringScript::isArabic($string);
```

```php
StringScript::isArmenian($string);
```

```php
StringScript::isBengali($string);
```

```php
StringScript::isBopomofo($string);
```

```php
StringScript::isBraille($string);
```

```php
StringScript::isBuhid($string);
```

```php
StringScript::isCanadian_Aboriginal($string);
```

```php
StringScript::isCherokee($string);
```

```php
StringScript::isCyrillic($string);
```

```php
StringScript::isDevanagari($string);
```

```php
StringScript::isEthiopic($string);
```

```php
StringScript::isGeorgian($string);
```

```php
StringScript::isGreek($string);
```

```php
StringScript::isGujarati($string);
```

```php
StringScript::isGurmukhi($string);
```

```php
StringScript::isHan($string);
```

```php
StringScript::isHangul($string);
```

```php
StringScript::isHanunoo($string);
```

```php
StringScript::isHebrew($string);
```

```php
StringScript::isHiragana($string);
```

```php
StringScript::isInherited($string);
```

```php
StringScript::isKannada($string);
```

```php
StringScript::isKatakana($string);
```

```php
StringScript::isKhmer($string);
```

```php
StringScript::isLao($string);
```

```php
StringScript::isLatin($string);
```

```php
StringScript::isLimbu($string);
```

```php
StringScript::isMalayalam($string);
```

```php
StringScript::isMongolian($string);
```

```php
StringScript::isMyanmar($string);
```

```php
StringScript::isOgham($string);
```

```php
StringScript::isOriya($string);
```

```php
StringScript::isRunic($string);
```

```php
StringScript::isSinhala($string);
```

```php
StringScript::isSyriac($string);
```

```php
StringScript::isTagalog($string);
```

```php
StringScript::isTagbanwa($string);
```

```php
StringScript::isTaiLe($string);
```

```php
StringScript::isTamil($string);
```

```php
StringScript::isTelugu($string);
```

```php
StringScript::isThaana($string);
```

```php
StringScript::isThai($string);
```

```php
StringScript::isTibetan($string);
```

```php
StringScript::isYi($string);
```

```php
StringScript::isChinese($string);
```

```php
StringScript::isJapanese($string);
```

## Requirements
* PHP 5.6, 7.0 or 7.1
