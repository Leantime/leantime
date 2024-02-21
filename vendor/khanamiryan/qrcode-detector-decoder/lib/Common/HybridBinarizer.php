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

namespace Zxing\Common;

use Zxing\Binarizer;

/**
 * This class implements a local thresholding algorithm, which while slower than the
 * GlobalHistogramBinarizer, is fairly efficient for what it does. It is designed for
 * high frequency images of barcodes with black data on white backgrounds. For this application,
 * it does a much better job than a global blackpoint with severe shadows and gradients.
 * However it tends to produce artifacts on lower frequency images and is therefore not
 * a good general purpose binarizer for uses outside ZXing.
 *
 * This class extends GlobalHistogramBinarizer, using the older histogram approach for 1D readers,
 * and the newer local approach for 2D readers. 1D decoding using a per-row histogram is already
 * inherently local, and only fails for horizontal gradients. We can revisit that problem later,
 * but for now it was not a win to use local blocks for 1D.
 *
 * This Binarizer is the default for the unit tests and the recommended class for library users.
 *
 * @author dswitkin@google.com (Daniel Switkin)
 */
final class HybridBinarizer extends GlobalHistogramBinarizer
{
	// This class uses 5x5 blocks to compute local luminance, where each block is 8x8 pixels.
	// So this is the smallest dimension in each axis we can accept.
	private static int $BLOCK_SIZE_POWER = 3;
	private static int $BLOCK_SIZE = 8; // ...0100...00
	private static int $BLOCK_SIZE_MASK = 7;   // ...0011...11
	private static int $MINIMUM_DIMENSION = 40;
	private static int $MIN_DYNAMIC_RANGE = 24;

	private ?\Zxing\Common\BitMatrix $matrix = null;

	public function __construct($source)
	{
		parent::__construct($source);
		self::$BLOCK_SIZE_POWER = 3;
		self::$BLOCK_SIZE = 1 << self::$BLOCK_SIZE_POWER; // ...0100...00
		self::$BLOCK_SIZE_MASK = self::$BLOCK_SIZE - 1;   // ...0011...11
		self::$MINIMUM_DIMENSION = self::$BLOCK_SIZE * 5;
		self::$MIN_DYNAMIC_RANGE = 24;
	}

	/**
	 * Calculates the final BitMatrix once for all requests. This could be called once from the
	 * constructor instead, but there are some advantages to doing it lazily, such as making
	 * profiling easier, and not doing heavy lifting when callers don't expect it.
	 */
	public function getBlackMatrix()
	{
		if ($this->matrix !== null) {
			return $this->matrix;
		}
		$source = $this->getLuminanceSource();
		$width = $source->getWidth();
		$height = $source->getHeight();
		if ($width >= self::$MINIMUM_DIMENSION && $height >= self::$MINIMUM_DIMENSION) {
			$luminances = $source->getMatrix();
			$subWidth = $width >> self::$BLOCK_SIZE_POWER;
			if (($width & self::$BLOCK_SIZE_MASK) != 0) {
				$subWidth++;
			}
			$subHeight = $height >> self::$BLOCK_SIZE_POWER;
			if (($height & self::$BLOCK_SIZE_MASK) != 0) {
				$subHeight++;
			}
			$blackPoints = self::calculateBlackPoints($luminances, $subWidth, $subHeight, $width, $height);

			$newMatrix = new BitMatrix($width, $height);
			self::calculateThresholdForBlock($luminances, $subWidth, $subHeight, $width, $height, $blackPoints, $newMatrix);
			$this->matrix = $newMatrix;
		} else {
			// If the image is too small, fall back to the global histogram approach.
			$this->matrix = parent::getBlackMatrix();
		}

		return $this->matrix;
	}

	/**
	 * Calculates a single black point for each block of pixels and saves it away.
	 * See the following thread for a discussion of this algorithm:
	 *  http://groups.google.com/group/zxing/browse_thread/thread/d06efa2c35a7ddc0
	 */
	private static function calculateBlackPoints(
		$luminances,
		$subWidth,
		$subHeight,
		$width,
		$height
	) {
		$blackPoints = fill_array(0, $subHeight, 0);
		foreach ($blackPoints as $key => $point) {
			$blackPoints[$key] = fill_array(0, $subWidth, 0);
		}
		for ($y = 0; $y < $subHeight; $y++) {
			$yoffset = ($y << self::$BLOCK_SIZE_POWER);
			$maxYOffset = $height - self::$BLOCK_SIZE;
			if ($yoffset > $maxYOffset) {
				$yoffset = $maxYOffset;
			}
			for ($x = 0; $x < $subWidth; $x++) {
				$xoffset = ($x << self::$BLOCK_SIZE_POWER);
				$maxXOffset = $width - self::$BLOCK_SIZE;
				if ($xoffset > $maxXOffset) {
					$xoffset = $maxXOffset;
				}
				$sum = 0;
				$min = 0xFF;
				$max = 0;
				for ($yy = 0, $offset = $yoffset * $width + $xoffset; $yy < self::$BLOCK_SIZE; $yy++, $offset += $width) {
					for ($xx = 0; $xx < self::$BLOCK_SIZE; $xx++) {
						$pixel = ((int)($luminances[(int)($offset + $xx)]) & 0xFF);
						$sum += $pixel;
						// still looking for good contrast
						if ($pixel < $min) {
							$min = $pixel;
						}
						if ($pixel > $max) {
							$max = $pixel;
						}
					}
					// short-circuit min/max tests once dynamic range is met
					if ($max - $min > self::$MIN_DYNAMIC_RANGE) {
						// finish the rest of the rows quickly
						for ($yy++, $offset += $width; $yy < self::$BLOCK_SIZE; $yy++, $offset += $width) {
							for ($xx = 0; $xx < self::$BLOCK_SIZE; $xx++) {
								$sum += ($luminances[$offset + $xx] & 0xFF);
							}
						}
					}
				}

				// The default estimate is the average of the values in the block.
				$average = ($sum >> (self::$BLOCK_SIZE_POWER * 2));
				if ($max - $min <= self::$MIN_DYNAMIC_RANGE) {
					// If variation within the block is low, assume this is a block with only light or only
					// dark pixels. In that case we do not want to use the average, as it would divide this
					// low contrast area into black and white pixels, essentially creating data out of noise.
					//
					// The default assumption is that the block is light/background. Since no estimate for
					// the level of dark pixels exists locally, use half the min for the block.
					$average = (int)($min / 2);

					if ($y > 0 && $x > 0) {
						// Correct the "white background" assumption for blocks that have neighbors by comparing
						// the pixels in this block to the previously calculated black points. This is based on
						// the fact that dark barcode symbology is always surrounded by some amount of light
						// background for which reasonable black point estimates were made. The bp estimated at
						// the boundaries is used for the interior.

						// The (min < bp) is arbitrary but works better than other heuristics that were tried.
						$averageNeighborBlackPoint =
							(int)(($blackPoints[$y - 1][$x] + (2 * $blackPoints[$y][$x - 1]) + $blackPoints[$y - 1][$x - 1]) / 4);
						if ($min < $averageNeighborBlackPoint) {
							$average = $averageNeighborBlackPoint;
						}
					}
				}
				$blackPoints[$y][$x] = (int)($average);
			}
		}

		return $blackPoints;
	}

	/**
	 * For each block in the image, calculate the average black point using a 5x5 grid
	 * of the blocks around it. Also handles the corner cases (fractional blocks are computed based
	 * on the last pixels in the row/column which are also used in the previous block).
	 */
	private static function calculateThresholdForBlock(
		$luminances,
		$subWidth,
		$subHeight,
		$width,
		$height,
		$blackPoints,
		$matrix
	): void {
		for ($y = 0; $y < $subHeight; $y++) {
			$yoffset = ($y << self::$BLOCK_SIZE_POWER);
			$maxYOffset = $height - self::$BLOCK_SIZE;
			if ($yoffset > $maxYOffset) {
				$yoffset = $maxYOffset;
			}
			for ($x = 0; $x < $subWidth; $x++) {
				$xoffset = ($x << self::$BLOCK_SIZE_POWER);
				$maxXOffset = $width - self::$BLOCK_SIZE;
				if ($xoffset > $maxXOffset) {
					$xoffset = $maxXOffset;
				}
				$left = self::cap($x, 2, $subWidth - 3);
				$top = self::cap($y, 2, $subHeight - 3);
				$sum = 0;
				for ($z = -2; $z <= 2; $z++) {
					$blackRow = $blackPoints[$top + $z];
					$sum += $blackRow[$left - 2] + $blackRow[$left - 1] + $blackRow[$left] + $blackRow[$left + 1] + $blackRow[$left + 2];
				}
				$average = (int)($sum / 25);

				self::thresholdBlock($luminances, $xoffset, $yoffset, $average, $width, $matrix);
			}
		}
	}

	private static function cap($value, $min, $max)
	{
		if ($value < $min) {
			return $min;
		} elseif ($value > $max) {
			return $max;
		} else {
			return $value;
		}
	}

	/**
	 * Applies a single threshold to a block of pixels.
	 */
	private static function thresholdBlock(
		$luminances,
		$xoffset,
		$yoffset,
		$threshold,
		$stride,
		$matrix
	): void {
		for ($y = 0, $offset = $yoffset * $stride + $xoffset; $y < self::$BLOCK_SIZE; $y++, $offset += $stride) {
			for ($x = 0; $x < self::$BLOCK_SIZE; $x++) {
				// Comparison needs to be <= so that black == 0 pixels are black even if the threshold is 0.
				if (($luminances[$offset + $x] & 0xFF) <= $threshold) {
					$matrix->set($xoffset + $x, $yoffset + $y);
				}
			}
		}
	}

	public function createBinarizer($source): \Zxing\Common\HybridBinarizer
	{
		return new HybridBinarizer($source);
	}
}
