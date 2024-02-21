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

namespace Zxing\Common\Reedsolomon;

/**
 * <p>Represents a polynomial whose coefficients are elements of a GF.
 * Instances of this class are immutable.</p>
 *
 * <p>Much credit is due to William Rucklidge since portions of this code are an indirect
 * port of his C++ Reed-Solomon implementation.</p>
 *
 * @author Sean Owen
 */
final class GenericGFPoly
{
	/**
  * @var int[]|mixed|null
  */
 private $coefficients;

	/**
	 * @param the        $field {@link GenericGF} instance representing the field to use
	 * to perform computations
	 * @param array $coefficients coefficients as ints representing elements of GF(size), arranged
	 *                     from most significant (highest-power term) coefficient to least significant
	 *
	 * @throws InvalidArgumentException if argument is null or empty,
	 * or if leading coefficient is 0 and this is not a
	 * constant polynomial (that is, it is not the monomial "0")
	 */
	public function __construct(private $field, $coefficients)
	{
		if (count($coefficients) == 0) {
			throw new \InvalidArgumentException();
		}
		$coefficientsLength = count($coefficients);
		if ($coefficientsLength > 1 && $coefficients[0] == 0) {
			// Leading term must be non-zero for anything except the constant polynomial "0"
			$firstNonZero = 1;
			while ($firstNonZero < $coefficientsLength && $coefficients[$firstNonZero] == 0) {
				$firstNonZero++;
			}
			if ($firstNonZero == $coefficientsLength) {
				$this->coefficients = [0];
			} else {
				$this->coefficients = fill_array(0, $coefficientsLength - $firstNonZero, 0);
				$this->coefficients = arraycopy(
					$coefficients,
					$firstNonZero,
					$this->coefficients,
					0,
					is_countable($this->coefficients) ? count($this->coefficients) : 0
				);
			}
		} else {
			$this->coefficients = $coefficients;
		}
	}

	public function getCoefficients()
	{
		return $this->coefficients;
	}

	/**
	 * @return evaluation of this polynomial at a given point
	 */
	public function evaluateAt($a)
	{
		if ($a == 0) {
			// Just return the x^0 coefficient
			return $this->getCoefficient(0);
		}
		$size = is_countable($this->coefficients) ? count($this->coefficients) : 0;
		if ($a == 1) {
			// Just the sum of the coefficients
			$result = 0;
			foreach ($this->coefficients as $coefficient) {
				$result = GenericGF::addOrSubtract($result, $coefficient);
			}

			return $result;
		}
		$result = $this->coefficients[0];
		for ($i = 1; $i < $size; $i++) {
			$result = GenericGF::addOrSubtract($this->field->multiply($a, $result), $this->coefficients[$i]);
		}

		return $result;
	}

	/**
	 * @return coefficient of x^degree term in this polynomial
	 */
	public function getCoefficient($degree)
	{
		return $this->coefficients[(is_countable($this->coefficients) ? count($this->coefficients) : 0) - 1 - $degree];
	}

	public function multiply($other)
	{
		$aCoefficients = [];
  $bCoefficients = [];
  $aLength = null;
  $bLength = null;
  $product = [];
  if (is_int($other)) {
			return $this->multiply_($other);
		}
		if ($this->field !== $other->field) {
			throw new \InvalidArgumentException("GenericGFPolys do not have same GenericGF field");
		}
		if ($this->isZero() || $other->isZero()) {
			return $this->field->getZero();
		}
		$aCoefficients = $this->coefficients;
		$aLength = count($aCoefficients);
		$bCoefficients = $other->coefficients;
		$bLength = count($bCoefficients);
		$product = fill_array(0, $aLength + $bLength - 1, 0);
		for ($i = 0; $i < $aLength; $i++) {
			$aCoeff = $aCoefficients[$i];
			for ($j = 0; $j < $bLength; $j++) {
				$product[$i + $j] = GenericGF::addOrSubtract(
					$product[$i + $j],
					$this->field->multiply($aCoeff, $bCoefficients[$j])
				);
			}
		}

		return new GenericGFPoly($this->field, $product);
	}

	public function multiply_($scalar)
	{
		if ($scalar == 0) {
			return $this->field->getZero();
		}
		if ($scalar == 1) {
			return $this;
		}
		$size = is_countable($this->coefficients) ? count($this->coefficients) : 0;
		$product = fill_array(0, $size, 0);
		for ($i = 0; $i < $size; $i++) {
			$product[$i] = $this->field->multiply($this->coefficients[$i], $scalar);
		}

		return new GenericGFPoly($this->field, $product);
	}

	/**
	 * @return true iff this polynomial is the monomial "0"
	 */
	public function isZero()
	{
		return $this->coefficients[0] == 0;
	}

	public function multiplyByMonomial($degree, $coefficient)
	{
		if ($degree < 0) {
			throw new \InvalidArgumentException();
		}
		if ($coefficient == 0) {
			return $this->field->getZero();
		}
		$size = is_countable($this->coefficients) ? count($this->coefficients) : 0;
		$product = fill_array(0, $size + $degree, 0);
		for ($i = 0; $i < $size; $i++) {
			$product[$i] = $this->field->multiply($this->coefficients[$i], $coefficient);
		}

		return new GenericGFPoly($this->field, $product);
	}

	public function divide($other)
	{
		if ($this->field !== $other->field) {
			throw new \InvalidArgumentException("GenericGFPolys do not have same GenericGF field");
		}
		if ($other->isZero()) {
			throw new \InvalidArgumentException("Divide by 0");
		}

		$quotient = $this->field->getZero();
		$remainder = $this;

		$denominatorLeadingTerm = $other->getCoefficient($other->getDegree());
		$inverseDenominatorLeadingTerm = $this->field->inverse($denominatorLeadingTerm);

		while ($remainder->getDegree() >= $other->getDegree() && !$remainder->isZero()) {
			$degreeDifference = $remainder->getDegree() - $other->getDegree();
			$scale = $this->field->multiply($remainder->getCoefficient($remainder->getDegree()), $inverseDenominatorLeadingTerm);
			$term = $other->multiplyByMonomial($degreeDifference, $scale);
			$iterationQuotient = $this->field->buildMonomial($degreeDifference, $scale);
			$quotient = $quotient->addOrSubtract($iterationQuotient);
			$remainder = $remainder->addOrSubtract($term);
		}

		return [$quotient, $remainder];
	}

	/**
	 * @return degree of this polynomial
	 */
	public function getDegree()
	{
		return (is_countable($this->coefficients) ? count($this->coefficients) : 0) - 1;
	}

	public function addOrSubtract($other)
	{
		$smallerCoefficients = [];
  $largerCoefficients = [];
  $sumDiff = [];
  $lengthDiff = null;
  $countLargerCoefficients = null;
  if ($this->field !== $other->field) {
			throw new \InvalidArgumentException("GenericGFPolys do not have same GenericGF field");
		}
		if ($this->isZero()) {
			return $other;
		}
		if ($other->isZero()) {
			return $this;
		}

		$smallerCoefficients = $this->coefficients;
		$largerCoefficients = $other->coefficients;
		if (count($smallerCoefficients) > count($largerCoefficients)) {
			$temp = $smallerCoefficients;
			$smallerCoefficients = $largerCoefficients;
			$largerCoefficients = $temp;
		}
		$sumDiff = fill_array(0, count($largerCoefficients), 0);
		$lengthDiff = count($largerCoefficients) - count($smallerCoefficients);
		// Copy high-order terms only found in higher-degree polynomial's coefficients
		$sumDiff = arraycopy($largerCoefficients, 0, $sumDiff, 0, $lengthDiff);

		$countLargerCoefficients = count($largerCoefficients);
		for ($i = $lengthDiff; $i < $countLargerCoefficients; $i++) {
			$sumDiff[$i] = GenericGF::addOrSubtract($smallerCoefficients[$i - $lengthDiff], $largerCoefficients[$i]);
		}

		return new GenericGFPoly($this->field, $sumDiff);
	}

	//@Override

	public function toString()
	{
		$result = '';
		for ($degree = $this->getDegree(); $degree >= 0; $degree--) {
			$coefficient = $this->getCoefficient($degree);
			if ($coefficient != 0) {
				if ($coefficient < 0) {
					$result .= " - ";
					$coefficient = -$coefficient;
				} else {
					if (strlen((string) $result) > 0) {
						$result .= " + ";
					}
				}
				if ($degree == 0 || $coefficient != 1) {
					$alphaPower = $this->field->log($coefficient);
					if ($alphaPower == 0) {
						$result .= '1';
					} elseif ($alphaPower == 1) {
						$result .= 'a';
					} else {
						$result .= "a^";
						$result .= ($alphaPower);
					}
				}
				if ($degree != 0) {
					if ($degree == 1) {
						$result .= 'x';
					} else {
						$result .= "x^";
						$result .= $degree;
					}
				}
			}
		}

		return $result;
	}
}
