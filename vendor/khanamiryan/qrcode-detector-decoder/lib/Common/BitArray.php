<?php
/**
 * Created by PhpStorm.
 * User: Ashot
 * Date: 3/25/15
 * Time: 11:51
 */

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
 * <p>A simple, fast array of bits, represented compactly by an array of ints internally.</p>
 *
 * @author Sean Owen
 */

final class BitArray
{
	/**
  * @var mixed[]|mixed|int[]|null
  */
 private $bits;
	/**
  * @var mixed|null
  */
 private $size;


	public function __construct($bits = [], $size = 0)
	{
		if (!$bits && !$size) {
			$this->$size = 0;
			$this->bits = [];
		} elseif ($bits && !$size) {
			$this->size = $bits;
			$this->bits = self::makeArray($bits);
		} else {
			$this->bits = $bits;
			$this->size = $size;
		}
	}

	private static function makeArray($size)
	{
		return [];
	}

	public function getSize()
	{
		return $this->size;
	}

	public function getSizeInBytes()
	{
		return ($this->size + 7) / 8;
	}

	/**
	 * Sets bit i.
	 *
	 * @param bit $i to set
	 */
	public function set($i): void
	{
		$this->bits[(int)($i / 32)] |= 1 << ($i & 0x1F);
		$this->bits[(int)($i / 32)] = ($this->bits[(int)($i / 32)]);
	}

	/**
	 * Flips bit i.
	 *
	 * @param bit $i to set
	 */
	public function flip($i): void
	{
		$this->bits[(int)($i / 32)] ^= 1 << ($i & 0x1F);
		$this->bits[(int)($i / 32)] = ($this->bits[(int)($i / 32)]);
	}

	/**
	 * @param first $from bit to check
	 *
	 * @return index of first bit that is set, starting from the given index, or size if none are set
	 *  at or beyond this given index
	 * @see #getNextUnset(int)
	 */
	public function getNextSet($from)
	{
		if ($from >= $this->size) {
			return $this->size;
		}
		$bitsOffset = (int)($from / 32);
		$currentBits = (int)$this->bits[$bitsOffset];
		// mask off lesser bits first
		$currentBits &= ~((1 << ($from & 0x1F)) - 1);
		while ($currentBits == 0) {
			if (++$bitsOffset == (is_countable($this->bits) ? count($this->bits) : 0)) {
				return $this->size;
			}
			$currentBits = $this->bits[$bitsOffset];
		}
		$result = ($bitsOffset * 32) + numberOfTrailingZeros($currentBits); //numberOfTrailingZeros

		return $result > $this->size ? $this->size : $result;
	}

	/**
	 * @param index $from to start looking for unset bit
	 *
	 * @return index of next unset bit, or {@code size} if none are unset until the end
	 * @see #getNextSet(int)
	 */
	public function getNextUnset($from)
	{
		if ($from >= $this->size) {
			return $this->size;
		}
		$bitsOffset = (int)($from / 32);
		$currentBits = ~$this->bits[$bitsOffset];
		// mask off lesser bits first
		$currentBits &= ~((1 << ($from & 0x1F)) - 1);
		while ($currentBits == 0) {
			if (++$bitsOffset == (is_countable($this->bits) ? count($this->bits) : 0)) {
				return $this->size;
			}
			$currentBits = (~$this->bits[$bitsOffset]);
		}
		$result = ($bitsOffset * 32) + numberOfTrailingZeros($currentBits);

		return $result > $this->size ? $this->size : $result;
	}

	/**
	 * Sets a block of 32 bits, starting at bit i.
	 *
	 * @param first       $i bit to set
	 * @param the $newBits new value of the next 32 bits. Note again that the least-significant bit
	 *                corresponds to bit i, the next-least-significant to i+1, and so on.
	 */
	public function setBulk($i, $newBits): void
	{
		$this->bits[(int)($i / 32)] = $newBits;
	}

	/**
	 * Sets a range of bits.
	 *
	 * @param start $start of range, inclusive.
	 * @param end   $end of range, exclusive
	 */
	public function setRange($start, $end)
	{
		if ($end < $start) {
			throw new \InvalidArgumentException();
		}
		if ($end == $start) {
			return;
		}
		$end--; // will be easier to treat this as the last actually set bit -- inclusive
		$firstInt = (int)($start / 32);
		$lastInt = (int)($end / 32);
		for ($i = $firstInt; $i <= $lastInt; $i++) {
			$firstBit = $i > $firstInt ? 0 : $start & 0x1F;
			$lastBit = $i < $lastInt ? 31 : $end & 0x1F;
			$mask = 0;
			if ($firstBit == 0 && $lastBit == 31) {
				$mask = -1;
			} else {
				$mask = 0;
				for ($j = $firstBit; $j <= $lastBit; $j++) {
					$mask |= 1 << $j;
				}
			}
			$this->bits[$i] = ($this->bits[$i] | $mask);
		}
	}

	/**
	 * Clears all bits (sets to false).
	 */
	public function clear(): void
	{
		$max = is_countable($this->bits) ? count($this->bits) : 0;
		for ($i = 0; $i < $max; $i++) {
			$this->bits[$i] = 0;
		}
	}

	/**
	 * Efficient method to check if a range of bits is set, or not set.
	 *
	 * @param start $start of range, inclusive.
	 * @param end   $end of range, exclusive
	 * @param if $value true, checks that bits in range are set, otherwise checks that they are not set
	 *
	 * @return true iff all bits are set or not set in range, according to value argument
	 * @throws InvalidArgumentException if end is less than or equal to start
	 */
	public function isRange($start, $end, $value)
	{
		if ($end < $start) {
			throw new \InvalidArgumentException();
		}
		if ($end == $start) {
			return true; // empty range matches
		}
		$end--; // will be easier to treat this as the last actually set bit -- inclusive
		$firstInt = (int)($start / 32);
		$lastInt = (int)($end / 32);
		for ($i = $firstInt; $i <= $lastInt; $i++) {
			$firstBit = $i > $firstInt ? 0 : $start & 0x1F;
			$lastBit = $i < $lastInt ? 31 : $end & 0x1F;
			$mask = 0;
			if ($firstBit == 0 && $lastBit == 31) {
				$mask = -1;
			} else {
				$mask = 0;
				for ($j = $firstBit; $j <= $lastBit; $j++) {
					$mask = ($mask | (1 << $j));
				}
			}

			// Return false if we're looking for 1s and the masked bits[i] isn't all 1s (that is,
			// equals the mask, or we're looking for 0s and the masked portion is not all 0s
			if (($this->bits[$i] & $mask) != ($value ? $mask : 0)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Appends the least-significant bits, from value, in order from most-significant to
	 * least-significant. For example, appending 6 bits from 0x000001E will append the bits
	 * 0, 1, 1, 1, 1, 0 in that order.
	 *
	 * @param $value   {@code int} containing bits to append
	 * @param bits $numBits from value to append
	 */
	public function appendBits($value, $numBits)
	{
		if ($numBits < 0 || $numBits > 32) {
			throw new \InvalidArgumentException("Num bits must be between 0 and 32");
		}
		$this->ensureCapacity($this->size + $numBits);
		for ($numBitsLeft = $numBits; $numBitsLeft > 0; $numBitsLeft--) {
			$this->appendBit((($value >> ($numBitsLeft - 1)) & 0x01) == 1);
		}
	}

	private function ensureCapacity($size): void
	{
		if ($size > (is_countable($this->bits) ? count($this->bits) : 0) * 32) {
			$newBits = self::makeArray($size);
			$newBits = arraycopy($this->bits, 0, $newBits, 0, is_countable($this->bits) ? count($this->bits) : 0);
			$this->bits = $newBits;
		}
	}

	public function appendBit($bit): void
	{
		$this->ensureCapacity($this->size + 1);
		if ($bit) {
			$this->bits[(int)($this->size / 32)] |= 1 << ($this->size & 0x1F);
		}
		$this->size++;
	}

	public function appendBitArray($other): void
	{
		$otherSize = $other->size;
		$this->ensureCapacity($this->size + $otherSize);
		for ($i = 0; $i < $otherSize; $i++) {
			$this->appendBit($other->get($i));
		}
	}

	public function _xor($other)
	{
		if ((is_countable($this->bits) ? count($this->bits) : 0) !== (is_countable($other->bits) ? count($other->bits) : 0)) {
			throw new \InvalidArgumentException("Sizes don't match");
		}
		$count = is_countable($this->bits) ? count($this->bits) : 0;
		for ($i = 0; $i < $count; $i++) {
			// The last byte could be incomplete (i.e. not have 8 bits in
			// it) but there is no problem since 0 XOR 0 == 0.
			$this->bits[$i] ^= $other->bits[$i];
		}
	}

	/**
	 *
	 * @param first $bitOffset bit to start writing
	 * @param array     $array to write into. Bytes are written most-significant byte first. This is the opposite
	 *                  of the internal representation, which is exposed by {@link #getBitArray()}
	 * @param position    $offset in array to start writing
	 * @param how  $numBytes many bytes to write
	 */
	public function toBytes($bitOffset, &$array, $offset, $numBytes): void
	{
		for ($i = 0; $i < $numBytes; $i++) {
			$theByte = 0;
			for ($j = 0; $j < 8; $j++) {
				if ($this->get($bitOffset)) {
					$theByte |= 1 << (7 - $j);
				}
				$bitOffset++;
			}
			$array[(int)($offset + $i)] = $theByte;
		}
	}

	/**
	 * @param $i ; bit to get
	 *
	 * @return true iff bit i is set
	 */
	public function get($i)
	{
		$key = (int)($i / 32);

		return ($this->bits[$key] & (1 << ($i & 0x1F))) != 0;
	}

	/**
	 * @return array underlying array of ints. The first element holds the first 32 bits, and the least
	 *         significant bit is bit 0.
	 */
	public function getBitArray()
	{
		return $this->bits;
	}

	/**
	 * Reverses all bits in the array.
	 */
	public function reverse(): void
	{
		$newBits = [];
		// reverse all int's first
		$len = (($this->size - 1) / 32);
		$oldBitsLen = $len + 1;
		for ($i = 0; $i < $oldBitsLen; $i++) {
			$x = $this->bits[$i];/*
			 $x = (($x >>  1) & 0x55555555L) | (($x & 0x55555555L) <<  1);
				  $x = (($x >>  2) & 0x33333333L) | (($x & 0x33333333L) <<  2);
				  $x = (($x >>  4) & 0x0f0f0f0fL) | (($x & 0x0f0f0f0fL) <<  4);
				  $x = (($x >>  8) & 0x00ff00ffL) | (($x & 0x00ff00ffL) <<  8);
				  $x = (($x >> 16) & 0x0000ffffL) | (($x & 0x0000ffffL) << 16);*/
			$x = (($x >> 1) & 0x55555555) | (($x & 0x55555555) << 1);
			$x = (($x >> 2) & 0x33333333) | (($x & 0x33333333) << 2);
			$x = (($x >> 4) & 0x0f0f0f0f) | (($x & 0x0f0f0f0f) << 4);
			$x = (($x >> 8) & 0x00ff00ff) | (($x & 0x00ff00ff) << 8);
			$x = (($x >> 16) & 0x0000ffff) | (($x & 0x0000ffff) << 16);
			$newBits[(int)$len - $i] = (int)$x;
		}
		// now correct the int's if the bit size isn't a multiple of 32
		if ($this->size != $oldBitsLen * 32) {
			$leftOffset = $oldBitsLen * 32 - $this->size;
			$mask = 1;
			for ($i = 0; $i < 31 - $leftOffset; $i++) {
				$mask = ($mask << 1) | 1;
			}
			$currentInt = ($newBits[0] >> $leftOffset) & $mask;
			for ($i = 1; $i < $oldBitsLen; $i++) {
				$nextInt = $newBits[$i];
				$currentInt |= $nextInt << (32 - $leftOffset);
				$newBits[(int)($i) - 1] = $currentInt;
				$currentInt = ($nextInt >> $leftOffset) & $mask;
			}
			$newBits[(int)($oldBitsLen) - 1] = $currentInt;
		}
		//        $bits = $newBits;
	}

	public function equals($o)
	{
		if (!($o instanceof BitArray)) {
			return false;
		}
		$other = $o;

		return $this->size == $other->size && $this->bits === $other->bits;
	}

	public function hashCode()
	{
		return 31 * $this->size + hashCode($this->bits);
	}

	public function toString()
	{
		$result = '';
		for ($i = 0; $i < $this->size; $i++) {
			if (($i & 0x07) == 0) {
				$result .= ' ';
			}
			$result .= ($this->get($i) ? 'X' : '.');
		}

		return (string)$result;
	}

	public function _clone(): \Zxing\Common\BitArray
	{
		return new BitArray($this->bits, $this->size);
	}
}
