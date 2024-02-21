<?php

namespace Khanamiryan\QrCodeTests;

use PHPUnit\Framework\TestCase;
use Zxing\QrReader;

class QrReaderTest extends TestCase
{
	public function testText1()
	{
		$image = __DIR__ . "/qrcodes/hello_world.png";

		$qrcode = new QrReader($image);
		$this->assertSame("Hello world!", $qrcode->text());
	}

	public function testNoText()
	{
		$image = __DIR__ . "/qrcodes/empty.png";
		$qrcode = new QrReader($image);
		$this->assertSame(false, $qrcode->text());
	}
}
