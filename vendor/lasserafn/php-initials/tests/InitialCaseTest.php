<?php

use PHPUnit\Framework\TestCase;

class InitialCaseTest extends TestCase
{
    public function testCanPreventUppercase()
    {
        $avatar = new \LasseRafn\Initials\Initials();

        $avatar->name('lRa')->keepCase()->length(3);

        $this->assertEquals('lRa', $avatar->getInitials());
        $this->assertEquals(3, strlen($avatar->getInitials()));
    }
}
