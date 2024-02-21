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

use Zxing\Common\BitMatrix;
use Zxing\NotFoundException;
use Zxing\ResultPoint;

/**
 * <p>This class attempts to find finder patterns in a QR Code. Finder patterns are the square
 * markers at three corners of a QR Code.</p>
 *
 * <p>This class is thread-safe but not reentrant. Each thread must allocate its own object.
 *
 * @author Sean Owen
 */
class FinderPatternFinder
{
	protected static int $MIN_SKIP = 3;
	protected static int $MAX_MODULES = 57; // 1 pixel/module times 3 modules/center
	private static int $CENTER_QUORUM = 2;
	private ?float $average = null;
	private array $possibleCenters = []; //private final List<FinderPattern> possibleCenters;
	private bool $hasSkipped = false;
	/**
  * @var mixed|int[]
  */
 private $crossCheckStateCount;

	/**
	 * <p>Creates a finder that will search the image for three finder patterns.</p>
	 *
	 * @param BitMatrix $image image to search
	 */
	public function __construct(private $image, private $resultPointCallback = null)
	{
		//new ArrayList<>();
		$this->crossCheckStateCount = fill_array(0, 5, 0);
	}

	final public function find($hints): \Zxing\Qrcode\Detector\FinderPatternInfo
	{/*final FinderPatternInfo find(Map<DecodeHintType,?> hints) throws NotFoundException {*/
		$tryHarder = $hints != null && $hints['TRY_HARDER'];
		$pureBarcode = $hints != null && $hints['PURE_BARCODE'];
		$maxI = $this->image->getHeight();
		$maxJ = $this->image->getWidth();
		// We are looking for black/white/black/white/black modules in
		// 1:1:3:1:1 ratio; this tracks the number of such modules seen so far

		// Let's assume that the maximum version QR Code we support takes up 1/4 the height of the
		// image, and then account for the center being 3 modules in size. This gives the smallest
		// number of pixels the center could be, so skip this often. When trying harder, look for all
		// QR versions regardless of how dense they are.
		$iSkip = (int)((3 * $maxI) / (4 * self::$MAX_MODULES));
		if ($iSkip < self::$MIN_SKIP || $tryHarder) {
			$iSkip = self::$MIN_SKIP;
		}

		$done = false;
		$stateCount = [];
		for ($i = $iSkip - 1; $i < $maxI && !$done; $i += $iSkip) {
			// Get a row of black/white values
			$stateCount[0] = 0;
			$stateCount[1] = 0;
			$stateCount[2] = 0;
			$stateCount[3] = 0;
			$stateCount[4] = 0;
			$currentState = 0;
			for ($j = 0; $j < $maxJ; $j++) {
				if ($this->image->get($j, $i)) {
					// Black pixel
					if (($currentState & 1) == 1) { // Counting white pixels
						$currentState++;
					}
					$stateCount[$currentState]++;
				} else { // White pixel
					if (($currentState & 1) == 0) { // Counting black pixels
						if ($currentState == 4) { // A winner?
							if (self::foundPatternCross($stateCount)) { // Yes
								$confirmed = $this->handlePossibleCenter($stateCount, $i, $j, $pureBarcode);
								if ($confirmed) {
									// Start examining every other line. Checking each line turned out to be too
									// expensive and didn't improve performance.
									$iSkip = 3;
									if ($this->hasSkipped) {
										$done = $this->haveMultiplyConfirmedCenters();
									} else {
										$rowSkip = $this->findRowSkip();
										if ($rowSkip > $stateCount[2]) {
											// Skip rows between row of lower confirmed center
											// and top of presumed third confirmed center
											// but back up a bit to get a full chance of detecting
											// it, entire width of center of finder pattern

											// Skip by rowSkip, but back off by $stateCount[2] (size of last center
											// of pattern we saw) to be conservative, and also back off by iSkip which
											// is about to be re-added
											$i += $rowSkip - $stateCount[2] - $iSkip;
											$j = $maxJ - 1;
										}
									}
								} else {
									$stateCount[0] = $stateCount[2];
									$stateCount[1] = $stateCount[3];
									$stateCount[2] = $stateCount[4];
									$stateCount[3] = 1;
									$stateCount[4] = 0;
									$currentState = 3;
									continue;
								}
								// Clear state to start looking again
								$currentState = 0;
								$stateCount[0] = 0;
								$stateCount[1] = 0;
								$stateCount[2] = 0;
								$stateCount[3] = 0;
								$stateCount[4] = 0;
							} else { // No, shift counts back by two
								$stateCount[0] = $stateCount[2];
								$stateCount[1] = $stateCount[3];
								$stateCount[2] = $stateCount[4];
								$stateCount[3] = 1;
								$stateCount[4] = 0;
								$currentState = 3;
							}
						} else {
							$stateCount[++$currentState]++;
						}
					} else { // Counting white pixels
						$stateCount[$currentState]++;
					}
				}
			}
			if (self::foundPatternCross($stateCount)) {
				$confirmed = $this->handlePossibleCenter($stateCount, $i, $maxJ, $pureBarcode);
				if ($confirmed) {
					$iSkip = $stateCount[0];
					if ($this->hasSkipped) {
						// Found a third one
						$done = $this->haveMultiplyConfirmedCenters();
					}
				}
			}
		}

		$patternInfo = $this->selectBestPatterns();
		$patternInfo = ResultPoint::orderBestPatterns($patternInfo);

		return new FinderPatternInfo($patternInfo);
	}

	/**
	 * @param $stateCount ; count of black/white/black/white/black pixels just read
	 *
	 * @return true iff the proportions of the counts is close enough to the 1/1/3/1/1 ratios
	 *         used by finder patterns to be considered a match
	 */
	protected static function foundPatternCross($stateCount)
	{
		$totalModuleSize = 0;
		for ($i = 0; $i < 5; $i++) {
			$count = $stateCount[$i];
			if ($count == 0) {
				return false;
			}
			$totalModuleSize += $count;
		}
		if ($totalModuleSize < 7) {
			return false;
		}
		$moduleSize = $totalModuleSize / 7.0;
		$maxVariance = $moduleSize / 2.0;

		// Allow less than 50% variance from 1-1-3-1-1 proportions
		return
			abs($moduleSize - $stateCount[0]) < $maxVariance &&
			abs($moduleSize - $stateCount[1]) < $maxVariance &&
			abs(3.0 * $moduleSize - $stateCount[2]) < 3 * $maxVariance &&
			abs($moduleSize - $stateCount[3]) < $maxVariance &&
			abs($moduleSize - $stateCount[4]) < $maxVariance;
	}

	/**
	 * <p>This is called when a horizontal scan finds a possible alignment pattern. It will
	 * cross check with a vertical scan, and if successful, will, ah, cross-cross-check
	 * with another horizontal scan. This is needed primarily to locate the real horizontal
	 * center of the pattern in cases of extreme skew.
	 * And then we cross-cross-cross check with another diagonal scan.</p>
	 *
	 * <p>If that succeeds the finder pattern location is added to a list that tracks
	 * the number of times each location has been nearly-matched as a finder pattern.
	 * Each additional find is more evidence that the location is in fact a finder
	 * pattern center
	 *
	 * @param reading $stateCount state module counts from horizontal scan
	 * @param row           $i where finder pattern may be found
	 * @param end           $j of possible finder pattern in row
	 * @param true $pureBarcode if in "pure barcode" mode
	 *
	 * @return true if a finder pattern candidate was found this time
	 */
	final protected function handlePossibleCenter($stateCount, $i, $j, $pureBarcode)
	{
		$stateCountTotal = $stateCount[0] + $stateCount[1] + $stateCount[2] + $stateCount[3] +
			$stateCount[4];
		$centerJ = self::centerFromEnd($stateCount, $j);
		$centerI = $this->crossCheckVertical($i, (int)($centerJ), $stateCount[2], $stateCountTotal);
		if (!is_nan($centerI)) {
			// Re-cross check
			$centerJ = $this->crossCheckHorizontal((int)($centerJ), (int)($centerI), $stateCount[2], $stateCountTotal);
			if (!is_nan($centerJ) &&
				(!$pureBarcode || $this->crossCheckDiagonal((int)($centerI), (int)($centerJ), $stateCount[2], $stateCountTotal))
			) {
				$estimatedModuleSize = (float)$stateCountTotal / 7.0;
				$found = false;
				for ($index = 0; $index < count($this->possibleCenters); $index++) {
					$center = $this->possibleCenters[$index];
					// Look for about the same center and module size:
					if ($center->aboutEquals($estimatedModuleSize, $centerI, $centerJ)) {
						$this->possibleCenters[$index] = $center->combineEstimate($centerI, $centerJ, $estimatedModuleSize);
						$found = true;
						break;
					}
				}
				if (!$found) {
					$point = new FinderPattern($centerJ, $centerI, $estimatedModuleSize);
					$this->possibleCenters[] = $point;
					if ($this->resultPointCallback != null) {
						$this->resultPointCallback->foundPossibleResultPoint($point);
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Given a count of black/white/black/white/black pixels just seen and an end position,
	 * figures the location of the center of this run.
	 */
	private static function centerFromEnd($stateCount, $end)
	{
		return (float)($end - $stateCount[4] - $stateCount[3]) - $stateCount[2] / 2.0;
	}

	/**
	 * <p>After a horizontal scan finds a potential finder pattern, this method
	 * "cross-checks" by scanning down vertically through the center of the possible
	 * finder pattern to see if the same proportion is detected.</p>
	 *
	 * @param $startI   ;  row where a finder pattern was detected
	 * @param $centerJ   ; center of the section that appears to cross a finder pattern
	 * @param $maxCount ; maximum reasonable number of modules that should be
	 *                  observed in any reading state, based on the results of the horizontal scan
	 *
	 * @return float vertical center of finder pattern, or {@link Float#NaN} if not found
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
		$stateCount = $this->getCrossCheckStateCount();

		// Start counting up from center
		$i = $startI;
		while ($i >= 0 && $image->get($centerJ, $i)) {
			$stateCount[2]++;
			$i--;
		}
		if ($i < 0) {
			return NAN;
		}
		while ($i >= 0 && !$image->get($centerJ, $i) && $stateCount[1] <= $maxCount) {
			$stateCount[1]++;
			$i--;
		}
		// If already too many modules in this state or ran off the edge:
		if ($i < 0 || $stateCount[1] > $maxCount) {
			return NAN;
		}
		while ($i >= 0 && $image->get($centerJ, $i) && $stateCount[0] <= $maxCount) {
			$stateCount[0]++;
			$i--;
		}
		if ($stateCount[0] > $maxCount) {
			return NAN;
		}

		// Now also count down from center
		$i = $startI + 1;
		while ($i < $maxI && $image->get($centerJ, $i)) {
			$stateCount[2]++;
			$i++;
		}
		if ($i == $maxI) {
			return NAN;
		}
		while ($i < $maxI && !$image->get($centerJ, $i) && $stateCount[3] < $maxCount) {
			$stateCount[3]++;
			$i++;
		}
		if ($i == $maxI || $stateCount[3] >= $maxCount) {
			return NAN;
		}
		while ($i < $maxI && $image->get($centerJ, $i) && $stateCount[4] < $maxCount) {
			$stateCount[4]++;
			$i++;
		}
		if ($stateCount[4] >= $maxCount) {
			return NAN;
		}

		// If we found a finder-pattern-like section, but its size is more than 40% different than
		// the original, assume it's a false positive
		$stateCountTotal = $stateCount[0] + $stateCount[1] + $stateCount[2] + $stateCount[3] +
			$stateCount[4];
		if (5 * abs($stateCountTotal - $originalStateCountTotal) >= 2 * $originalStateCountTotal) {
			return NAN;
		}

		return self::foundPatternCross($stateCount) ? self::centerFromEnd($stateCount, $i) : NAN;
	}

	private function getCrossCheckStateCount()
	{
		$this->crossCheckStateCount[0] = 0;
		$this->crossCheckStateCount[1] = 0;
		$this->crossCheckStateCount[2] = 0;
		$this->crossCheckStateCount[3] = 0;
		$this->crossCheckStateCount[4] = 0;

		return $this->crossCheckStateCount;
	}

	/**
	 * <p>Like {@link #crossCheckVertical(int, int, int, int)}, and in fact is basically identical,
	 * except it reads horizontally instead of vertically. This is used to cross-cross
	 * check a vertical cross check and locate the real center of the alignment pattern.</p>
	 */
	private function crossCheckHorizontal(
		$startJ,
		$centerI,
		$maxCount,
		$originalStateCountTotal
	)
	{
		$image = $this->image;

		$maxJ = $this->image->getWidth();
		$stateCount = $this->getCrossCheckStateCount();

		$j = $startJ;
		while ($j >= 0 && $image->get($j, $centerI)) {
			$stateCount[2]++;
			$j--;
		}
		if ($j < 0) {
			return NAN;
		}
		while ($j >= 0 && !$image->get($j, $centerI) && $stateCount[1] <= $maxCount) {
			$stateCount[1]++;
			$j--;
		}
		if ($j < 0 || $stateCount[1] > $maxCount) {
			return NAN;
		}
		while ($j >= 0 && $image->get($j, $centerI) && $stateCount[0] <= $maxCount) {
			$stateCount[0]++;
			$j--;
		}
		if ($stateCount[0] > $maxCount) {
			return NAN;
		}

		$j = $startJ + 1;
		while ($j < $maxJ && $image->get($j, $centerI)) {
			$stateCount[2]++;
			$j++;
		}
		if ($j == $maxJ) {
			return NAN;
		}
		while ($j < $maxJ && !$image->get($j, $centerI) && $stateCount[3] < $maxCount) {
			$stateCount[3]++;
			$j++;
		}
		if ($j == $maxJ || $stateCount[3] >= $maxCount) {
			return NAN;
		}
		while ($j < $maxJ && $this->image->get($j, $centerI) && $stateCount[4] < $maxCount) {
			$stateCount[4]++;
			$j++;
		}
		if ($stateCount[4] >= $maxCount) {
			return NAN;
		}

		// If we found a finder-pattern-like section, but its size is significantly different than
		// the original, assume it's a false positive
		$stateCountTotal = $stateCount[0] + $stateCount[1] + $stateCount[2] + $stateCount[3] +
			$stateCount[4];
		if (5 * abs($stateCountTotal - $originalStateCountTotal) >= $originalStateCountTotal) {
			return NAN;
		}

		return static::foundPatternCross($stateCount) ? self::centerFromEnd($stateCount, $j) : NAN;
	}

	/**
	 * After a vertical and horizontal scan finds a potential finder pattern, this method
	 * "cross-cross-cross-checks" by scanning down diagonally through the center of the possible
	 * finder pattern to see if the same proportion is detected.
	 *
	 * @param $startI                 ;  row where a finder pattern was detected
	 * @param $centerJ                 ; center of the section that appears to cross a finder pattern
	 * @param $maxCount               ; maximum reasonable number of modules that should be
	 *                                observed in any reading state, based on the results of the horizontal scan
	 * @param $originalStateCountTotal ; The original state count total.
	 *
	 * @return true if proportions are withing expected limits
	 */
	private function crossCheckDiagonal($startI, $centerJ, $maxCount, $originalStateCountTotal)
	{
		$stateCount = $this->getCrossCheckStateCount();

		// Start counting up, left from center finding black center mass
		$i = 0;
		$startI = (int)($startI);
		$centerJ = (int)($centerJ);
		while ($startI >= $i && $centerJ >= $i && $this->image->get($centerJ - $i, $startI - $i)) {
			$stateCount[2]++;
			$i++;
		}

		if ($startI < $i || $centerJ < $i) {
			return false;
		}

		// Continue up, left finding white space
		while ($startI >= $i && $centerJ >= $i && !$this->image->get($centerJ - $i, $startI - $i) &&
			$stateCount[1] <= $maxCount) {
			$stateCount[1]++;
			$i++;
		}

		// If already too many modules in this state or ran off the edge:
		if ($startI < $i || $centerJ < $i || $stateCount[1] > $maxCount) {
			return false;
		}

		// Continue up, left finding black border
		while ($startI >= $i && $centerJ >= $i && $this->image->get($centerJ - $i, $startI - $i) &&
			$stateCount[0] <= $maxCount) {
			$stateCount[0]++;
			$i++;
		}
		if ($stateCount[0] > $maxCount) {
			return false;
		}

		$maxI = $this->image->getHeight();
		$maxJ = $this->image->getWidth();

		// Now also count down, right from center
		$i = 1;
		while ($startI + $i < $maxI && $centerJ + $i < $maxJ && $this->image->get($centerJ + $i, $startI + $i)) {
			$stateCount[2]++;
			$i++;
		}

		// Ran off the edge?
		if ($startI + $i >= $maxI || $centerJ + $i >= $maxJ) {
			return false;
		}

		while ($startI + $i < $maxI && $centerJ + $i < $maxJ && !$this->image->get($centerJ + $i, $startI + $i) &&
			$stateCount[3] < $maxCount) {
			$stateCount[3]++;
			$i++;
		}

		if ($startI + $i >= $maxI || $centerJ + $i >= $maxJ || $stateCount[3] >= $maxCount) {
			return false;
		}

		while ($startI + $i < $maxI && $centerJ + $i < $maxJ && $this->image->get($centerJ + $i, $startI + $i) &&
			$stateCount[4] < $maxCount) {
			$stateCount[4]++;
			$i++;
		}

		if ($stateCount[4] >= $maxCount) {
			return false;
		}

		// If we found a finder-pattern-like section, but its size is more than 100% different than
		// the original, assume it's a false positive
		$stateCountTotal = $stateCount[0] + $stateCount[1] + $stateCount[2] + $stateCount[3] + $stateCount[4];

		return
			abs($stateCountTotal - $originalStateCountTotal) < 2 * $originalStateCountTotal &&
			self::foundPatternCross($stateCount);
	}

	/**
	 * @return true iff we have found at least 3 finder patterns that have been detected
	 *         at least {@link #CENTER_QUORUM} times each, and, the estimated module size of the
	 *         candidates is "pretty similar"
	 */
	private function haveMultiplyConfirmedCenters()
	{
		$confirmedCount = 0;
		$totalModuleSize = 0.0;
		$max = count($this->possibleCenters);
		foreach ($this->possibleCenters as $pattern) {
			if ($pattern->getCount() >= self::$CENTER_QUORUM) {
				$confirmedCount++;
				$totalModuleSize += $pattern->getEstimatedModuleSize();
			}
		}
		if ($confirmedCount < 3) {
			return false;
		}
		// OK, we have at least 3 confirmed centers, but, it's possible that one is a "false positive"
		// and that we need to keep looking. We detect this by asking if the estimated module sizes
		// vary too much. We arbitrarily say that when the total deviation from average exceeds
		// 5% of the total module size estimates, it's too much.
		$average = $totalModuleSize / (float)$max;
		$totalDeviation = 0.0;
		foreach ($this->possibleCenters as $pattern) {
			$totalDeviation += abs($pattern->getEstimatedModuleSize() - $average);
		}

		return $totalDeviation <= 0.05 * $totalModuleSize;
	}

	/**
	 * @return int number of rows we could safely skip during scanning, based on the first
	 *         two finder patterns that have been located. In some cases their position will
	 *         allow us to infer that the third pattern must lie below a certain point farther
	 *         down in the image.
	 */
	private function findRowSkip()
	{
		$max = count($this->possibleCenters);
		if ($max <= 1) {
			return 0;
		}
		$firstConfirmedCenter = null;
		foreach ($this->possibleCenters as $center) {
			if ($center->getCount() >= self::$CENTER_QUORUM) {
				if ($firstConfirmedCenter == null) {
					$firstConfirmedCenter = $center;
				} else {
					// We have two confirmed centers
					// How far down can we skip before resuming looking for the next
					// pattern? In the worst case, only the difference between the
					// difference in the x / y coordinates of the two centers.
					// This is the case where you find top left last.
					$this->hasSkipped = true;

					return (int)((abs($firstConfirmedCenter->getX() - $center->getX()) -
							abs($firstConfirmedCenter->getY() - $center->getY())) / 2);
				}
			}
		}

		return 0;
	}

	/**
	 * @return array the 3 best {@link FinderPattern}s from our list of candidates. The "best" are
	 *         those that have been detected at least {@link #CENTER_QUORUM} times, and whose module
	 *         size differs from the average among those patterns the least
	 * @throws NotFoundException if 3 such finder patterns do not exist
	 */
	private function selectBestPatterns()
	{
		$startSize = count($this->possibleCenters);
		if ($startSize < 3) {
			// Couldn't find enough finder patterns
			throw new NotFoundException();
		}

		// Filter outlier possibilities whose module size is too different
		if ($startSize > 3) {
			// But we can only afford to do so if we have at least 4 possibilities to choose from
			$totalModuleSize = 0.0;
			$square = 0.0;
			foreach ($this->possibleCenters as $center) {
				$size = $center->getEstimatedModuleSize();
				$totalModuleSize += $size;
				$square += $size * $size;
			}
			$this->average = $totalModuleSize / (float)$startSize;
			$stdDev = (float)sqrt($square / $startSize - $this->average * $this->average);

			usort($this->possibleCenters, $this->FurthestFromAverageComparator(...));

			$limit = max(0.2 * $this->average, $stdDev);

			for ($i = 0; $i < count($this->possibleCenters) && count($this->possibleCenters) > 3; $i++) {
				$pattern = $this->possibleCenters[$i];
				if (abs($pattern->getEstimatedModuleSize() - $this->average) > $limit) {
					unset($this->possibleCenters[$i]);//возможно что ключи меняются в java при вызове .remove(i) ???
					$this->possibleCenters = array_values($this->possibleCenters);
					$i--;
				}
			}
		}

		if (count($this->possibleCenters) > 3) {
			// Throw away all but those first size candidate points we found.

			$totalModuleSize = 0.0;
			foreach ($this->possibleCenters as $possibleCenter) {
				$totalModuleSize += $possibleCenter->getEstimatedModuleSize();
			}

			$this->average = $totalModuleSize / (float)count($this->possibleCenters);

			usort($this->possibleCenters, $this->CenterComparator(...));

			array_slice($this->possibleCenters, 3, count($this->possibleCenters) - 3);
		}

		return [$this->possibleCenters[0], $this->possibleCenters[1], $this->possibleCenters[2]];
	}

	/**
	 * <p>Orders by furthest from average</p>
	 */
	public function FurthestFromAverageComparator($center1, $center2)
	{
		$dA = abs($center2->getEstimatedModuleSize() - $this->average);
		$dB = abs($center1->getEstimatedModuleSize() - $this->average);
		if ($dA < $dB) {
			return -1;
		} elseif ($dA == $dB) {
			return 0;
		} else {
			return 1;
		}
	}

	public function CenterComparator($center1, $center2)
	{
		if ($center2->getCount() == $center1->getCount()) {
			$dA = abs($center2->getEstimatedModuleSize() - $this->average);
			$dB = abs($center1->getEstimatedModuleSize() - $this->average);
			if ($dA < $dB) {
				return 1;
			} elseif ($dA == $dB) {
				return 0;
			} else {
				return -1;
			}
		} else {
			return $center2->getCount() - $center1->getCount();
		}
	}

	final protected function getImage()
	{
		return $this->image;
	}
	/**
	 * <p>Orders by {@link FinderPattern#getCount()}, descending.</p>
	 */

	//@Override
	final protected function getPossibleCenters()
	{ //List<FinderPattern> getPossibleCenters()
		return $this->possibleCenters;
	}
}
