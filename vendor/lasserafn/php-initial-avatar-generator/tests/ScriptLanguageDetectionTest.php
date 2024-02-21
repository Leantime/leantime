<?php

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use PHPUnit\Framework\TestCase;

class ScriptLanguageDetectionTest extends TestCase
{
    /** @test */
    public function can_detect_and_use_script_Arabic()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('الحزمة');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Armenian()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('բենգիմžē');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Bengali()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('ǰǰô জ');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Georgian()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('გამარჯობა');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Hebrew()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('ה ו ז ח ט');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Mongolian()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('ᠪᠣᠯᠠᠢ᠃');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Thai()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('สวัสดีชาวโลกและยินดีต้อนรับแพ็กเกจนี้');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Tibetan()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('ཀཁཆཇའ');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_detect_and_use_script_Uncommon()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->autoFont()->generate('ψψ');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }
}
