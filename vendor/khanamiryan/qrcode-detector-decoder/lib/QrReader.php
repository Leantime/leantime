<?php

namespace Zxing;

use Zxing\Common\HybridBinarizer;
use Zxing\Qrcode\QRCodeReader;

final class QrReader
{
	public const SOURCE_TYPE_FILE = 'file';
	public const SOURCE_TYPE_BLOB = 'blob';
	public const SOURCE_TYPE_RESOURCE = 'resource';

	private readonly \Zxing\BinaryBitmap $bitmap;
	private readonly \Zxing\Qrcode\QRCodeReader $reader;
	private \Zxing\Result|bool|null $result = null;

	public function __construct($imgSource, $sourceType = QrReader::SOURCE_TYPE_FILE, $useImagickIfAvailable = true)
	{
		if (!in_array($sourceType, [
			self::SOURCE_TYPE_FILE,
			self::SOURCE_TYPE_BLOB,
			self::SOURCE_TYPE_RESOURCE,
		], true)) {
			throw new \InvalidArgumentException('Invalid image source.');
		}
		$im = null;
		switch ($sourceType) {
			case QrReader::SOURCE_TYPE_FILE:
				if ($useImagickIfAvailable && extension_loaded('imagick')) {
					$im = new \Imagick();
					$im->readImage($imgSource);
				} else {
					$image = file_get_contents($imgSource);
					$im = imagecreatefromstring($image);
				}
				break;

			case QrReader::SOURCE_TYPE_BLOB:
				if ($useImagickIfAvailable && extension_loaded('imagick')) {
					$im = new \Imagick();
					$im->readImageBlob($imgSource);
				} else {
					$im = imagecreatefromstring($imgSource);
				}
				break;

			case QrReader::SOURCE_TYPE_RESOURCE:
				$im = $imgSource;
				if ($useImagickIfAvailable && extension_loaded('imagick')) {
					$useImagickIfAvailable = true;
				} else {
					$useImagickIfAvailable = false;
				}
				break;
		}
		if ($useImagickIfAvailable && extension_loaded('imagick')) {
			if (!$im instanceof \Imagick) {
				throw new \InvalidArgumentException('Invalid image source.');
			}
			$width = $im->getImageWidth();
			$height = $im->getImageHeight();
			$source = new IMagickLuminanceSource($im, $width, $height);
		} else {
			if (!$im instanceof \GdImage && !is_object($im)) {
				throw new \InvalidArgumentException('Invalid image source.');
			}
			$width = imagesx($im);
			$height = imagesy($im);
			$source = new GDLuminanceSource($im, $width, $height);
		}
		$histo = new HybridBinarizer($source);
		$this->bitmap = new BinaryBitmap($histo);
		$this->reader = new QRCodeReader();
	}

	public function decode($hints = null): void
	{
		try {
			$this->result = $this->reader->decode($this->bitmap, $hints);
		} catch (NotFoundException|FormatException|ChecksumException) {
			$this->result = false;
		}
	}

	public function text($hints = null)
	{
		$this->decode($hints);

		if ($this->result !== false && method_exists($this->result, 'toString')) {
			return $this->result->toString();
		}

		return $this->result;
	}

	public function getResult()
	{
		return $this->result;
	}
}
