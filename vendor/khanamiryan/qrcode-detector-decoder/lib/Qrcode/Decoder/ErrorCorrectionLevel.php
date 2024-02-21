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

/**
 * <p>See ISO 18004:2006, 6.5.1. This enum encapsulates the four error correction levels
 * defined by the QR code standard.</p>
 *
 * @author Sean Owen
 */
class ErrorCorrectionLevel
{
	/**
  * @var \Zxing\Qrcode\Decoder\ErrorCorrectionLevel[]|null
  */
 private static ?array $FOR_BITS = null;

	public function __construct(private $bits, private $ordinal = 0)
 {
 }

	public static function Init(): void
	{
		self::$FOR_BITS = [


			new ErrorCorrectionLevel(0x00, 1), //M
			new ErrorCorrectionLevel(0x01, 0), //L
			new ErrorCorrectionLevel(0x02, 3), //H
			new ErrorCorrectionLevel(0x03, 2), //Q

		];
	}
	/** L = ~7% correction */
	//  self::$L = new ErrorCorrectionLevel(0x01);
	/** M = ~15% correction */
	//self::$M = new ErrorCorrectionLevel(0x00);
	/** Q = ~25% correction */
	//self::$Q = new ErrorCorrectionLevel(0x03);
	/** H = ~30% correction */
	//self::$H = new ErrorCorrectionLevel(0x02);
	/**
	 * @param int $bits containing the two bits encoding a QR Code's error correction level
	 *
	 * @return ErrorCorrectionLevel representing the encoded error correction level
	 */
	public static function forBits($bits)
	{
		if ($bits < 0 || $bits >= (is_countable(self::$FOR_BITS) ? count(self::$FOR_BITS) : 0)) {
			throw new \InvalidArgumentException();
		}
		$level = self::$FOR_BITS[$bits];

		// $lev = self::$$bit;
		return $level;
	}


	public function getBits()
	{
		return $this->bits;
	}

	public function toString()
	{
		return $this->bits;
	}

	public function getOrdinal()
	{
		return $this->ordinal;
	}
}

ErrorCorrectionLevel::Init();
