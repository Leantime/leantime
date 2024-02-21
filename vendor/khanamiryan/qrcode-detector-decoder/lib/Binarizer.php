<?php
/*
* Copyright 2009 ZXing authors
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

namespace Zxing;

use Zxing\Common\BitArray;
use Zxing\Common\BitMatrix;

/**
 * This class hierarchy provides a set of methods to convert luminance data to 1 bit data.
 * It allows the algorithm to vary polymorphically, for example allowing a very expensive
 * thresholding technique for servers and a fast one for mobile. It also permits the implementation
 * to vary, e.g. a JNI version for Android and a Java fallback version for other platforms.
 *
 * @author dswitkin@google.com (Daniel Switkin)
 */
abstract class Binarizer
{
	protected function __construct(private $source)
 {
 }

	/**
	 * @return LuminanceSource
	 */
	final public function getLuminanceSource()
	{
		return $this->source;
	}

	/**
	 * Converts one row of luminance data to 1 bit data. May actually do the conversion, or return
	 * cached data. Callers should assume this method is expensive and call it as seldom as possible.
	 * This method is intended for decoding 1D barcodes and may choose to apply sharpening.
	 * For callers which only examine one row of pixels at a time, the same BitArray should be reused
	 * and passed in with each call for performance. However it is legal to keep more than one row
	 * at a time if needed.
	 *
	 * @param $y   The row to fetch, which must be in [0, bitmap height)
	 * @param An $row optional preallocated array. If null or too small, it will be ignored.
	 *            If used, the Binarizer will call BitArray.clear(). Always use the returned object.
	 *
	 * @return array The array of bits for this row (true means black).
	 * @throws NotFoundException if row can't be binarized
	 */
	abstract public function getBlackRow($y, $row);

	/**
	 * Converts a 2D array of luminance data to 1 bit data. As above, assume this method is expensive
	 * and do not call it repeatedly. This method is intended for decoding 2D barcodes and may or
	 * may not apply sharpening. Therefore, a row from this matrix may not be identical to one
	 * fetched using getBlackRow(), so don't mix and match between them.
	 *
	 * @return BitMatrix The 2D array of bits for the image (true means black).
	 * @throws NotFoundException if image can't be binarized to make a matrix
	 */
	abstract public function getBlackMatrix();

	/**
	 * Creates a new object with the same type as this Binarizer implementation, but with pristine
	 * state. This is needed because Binarizer implementations may be stateful, e.g. keeping a cache
	 * of 1 bit data. See Effective Java for why we can't use Java's clone() method.
	 *
	 * @param $source The LuminanceSource this Binarizer will operate on.
	 *
	 * @return Binarizer A new concrete Binarizer implementation object.
	 */
	abstract public function createBinarizer($source);

	final public function getWidth()
	{
		return $this->source->getWidth();
	}

	final public function getHeight()
	{
		return $this->source->getHeight();
	}
}
