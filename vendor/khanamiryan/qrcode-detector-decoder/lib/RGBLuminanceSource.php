<?php
/*
* Copyright 2009 ZXing authors
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*      http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

namespace Zxing;

/**
 * This class is used to help decode images from files which arrive as RGB data from
 * an ARGB pixel array. It does not support rotation.
 *
 * @author dswitkin@google.com (Daniel Switkin)
 * @author Betaminos
 */
final class RGBLuminanceSource extends LuminanceSource
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
	/**
  * @var mixed|null
  */
 private $pixels;


	public function __construct(
		$pixels,
		$dataWidth,
		$dataHeight,
		$left = null,
		$top = null,
		$width = null,
		$height = null
	) {
		if (!$left && !$top && !$width && !$height) {
			$this->RGBLuminanceSource_($pixels, $dataWidth, $dataHeight);

			return;
		}
		parent::__construct($width, $height);
		if ($left + $width > $dataWidth || $top + $height > $dataHeight) {
			throw new \InvalidArgumentException("Crop rectangle does not fit within image data.");
		}
		$this->luminances = $pixels;
		$this->dataWidth = $dataWidth;
		$this->dataHeight = $dataHeight;
		$this->left = $left;
		$this->top = $top;
	}

	public function RGBLuminanceSource_($width, $height, $pixels): void
	{
		parent::__construct($width, $height);

		$this->dataWidth = $width;
		$this->dataHeight = $height;
		$this->left = 0;
		$this->top = 0;
		$this->pixels = $pixels;


		// In order to measure pure decoding speed, we convert the entire image to a greyscale array
		// up front, which is the same as the Y channel of the YUVLuminanceSource in the real app.
		$this->luminances = [];
		//$this->luminances = $this->grayScaleToBitmap($this->grayscale());

		foreach ($pixels as $key => $pixel) {
			$r = $pixel['red'];
			$g = $pixel['green'];
			$b = $pixel['blue'];

			/* if (($pixel & 0xFF000000) == 0) {
				 $pixel = 0xFFFFFFFF; // = white
			 }

			 // .229R + 0.587G + 0.114B (YUV/YIQ for PAL and NTSC)

			 $this->luminances[$key] =
				 (306 * (($pixel >> 16) & 0xFF) +
					 601 * (($pixel >> 8) & 0xFF) +
					 117 * ($pixel & 0xFF) +
					 0x200) >> 10;

			*/
			//$r = ($pixel >> 16) & 0xff;
			//$g = ($pixel >> 8) & 0xff;
			//$b = $pixel & 0xff;
			if ($r == $g && $g == $b) {
				// Image is already greyscale, so pick any channel.

				$this->luminances[$key] = $r;//(($r + 128) % 256) - 128;
			} else {
				// Calculate luminance cheaply, favoring green.
				$this->luminances[$key] = ($r + 2 * $g + $b) / 4;//(((($r + 2 * $g + $b) / 4) + 128) % 256) - 128;
			}
		}

		/*

		for ($y = 0; $y < $height; $y++) {
			$offset = $y * $width;
			for ($x = 0; $x < $width; $x++) {
				$pixel = $pixels[$offset + $x];
				$r = ($pixel >> 16) & 0xff;
				$g = ($pixel >> 8) & 0xff;
				$b = $pixel & 0xff;
				if ($r == $g && $g == $b) {
// Image is already greyscale, so pick any channel.

					$this->luminances[(int)($offset + $x)] = (($r+128) % 256) - 128;
				} else {
// Calculate luminance cheaply, favoring green.
					$this->luminances[(int)($offset + $x)] =  (((($r + 2 * $g + $b) / 4)+128)%256) - 128;
				}



			}
		*/
		//}
		//   $this->luminances = $this->grayScaleToBitmap($this->luminances);
	}

	public function grayscale()
	{
		$width = $this->dataWidth;
		$height = $this->dataHeight;

		$ret = fill_array(0, $width * $height, 0);
		for ($y = 0; $y < $height; $y++) {
			for ($x = 0; $x < $width; $x++) {
				$gray = $this->getPixel($x, $y, $width, $height);

				$ret[$x + $y * $width] = $gray;
			}
		}

		return $ret;
	}

	public function getPixel($x, $y, $width, $height)
	{
		$image = $this->pixels;
		if ($width < $x) {
			die('error');
		}
		if ($height < $y) {
			die('error');
		}
		$point = ($x) + ($y * $width);

		$r = $image[$point]['red'];//($image[$point] >> 16) & 0xff;
		$g = $image[$point]['green'];//($image[$point] >> 8) & 0xff;
		$b = $image[$point]['blue'];//$image[$point] & 0xff;

		$p = (int)(($r * 33 + $g * 34 + $b * 33) / 100);


		return $p;
	}

	public function grayScaleToBitmap($grayScale)
	{
		$middle = $this->getMiddleBrightnessPerArea($grayScale);
		$sqrtNumArea = is_countable($middle) ? count($middle) : 0;
		$areaWidth = floor($this->dataWidth / $sqrtNumArea);
		$areaHeight = floor($this->dataHeight / $sqrtNumArea);
		$bitmap = fill_array(0, $this->dataWidth * $this->dataHeight, 0);

		for ($ay = 0; $ay < $sqrtNumArea; $ay++) {
			for ($ax = 0; $ax < $sqrtNumArea; $ax++) {
				for ($dy = 0; $dy < $areaHeight; $dy++) {
					for ($dx = 0; $dx < $areaWidth; $dx++) {
						$bitmap[(int)($areaWidth * $ax + $dx + ($areaHeight * $ay + $dy) * $this->dataWidth)] = ($grayScale[(int)($areaWidth * $ax + $dx + ($areaHeight * $ay + $dy) * $this->dataWidth)] < $middle[$ax][$ay]) ? 0 : 255;
					}
				}
			}
		}

		return $bitmap;
	}

	public function getMiddleBrightnessPerArea($image)
	{
		$numSqrtArea = 4;
		//obtain middle brightness((min + max) / 2) per area
		$areaWidth = floor($this->dataWidth / $numSqrtArea);
		$areaHeight = floor($this->dataHeight / $numSqrtArea);
		$minmax = fill_array(0, $numSqrtArea, 0);
		for ($i = 0; $i < $numSqrtArea; $i++) {
			$minmax[$i] = fill_array(0, $numSqrtArea, 0);
			for ($i2 = 0; $i2 < $numSqrtArea; $i2++) {
				$minmax[$i][$i2] = [0, 0];
			}
		}
		for ($ay = 0; $ay < $numSqrtArea; $ay++) {
			for ($ax = 0; $ax < $numSqrtArea; $ax++) {
				$minmax[$ax][$ay][0] = 0xFF;
				for ($dy = 0; $dy < $areaHeight; $dy++) {
					for ($dx = 0; $dx < $areaWidth; $dx++) {
						$target = $image[(int)($areaWidth * $ax + $dx + ($areaHeight * $ay + $dy) * $this->dataWidth)];
						if ($target < $minmax[$ax][$ay][0]) {
							$minmax[$ax][$ay][0] = $target;
						}
						if ($target > $minmax[$ax][$ay][1]) {
							$minmax[$ax][$ay][1] = $target;
						}
					}
				}
				//minmax[ax][ay][0] = (minmax[ax][ay][0] + minmax[ax][ay][1]) / 2;
			}
		}
		$middle = [];
		for ($i3 = 0; $i3 < $numSqrtArea; $i3++) {
			$middle[$i3] = [];
		}
		for ($ay = 0; $ay < $numSqrtArea; $ay++) {
			for ($ax = 0; $ax < $numSqrtArea; $ax++) {
				$middle[$ax][$ay] = floor(($minmax[$ax][$ay][0] + $minmax[$ax][$ay][1]) / 2);
				//Console.out.print(middle[ax][ay] + ",");
			}
			//Console.out.println("");
		}

		//Console.out.println("");

		return $middle;
	}

	//@Override
	public function getRow($y, $row = null)
	{
		if ($y < 0 || $y >= $this->getHeight()) {
			throw new \InvalidArgumentException("Requested row is outside the image: " + \Y);
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
	public function isCropSupported()
	{
		return true;
	}

	//@Override
	public function crop($left, $top, $width, $height): \Zxing\RGBLuminanceSource
	{
		return new RGBLuminanceSource(
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
