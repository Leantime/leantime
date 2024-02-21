<?php

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use PHPUnit\Framework\TestCase;

class LimitInitialLenghtTest extends TestCase
{
    /** @test */
    public function can_limit_length_to_one_letter()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John Doe')->length(1);

        $this->assertEquals('J', $avatar->getInitials());
        $this->assertEquals(1, strlen($avatar->getInitials()));
    }

    /** @test */
    public function can_limit_length_to_two_letters()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John Doe')->length(2);

        $this->assertEquals('JD', $avatar->getInitials());
        $this->assertEquals(2, strlen($avatar->getInitials()));
    }

    /** @test */
    public function can_limit_length_to_three_letters()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John Doe Johnson')->length(3);

        $this->assertEquals('JDJ', $avatar->getInitials());
        $this->assertEquals(3, strlen($avatar->getInitials()));
    }

    /** @test */
    public function can_limit_length_to_three_letters_from_two_names()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John Doe')->length(3);

        $this->assertEquals('JDO', $avatar->getInitials());
        $this->assertEquals(3, strlen($avatar->getInitials()));
    }

    /** @test */
    public function can_limit_length_to_four_letters_from_two_names()
    {
        $avatar = new InitialAvatar();

        $avatar->name('John Doe')->length(4);

        $this->assertEquals('JDOE', $avatar->getInitials());
        $this->assertEquals(4, strlen($avatar->getInitials()));
    }

    /** @test */
    public function cannot_limit_length_to_five_letters_with_only_one_name_of_4_letters()
    {
        // This is not possible, so it will end in 4 letters
        $avatar = new InitialAvatar();

        $avatar->name('John')->length(5);

        $this->assertEquals('JOHN', $avatar->getInitials());
        $this->assertEquals(4, strlen($avatar->getInitials()));
    }

    /** @test */
    public function can_limit_length_to_five_letters_with_one_name_of_5_letters()
    {
        $avatar = new InitialAvatar();

        $avatar->name('Lasse')->length(5);

        $this->assertEquals('LASSE', $avatar->getInitials());
        $this->assertEquals(5, strlen($avatar->getInitials()));
    }

    /** @test */
    public function can_limit_length_to_one_letter_from_one_letter_name()
    {
        $avatar = new InitialAvatar();

        $avatar->name('L')->length(1);

        $this->assertEquals('L', $avatar->getInitials());
        $this->assertEquals(1, strlen($avatar->getInitials()));
    }
}
