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
use Zxing\NotFoundException;

/**
 * This Binarizer implementation uses the old ZXing global histogram approach. It is suitable
 * for low-end mobile devices which don't have enough CPU or memory to use a local thresholding
 * algorithm. However, because it picks a global black point, it cannot handle difficult shadows
 * and gradients.
 *
 * Faster mobile devices and all desktop applications should probably use HybridBinarizer instead.
 *
 * @author dswitkin@google.com (Daniel Switkin)
 * @author Sean Owen
 */
class GlobalHistogramBinarizer extends Binarizer
{
	private static int $LUMINANCE_BITS = 5;
	private static int $LUMINANCE_SHIFT = 3;
	private static int $LUMINANCE_BUCKETS = 32;

	private static array $EMPTY = [];

	private array $luminances = [];
	private array $buckets = [];
	/**
  * @var mixed|\Zxing\LuminanceSource
  */
 private $source = [];

	public function __construct($source)
	{
		self::$LUMINANCE_SHIFT = 8 - self::$LUMINANCE_BITS;
		self::$LUMINANCE_BUCKETS = 1 << self::$LUMINANCE_BITS;

		parent::__construct($source);

		$this->luminances = self::$EMPTY;
		$this->buckets = fill_array(0, self::$LUMINANCE_BUCKETS, 0);
		$this->source = $source;
	}

	// Applies simple sharpening to the row data to improve performance of the 1D Readers.
	public function getBlackRow($y, $row = null)
	{
		$this->source = $this->getLuminanceSource();
		$width = $this->source->getWidth();
		if ($row == null || $row->getSize() < $width) {
			$row = new BitArray($width);
		} else {
			$row->clear();
		}

		$this->initArrays($width);
		$localLuminances = $this->source->getRow($y, $this->luminances);
		$localBuckets = $this->buckets;
		for ($x = 0; $x < $width; $x++) {
			$pixel = $localLuminances[$x] & 0xff;
			$localBuckets[$pixel >> self::$LUMINANCE_SHIFT]++;
		}
		$blackPoint = self::estimateBlackPoint($localBuckets);

		$left = $localLuminances[0] & 0xff;
		$center = $localLuminances[1] & 0xff;
		for ($x = 1; $x < $width - 1; $x++) {
			$right = $localLuminances[$x + 1] & 0xff;
			// A simple -1 4 -1 box filter with a weight of 2.
			$luminance = (($center * 4) - $left - $right) / 2;
			if ($luminance < $blackPoint) {
				$row->set($x);
			}
			$left = $center;
			$center = $right;
		}

		return $row;
	}

	// Does not sharpen the data, as this call is intended to only be used by 2D Readers.
	private function initArrays($luminanceSize): void
	{
		if (count($this->luminances) < $luminanceSize) {
			$this->luminances = [];
		}
		for ($x = 0; $x < self::$LUMINANCE_BUCKETS; $x++) {
			$this->buckets[$x] = 0;
		}
	}

	private static function estimateBlackPoint($buckets)
	{
		// Find the tallest peak in the histogram.
		$numBuckets = is_countable($buckets) ? count($buckets) : 0;
		$maxBucketCount = 0;
		$firstPeak = 0;
		$firstPeakSize = 0;
		for ($x = 0; $x < $numBuckets; $x++) {
			if ($buckets[$x] > $firstPeakSize) {
				$firstPeak = $x;
				$firstPeakSize = $buckets[$x];
			}
			if ($buckets[$x] > $maxBucketCount) {
				$maxBucketCount = $buckets[$x];
			}
		}

		// Find the second-tallest peak which is somewhat far from the tallest peak.
		$secondPeak = 0;
		$secondPeakScore = 0;
		for ($x = 0; $x < $numBuckets; $x++) {
			$distanceToBiggest = $x - $firstPeak;
			// Encourage more distant second peaks by multiplying by square of distance.
			$score = $buckets[$x] * $distanceToBiggest * $distanceToBiggest;
			if ($score > $secondPeakScore) {
				$secondPeak = $x;
				$secondPeakScore = $score;
			}
		}

		// Make sure firstPeak corresponds to the black peak.
		if ($firstPeak > $secondPeak) {
			$temp = $firstPeak;
			$firstPeak = $secondPeak;
			$secondPeak = $temp;
		}

		// If there is too little contrast in the image to pick a meaningful black point, throw rather
		// than waste time trying to decode the image, and risk false positives.
		if ($secondPeak - $firstPeak <= $numBuckets / 16) {
			throw NotFoundException::getNotFoundInstance();
		}

		// Find a valley between them that is low and closer to the white peak.
		$bestValley = $secondPeak - 1;
		$bestValleyScore = -1;
		for ($x = $secondPeak - 1; $x > $firstPeak; $x--) {
			$fromFirst = $x - $firstPeak;
			$score = $fromFirst * $fromFirst * ($secondPeak - $x) * ($maxBucketCount - $buckets[$x]);
			if ($score > $bestValleyScore) {
				$bestValley = $x;
				$bestValleyScore = $score;
			}
		}

		return $bestValley << self::$LUMINANCE_SHIFT;
	}

	public function getBlackMatrix()
	{
		$source = $this->getLuminanceSource();
		$width = $source->getWidth();
		$height = $source->getHeight();
		$matrix = new BitMatrix($width, $height);

		// Quickly calculates the histogram by sampling four rows from the image. This proved to be
		// more robust on the blackbox tests than sampling a diagonal as we used to do.
		$this->initArrays($width);
		$localBuckets = $this->buckets;
		for ($y = 1; $y < 5; $y++) {
			$row = (int)($height * $y / 5);
			$localLuminances = $source->getRow($row, $this->luminances);
			$right = (int)(($width * 4) / 5);
			for ($x = (int)($width / 5); $x < $right; $x++) {
				$pixel = ($localLuminances[(int)($x)] & 0xff);
				$localBuckets[($pixel >> self::$LUMINANCE_SHIFT)]++;
			}
		}
		$blackPoint = self::estimateBlackPoint($localBuckets);

		// We delay reading the entire image luminance until the black point estimation succeeds.
		// Although we end up reading four rows twice, it is consistent with our motto of
		// "fail quickly" which is necessary for continuous scanning.
		$localLuminances = $source->getMatrix();
		for ($y = 0; $y < $height; $y++) {
			$offset = $y * $width;
			for ($x = 0; $x < $width; $x++) {
				$pixel = (int)($localLuminances[$offset + $x] & 0xff);
				if ($pixel < $blackPoint) {
					$matrix->set($x, $y);
				}
			}
		}

		return $matrix;
	}

	public function createBinarizer($source): \Zxing\Common\GlobalHistogramBinarizer
	{
		return new GlobalHistogramBinarizer($source);
	}
}
