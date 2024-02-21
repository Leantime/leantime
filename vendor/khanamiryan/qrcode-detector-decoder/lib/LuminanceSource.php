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

/**
 * The purpose of this class hierarchy is to abstract different bitmap implementations across
 * platforms into a standard interface for requesting greyscale luminance values. The interface
 * only provides immutable methods; therefore crop and rotation create copies. This is to ensure
 * that one Reader does not modify the original luminance source and leave it in an unknown state
 * for other Readers in the chain.
 *
 * @author dswitkin@google.com (Daniel Switkin)
 */
abstract class LuminanceSource
{
    public function __construct(private $width, private $height)
    {
    }

    /**
     * Fetches luminance data for the underlying bitmap. Values should be fetched using:
     * {@code int luminance = array[y * width + x] & 0xff}
     *
     * @return A row-major 2D array of luminance values. Do not use result.length as it may be
     *         larger than width * height bytes on some platforms. Do not modify the contents
     *         of the result.
     */
    abstract public function getMatrix();

    /**
     * @return float The width of the bitmap.
     */
    final public function getWidth(): float
    {
        return $this->width;
    }

    /**
     * @return float The height of the bitmap.
     */
    final public function getHeight(): float
    {
        return $this->height;
    }

    /**
     * @return bool Whether this subclass supports cropping.
     */
    public function isCropSupported(): bool
    {
        return false;
    }

    /**
     * Returns a new object with cropped image data. Implementations may keep a reference to the
     * original data rather than a copy. Only callable if isCropSupported() is true.
     *
     * @param $left   The left coordinate, which must be in [0,getWidth())
     * @param $top    The top coordinate, which must be in [0,getHeight())
     * @param $width  The width of the rectangle to crop.
     * @param $height The height of the rectangle to crop.
     *
     * @return mixed A cropped version of this object.
     */
    public function crop($left, $top, $width, $height)
    {
        throw new \Exception("This luminance source does not support cropping.");
    }

    /**
     * @return bool Whether this subclass supports counter-clockwise rotation.
     */
    public function isRotateSupported(): bool
    {
        return false;
    }

    /**
     * @return a wrapper of this {@code LuminanceSource} which inverts the luminances it returns -- black becomes
     *  white and vice versa, and each value becomes (255-value).
     */
    // public function invert()
    // {
    // 	return new InvertedLuminanceSource($this);
    // }

    /**
     * Returns a new object with rotated image data by 90 degrees counterclockwise.
     * Only callable if {@link #isRotateSupported()} is true.
     *
     * @return mixed A rotated version of this object.
     */
    public function rotateCounterClockwise()
    {
        throw new \Exception("This luminance source does not support rotation by 90 degrees.");
    }

    /**
     * Returns a new object with rotated image data by 45 degrees counterclockwise.
     * Only callable if {@link #isRotateSupported()} is true.
     *
     * @return mixed A rotated version of this object.
     */
    public function rotateCounterClockwise45()
    {
        throw new \Exception("This luminance source does not support rotation by 45 degrees.");
    }

    final public function toString()
    {
        $row = [];
        $result = '';
        for ($y = 0; $y < $this->height; $y++) {
            $row = $this->getRow($y, $row);
            for ($x = 0; $x < $this->width; $x++) {
                $luminance = $row[$x] & 0xFF;
                $c = '';
                if ($luminance < 0x40) {
                    $c = '#';
                } elseif ($luminance < 0x80) {
                    $c = '+';
                } elseif ($luminance < 0xC0) {
                    $c = '.';
                } else {
                    $c = ' ';
                }
                $result .= ($c);
            }
            $result .= ('\n');
        }

        return $result;
    }

    /**
     * Fetches one row of luminance data from the underlying platform's bitmap. Values range from
     * 0 (black) to 255 (white). Because Java does not have an unsigned byte type, callers will have
     * to bitwise and with 0xff for each value. It is preferable for implementations of this method
     * to only fetch this row rather than the whole image, since no 2D Readers may be installed and
     * getMatrix() may never be called.
     *
     * @param $y   ; The row to fetch, which must be in [0,getHeight())
     * @param $row ; An optional preallocated array. If null or too small, it will be ignored.
     *             Always use the returned object, and ignore the .length of the array.
     *
     * @return array
     * An array containing the luminance data.
     */
    abstract public function getRow($y, $row);
}
