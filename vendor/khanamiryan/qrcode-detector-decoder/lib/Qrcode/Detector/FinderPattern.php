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

use  Zxing\ResultPoint;

/**
 * <p>Encapsulates a finder pattern, which are the three square patterns found in
 * the corners of QR Codes. It also encapsulates a count of similar finder patterns,
 * as a convenience to the finder's bookkeeping.</p>
 *
 * @author Sean Owen
 */
final class FinderPattern extends ResultPoint
{
	public function __construct($posX, $posY, private $estimatedModuleSize, private $count = 1)
	{
		parent::__construct($posX, $posY);
	}

	public function getEstimatedModuleSize()
	{
		return $this->estimatedModuleSize;
	}

	public function getCount()
	{
		return $this->count;
	}

	/*
	void incrementCount() {
	  this.count++;
	}
	 */

	/**
	 * <p>Determines if this finder pattern "about equals" a finder pattern at the stated
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
	 * with a new estimate. It returns a new {@code FinderPattern} containing a weighted average
	 * based on count.
	 */
	public function combineEstimate($i, $j, $newModuleSize): \Zxing\Qrcode\Detector\FinderPattern
	{
		$combinedCount = $this->count + 1;
		$combinedX = ($this->count * $this->getX() + $j) / $combinedCount;
		$combinedY = ($this->count * $this->getY() + $i) / $combinedCount;
		$combinedModuleSize = ($this->count * $this->estimatedModuleSize + $newModuleSize) / $combinedCount;

		return new FinderPattern($combinedX, $combinedY, $combinedModuleSize, $combinedCount);
	}
}
