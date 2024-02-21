<?php

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use PHPUnit\Framework\TestCase;

class InitialGenerationTest extends TestCase
{
    /** @test */
    public function initials_are_generated_from_full_name()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John Doe');

        $this->assertEquals('JD', $avatar->getInitials());
    }

    /** @test */
    public function initials_are_generated_from_single_name()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John');

        $this->assertEquals('JO', $avatar->getInitials());
    }

    /** @test */
    public function initials_are_generated_from_initals()
    {
        $avatar = new InitialAvatar();

        $avatar->name('MA');

        $this->assertEquals('MA', $avatar->getInitials());
    }

    /** @test */
    public function initials_are_generated_from_three_names()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John Doe Bergerson');

        $this->assertEquals('JB', $avatar->getInitials());
    }

    /** @test */
    public function initials_are_generated_with_dialect_specific_letters()
    {
        $avatar = new InitialAvatar();

        $avatar->name('Gustav Årgonson');

        $this->assertEquals('GÅ', $avatar->getInitials());
    }

    /** @test */
    public function initials_are_generated_from_name()
    {
        $avatar = new InitialAvatar();

        $avatar->name('Chanel Butterman');

        $this->assertNotEquals('AB', $avatar->getInitials());
        $this->assertEquals('CB', $avatar->getInitials());
    }
}
