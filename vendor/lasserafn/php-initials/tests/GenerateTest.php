<?php

use PHPUnit\Framework\TestCase;

class GenerateTest extends TestCase
{
    public function testCanGenerateInitialsWithoutNameParameter()
    {
        $avatar = new \LasseRafn\Initials\Initials();

        $avatar->generate('Lasse Rafn');

        $this->assertEquals('LR', $avatar->getInitials());

        // With emoji
        $avatar = new \LasseRafn\Initials\Initials();

        $avatar->generate('😅');

        $this->assertEquals('😅', $avatar->getInitials());

        // With Japanese letters
        $avatar = new \LasseRafn\Initials\Initials();

        $avatar->generate('こんにちは');

        $this->assertEquals('こん', $avatar->getInitials());
    }
}
