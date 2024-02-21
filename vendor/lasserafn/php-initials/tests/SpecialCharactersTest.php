<?php

use PHPUnit\Framework\TestCase;

class SpecialCharactersTest extends TestCase
{
	public function testDoesNotIncludeSpecialCharactersByDefault() {
		// without new feature
		$this->assertEquals( 'J(', ( new \LasseRafn\Initials\Initials )->name( 'John Doe (Anderson)' )->getInitials() );

		// With new feature
		$this->assertEquals( 'JA', ( new \LasseRafn\Initials\Initials )->allowSpecialCharacters( false )->name( 'John Doe (Anderson)' )->getInitials() );
		$this->assertEquals( 'JD', ( new \LasseRafn\Initials\Initials )->allowSpecialCharacters( false )->name( 'John (Doe)' )->getInitials() );

		$this->assertEquals( 'JD', ( new \LasseRafn\Initials\Initials )->allowSpecialCharacters( false )->name( 'John (!Doe$ !--' )->getInitials() );
		$this->assertEquals( 'JG', ( new \LasseRafn\Initials\Initials )->allowSpecialCharacters( false )->name( 'John Mc-Guire' )->getInitials() );

		$this->assertEquals( 'JMG', ( new \LasseRafn\Initials\Initials )->allowSpecialCharacters( false )->name( 'John Mc-Guire' )->length( 3 )->getInitials() );
	}
}
