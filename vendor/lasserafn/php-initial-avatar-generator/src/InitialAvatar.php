<?php

namespace LasseRafn\InitialAvatarGenerator;

use Intervention\Image\AbstractFont;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use LasseRafn\InitialAvatarGenerator\Translator\Base;
use LasseRafn\InitialAvatarGenerator\Translator\En;
use LasseRafn\InitialAvatarGenerator\Translator\ZhCN;
use LasseRafn\Initials\Initials;
use LasseRafn\StringScript;
use SVG\Nodes\Shapes\SVGCircle;
use SVG\Nodes\Shapes\SVGRect;
use SVG\Nodes\Structures\SVGFont;
use SVG\Nodes\Texts\SVGText;
use SVG\SVG;

class InitialAvatar
{
    /** @var ImageManager */
    protected $image;

    /** @var Initials */
    protected $initials_generator;

    protected $driver = 'gd'; // imagick or gd
    protected $fontSize = 0.5;
    protected $name = 'John Doe';
    protected $width = 48;
    protected $height = 48;
    protected $bgColor = '#f0e9e9';
    protected $fontColor = '#8b5d5d';
    protected $rounded = false;
    protected $smooth = false;
    protected $autofont = false;
    protected $keepCase = false;
    protected $allowSpecialCharacters = true;
    protected $fontFile = '/fonts/OpenSans-Regular.ttf';
    protected $fontName = 'OpenSans, sans-serif';
    protected $generated_initials = 'JD';
    protected $preferBold = false;

    /**
     * Language eg.en zh-CN.
     *
     * @var string
     */
    protected $language = 'en';

    /**
     * Role translator.
     *
     * @var Base
     */
    protected $translator;

    /**
     * Language related to translator.
     *
     * @var array
     */
    protected $translatorMap = [
        'en'    => En::class,
        'zh-CN' => ZhCN::class,
    ];

    public function __construct()
    {
        $this->setupImageManager();
        $this->initials_generator = new Initials();
    }

    /**
     * Create a ImageManager instance.
     */
    protected function setupImageManager()
    {
        $this->image = new ImageManager(['driver' => $this->getDriver()]);
    }

    /**
     * Set the name used for generating initials.
     *
     * @param string $nameOrInitials
     *
     * @return $this
     */
    public function name($nameOrInitials)
    {
        $nameOrInitials = $this->translate($nameOrInitials);
        $this->name = $nameOrInitials;
        $this->initials_generator->name($nameOrInitials);

        return $this;
    }

    /**
     * Transforms a unicode string to the proper format.
     *
     * @param string $char the code to be converted (e.g., f007 would mean the "user" symbol)
     *
     * @return $this
     */
    public function glyph($char)
    {
        $uChar = json_decode(sprintf('"\u%s"', $char), false);
        $this->name($uChar);

        return $this;
    }

    /**
     * Set the length of the generated initials.
     *
     * @param int $length
     *
     * @return $this
     */
    public function length($length = 2)
    {
        $this->initials_generator->length($length);

        return $this;
    }

    /**
     * Set the avatar/image size in pixels.
     *
     * @param int $size
     *
     * @return $this
     */
    public function size($size)
    {
        $this->width = (int) $size;
        $this->height = (int) $size;

        return $this;
    }

    /**
     * Set the avatar/image height in pixels.
     *
     * @param int $height
     *
     * @return $this
     */
    public function height($height)
    {
        $this->height = (int) $height;

        return $this;
    }

    /**
     * Set the avatar/image width in pixels.
     *
     * @param int $width
     *
     * @return $this
     */
    public function width($width)
    {
        $this->width = (int) $width;

        return $this;
    }

    /**
     * Prefer bold fonts (if possible).
     *
     * @return $this
     */
    public function preferBold()
    {
        $this->preferBold = true;

        return $this;
    }

    /**
     * Prefer regular fonts (if possible).
     *
     * @return $this
     */
    public function preferRegular()
    {
        $this->preferBold = false;

        return $this;
    }

    /**
     * Set the background color.
     *
     * @param string $background
     *
     * @return $this
     */
    public function background($background)
    {
        $this->bgColor = (string) $background;

        return $this;
    }

    /**
     * Set the font color.
     *
     * @param string $color
     *
     * @return $this
     */
    public function color($color)
    {
        $this->fontColor = (string) $color;

        return $this;
    }

    /**
     * Automatically set a font and/or background color based on the supplied name.
     *
     * @param bool $foreground
     * @param bool $background
     *
     * @return $this
     */
    public function autoColor(bool $foreground = true, bool $background = true, int $saturation = 85, int $luminance = 60)
    {
        $hue = (crc32($this->name) % 360) / 360;
        $saturation /= 100;
        $luminance /= 100;

        $this->bgColor = $this->convertHSLtoRGB($hue, $saturation, $luminance);
        $this->fontColor = $this->getContrastColor($this->bgColor);

        return $this;
    }

    /**
     * Set the font file by path or int (1-5).
     *
     * @param string|int $font
     *
     * @return $this
     */
    public function font($font)
    {
        $this->fontFile = $font;

        return $this;
    }

    /**
     * Set the font name.
     *
     * Example: "Open Sans"
     *
     * @param string $name
     *
     * @return $this
     */
    public function fontName($name)
    {
        $this->fontName = $name;

        return $this;
    }

    /**
     * Use imagick as the driver.
     *
     * @return $this
     */
    public function imagick()
    {
        $this->driver = 'imagick';

        $this->setupImageManager();

        return $this;
    }

    /**
     * Use GD as the driver.
     *
     * @return $this
     */
    public function gd()
    {
        $this->driver = 'gd';

        $this->setupImageManager();

        return $this;
    }

    /**
     * Set if should make a round image or not.
     *
     * @param bool $rounded
     *
     * @return $this
     */
    public function rounded($rounded = true)
    {
        $this->rounded = (bool) $rounded;

        return $this;
    }

    /**
     * Set if should detect character script
     * and use a font that supports it.
     *
     * @param bool $autofont
     *
     * @return $this
     */
    public function autoFont($autofont = true)
    {
        $this->autofont = (bool) $autofont;

        return $this;
    }

    /**
     * Set if should make a rounding smoother with a resizing hack.
     *
     * @param bool $smooth
     *
     * @return $this
     */
    public function smooth($smooth = true)
    {
        $this->smooth = (bool) $smooth;

        return $this;
    }

    /**
     * Set if should skip uppercasing the name.
     *
     * @param bool $keepCase
     *
     * @return $this
     */
    public function keepCase($keepCase = true)
    {
        $this->keepCase = (bool) $keepCase;

        return $this;
    }

    /**
     * Set if should allow (or remove) special characters.
     *
     * @param bool $allowSpecialCharacters
     *
     * @return $this
     */
    public function allowSpecialCharacters($allowSpecialCharacters = true)
    {
        $this->allowSpecialCharacters = (bool) $allowSpecialCharacters;

        return $this;
    }

    /**
     * Set the font size in percentage
     * (0.1 = 10%).
     *
     * @param float $size
     *
     * @return $this
     */
    public function fontSize($size = 0.5)
    {
        $this->fontSize = (float) round($size, 2);

        return $this;
    }

    /**
     * Generate the image.
     *
     * @param null|string $name
     *
     * @return Image
     */
    public function generate($name = null)
    {
        if ($name !== null) {
            $this->name = $name;
            $this->generated_initials = $this->initials_generator->keepCase($this->getKeepCase())
                                                                 ->allowSpecialCharacters($this->getAllowSpecialCharacters())
                                                                 ->generate($name);
        }

        return $this->makeAvatar($this->image);
    }

    /**
     * Generate the image.
     *
     * @param null|string $name
     *
     * @return SVG
     */
    public function generateSvg($name = null)
    {
        if ($name !== null) {
            $this->name = $name;
            $this->generated_initials = $this->initials_generator->keepCase($this->getKeepCase())
                                                                 ->allowSpecialCharacters($this->getAllowSpecialCharacters())
                                                                 ->generate($name);
        }

        return $this->makeSvgAvatar();
    }

    /**
     * Will return the generated initials.
     *
     * @return string
     */
    public function getInitials()
    {
        return $this->initials_generator->keepCase($this->getKeepCase())
                                        ->allowSpecialCharacters($this->getAllowSpecialCharacters())
                                        ->name($this->name)
                                        ->getInitials();
    }

    /**
     * Will return the background color parameter.
     *
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->bgColor;
    }

    /**
     * Will return the set driver.
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Will return the font color parameter.
     *
     * @return string
     */
    public function getColor()
    {
        return $this->fontColor;
    }

    /**
     * Will return the font size parameter.
     *
     * @return float
     */
    public function getFontSize()
    {
        return $this->fontSize;
    }

    /**
     * Will return the font file parameter.
     *
     * @return string|int
     */
    public function getFontFile()
    {
        return $this->fontFile;
    }

    /**
     * Will return the font name parameter for SVGs.
     *
     * @return string
     */
    public function getFontName()
    {
        return $this->fontName;
    }

    /**
     * Will return the round parameter.
     *
     * @return bool
     */
    public function getRounded()
    {
        return $this->rounded;
    }

    /**
     * Will return the smooth parameter.
     *
     * @return bool
     */
    public function getSmooth()
    {
        return $this->smooth;
    }

    /**
     * @deprecated for getWidth and getHeight
     */
    public function getSize()
    {
        return $this->getWidth();
    }

    /**
     * Will return the width parameter.
     *
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Will return the height parameter.
     *
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Will return the keepCase parameter.
     *
     * @return bool
     */
    public function getKeepCase()
    {
        return $this->keepCase;
    }

    /**
     * Will return the allowSpecialCharacters parameter.
     *
     * @return bool
     */
    public function getAllowSpecialCharacters()
    {
        return $this->allowSpecialCharacters;
    }

    /**
     * Will return the autofont parameter.
     *
     * @return bool
     */
    public function getAutoFont()
    {
        return $this->autofont;
    }

    /**
     * Set language of name, pls use `language` before `name`, just like
     * ```php
     * $avatar->language('en')->name('Mr Green'); // Right
     * $avatar->name('Mr Green')->language('en'); // Wrong
     * ```.
     *
     * @param string $language
     *
     * @return $this
     */
    public function language($language)
    {
        $this->language = $language ?: 'en';

        return $this;
    }

    /**
     * Add new translators designed by user.
     *
     * @param array $translatorMap
     *                             ```php
     *                             $translatorMap = [
     *                             'fr' => 'foo\bar\Fr',
     *                             'zh-TW' => 'foo\bar\ZhTW'
     *                             ];
     *                             ```
     *
     * @return $this
     */
    public function addTranslators($translatorMap)
    {
        $this->translatorMap = array_merge($this->translatorMap, $translatorMap);

        return $this;
    }

    protected function translate($nameOrInitials)
    {
        return $this->getTranslator()->translate($nameOrInitials);
    }

    /**
     * Instance the translator by language.
     *
     * @return Base
     */
    protected function getTranslator()
    {
        if ($this->translator instanceof Base && $this->translator->getSourceLanguage() === $this->language) {
            return $this->translator;
        }

        $translatorClass = array_key_exists($this->language, $this->translatorMap) ? $this->translatorMap[$this->language] : 'LasseRafn\\InitialAvatarGenerator\\Translator\\En';

        return $this->translator = new $translatorClass();
    }

    /**
     * @param ImageManager $image
     *
     * @return Image
     */
    protected function makeAvatar($image)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $bgColor = $this->getBackgroundColor();
        $name = $this->getInitials();
        $fontFile = $this->findFontFile();
        $color = $this->getColor();
        $fontSize = $this->getFontSize();

        if ($this->getRounded() && $this->getSmooth()) {
            $width *= 5;
            $height *= 5;
        }

        $avatar = $image->canvas($width, $height, !$this->getRounded() ? $bgColor : null);

        if ($this->getRounded()) {
            $avatar = $avatar->circle($width - 2, $width / 2, $height / 2, function ($draw) use ($bgColor) {
                return $draw->background($bgColor);
            });
        }

        if ($this->getRounded() && $this->getSmooth()) {
            $width /= 5;
            $height /= 5;
            $avatar->resize($width, $height);
        }

        return $avatar->text($name, $width / 2, $height / 2, function (AbstractFont $font) use ($width, $color, $fontFile, $fontSize) {
            $font->file($fontFile);
            $font->size($width * $fontSize);
            $font->color($color);
            $font->align('center');
            $font->valign('center');
        });
    }

    /**
     * @return SVG
     */
    protected function makeSvgAvatar()
    {
        // Original document
        $image = new SVG($this->getWidth(), $this->getHeight());
        $document = $image->getDocument();

        // Background
        if ($this->getRounded()) {
            // Circle
            $background = new SVGCircle($this->getWidth() / 2, $this->getHeight() / 2, $this->getWidth() / 2);
        } else {
            // Rectangle
            $background = new SVGRect(0, 0, $this->getWidth(), $this->getHeight());
        }

        $background->setStyle('fill', $this->getBackgroundColor());
        $document->addChild($background);

        // Text
        $text = new SVGText($this->getInitials(), '50%', '50%');
        $text->setFont(new SVGFont($this->getFontName(), $this->findFontFile()));
        $text->setStyle('line-height', 1);
        $text->setAttribute('dy', '.1em');
        $text->setAttribute('fill', $this->getColor());
        $text->setAttribute('font-size', $this->getFontSize() * $this->getWidth());
        $text->setAttribute('text-anchor', 'middle');
        $text->setAttribute('dominant-baseline', 'middle');

        if ($this->preferBold) {
            $text->setStyle('font-weight', 600);
        }

        $document->addChild($text);

        return $image;
    }

    protected function findFontFile()
    {
        $fontFile = $this->getFontFile();

        if ($this->getAutoFont()) {
            $fontFile = $this->getFontByScript();
        }

        if (is_int($fontFile) && \in_array($fontFile, [1, 2, 3, 4, 5], false)) {
            return $fontFile;
        }

        $weightsToTry = ['Regular'];

        if ($this->preferBold) {
            $weightsToTry = ['Bold', 'Semibold', 'Regular'];
        }

        $originalFile = $fontFile;

        foreach ($weightsToTry as $weight) {
            $fontFile = preg_replace('/(\-(Bold|Semibold|Regular))/', "-{$weight}", $originalFile);

            if (file_exists($fontFile)) {
                return $fontFile;
            }

            if (file_exists(__DIR__.$fontFile)) {
                return __DIR__.$fontFile;
            }

            if (file_exists(__DIR__.'/'.$fontFile)) {
                return __DIR__.'/'.$fontFile;
            }
        }

        return 1;
    }

    protected function getFontByScript()
    {
        // Arabic
        if (StringScript::isArabic($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Arabic-Regular.ttf';
        }

        // Armenian
        if (StringScript::isArmenian($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Armenian-Regular.ttf';
        }

        // Bengali
        if (StringScript::isBengali($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Bengali-Regular.ttf';
        }

        // Georgian
        if (StringScript::isGeorgian($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Georgian-Regular.ttf';
        }

        // Hebrew
        if (StringScript::isHebrew($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Hebrew-Regular.ttf';
        }

        // Mongolian
        if (StringScript::isMongolian($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Mongolian-Regular.ttf';
        }

        // Thai
        if (StringScript::isThai($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Thai-Regular.ttf';
        }

        // Tibetan
        if (StringScript::isTibetan($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-Tibetan-Regular.ttf';
        }

        // Chinese & Japanese
        if (StringScript::isJapanese($this->getInitials()) || StringScript::isChinese($this->getInitials())) {
            return __DIR__.'/fonts/script/Noto-CJKJP-Regular.otf';
        }

        return $this->getFontFile();
    }

    /**
     * Convert HSL color value produced by autoColor() to RGB value expected by image driver.
     */
    protected function convertHSLtoRGB($h, $s, $l, $toHex = true)
    {
        assert((0 <= $h) && ($h <= 1));

        $red = $l;
        $green = $l;
        $blue = $l;

        $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
        if ($v > 0) {
            $m = $l + $l - $v;
            $sv = ($v - $m) / $v;
            $h *= 6.0;
            $sextant = floor($h);
            $fract = $h - $sextant;
            $vsf = $v * $sv * $fract;
            $mid1 = $m + $vsf;
            $mid2 = $v - $vsf;

            switch ($sextant) {
                case 0:
                    $red = $v;
                    $green = $mid1;
                    $blue = $m;
                    break;
                case 1:
                    $red = $mid2;
                    $green = $v;
                    $blue = $m;
                    break;
                case 2:
                    $red = $m;
                    $green = $v;
                    $blue = $mid1;
                    break;
                case 3:
                    $red = $m;
                    $green = $mid2;
                    $blue = $v;
                    break;
                case 4:
                    $red = $mid1;
                    $green = $m;
                    $blue = $v;
                    break;
                case 5:
                    $red = $v;
                    $green = $m;
                    $blue = $mid2;
                    break;
            }
        }

        $red = round($red * 255, 0);
        $green = round($green * 255, 0);
        $blue = round($blue * 255, 0);

        if ($toHex) {
            $red = ($red < 15) ? '0'.dechex($red) : dechex($red);
            $green = ($green < 15) ? '0'.dechex($green) : dechex($green);
            $blue = ($blue < 15) ? '0'.dechex($blue) : dechex($blue);

            return "#{$red}{$green}{$blue}";
        } else {
            return ['red' => $red, 'green' => $green, 'blue' => $blue];
        }
    }

    /**
     * Get contrasting foreground color for autoColor background.
     */
    protected function getContrastColor($hexColor)
    {
        // hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        // Black RGB
        $blackColor = '#000000';
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

        // Calc contrast ratio
        $L1 = 0.2126 * pow($R1 / 255, 2.2) +
            0.7152 * pow($G1 / 255, 2.2) +
            0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
            0.7152 * pow($G2BlackColor / 255, 2.2) +
            0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int) (($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int) (($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else {
            // if not, return white color.
            return '#FFFFFF';
        }
    }
}
