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

/**
 * <p>This class implements a perspective transform in two dimensions. Given four source and four
 * destination points, it will compute the transformation implied between them. The code is based
 * directly upon section 3.4.2 of George Wolberg's "Digital Image Warping"; see pages 54-56.</p>
 *
 * @author Sean Owen
 */
final class PerspectiveTransform
{
	private function __construct(private $a11, private $a21, private $a31, private $a12, private $a22, private $a32, private $a13, private $a23, private $a33)
 {
 }

	public static function quadrilateralToQuadrilateral(
		$x0,
		$y0,
		$x1,
		$y1,
		$x2,
		$y2,
		$x3,
		$y3,
		$x0p,
		$y0p,
		$x1p,
		$y1p,
		$x2p,
		$y2p,
		$x3p,
		$y3p
	) {
		$qToS = self::quadrilateralToSquare($x0, $y0, $x1, $y1, $x2, $y2, $x3, $y3);
		$sToQ = self::squareToQuadrilateral($x0p, $y0p, $x1p, $y1p, $x2p, $y2p, $x3p, $y3p);

		return $sToQ->times($qToS);
	}

	public static function quadrilateralToSquare(
		$x0,
		$y0,
		$x1,
		$y1,
		$x2,
		$y2,
		$x3,
		$y3
	) {
		// Here, the adjoint serves as the inverse:
		return self::squareToQuadrilateral($x0, $y0, $x1, $y1, $x2, $y2, $x3, $y3)->buildAdjoint();
	}

	public function buildAdjoint(): \Zxing\Common\PerspectiveTransform
	{
		// Adjoint is the transpose of the cofactor matrix:
		return new PerspectiveTransform(
			$this->a22 * $this->a33 - $this->a23 * $this->a32,
			$this->a23 * $this->a31 - $this->a21 * $this->a33,
			$this->a21 * $this->a32 - $this->a22 * $this->a31,
			$this->a13 * $this->a32 - $this->a12 * $this->a33,
			$this->a11 * $this->a33 - $this->a13 * $this->a31,
			$this->a12 * $this->a31 - $this->a11 * $this->a32,
			$this->a12 * $this->a23 - $this->a13 * $this->a22,
			$this->a13 * $this->a21 - $this->a11 * $this->a23,
			$this->a11 * $this->a22 - $this->a12 * $this->a21
		);
	}

	public static function squareToQuadrilateral(
		$x0,
		$y0,
		$x1,
		$y1,
		$x2,
		$y2,
		$x3,
		$y3
	): \Zxing\Common\PerspectiveTransform {
		$dx3 = $x0 - $x1 + $x2 - $x3;
		$dy3 = $y0 - $y1 + $y2 - $y3;
		if ($dx3 == 0.0 && $dy3 == 0.0) {
			// Affine
			return new PerspectiveTransform(
				$x1 - $x0,
				$x2 - $x1,
				$x0,
				$y1 - $y0,
				$y2 - $y1,
				$y0,
				0.0,
				0.0,
				1.0
			);
		} else {
			$dx1 = $x1 - $x2;
			$dx2 = $x3 - $x2;
			$dy1 = $y1 - $y2;
			$dy2 = $y3 - $y2;
			$denominator = $dx1 * $dy2 - $dx2 * $dy1;
			$a13 = ($dx3 * $dy2 - $dx2 * $dy3) / $denominator;
			$a23 = ($dx1 * $dy3 - $dx3 * $dy1) / $denominator;

			return new PerspectiveTransform(
				$x1 - $x0 + $a13 * $x1,
				$x3 - $x0 + $a23 * $x3,
				$x0,
				$y1 - $y0 + $a13 * $y1,
				$y3 - $y0 + $a23 * $y3,
				$y0,
				$a13,
				$a23,
				1.0
			);
		}
	}

	public function times($other): \Zxing\Common\PerspectiveTransform
	{
		return new PerspectiveTransform(
			$this->a11 * $other->a11 + $this->a21 * $other->a12 + $this->a31 * $other->a13,
			$this->a11 * $other->a21 + $this->a21 * $other->a22 + $this->a31 * $other->a23,
			$this->a11 * $other->a31 + $this->a21 * $other->a32 + $this->a31 * $other->a33,
			$this->a12 * $other->a11 + $this->a22 * $other->a12 + $this->a32 * $other->a13,
			$this->a12 * $other->a21 + $this->a22 * $other->a22 + $this->a32 * $other->a23,
			$this->a12 * $other->a31 + $this->a22 * $other->a32 + $this->a32 * $other->a33,
			$this->a13 * $other->a11 + $this->a23 * $other->a12 + $this->a33 * $other->a13,
			$this->a13 * $other->a21 + $this->a23 * $other->a22 + $this->a33 * $other->a23,
			$this->a13 * $other->a31 + $this->a23 * $other->a32 + $this->a33 * $other->a33
		);
	}

	public function transformPoints(&$points, &$yValues = 0): void
	{
		if ($yValues) {
			$this->transformPoints_($points, $yValues);

			return;
		}
		$max = is_countable($points) ? count($points) : 0;
		$a11 = $this->a11;
		$a12 = $this->a12;
		$a13 = $this->a13;
		$a21 = $this->a21;
		$a22 = $this->a22;
		$a23 = $this->a23;
		$a31 = $this->a31;
		$a32 = $this->a32;
		$a33 = $this->a33;
		for ($i = 0; $i < $max; $i += 2) {
			$x = $points[$i];
			$y = $points[$i + 1];
			$denominator = $a13 * $x + $a23 * $y + $a33;
			$points[$i] = ($a11 * $x + $a21 * $y + $a31) / $denominator;
			$points[$i + 1] = ($a12 * $x + $a22 * $y + $a32) / $denominator;
		}
	}

	public function transformPoints_(&$xValues, &$yValues): void
	{
		$n = is_countable($xValues) ? count($xValues) : 0;
		for ($i = 0; $i < $n; $i++) {
			$x = $xValues[$i];
			$y = $yValues[$i];
			$denominator = $this->a13 * $x + $this->a23 * $y + $this->a33;
			$xValues[$i] = ($this->a11 * $x + $this->a21 * $y + $this->a31) / $denominator;
			$yValues[$i] = ($this->a12 * $x + $this->a22 * $y + $this->a32) / $denominator;
		}
	}
}
