<?php

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use PHPUnit\Framework\TestCase;

class AutoColorUtilsTest extends TestCase
{
    /** @test */
    public function can_create_all_colors()
    {
        $avatar = new InitialAvatar();

        $avatar->name('A')->autoColor();
        $this->assertEquals('#f0a742', $avatar->getBackgroundColor());
        $this->assertEquals('#000000', $avatar->getColor());

        $avatar->name('B')->autoColor();
        $this->assertEquals('#42caf0', $avatar->getBackgroundColor());
        $this->assertEquals('#000000', $avatar->getColor());

        $avatar->name('C')->autoColor();
        $this->assertEquals('#42f085', $avatar->getBackgroundColor());
        $this->assertEquals('#000000', $avatar->getColor());

        $avatar->name('D')->autoColor();
        $this->assertEquals('#f04293', $avatar->getBackgroundColor());
        $this->assertEquals('#FFFFFF', $avatar->getColor());
    }
}
