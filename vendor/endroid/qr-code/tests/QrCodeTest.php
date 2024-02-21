<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode\Tests;

use Endroid\QrCode\Exception\GenerateImageException;
use Endroid\QrCode\Factory\QrCodeFactory;
use Endroid\QrCode\QrCode;
use PHPUnit\Framework\TestCase;
use Zxing\QrReader;

class QrCodeTest extends TestCase
{
    /**
     * @dataProvider stringProvider
     * @testdox QR code created with text $text is readable
     */
    public function testReadable(string $text): void
    {
        $qrCode = new QrCode();
        $qrCode->setSize(300);
        $qrCode->setText($text);
        $pngData = $qrCode->writeString();
        $this->assertTrue(is_string($pngData));
        $reader = new QrReader($pngData, QrReader::SOURCE_TYPE_BLOB);
        $this->assertEquals($text, $reader->text());
    }

    public function stringProvider(): array
    {
        return [
            ['Tiny'],
            ['This one has spaces'],
            ['d2llMS9uU01BVmlvalM2YU9BUFBPTTdQMmJabHpqdndt'],
            ['http://this.is.an/url?with=query&string=attached'],
            ['11111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111111'],
            ['{"i":"serialized.data","v":1,"t":1,"d":"4AEPc9XuIQ0OjsZoSRWp9DRWlN6UyDvuMlyOYy8XjOw="}'],
            ['Spëci&al ch@ract3rs'],
            ['有限公司'],
        ];
    }

    /**
     * @dataProvider writerNameProvider
     * @testdox Writer set by name $writerName results in the correct data type
     */
    public function testWriteQrCodeByWriterName(string $writerName, ?string $fileContent): void
    {
        $qrCode = new QrCode('QR Code');
        $qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
        $qrCode->setLogoWidth(100);

        $qrCode->setWriterByName($writerName);
        $data = $qrCode->writeString();
        $this->assertTrue(is_string($data));

        if (null !== $fileContent) {
            $uriData = $qrCode->writeDataUri();
            $this->assertTrue(0 === strpos($uriData, $fileContent));
        }
    }

    public function writerNameProvider(): array
    {
        return [
            ['binary', null],
            ['debug', null],
            ['eps', null],
            ['png', 'data:image/png;base64'],
            ['svg', 'data:image/svg+xml;base64'],
        ];
    }

    /**
     * @dataProvider extensionsProvider
     * @testdox Writer set by extension $extension results in the correct data type
     */
    public function testWriteQrCodeByWriterExtension(string $extension, ?string $fileContent): void
    {
        $qrCode = new QrCode('QR Code');
        $qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
        $qrCode->setLogoWidth(100);

        $qrCode->setWriterByExtension($extension);
        $data = $qrCode->writeString();
        $this->assertTrue(is_string($data));

        if (null !== $fileContent) {
            $uriData = $qrCode->writeDataUri();
            $this->assertTrue(0 === strpos($uriData, $fileContent));
        }
    }

    public function extensionsProvider(): array
    {
        return [
            ['bin', null],
            ['txt', null],
            ['eps', null],
            ['png', 'data:image/png;base64'],
            ['svg', 'data:image/svg+xml;base64'],
        ];
    }

    /**
     * @testdox Factory creates a valid QR code
     */
    public function testFactory(): void
    {
        $qrCodeFactory = new QrCodeFactory();
        $qrCode = $qrCodeFactory->create('QR Code', [
            'writer' => 'png',
            'size' => 300,
            'margin' => 10,
            'round_block_size_mode' => 'shrink',
        ]);

        $pngData = $qrCode->writeString();
        $this->assertTrue(is_string($pngData));
        $reader = new QrReader($pngData, QrReader::SOURCE_TYPE_BLOB);
        $this->assertEquals('QR Code', $reader->text());
    }

    /**
     * @testdox Size and margin are handled correctly
     */
    public function testSetSize(): void
    {
        $size = 400;
        $margin = 10;

        $qrCode = new QrCode('QR Code');
        $qrCode->setSize($size);
        $qrCode->setMargin($margin);

        $pngData = $qrCode->writeString();
        $image = imagecreatefromstring($pngData);

        $this->assertTrue(imagesx($image) === $size + 2 * $margin);
        $this->assertTrue(imagesy($image) === $size + 2 * $margin);
    }

    /**
     * @testdox Size and margin are handled correctly with rounded blocks
     * @dataProvider roundedSizeProvider
     */
    public function testSetSizeRounded($size, $margin, $round, $mode, $expectedSize): void
    {
        $qrCode = new QrCode('QR Code contents with some length to have some data');
        $qrCode->setRoundBlockSize($round, $mode);
        $qrCode->setSize($size);
        $qrCode->setMargin($margin);

        $pngData = $qrCode->writeString();
        $image = imagecreatefromstring($pngData);

        $this->assertTrue(imagesx($image) === $expectedSize);
        $this->assertTrue(imagesy($image) === $expectedSize);
    }

    public function roundedSizeProvider()
    {
        return [
            [
                'size' => 400,
                'margin' => 0,
                'round' => true,
                'mode' => QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE,
                'expectedSize' => 406,
            ],
            [
                'size' => 400,
                'margin' => 5,
                'round' => true,
                'mode' => QrCode::ROUND_BLOCK_SIZE_MODE_ENLARGE,
                'expectedSize' => 416,
            ],
            [
                'size' => 400,
                'margin' => 0,
                'round' => true,
                'mode' => QrCode::ROUND_BLOCK_SIZE_MODE_MARGIN,
                'expectedSize' => 400,
            ],
            [
                'size' => 400,
                'margin' => 5,
                'round' => true,
                'mode' => QrCode::ROUND_BLOCK_SIZE_MODE_MARGIN,
                'expectedSize' => 410,
            ],
            [
                'size' => 400,
                'margin' => 0,
                'round' => true,
                'mode' => QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK,
                'expectedSize' => 377,
            ],
            [
                'size' => 400,
                'margin' => 5,
                'round' => true,
                'mode' => QrCode::ROUND_BLOCK_SIZE_MODE_SHRINK,
                'expectedSize' => 387,
            ],
        ];
    }

    /**
     * @testdox Label can be added and QR code is still readable
     */
    public function testSetLabel(): void
    {
        $qrCode = new QrCode('QR Code');
        $qrCode->setSize(300);
        $qrCode->setLabel('Scan the code', 15);

        $pngData = $qrCode->writeString();
        $this->assertTrue(is_string($pngData));
        $reader = new QrReader($pngData, QrReader::SOURCE_TYPE_BLOB);
        $this->assertEquals('QR Code', $reader->text());
    }

    /**
     * @testdox Logo can be added and QR code is still readable
     */
    public function testSetLogo(): void
    {
        $qrCode = new QrCode('QR Code');
        $qrCode->setSize(500);
        $qrCode->setLogoPath(__DIR__.'/../assets/images/symfony.png');
        $qrCode->setLogoWidth(100);
        $qrCode->setValidateResult(true);

        $pngData = $qrCode->writeString();
        $this->assertTrue(is_string($pngData));
    }

    /**
     * @testdox Resulting QR code can be written to file
     */
    public function testWriteFile(): void
    {
        $filename = __DIR__.'/output/qr-code.png';

        $qrCode = new QrCode('QR Code');
        $qrCode->writeFile($filename);

        $image = imagecreatefromstring(file_get_contents($filename));

        $this->assertTrue(false !== $image);

        imagedestroy($image);
    }

    /**
     * @testdox QR code data can be retrieved
     */
    public function testData(): void
    {
        $qrCode = new QrCode('QR Code');

        $data = $qrCode->getData();

        $this->assertArrayHasKey('block_count', $data);
        $this->assertArrayHasKey('block_size', $data);
        $this->assertArrayHasKey('inner_width', $data);
        $this->assertArrayHasKey('inner_height', $data);
        $this->assertArrayHasKey('outer_width', $data);
        $this->assertArrayHasKey('outer_height', $data);
        $this->assertArrayHasKey('margin_left', $data);
        $this->assertArrayHasKey('margin_right', $data);
    }

    /**
     * @testdox Invalid image data results in appropriate exception
     */
    public function testNonImageData(): void
    {
        $qrCode = new QrCode('QR Code');
        $qrCode->setLogoPath(__DIR__.'/QrCodeTest.php');
        $qrCode->setLogoSize(200, 200);
        $qrCode->setWriterByExtension('svg');

        $this->expectException(GenerateImageException::class);
        $qrCode->writeString();
    }
}
