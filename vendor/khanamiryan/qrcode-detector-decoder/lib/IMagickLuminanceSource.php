<?php

namespace Zxing;

/**
 * This class is used to help decode images from files which arrive as GD Resource
 * It does not support rotation.
 */
final class IMagickLuminanceSource extends LuminanceSource
{
	public $luminances;
	private $dataWidth;
	private $dataHeight;
	/**
  * @var mixed|int
  */
 private $left;
	/**
  * @var mixed|int
  */
 private $top;
	private ?\Imagick $image = null;

	public function __construct(
		\Imagick $image,
		$dataWidth,
		$dataHeight,
		$left = null,
		$top = null,
		$width = null,
		$height = null
	) {
		if (!$left && !$top && !$width && !$height) {
			$this->_IMagickLuminanceSource($image, $dataWidth, $dataHeight);

			return;
		}
		parent::__construct($width, $height);
		if ($left + $width > $dataWidth || $top + $height > $dataHeight) {
			throw new \InvalidArgumentException("Crop rectangle does not fit within image data.");
		}
		$this->luminances = $image;
		$this->dataWidth = $dataWidth;
		$this->dataHeight = $dataHeight;
		$this->left = $left;
		$this->top = $top;
	}

	public function _IMagickLuminanceSource(\Imagick $image, $width, $height): void
	{
		parent::__construct($width, $height);

		$this->dataWidth = $width;
		$this->dataHeight = $height;
		$this->left = 0;
		$this->top = 0;
		$this->image = $image;


		// In order to measure pure decoding speed, we convert the entire image to a greyscale array
		// up front, which is the same as the Y channel of the YUVLuminanceSource in the real app.
		$this->luminances = [];

		$image->setImageColorspace(\Imagick::COLORSPACE_GRAY);
		// $image->newPseudoImage(0, 0, "magick:rose");
		$pixels = $image->exportImagePixels(1, 1, $width, $height, "RGB", \Imagick::PIXEL_CHAR);

		$array = [];
		$rgb = [];

		$countPixels = count($pixels);
		for ($i = 0; $i < $countPixels; $i += 3) {
			$r = $pixels[$i] & 0xff;
			$g = $pixels[$i + 1] & 0xff;
			$b = $pixels[$i + 2] & 0xff;
			if ($r == $g && $g == $b) {
				// Image is already greyscale, so pick any channel.

				$this->luminances[] = $r;//(($r + 128) % 256) - 128;
			} else {
				// Calculate luminance cheaply, favoring green.
				$this->luminances[] = ($r + 2 * $g + $b) / 4;//(((($r + 2 * $g + $b) / 4) + 128) % 256) - 128;
			}
		}
	}

	//@Override
	public function getRow($y, $row = null)
	{
		if ($y < 0 || $y >= $this->getHeight()) {
			throw new \InvalidArgumentException('Requested row is outside the image: ' . $y);
		}
		$width = $this->getWidth();
		if ($row == null || (is_countable($row) ? count($row) : 0) < $width) {
			$row = [];
		}
		$offset = ($y + $this->top) * $this->dataWidth + $this->left;
		$row = arraycopy($this->luminances, $offset, $row, 0, $width);

		return $row;
	}

	//@Override
	public function getMatrix()
	{
		$width = $this->getWidth();
		$height = $this->getHeight();

		// If the caller asks for the entire underlying image, save the copy and give them the
		// original data. The docs specifically warn that result.length must be ignored.
		if ($width == $this->dataWidth && $height == $this->dataHeight) {
			return $this->luminances;
		}

		$area = $width * $height;
		$matrix = [];
		$inputOffset = $this->top * $this->dataWidth + $this->left;

		// If the width matches the full width of the underlying data, perform a single copy.
		if ($width == $this->dataWidth) {
			$matrix = arraycopy($this->luminances, $inputOffset, $matrix, 0, $area);

			return $matrix;
		}

		// Otherwise copy one cropped row at a time.
		$rgb = $this->luminances;
		for ($y = 0; $y < $height; $y++) {
			$outputOffset = $y * $width;
			$matrix = arraycopy($rgb, $inputOffset, $matrix, $outputOffset, $width);
			$inputOffset += $this->dataWidth;
		}

		return $matrix;
	}

	//@Override
	public function isCropSupported(): bool 
	{
		return true;
	}

	//@Override
	public function crop($left, $top, $width, $height)
	{
		return $this->luminances->cropImage($width, $height, $left, $top);

		return new GDLuminanceSource(
			$this->luminances,
			$this->dataWidth,
			$this->dataHeight,
			$this->left + $left,
			$this->top + $top,
			$width,
			$height
		);
	}
}
