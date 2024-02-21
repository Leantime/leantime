<?php
/*
 * Copyright 2007 ZXing authors
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

namespace Zxing\Qrcode\Detector;

use Zxing\NotFoundException;

/**
 * <p>This class attempts to find alignment patterns in a QR Code. Alignment patterns look like finder
 * patterns but are smaller and appear at regular intervals throughout the image.</p>
 *
 * <p>At the moment this only looks for the bottom-right alignment pattern.</p>
 *
 * <p>This is mostly a simplified copy of {@link FinderPatternFinder}. It is copied,
 * pasted and stripped down here for maximum performance but does unfortunately duplicate
 * some code.</p>
 *
 * <p>This class is thread-safe but not reentrant. Each thread must allocate its own object.</p>
 *
 * @author Sean Owen
 */
final class AlignmentPatternFinder
{
	private array $possibleCenters = [];
	private array $crossCheckStateCount = [];

	/**
	 * <p>Creates a finder that will look in a portion of the whole image.</p>
	 *
	 * @param \Imagick image      $image to search
	 * @param int left     $startX column from which to start searching
	 * @param int top     $startY row from which to start searching
	 * @param float width      $width of region to search
	 * @param float height     $height of region to search
	 * @param float estimated $moduleSize module size so far
	 */
	public function __construct(private $image, private $startX, private $startY, private $width, private $height, private $moduleSize, private $resultPointCallback)
 {
 }

	/**
	 * <p>This method attempts to find the bottom-right alignment pattern in the image. It is a bit messy since
	 * it's pretty performance-critical and so is written to be fast foremost.</p>
	 *
	 * @return {@link AlignmentPattern} if found
	 * @throws NotFoundException if not found
	 */
	public function find()
	{
		$startX = $this->startX;
		$height = $this->height;
		$maxJ = $startX + $this->width;
		$middleI = $this->startY + ($height / 2);
		// We are looking for black/white/black modules in 1:1:1 ratio;
		// this tracks the number of black/white/black modules seen so far
		$stateCount = [];
		for ($iGen = 0; $iGen < $height; $iGen++) {
			// Search from middle outwards
			$i = $middleI + (($iGen & 0x01) == 0 ? ($iGen + 1) / 2 : -(($iGen + 1) / 2));
			$i = (int)($i);
			$stateCount[0] = 0;
			$stateCount[1] = 0;
			$stateCount[2] = 0;
			$j = $startX;
			// Burn off leading white pixels before anything else; if we start in the middle of
			// a white run, it doesn't make sense to count its length, since we don't know if the
			// white run continued to the left of the start point
			while ($j < $maxJ && !$this->image->get($j, $i)) {
				$j++;
			}
			$currentState = 0;
			while ($j < $maxJ) {
				if ($this->image->get($j, $i)) {
					// Black pixel
					if ($currentState == 1) { // Counting black pixels
						$stateCount[$currentState]++;
					} else { // Counting white pixels
						if ($currentState == 2) { // A winner?
							if ($this->foundPatternCross($stateCount)) { // Yes
								$confirmed = $this->handlePossibleCenter($stateCount, $i, $j);
								if ($confirmed != null) {
									return $confirmed;
								}
							}
							$stateCount[0] = $stateCount[2];
							$stateCount[1] = 1;
							$stateCount[2] = 0;
							$currentState = 1;
						} else {
							$stateCount[++$currentState]++;
						}
					}
				} else { // White pixel
					if ($currentState == 1) { // Counting black pixels
						$currentState++;
					}
					$stateCount[$currentState]++;
				}
				$j++;
			}
			if ($this->foundPatternCross($stateCount)) {
				$confirmed = $this->handlePossibleCenter($stateCount, $i, $maxJ);
				if ($confirmed != null) {
					return $confirmed;
				}
			}
		}

		// Hmm, nothing we saw was observed and confirmed twice. If we had
		// any guess at all, return it.
		if (count($this->possibleCenters)) {
			return $this->possibleCenters[0];
		}

		throw  NotFoundException::getNotFoundInstance();
	}

	/**
	 * @param count $stateCount of black/white/black pixels just read
	 *
	 * @return true iff the proportions of the counts is close enough to the 1/1/1 ratios
	 *         used by alignment patterns to be considered a match
	 */
	private function foundPatternCross($stateCount)
	{
		$moduleSize = $this->moduleSize;
		$maxVariance = $moduleSize / 2.0;
		for ($i = 0; $i < 3; $i++) {
			if (abs($moduleSize - $stateCount[$i]) >= $maxVariance) {
				return false;
			}
		}

		return true;
	}

	/**
	 * <p>This is called when a horizontal scan finds a possible alignment pattern. It will
	 * cross check with a vertical scan, and if successful, will see if this pattern had been
	 * found on a previous horizontal scan. If so, we consider it confirmed and conclude we have
	 * found the alignment pattern.</p>
	 *
	 * @param reading $stateCount state module counts from horizontal scan
	 * @param row          $i where alignment pattern may be found
	 * @param end          $j of possible alignment pattern in row
	 *
	 * @return {@link AlignmentPattern} if we have found the same pattern twice, or null if not
	 */
	private function handlePossibleCenter($stateCount, $i, $j)
	{
		$stateCountTotal = $stateCount[0] + $stateCount[1] + $stateCount[2];
		$centerJ = self::centerFromEnd($stateCount, $j);
		$centerI = $this->crossCheckVertical($i, (int)$centerJ, 2 * $stateCount[1], $stateCountTotal);
		if (!is_nan($centerI)) {
			$estimatedModuleSize = (float)($stateCount[0] + $stateCount[1] + $stateCount[2]) / 3.0;
			foreach ($this->possibleCenters as $center) {
				// Look for about the same center and module size:
				if ($center->aboutEquals($estimatedModuleSize, $centerI, $centerJ)) {
					return $center->combineEstimate($centerI, $centerJ, $estimatedModuleSize);
				}
			}
			// Hadn't found this before; save it
			$point = new AlignmentPattern($centerJ, $centerI, $estimatedModuleSize);
			$this->possibleCenters[] = $point;
			if ($this->resultPointCallback != null) {
				$this->resultPointCallback->foundPossibleResultPoint($point);
			}
		}

		return null;
	}

	/**
	 * Given a count of black/white/black pixels just seen and an end position,
	 * figures the location of the center of this black/white/black run.
	 */
	private static function centerFromEnd($stateCount, $end)
	{
		return (float)($end - $stateCount[2]) - $stateCount[1] / 2.0;
	}

	/**
	 * <p>After a horizontal scan finds a potential alignment pattern, this method
	 * "cross-checks" by scanning down vertically through the center of the possible
	 * alignment pattern to see if the same proportion is detected.</p>
	 *
	 * @param int row   $startI where an alignment pattern was detected
	 * @param float center  $centerJ of the section that appears to cross an alignment pattern
	 * @param int maximum $maxCount reasonable number of modules that should be
	 *                 observed in any reading state, based on the results of the horizontal scan
	 *
	 * @return float vertical center of alignment pattern, or {@link Float#NaN} if not found
	 */
	private function crossCheckVertical(
		$startI,
		$centerJ,
		$maxCount,
		$originalStateCountTotal
	)
	{
		$image = $this->image;

		$maxI = $image->getHeight();
		$stateCount = $this->crossCheckStateCount;
		$stateCount[0] = 0;
		$stateCount[1] = 0;
		$stateCount[2] = 0;

		// Start counting up from center
		$i = $startI;
		while ($i >= 0 && $image->get($centerJ, $i) && $stateCount[1] <= $maxCount) {
			$stateCount[1]++;
			$i--;
		}
		// If already too many modules in this state or ran off the edge:
		if ($i < 0 || $stateCount[1] > $maxCount) {
			return NAN;
		}
		while ($i >= 0 && !$image->get($centerJ, $i) && $stateCount[0] <= $maxCount) {
			$stateCount[0]++;
			$i--;
		}
		if ($stateCount[0] > $maxCount) {
			return NAN;
		}

		// Now also count down from center
		$i = $startI + 1;
		while ($i < $maxI && $image->get($centerJ, $i) && $stateCount[1] <= $maxCount) {
			$stateCount[1]++;
			$i++;
		}
		if ($i == $maxI || $stateCount[1] > $maxCount) {
			return NAN;
		}
		while ($i < $maxI && !$image->get($centerJ, $i) && $stateCount[2] <= $maxCount) {
			$stateCount[2]++;
			$i++;
		}
		if ($stateCount[2] > $maxCount) {
			return NAN;
		}

		$stateCountTotal = $stateCount[0] + $stateCount[1] + $stateCount[2];
		if (5 * abs($stateCountTotal - $originalStateCountTotal) >= 2 * $originalStateCountTotal) {
			return NAN;
		}

		return $this->foundPatternCross($stateCount) ? self::centerFromEnd($stateCount, $i) : NAN;
	}
}
