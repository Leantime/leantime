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

use Zxing\ResultPoint;

/**
 * <p>Encapsulates an alignment pattern, which are the smaller square patterns found in
 * all but the simplest QR Codes.</p>
 *
 * @author Sean Owen
 */
final class AlignmentPattern extends ResultPoint
{
	public function __construct($posX, $posY, private $estimatedModuleSize)
	{
		parent::__construct($posX, $posY);
	}

	/**
	 * <p>Determines if this alignment pattern "about equals" an alignment pattern at the stated
	 * position and size -- meaning, it is at nearly the same center with nearly the same size.</p>
	 */
	public function aboutEquals($moduleSize, $i, $j)
	{
		if (abs($i - $this->getY()) <= $moduleSize && abs($j - $this->getX()) <= $moduleSize) {
			$moduleSizeDiff = abs($moduleSize - $this->estimatedModuleSize);

			return $moduleSizeDiff <= 1.0 || $moduleSizeDiff <= $this->estimatedModuleSize;
		}

		return false;
	}

	/**
	 * Combines this object's current estimate of a finder pattern position and module size
	 * with a new estimate. It returns a new {@code FinderPattern} containing an average of the two.
	 */
	public function combineEstimate($i, $j, $newModuleSize): \Zxing\Qrcode\Detector\AlignmentPattern
	{
		$combinedX = ($this->getX() + $j) / 2.0;
		$combinedY = ($this->getY() + $i) / 2.0;
		$combinedModuleSize = ($this->estimatedModuleSize + $newModuleSize) / 2.0;

		return new AlignmentPattern($combinedX, $combinedY, $combinedModuleSize);
	}
}
