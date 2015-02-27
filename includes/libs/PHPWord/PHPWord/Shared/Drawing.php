<?php
/**
 * PHPWord
 *
 * Copyright (c) 2011 PHPWord
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 010 PHPWord
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    Beta 0.6.3, 08.07.2011
 */


class PHPWord_Shared_Drawing
{
	/**
	 * Convert pixels to EMU
	 *
	 * @param 	int $pValue	Value in pixels
	 * @return 	int			Value in EMU
	 */
	public static function pixelsToEMU($pValue = 0) {
		return round($pValue * 9525);
	}
	
	/**
	 * Convert EMU to pixels
	 *
	 * @param 	int $pValue	Value in EMU
	 * @return 	int			Value in pixels
	 */
	public static function EMUToPixels($pValue = 0) {
		if ($pValue != 0) {
			return round($pValue / 9525);
		} else {
			return 0;
		}
	}
	
	/**
	 * Convert pixels to points
	 *
	 * @param 	int $pValue	Value in pixels
	 * @return 	int			Value in points
	 */
	public static function pixelsToPoints($pValue = 0) {
		return $pValue * 0.67777777;
	}
	
	/**
	 * Convert points width to pixels
	 *
	 * @param 	int $pValue	Value in points
	 * @return 	int			Value in pixels
	 */
	public static function pointsToPixels($pValue = 0) {
		if ($pValue != 0) {
			return $pValue * 1.333333333;
		} else {
			return 0;
		}
	}

	/**
	 * Convert degrees to angle
	 *
	 * @param 	int $pValue	Degrees
	 * @return 	int			Angle
	 */
	public static function degreesToAngle($pValue = 0) {
		return (int)round($pValue * 60000);
	}
	
	/**
	 * Convert angle to degrees
	 *
	 * @param 	int $pValue	Angle
	 * @return 	int			Degrees
	 */
	public static function angleToDegrees($pValue = 0) {
		if ($pValue != 0) {
			return round($pValue / 60000);
		} else {
			return 0;
		}
	}
}
