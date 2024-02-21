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

namespace Zxing\Common;

use Zxing\NotFoundException;

/**
 * @author Sean Owen
 */
final class DefaultGridSampler extends GridSampler
{
	//@Override
	public function sampleGrid(
		$image,
		$dimensionX,
		$dimensionY,
		$p1ToX,
		$p1ToY,
		$p2ToX,
		$p2ToY,
		$p3ToX,
		$p3ToY,
		$p4ToX,
		$p4ToY,
		$p1FromX,
		$p1FromY,
		$p2FromX,
		$p2FromY,
		$p3FromX,
		$p3FromY,
		$p4FromX,
		$p4FromY
	) {
		$transform = PerspectiveTransform::quadrilateralToQuadrilateral(
			$p1ToX,
			$p1ToY,
			$p2ToX,
			$p2ToY,
			$p3ToX,
			$p3ToY,
			$p4ToX,
			$p4ToY,
			$p1FromX,
			$p1FromY,
			$p2FromX,
			$p2FromY,
			$p3FromX,
			$p3FromY,
			$p4FromX,
			$p4FromY
		);

		return $this->sampleGrid_($image, $dimensionX, $dimensionY, $transform);
	}

	//@Override
	public function sampleGrid_(
		$image,
		$dimensionX,
		$dimensionY,
		$transform
	) {
		if ($dimensionX <= 0 || $dimensionY <= 0) {
			throw NotFoundException::getNotFoundInstance();
		}
		$bits = new BitMatrix($dimensionX, $dimensionY);
		$points = fill_array(0, 2 * $dimensionX, 0.0);
		for ($y = 0; $y < $dimensionY; $y++) {
			$max = is_countable($points) ? count($points) : 0;
			$iValue = (float)$y + 0.5;
			for ($x = 0; $x < $max; $x += 2) {
				$points[$x] = (float)($x / 2) + 0.5;
				$points[$x + 1] = $iValue;
			}
			$transform->transformPoints($points);
			// Quick check to see if points transformed to something inside the image;
			// sufficient to check the endpoints
			self::checkAndNudgePoints($image, $points);
			try {
				for ($x = 0; $x < $max; $x += 2) {
					if ($image->get((int)$points[$x], (int)$points[$x + 1])) {
						// Black(-ish) pixel
						$bits->set($x / 2, $y);
					}
				}
			} catch (\Exception) {//ArrayIndexOutOfBoundsException
				// This feels wrong, but, sometimes if the finder patterns are misidentified, the resulting
				// transform gets "twisted" such that it maps a straight line of points to a set of points
				// whose endpoints are in bounds, but others are not. There is probably some mathematical
				// way to detect this about the transformation that I don't know yet.
				// This results in an ugly runtime exception despite our clever checks above -- can't have
				// that. We could check each point's coordinates but that feels duplicative. We settle for
				// catching and wrapping ArrayIndexOutOfBoundsException.
				throw NotFoundException::getNotFoundInstance();
			}
		}

		return $bits;
	}
}
