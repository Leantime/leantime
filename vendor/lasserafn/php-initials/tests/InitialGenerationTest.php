<?php

use PHPUnit\Framework\TestCase;

class InitialGenerationTest extends TestCase
{
    public function testInitialsAreGeneratedFromFullname()
    {
        $avatar = new \LasseRafn\Initials\Initials();

        // Two names

        $avatar->name('John Doe');

        $this->assertEquals('JD', $avatar->getInitials());

        // Single name

        $avatar->name('John');

        $this->assertEquals('JO', $avatar->getInitials());

        // Initials

        $avatar->name('MA');

        $this->assertEquals('MA', $avatar->getInitials());

        // Three names

        $avatar->name('John Doe Bergerson');

        $this->assertEquals('JB', $avatar->getInitials());

        // Other name

        $avatar->name('Gustav Årgonson');

        $this->assertEquals('GÅ', $avatar->getInitials());

        $avatar->name('Chanel Butterman');

        $this->assertNotEquals('AB', $avatar->getInitials());
        $this->assertEquals('CB', $avatar->getInitials());

        $avatar->name('Gustav Årgonson');

        $this->assertEquals('GA', $avatar->getUrlfriendlyInitials());

        $avatar->length(3)->name('Morten Cæster');

        $this->assertEquals('MCA', $avatar->getUrlfriendlyInitials());
        $this->assertEquals(3, strlen($avatar->getUrlfriendlyInitials()));

        $avatar->length(3)->name('Jens Ølsted');

        $this->assertEquals('JOL', $avatar->getUrlfriendlyInitials());
        $this->assertEquals(3, strlen($avatar->getUrlfriendlyInitials()));

        $avatar->length(2)->name('Jens Ølsted');

        $this->assertEquals('JO', $avatar->getUrlfriendlyInitials());
        $this->assertEquals(2, strlen($avatar->getUrlfriendlyInitials()));

        $avatar->name('Dr. Dre');

        $this->assertEquals('DD', $avatar->getUrlfriendlyInitials());
        $this->assertEquals(2, strlen($avatar->getUrlfriendlyInitials()));
    }

    public function testCanGetInitialsWithDifferentLengthWhenUsingPredefinedInitials() {
	    $avatar = new \LasseRafn\Initials\Initials();

	    $avatar->name('AMA')->length(3);

	    $this->assertEquals('AMA', $avatar->generate());
	    $this->assertEquals(3, strlen($avatar->generate()));
    }

    public function testLimitingWorks() {
	    $avatar = new \LasseRafn\Initials\Initials();

	    $avatar->name('Amanda Rochnick Lorentz');

	    $this->assertEquals('AL', $avatar->generate());
	    $this->assertEquals(2, strlen($avatar->generate()));
    }
}
