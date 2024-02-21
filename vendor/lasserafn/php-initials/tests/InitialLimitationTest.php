<?php

use PHPUnit\Framework\TestCase;

class InitialLimitationTest extends TestCase
{
    public function testCanLimitInitials()
    {
        $avatar = new \LasseRafn\Initials\Initials();

        // One letter

        $avatar->name('John Doe')->length(1);

        $this->assertEquals('J', $avatar->getInitials());
        $this->assertEquals(1, strlen($avatar->getInitials()));

        // Two letters
        $avatar->name('John Doe')->length(2);

        $this->assertEquals('JD', $avatar->getInitials());
        $this->assertEquals(2, strlen($avatar->getInitials()));

        // Three letters
        $avatar->name('John Doe Johnson')->length(3);

        $this->assertEquals('JDJ', $avatar->getInitials());
        $this->assertEquals(3, strlen($avatar->getInitials()));

        // Three letters with only two names
        $avatar->name('John Doe')->length(3);

        $this->assertEquals('JDO', $avatar->getInitials());
        $this->assertEquals(3, strlen($avatar->getInitials()));

        // Four letters with only two names
        $avatar->name('John Doe')->length(4);

        $this->assertEquals('JDOE', $avatar->getInitials());
        $this->assertEquals(4, strlen($avatar->getInitials()));

        // Five letters with only one name of 4 letters
        // This is not possible, of cause, so it will end in 4 letters
        $avatar->name('John')->length(5);

        $this->assertEquals('JOHN', $avatar->getInitials());
        $this->assertEquals(4, strlen($avatar->getInitials()));

        // Five letters with only one name of 5 letters
        $avatar->name('Lasse')->length(5);

        $this->assertEquals('LASSE', $avatar->getInitials());
        $this->assertEquals(5, strlen($avatar->getInitials()));

        // One letter from a one letter initial
        $avatar->name('L')->length(1);

        $this->assertEquals('L', $avatar->getInitials());
        $this->assertEquals(1, strlen($avatar->getInitials()));
    }
}
