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

namespace Zxing\Qrcode\Decoder;

use Zxing\Common\BitMatrix;

/**
 * <p>Encapsulates data masks for the data bits in a QR code, per ISO 18004:2006 6.8. Implementations
 * of this class can un-mask a raw BitMatrix. For simplicity, they will unmask the entire BitMatrix,
 * including areas used for finder patterns, timing patterns, etc. These areas should be unused
 * after the point they are unmasked anyway.</p>
 *
 * <p>Note that the diagram in section 6.8.1 is misleading since it indicates that i is column position
 * and j is row position. In fact, as the text says, i is row position and j is column position.</p>
 *
 * @author Sean Owen
 */
abstract class DataMask
{
	/**
	 * See ISO 18004:2006 6.8.1
	 */
	private static array $DATA_MASKS = [];

	public function __construct()
	{
	}

	public static function Init(): void
	{
		self::$DATA_MASKS = [
			new DataMask000(),
			new DataMask001(),
			new DataMask010(),
			new DataMask011(),
			new DataMask100(),
			new DataMask101(),
			new DataMask110(),
			new DataMask111(),
		];
	}

	/**
	 * @param a $reference value between 0 and 7 indicating one of the eight possible
	 *                  data mask patterns a QR Code may use
	 *
	 * @return DataMask encapsulating the data mask pattern
	 */
	public static function forReference($reference)
	{
		if ($reference < 0 || $reference > 7) {
			throw new \InvalidArgumentException();
		}

		return self::$DATA_MASKS[$reference];
	}

	/**
	 * <p>Implementations of this method reverse the data masking process applied to a QR Code and
	 * make its bits ready to read.</p>
	 *
	 * @param representation      $bits of QR Code bits
	 * @param dimension $dimension of QR Code, represented by bits, being unmasked
	 */
	final public function unmaskBitMatrix($bits, $dimension): void
	{
		for ($i = 0; $i < $dimension; $i++) {
			for ($j = 0; $j < $dimension; $j++) {
				if ($this->isMasked($i, $j)) {
					$bits->flip($j, $i);
				}
			}
		}
	}

	abstract public function isMasked($i, $j);
}

DataMask::Init();

/**
 * 000: mask bits for which (x + y) mod 2 == 0
 */
final class DataMask000 extends DataMask
{
	// @Override
	public function isMasked($i, $j)
	{
		return (($i + $j) & 0x01) == 0;
	}
}

/**
 * 001: mask bits for which x mod 2 == 0
 */
final class DataMask001 extends DataMask
{
	//@Override
	public function isMasked($i, $j)
	{
		return ($i & 0x01) == 0;
	}
}

/**
 * 010: mask bits for which y mod 3 == 0
 */
final class DataMask010 extends DataMask
{
	//@Override
	public function isMasked($i, $j)
	{
		return $j % 3 == 0;
	}
}

/**
 * 011: mask bits for which (x + y) mod 3 == 0
 */
final class DataMask011 extends DataMask
{
	//@Override
	public function isMasked($i, $j)
	{
		return ($i + $j) % 3 == 0;
	}
}

/**
 * 100: mask bits for which (x/2 + y/3) mod 2 == 0
 */
final class DataMask100 extends DataMask
{
	//@Override
	public function isMasked($i, $j)
	{
		return (int)(((int)($i / 2) + (int)($j / 3)) & 0x01) == 0;
	}
}

/**
 * 101: mask bits for which xy mod 2 + xy mod 3 == 0
 */
final class DataMask101 extends DataMask
{
	//@Override
	public function isMasked($i, $j)
	{
		$temp = $i * $j;

		return ($temp & 0x01) + ($temp % 3) == 0;
	}
}

/**
 * 110: mask bits for which (xy mod 2 + xy mod 3) mod 2 == 0
 */
final class DataMask110 extends DataMask
{
	//@Override
	public function isMasked($i, $j)
	{
		$temp = $i * $j;

		return ((($temp & 0x01) + ($temp % 3)) & 0x01) == 0;
	}
}

/**
 * 111: mask bits for which ((x+y)mod 2 + xy mod 3) mod 2 == 0
 */
final class DataMask111 extends DataMask
{
	//@Override
	public function isMasked($i, $j)
	{
		return (((($i + $j) & 0x01) + (($i * $j) % 3)) & 0x01) == 0;
	}
}
