<?php
/*
* Copyright 2012 ZXing authors
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

namespace Zxing\Common\Detector;

final class MathUtils
{
	private function __construct()
	{
	}

	/**
	 * Ends up being a bit faster than {@link Math#round(float)}. This merely rounds its
	 * argument to the nearest int, where x.5 rounds up to x+1. Semantics of this shortcut
	 * differ slightly from {@link Math#round(float)} in that half rounds down for negative
	 * values. -2.5 rounds to -3, not -2. For purposes here it makes no difference.
	 *
	 * @param float $d real value to round
	 *
	 * @return int {@code int}
	 */
	public static function round($d)
	{
		return (int)($d + ($d < 0.0 ? -0.5 : 0.5));
	}

	public static function distance($aX, $aY, $bX, $bY)
	{
		$xDiff = $aX - $bX;
		$yDiff = $aY - $bY;

		return (float)sqrt($xDiff * $xDiff + $yDiff * $yDiff);
	}
}
