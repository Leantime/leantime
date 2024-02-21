<?php

use LasseRafn\StringScript;
use PHPUnit\Framework\TestCase;

class DetectLanguageTest extends TestCase
{
    /** @test */
    public function can_detect_thai()
    {
        $this->assertTrue(StringScript::isThai('สวัสดีชาวโลกและยินดีต้อนรับแพ็กเกจนี้'));
        $this->assertFalse(StringScript::isThai('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_chinese()
    {
        $this->assertTrue(StringScript::isChinese('你好世界，歡迎這個包。'));
        $this->assertTrue(StringScript::isChinese('你好世界，欢迎这个包。'));
        $this->assertFalse(StringScript::isChinese('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_han()
    {
        $this->assertTrue(StringScript::isHan('你好世界，歡迎這個包。'));
        $this->assertTrue(StringScript::isHan('你好世界，欢迎这个包。'));
        $this->assertFalse(StringScript::isHan('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_japanese()
    {
        $this->assertTrue(StringScript::isJapanese('こんにちは、このパッケージを歓迎します。'));
        $this->assertFalse(StringScript::isJapanese('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_common()
    {
        $this->assertTrue(StringScript::isCommon('Hello world, and welcome this package.'));
        $this->assertTrue(StringScript::isCommon('こんにちは、このパッケージを歓迎します'));
        $this->assertFalse(StringScript::isCommon('ψ'));
    }

    /** @test */
    public function can_detect_latin()
    {
        $this->assertTrue(StringScript::isLatin('Salve mundi, et receperint hac sarcina.'));
        $this->assertTrue(StringScript::isLatin('Hello world, and welcome this package.'));
        $this->assertFalse(StringScript::isLatin('こんにちは、このパッケージを歓迎します'));
    }

    /** @test */
    public function can_detect_arabic()
    {
        $this->assertTrue(StringScript::isArabic('مرحبا العالم، ونرحب بهذه الحزمة.'));
        $this->assertFalse(StringScript::isArabic('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_georgian()
    {
        $this->assertTrue(StringScript::isGeorgian('გამარჯობა მსოფლიოში და მივესალმები ამ პაკეტს.'));
        $this->assertFalse(StringScript::isGeorgian('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_canadian_aboriginal()
    {
        $this->assertTrue(StringScript::isCanadian_Aboriginal('ᓭ	ᓭ	ᓯ	ᓯ	ᓱ	ᓱ	ᓴ	ᓴ ᐯ	ᐯ	ᐱ	ᐱ	ᐳ	ᐳ	ᐸ	ᐸ'));
        $this->assertFalse(StringScript::isCanadian_Aboriginal('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_tibetan()
    {
        $this->assertTrue(StringScript::isTibetan('ཀཁཆཇའ'));
        $this->assertFalse(StringScript::isTibetan('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_hangul()
    {
        $this->assertTrue(StringScript::isHangul('ㄱ	ㅋ	ㄲㅅ		ㅆㅈ	ㅊ	ㅉㄷ	ㅌ	ㄸㅂ	ㅍ	ㅃ'));
        $this->assertFalse(StringScript::isHangul('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_braille()
    {
        $this->assertTrue(StringScript::isBraille('⠡⠣⠩⠹⠱⠫⠻⠳⠪⠺'));
        $this->assertFalse(StringScript::isBraille('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_bopomofo()
    {
        $this->assertTrue(StringScript::isBopomofo('瓶	ㄆㄧㄥˊ子	ㄗ˙or	ㄆㄧㄥˊ	ㄗ˙瓶	子'));
        $this->assertFalse(StringScript::isBopomofo('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_bengali()
    {
        $this->assertTrue(StringScript::isBengali('জ্জ ǰǰô জ্ঞ'));
        $this->assertFalse(StringScript::isBengali('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_buhid()
    {
        $this->assertTrue(StringScript::isBuhid('ᝃ	ᝄ	ᝅ	ᝆ	ᝇ	ᝈ	ᝉ	ᝊ	ᝋ	ᝌ	ᝍ	ᝎ	ᝏ	ᝐ	ᝑ'));
        $this->assertFalse(StringScript::isBuhid('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_devanagari()
    {
        $this->assertTrue(StringScript::isDevanagari('सदाऽऽत्मा'));
        $this->assertFalse(StringScript::isDevanagari('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_armenian()
    {
        $this->assertTrue(StringScript::isArmenian('բենգիմžē'));
        $this->assertFalse(StringScript::isArmenian('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_hebrew()
    {
        $this->assertTrue(StringScript::isHebrew('א ב ג ד ה ו ז ח ט י'));
        $this->assertFalse(StringScript::isHebrew('Hello world, and welcome this package.'));
    }

    /** @test */
    public function can_detect_mongolian()
    {
        $this->assertTrue(StringScript::isMongolian('ᠪᠣᠯᠠᠢ᠃'));
        $this->assertFalse(StringScript::isMongolian('Hello world, and welcome this package.'));
    }
}
