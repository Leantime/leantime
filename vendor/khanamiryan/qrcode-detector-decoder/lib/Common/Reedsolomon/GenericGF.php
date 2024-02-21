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
 * <p>This class contains utility methods for performing mathematical operations over
 * the Galois Fields. Operations use a given primitive polynomial in calculations.</p>
 *
 * <p>Throughout this package, elements of the GF are represented as an {@code int}
 * for convenience and speed (but at the cost of memory).
 * </p>
 *
 * @author Sean Owen
 * @author David Olivier
 */
final class GenericGF
{
	public static $AZTEC_DATA_12;
	public static $AZTEC_DATA_10;
	public static $AZTEC_DATA_6;
	public static $AZTEC_PARAM;
	public static $QR_CODE_FIELD_256;
	public static $DATA_MATRIX_FIELD_256;
	public static $AZTEC_DATA_8;
	public static $MAXICODE_FIELD_64;

	private array $expTable = [];
	private array $logTable = [];
	private readonly \Zxing\Common\Reedsolomon\GenericGFPoly $zero;
	private readonly \Zxing\Common\Reedsolomon\GenericGFPoly $one;

	/**
 * Create a representation of GF(size) using the given primitive polynomial.
 *
 * @param irreducible $primitive polynomial whose coefficients are represented by
 *                  the bits of an int, where the least-significant bit represents the constant
 *                  coefficient
 * @param the      $size size of the field
  * @param the $generatorBase factor b in the generator polynomial can be 0- or 1-based
                  (g(x) = (x+a^b)(x+a^(b+1))...(x+a^(b+2t-1))).
                  In most cases it should be 1, but for QR code it is 0.
 */
 public function __construct(private $primitive, private $size, private $generatorBase)
	{
		$x = 1;
		for ($i = 0; $i < $size; $i++) {
			$this->expTable[$i] = $x;
			$x *= 2; // we're assuming the generator alpha is 2
			if ($x >= $size) {
				$x ^= $primitive;
				$x &= $size - 1;
			}
		}
		for ($i = 0; $i < $size - 1; $i++) {
			$this->logTable[$this->expTable[$i]] = $i;
		}
		// logTable[0] == 0 but this should never be used
		$this->zero = new GenericGFPoly($this, [0]);
		$this->one = new GenericGFPoly($this, [1]);
	}

	public static function Init(): void
	{
		self::$AZTEC_DATA_12 = new GenericGF(0x1069, 4096, 1); // x^12 + x^6 + x^5 + x^3 + 1
		self::$AZTEC_DATA_10 = new GenericGF(0x409, 1024, 1); // x^10 + x^3 + 1
		self::$AZTEC_DATA_6 = new GenericGF(0x43, 64, 1); // x^6 + x + 1
		self::$AZTEC_PARAM = new GenericGF(0x13, 16, 1); // x^4 + x + 1
		self::$QR_CODE_FIELD_256 = new GenericGF(0x011D, 256, 0); // x^8 + x^4 + x^3 + x^2 + 1
		self::$DATA_MATRIX_FIELD_256 = new GenericGF(0x012D, 256, 1); // x^8 + x^5 + x^3 + x^2 + 1
		self::$AZTEC_DATA_8 = self::$DATA_MATRIX_FIELD_256;
		self::$MAXICODE_FIELD_64 = self::$AZTEC_DATA_6;
	}

	/**
	 * Implements both addition and subtraction -- they are the same in GF(size).
	 *
	 * @return sum/difference of a and b
	 */
	public static function addOrSubtract($a, $b)
	{
		return $a ^ $b;
	}

	public function getZero()
	{
		return $this->zero;
	}

	public function getOne()
	{
		return $this->one;
	}

	/**
	 * @return GenericGFPoly  the monomial representing coefficient * x^degree
	 */
	public function buildMonomial($degree, $coefficient)
	{
		if ($degree < 0) {
			throw new \InvalidArgumentException();
		}
		if ($coefficient == 0) {
			return $this->zero;
		}
		$coefficients = fill_array(0, $degree + 1, 0);//new int[degree + 1];
		$coefficients[0] = $coefficient;

		return new GenericGFPoly($this, $coefficients);
	}

	/**
	 * @return 2 to the power of a in GF(size)
	 */
	public function exp($a)
	{
		return $this->expTable[$a];
	}

	/**
	 * @return base 2 log of a in GF(size)
	 */
	public function log($a)
	{
		if ($a == 0) {
			throw new \InvalidArgumentException();
		}

		return $this->logTable[$a];
	}

	/**
	 * @return multiplicative inverse of a
	 */
	public function inverse($a)
	{
		if ($a == 0) {
			throw new \Exception();
		}

		return $this->expTable[$this->size - $this->logTable[$a] - 1];
	}

	/**
	 * @return int product of a and b in GF(size)
	 */
	public function multiply($a, $b)
	{
		if ($a == 0 || $b == 0) {
			return 0;
		}

		return $this->expTable[($this->logTable[$a] + $this->logTable[$b]) % ($this->size - 1)];
	}

	public function getSize()
	{
		return $this->size;
	}

	public function getGeneratorBase()
	{
		return $this->generatorBase;
	}

	// @Override
	public function toString()
	{
		return "GF(0x" . dechex((int)($this->primitive)) . ',' . $this->size . ')';
	}
}

GenericGF::Init();
