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

use Zxing\Common\BitSource;
use Zxing\Common\CharacterSetECI;
use Zxing\Common\DecoderResult;
use Zxing\FormatException;

/**
 * <p>QR Codes can encode text as bits in one of several modes, and can use multiple modes
 * in one QR Code. This class decodes the bits back into text.</p>
 *
 * <p>See ISO 18004:2006, 6.4.3 - 6.4.7</p>
 *
 * @author Sean Owen
 */
final class DecodedBitStreamParser
{
	/**
	 * See ISO 18004:2006, 6.4.4 Table 5
	 */
	private static array $ALPHANUMERIC_CHARS = [
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B',
		'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N',
		'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
		' ', '$', '%', '*', '+', '-', '.', '/', ':',
	];
	private static int $GB2312_SUBSET = 1;

	public static function decode(
		$bytes,
		$version,
		$ecLevel,
		$hints
	): \Zxing\Common\DecoderResult
	{
		$bits = new BitSource($bytes);
		$result = '';//new StringBuilder(50);
		$byteSegments = [];
		$symbolSequence = -1;
		$parityData = -1;

		try {
			$currentCharacterSetECI = null;
			$fc1InEffect = false;
			$mode = '';
			do {
				// While still another segment to read...
				if ($bits->available() < 4) {
					// OK, assume we're done. Really, a TERMINATOR mode should have been recorded here
					$mode = Mode::$TERMINATOR;
				} else {
					$mode = Mode::forBits($bits->readBits(4)); // mode is encoded by 4 bits
				}
				if ($mode != Mode::$TERMINATOR) {
					if ($mode == Mode::$FNC1_FIRST_POSITION || $mode == Mode::$FNC1_SECOND_POSITION) {
						// We do little with FNC1 except alter the parsed result a bit according to the spec
						$fc1InEffect = true;
					} elseif ($mode == Mode::$STRUCTURED_APPEND) {
						if ($bits->available() < 16) {
							throw FormatException::getFormatInstance();
						}
						// sequence number and parity is added later to the result metadata
						// Read next 8 bits (symbol sequence #) and 8 bits (parity data), then continue
						$symbolSequence = $bits->readBits(8);
						$parityData = $bits->readBits(8);
					} elseif ($mode == Mode::$ECI) {
						// Count doesn't apply to ECI
						$value = self::parseECIValue($bits);
						$currentCharacterSetECI = CharacterSetECI::getCharacterSetECIByValue($value);
						if ($currentCharacterSetECI == null) {
							throw FormatException::getFormatInstance();
						}
					} else {
						// First handle Hanzi mode which does not start with character count
						if ($mode == Mode::$HANZI) {
							//chinese mode contains a sub set indicator right after mode indicator
							$subset = $bits->readBits(4);
							$countHanzi = $bits->readBits($mode->getCharacterCountBits($version));
							if ($subset == self::$GB2312_SUBSET) {
								self::decodeHanziSegment($bits, $result, $countHanzi);
							}
						} else {
							// "Normal" QR code modes:
							// How many characters will follow, encoded in this mode?
							$count = $bits->readBits($mode->getCharacterCountBits($version));
							if ($mode == Mode::$NUMERIC) {
								self::decodeNumericSegment($bits, $result, $count);
							} elseif ($mode == Mode::$ALPHANUMERIC) {
								self::decodeAlphanumericSegment($bits, $result, $count, $fc1InEffect);
							} elseif ($mode == Mode::$BYTE) {
								self::decodeByteSegment($bits, $result, $count, $currentCharacterSetECI, $byteSegments, $hints);
							} elseif ($mode == Mode::$KANJI) {
								self::decodeKanjiSegment($bits, $result, $count);
							} else {
								throw FormatException::getFormatInstance();
							}
						}
					}
				}
			} while ($mode != Mode::$TERMINATOR);
		} catch (\InvalidArgumentException) {
			// from readBits() calls
			throw FormatException::getFormatInstance();
		}

		return new DecoderResult(
			$bytes,
			$result,
			empty($byteSegments) ? null : $byteSegments,
			$ecLevel == null ? null : 'L',//ErrorCorrectionLevel::toString($ecLevel),
			$symbolSequence,
			$parityData
		);
	}

	private static function parseECIValue($bits)
	{
		$firstByte = $bits->readBits(8);
		if (($firstByte & 0x80) == 0) {
			// just one byte
			return $firstByte & 0x7F;
		}
		if (($firstByte & 0xC0) == 0x80) {
			// two bytes
			$secondByte = $bits->readBits(8);

			return (($firstByte & 0x3F) << 8) | $secondByte;
		}
		if (($firstByte & 0xE0) == 0xC0) {
			// three bytes
			$secondThirdBytes = $bits->readBits(16);

			return (($firstByte & 0x1F) << 16) | $secondThirdBytes;
		}
		throw FormatException::getFormatInstance();
	}

	/**
	 * See specification GBT 18284-2000
	 */
	private static function decodeHanziSegment(
		$bits,
		&$result,
		$count
	)
	{
		// Don't crash trying to read more bits than we have available.
		if ($count * 13 > $bits->available()) {
			throw FormatException::getFormatInstance();
		}

		// Each character will require 2 bytes. Read the characters as 2-byte pairs
		// and decode as GB2312 afterwards
		$buffer = fill_array(0, 2 * $count, 0);
		$offset = 0;
		while ($count > 0) {
			// Each 13 bits encodes a 2-byte character
			$twoBytes = $bits->readBits(13);
			$assembledTwoBytes = (($twoBytes / 0x060) << 8) | ($twoBytes % 0x060);
			if ($assembledTwoBytes < 0x003BF) {
				// In the 0xA1A1 to 0xAAFE range
				$assembledTwoBytes += 0x0A1A1;
			} else {
				// In the 0xB0A1 to 0xFAFE range
				$assembledTwoBytes += 0x0A6A1;
			}
			$buffer[$offset] = (($assembledTwoBytes >> 8) & 0xFF);//(byte)
			$buffer[$offset + 1] = ($assembledTwoBytes & 0xFF);//(byte)
			$offset += 2;
			$count--;
		}
		$result .= iconv('GB2312', 'UTF-8', implode($buffer));
	}

	private static function decodeNumericSegment(
		$bits,
		&$result,
		$count
	)
	{
		// Read three digits at a time
		while ($count >= 3) {
			// Each 10 bits encodes three digits
			if ($bits->available() < 10) {
				throw FormatException::getFormatInstance();
			}
			$threeDigitsBits = $bits->readBits(10);
			if ($threeDigitsBits >= 1000) {
				throw FormatException::getFormatInstance();
			}
			$result .= (self::toAlphaNumericChar($threeDigitsBits / 100));
			$result .= (self::toAlphaNumericChar(($threeDigitsBits / 10) % 10));
			$result .= (self::toAlphaNumericChar($threeDigitsBits % 10));
			$count -= 3;
		}
		if ($count == 2) {
			// Two digits left over to read, encoded in 7 bits
			if ($bits->available() < 7) {
				throw FormatException::getFormatInstance();
			}
			$twoDigitsBits = $bits->readBits(7);
			if ($twoDigitsBits >= 100) {
				throw FormatException::getFormatInstance();
			}
			$result .= (self::toAlphaNumericChar($twoDigitsBits / 10));
			$result .= (self::toAlphaNumericChar($twoDigitsBits % 10));
		} elseif ($count == 1) {
			// One digit left over to read
			if ($bits->available() < 4) {
				throw FormatException::getFormatInstance();
			}
			$digitBits = $bits->readBits(4);
			if ($digitBits >= 10) {
				throw FormatException::getFormatInstance();
			}
			$result .= (self::toAlphaNumericChar($digitBits));
		}
	}

	private static function toAlphaNumericChar($value)
	{
		if ($value >= count(self::$ALPHANUMERIC_CHARS)) {
			throw FormatException::getFormatInstance();
		}

		return self::$ALPHANUMERIC_CHARS[$value];
	}

	private static function decodeAlphanumericSegment(
		$bits,
		&$result,
		$count,
		$fc1InEffect
	)
	{
		// Read two characters at a time
		$start = strlen((string) $result);
		while ($count > 1) {
			if ($bits->available() < 11) {
				throw FormatException::getFormatInstance();
			}
			$nextTwoCharsBits = $bits->readBits(11);
			$result .= (self::toAlphaNumericChar($nextTwoCharsBits / 45));
			$result .= (self::toAlphaNumericChar($nextTwoCharsBits % 45));
			$count -= 2;
		}
		if ($count == 1) {
			// special case: one character left
			if ($bits->available() < 6) {
				throw FormatException::getFormatInstance();
			}
			$result .= self::toAlphaNumericChar($bits->readBits(6));
		}
		// See section 6.4.8.1, 6.4.8.2
		if ($fc1InEffect) {
			// We need to massage the result a bit if in an FNC1 mode:
			for ($i = $start; $i < strlen((string) $result); $i++) {
				if ($result[$i] == '%') {
					if ($i < strlen((string) $result) - 1 && $result[$i + 1] == '%') {
						// %% is rendered as %
						$result = substr_replace($result, '', $i + 1, 1);//deleteCharAt(i + 1);
					} else {
						// In alpha mode, % should be converted to FNC1 separator 0x1D
						$result . setCharAt($i, chr(0x1D));
					}
				}
			}
		}
	}

	private static function decodeByteSegment(
		$bits,
		&$result,
		$count,
		$currentCharacterSetECI,
		&$byteSegments,
		$hints
	)
	{
		// Don't crash trying to read more bits than we have available.
		if (8 * $count > $bits->available()) {
			throw FormatException::getFormatInstance();
		}

		$readBytes = fill_array(0, $count, 0);
		for ($i = 0; $i < $count; $i++) {
			$readBytes[$i] = $bits->readBits(8);//(byte)
		}
		$text = implode(array_map('chr', $readBytes));
		$encoding = '';
		if ($currentCharacterSetECI == null) {
			// The spec isn't clear on this mode; see
			// section 6.4.5: t does not say which encoding to assuming
			// upon decoding. I have seen ISO-8859-1 used as well as
			// Shift_JIS -- without anything like an ECI designator to
			// give a hint.

			$encoding = mb_detect_encoding($text, $hints);
		} else {
			$encoding = $currentCharacterSetECI->name();
		}
		//  $result.= mb_convert_encoding($text ,$encoding);//(new String(readBytes, encoding));
		$result .= $text;//(new String(readBytes, encoding));

		$byteSegments = array_merge($byteSegments, $readBytes);
	}

	private static function decodeKanjiSegment(
		$bits,
		&$result,
		$count
	)
	{
		// Don't crash trying to read more bits than we have available.
		if ($count * 13 > $bits->available()) {
			throw FormatException::getFormatInstance();
		}

		// Each character will require 2 bytes. Read the characters as 2-byte pairs
		// and decode as Shift_JIS afterwards
		$buffer = [0, 2 * $count, 0];
		$offset = 0;
		while ($count > 0) {
			// Each 13 bits encodes a 2-byte character
			$twoBytes = $bits->readBits(13);
			$assembledTwoBytes = (($twoBytes / 0x0C0) << 8) | ($twoBytes % 0x0C0);
			if ($assembledTwoBytes < 0x01F00) {
				// In the 0x8140 to 0x9FFC range
				$assembledTwoBytes += 0x08140;
			} else {
				// In the 0xE040 to 0xEBBF range
				$assembledTwoBytes += 0x0C140;
			}
			$buffer[$offset] = ($assembledTwoBytes >> 8);//(byte)
			$buffer[$offset + 1] = $assembledTwoBytes; //(byte)
			$offset += 2;
			$count--;
		}
		// Shift_JIS may not be supported in some environments:

		$result .= iconv('shift-jis', 'utf-8', implode($buffer));
	}

	private function DecodedBitStreamParser(): void
	{
	}
}
