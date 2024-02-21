<?php
/**
 * Created by PhpStorm.
 * User: Ashot
 * Date: 3/24/15
 * Time: 21:23
 */

namespace Zxing\Common\Detector;

use Zxing\BinaryBitmap;
use Zxing\NotFoundException;
use Zxing\ResultPoint;

/*
 *
 *
import com.google.zxing.NotFoundException;
import com.google.zxing.ResultPoint;
import com.google.zxing.common.BitMatrix;

 */
//require_once('./lib/NotFoundException.php');
//require_once('./lib/ResultPoint.php');
//require_once('./lib/common/BitMatrix.php');


/**
 * <p>A somewhat generic detector that looks for a barcode-like rectangular region within an image.
 * It looks within a mostly white region of an image for a region of black and white, but mostly
 * black. It returns the four corners of the region, as best it can determine.</p>
 *
 * @author Sean Owen
 * @port   Ashot Khanamiryan
 */
class MonochromeRectangleDetector
{
	private static int $MAX_MODULES = 32;

	public function __construct(private readonly BinaryBitmap $image)
 {
 }

	/**
	 * <p>Detects a rectangular region of black and white -- mostly black -- with a region of mostly
	 * white, in an image.</p>
	 *
	 * @return {@link ResultPoint}[] describing the corners of the rectangular region. The first and
	 *  last points are opposed on the diagonal, as are the second and third. The first point will be
	 *  the topmost point and the last, the bottommost. The second point will be leftmost and the
	 *  third, the rightmost
	 * @throws NotFoundException if no Data Matrix Code can be found
	 */
	public function detect(): \Zxing\ResultPoint
	{
		$height = $this->image->getHeight();
		$width = $this->image->getWidth();
		$halfHeight = $height / 2;
		$halfWidth = $width / 2;

		$deltaY = max(1, $height / (self::$MAX_MODULES * 8));
		$deltaX = max(1, $width / (self::$MAX_MODULES * 8));


		$top = 0;
		$bottom = $height;
		$left = 0;
		$right = $width;
		$pointA = $this->findCornerFromCenter(
			$halfWidth,
			0,
			$left,
			$right,
			$halfHeight,
			-$deltaY,
			$top,
			$bottom,
			$halfWidth / 2
		);
		$top = (int)$pointA->getY() - 1;
		$pointB = $this->findCornerFromCenter(
			$halfWidth,
			-$deltaX,
			$left,
			$right,
			$halfHeight,
			0,
			$top,
			$bottom,
			$halfHeight / 2
		);
		$left = (int)$pointB->getX() - 1;
		$pointC = $this->findCornerFromCenter(
			$halfWidth,
			$deltaX,
			$left,
			$right,
			$halfHeight,
			0,
			$top,
			$bottom,
			$halfHeight / 2
		);
		$right = (int)$pointC->getX() + 1;
		$pointD = $this->findCornerFromCenter(
			$halfWidth,
			0,
			$left,
			$right,
			$halfHeight,
			$deltaY,
			$top,
			$bottom,
			$halfWidth / 2
		);
		$bottom = (int)$pointD->getY() + 1;

		// Go try to find po$A again with better information -- might have been off at first.
		$pointA = $this->findCornerFromCenter(
			$halfWidth,
			0,
			$left,
			$right,
			$halfHeight,
			-$deltaY,
			$top,
			$bottom,
			$halfWidth / 4
		);

		return new ResultPoint($pointA, $pointB, $pointC, $pointD);
	}


	/**
	 * Attempts to locate a corner of the barcode by scanning up, down, left or right from a center
	 * point which should be within the barcode.
	 *
	 * @param float $centerX     center's x component (horizontal)
	 * @param float $deltaX      same as deltaY but change in x per step instead
	 * @param float $left        minimum value of x
	 * @param float $right       maximum value of x
	 * @param float $centerY     center's y component (vertical)
	 * @param float $deltaY      change in y per step. If scanning up this is negative; down, positive;
	 *                    left or right, 0
	 * @param float $top         minimum value of y to search through (meaningless when di == 0)
	 * @param float $bottom      maximum value of y
	 * @param float $maxWhiteRun maximum run of white pixels that can still be considered to be within
	 *                    the barcode
	 *
	 * @return ResultPoint {@link com.google.zxing.ResultPoint} encapsulating the corner that was found
	 * @throws NotFoundException if such a point cannot be found
	 */
	private function findCornerFromCenter(
		$centerX,
		$deltaX,
		$left,
		$right,
		$centerY,
		$deltaY,
		$top,
		$bottom,
		$maxWhiteRun
	): \Zxing\ResultPoint
	{
		$lastRange = null;
		for ($y = $centerY, $x = $centerX;
			 $y < $bottom && $y >= $top && $x < $right && $x >= $left;
			 $y += $deltaY, $x += $deltaX) {
			$range = 0;
			if ($deltaX == 0) {
				// horizontal slices, up and down
				$range = $this->blackWhiteRange($y, $maxWhiteRun, $left, $right, true);
			} else {
				// vertical slices, left and right
				$range = $this->blackWhiteRange($x, $maxWhiteRun, $top, $bottom, false);
			}
			if ($range == null) {
				if ($lastRange == null) {
					throw NotFoundException::getNotFoundInstance();
				}
				// lastRange was found
				if ($deltaX == 0) {
					$lastY = $y - $deltaY;
					if ($lastRange[0] < $centerX) {
						if ($lastRange[1] > $centerX) {
							// straddle, choose one or the other based on direction
							return new ResultPoint($deltaY > 0 ? $lastRange[0] : $lastRange[1], $lastY);
						}

						return new ResultPoint($lastRange[0], $lastY);
					} else {
						return new ResultPoint($lastRange[1], $lastY);
					}
				} else {
					$lastX = $x - $deltaX;
					if ($lastRange[0] < $centerY) {
						if ($lastRange[1] > $centerY) {
							return new ResultPoint($lastX, $deltaX < 0 ? $lastRange[0] : $lastRange[1]);
						}

						return new ResultPoint($lastX, $lastRange[0]);
					} else {
						return new ResultPoint($lastX, $lastRange[1]);
					}
				}
			}
			$lastRange = $range;
		}
		throw NotFoundException::getNotFoundInstance();
	}


	/**
	 * Computes the start and end of a region of pixels, either horizontally or vertically, that could
	 * be part of a Data Matrix barcode.
	 *
	 * @param if $fixedDimension scanning horizontally, this is the row (the fixed vertical location)
	 *                       where we are scanning. If scanning vertically it's the column, the fixed horizontal location
	 * @param largest    $maxWhiteRun run of white pixels that can still be considered part of the
	 *                       barcode region
	 * @param minimum         $minDim pixel location, horizontally or vertically, to consider
	 * @param maximum         $maxDim pixel location, horizontally or vertically, to consider
	 * @param if     $horizontal true, we're scanning left-right, instead of up-down
	 *
	 * @return int[] with start and end of found range, or null if no such range is found
	 *  (e.g. only white was found)
	 */

	private function blackWhiteRange($fixedDimension, $maxWhiteRun, $minDim, $maxDim, $horizontal)
	{
		$center = ($minDim + $maxDim) / 2;

		// Scan left/up first
		$start = $center;
		while ($start >= $minDim) {
			if ($horizontal ? $this->image->get($start, $fixedDimension) : $this->image->get($fixedDimension, $start)) {
				$start--;
			} else {
				$whiteRunStart = $start;
				do {
					$start--;
				} while ($start >= $minDim && !($horizontal ? $this->image->get($start, $fixedDimension) :
					$this->image->get($fixedDimension, $start)));
				$whiteRunSize = $whiteRunStart - $start;
				if ($start < $minDim || $whiteRunSize > $maxWhiteRun) {
					$start = $whiteRunStart;
					break;
				}
			}
		}
		$start++;

		// Then try right/down
		$end = $center;
		while ($end < $maxDim) {
			if ($horizontal ? $this->image->get($end, $fixedDimension) : $this->image->get($fixedDimension, $end)) {
				$end++;
			} else {
				$whiteRunStart = $end;
				do {
					$end++;
				} while ($end < $maxDim && !($horizontal ? $this->image->get($end, $fixedDimension) :
					$this->image->get($fixedDimension, $end)));
				$whiteRunSize = $end - $whiteRunStart;
				if ($end >= $maxDim || $whiteRunSize > $maxWhiteRun) {
					$end = $whiteRunStart;
					break;
				}
			}
		}
		$end--;

		return $end > $start ? [$start, $end] : null;
	}
}
