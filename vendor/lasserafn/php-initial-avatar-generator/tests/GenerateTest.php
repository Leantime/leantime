<?php

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use PHPUnit\Framework\TestCase;

class GenerateTest extends TestCase
{
    /** @test */
    public function CanGenerateInitialsWithoutNameParameter()
    {
        $avatar = new InitialAvatar();

        $avatar->generate('Lasse Rafn');

        $this->assertEquals('LR', $avatar->getInitials());
    }

    /** @test */
    public function returns_image_object()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->generate();

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }

    /** @test */
    public function returns_image_object_with_emoji()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->generate('😅');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }

    /** @test */
    public function returns_image_object_with_japanese_letters()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->font(__DIR__.'/fonts/NotoSans-Regular.otf')->generate('こんにちは');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }

    /** @test */
    public function returns_image_object_with_default_gd_wont()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->font(2)->gd()->generate('LR');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_use_imagick_driver()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->imagick()->generate('LR');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_use_gd_driver()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->gd()->generate('LR');

        $this->assertEquals('Intervention\Image\Image', get_class($image));
        $this->assertTrue($image->stream()->isReadable());
    }

    /** @test */
    public function can_make_rounded_images()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->rounded()->generate();

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }

    /** @test */
    public function can_make_a_smooth_rounded_image()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->rounded()->smooth()->generate();

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }

    /** @test */
    public function stream_is_readable()
    {
        $avatar = new InitialAvatar();

        $this->assertTrue($avatar->generate()->stream()->isReadable());
    }

    /** @test */
    public function can_use_local_font()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->font(__DIR__.'/fonts/NotoSans-Regular.ttf')->generate();

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }

    /** @test */
    public function has_a_font_fallback()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->font('no-font')->generate();

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }

    /** @test */
    public function can_handle_fonts_without_slash_first()
    {
        $avatar = new InitialAvatar();

        $image = $avatar->font('fonts/NotoSans-Regular.ttf')->generate();

        $this->assertEquals('Intervention\Image\Image', get_class($image));
    }
}
