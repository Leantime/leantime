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

namespace Zxing\Qrcode;

use Zxing\BinaryBitmap;
use Zxing\ChecksumException;
use Zxing\Common\BitMatrix;
use Zxing\FormatException;
use Zxing\NotFoundException;
use Zxing\Qrcode\Decoder\Decoder;
use Zxing\Qrcode\Detector\Detector;
use Zxing\Reader;
use Zxing\Result;

/**
 * This implementation can detect and decode QR Codes in an image.
 *
 * @author Sean Owen
 */
class QRCodeReader implements Reader
{
	private static array $NO_POINTS = [];
	private readonly \Zxing\Qrcode\Decoder\Decoder $decoder;

	public function __construct()
	{
		$this->decoder = new Decoder();
	}

	/**
  * @param null         $hints
  *
  * @return Result
  * @throws \Zxing\FormatException
  * @throws \Zxing\NotFoundException
  */
 public function decode(BinaryBitmap $image, $hints = null)
	{
		$decoderResult = null;
		if ($hints !== null && $hints['PURE_BARCODE']) {
			$bits = self::extractPureBits($image->getBlackMatrix());
			$decoderResult = $this->decoder->decode($bits, $hints);
			$points = self::$NO_POINTS;
		} else {
			$detector = new Detector($image->getBlackMatrix());
			$detectorResult = $detector->detect($hints);

			$decoderResult = $this->decoder->decode($detectorResult->getBits(), $hints);
			$points = $detectorResult->getPoints();
		}
		$result = new Result($decoderResult->getText(), $decoderResult->getRawBytes(), $points, 'QR_CODE');//BarcodeFormat.QR_CODE

		$byteSegments = $decoderResult->getByteSegments();
		if ($byteSegments !== null) {
			$result->putMetadata('BYTE_SEGMENTS', $byteSegments);//ResultMetadataType.BYTE_SEGMENTS
		}
		$ecLevel = $decoderResult->getECLevel();
		if ($ecLevel !== null) {
			$result->putMetadata('ERROR_CORRECTION_LEVEL', $ecLevel);//ResultMetadataType.ERROR_CORRECTION_LEVEL
		}
		if ($decoderResult->hasStructuredAppend()) {
			$result->putMetadata(
				'STRUCTURED_APPEND_SEQUENCE',//ResultMetadataType.STRUCTURED_APPEND_SEQUENCE
				$decoderResult->getStructuredAppendSequenceNumber()
			);
			$result->putMetadata(
				'STRUCTURED_APPEND_PARITY',//ResultMetadataType.STRUCTURED_APPEND_PARITY
				$decoderResult->getStructuredAppendParity()
			);
		}

		return $result;
	}

	/**
	 * Locates and decodes a QR code in an image.
	 *
	 * @return a String representing the content encoded by the QR code
	 * @throws NotFoundException if a QR code cannot be found
	 * @throws FormatException if a QR code cannot be decoded
	 * @throws ChecksumException if error correction fails
	 */

	/**
	 * This method detects a code in a "pure" image -- that is, pure monochrome image
	 * which contains only an unrotated, unskewed, image of a code, with some white border
	 * around it. This is a specialized method that works exceptionally fast in this special
	 * case.
	 *
	 * @see com.google.zxing.datamatrix.DataMatrixReader#extractPureBits(BitMatrix)
	 */
	private static function extractPureBits(BitMatrix $image)
	{
		$leftTopBlack = $image->getTopLeftOnBit();
		$rightBottomBlack = $image->getBottomRightOnBit();
		if ($leftTopBlack === null || $rightBottomBlack == null) {
			throw NotFoundException::getNotFoundInstance();
		}

		$moduleSize = self::moduleSize($leftTopBlack, $image);

		$top = $leftTopBlack[1];
		$bottom = $rightBottomBlack[1];
		$left = $leftTopBlack[0];
		$right = $rightBottomBlack[0];

		// Sanity check!
		if ($left >= $right || $top >= $bottom) {
			throw NotFoundException::getNotFoundInstance();
		}

		if ($bottom - $top != $right - $left) {
			// Special case, where bottom-right module wasn't black so we found something else in the last row
			// Assume it's a square, so use height as the width
			$right = $left + ($bottom - $top);
		}

		$matrixWidth = round(($right - $left + 1) / $moduleSize);
		$matrixHeight = round(($bottom - $top + 1) / $moduleSize);
		if ($matrixWidth <= 0 || $matrixHeight <= 0) {
			throw NotFoundException::getNotFoundInstance();
		}
		if ($matrixHeight != $matrixWidth) {
			// Only possibly decode square regions
			throw NotFoundException::getNotFoundInstance();
		}

		// Push in the "border" by half the module width so that we start
		// sampling in the middle of the module. Just in case the image is a
		// little off, this will help recover.
		$nudge = (int)($moduleSize / 2.0);// $nudge = (int) ($moduleSize / 2.0f);
		$top += $nudge;
		$left += $nudge;

		// But careful that this does not sample off the edge
		// "right" is the farthest-right valid pixel location -- right+1 is not necessarily
		// This is positive by how much the inner x loop below would be too large
		$nudgedTooFarRight = $left + (int)(($matrixWidth - 1) * $moduleSize) - $right;
		if ($nudgedTooFarRight > 0) {
			if ($nudgedTooFarRight > $nudge) {
				// Neither way fits; abort
				throw NotFoundException::getNotFoundInstance();
			}
			$left -= $nudgedTooFarRight;
		}
		// See logic above
		$nudgedTooFarDown = $top + (int)(($matrixHeight - 1) * $moduleSize) - $bottom;
		if ($nudgedTooFarDown > 0) {
			if ($nudgedTooFarDown > $nudge) {
				// Neither way fits; abort
				throw NotFoundException::getNotFoundInstance();
			}
			$top -= $nudgedTooFarDown;
		}

		// Now just read off the bits
		$bits = new BitMatrix($matrixWidth, $matrixHeight);
		for ($y = 0; $y < $matrixHeight; $y++) {
			$iOffset = $top + (int)($y * $moduleSize);
			for ($x = 0; $x < $matrixWidth; $x++) {
				if ($image->get($left + (int)($x * $moduleSize), $iOffset)) {
					$bits->set($x, $y);
				}
			}
		}

		return $bits;
	}

	private static function moduleSize($leftTopBlack, BitMatrix $image)
	{
		$height = $image->getHeight();
		$width = $image->getWidth();
		$x = $leftTopBlack[0];
		$y = $leftTopBlack[1];
		/*$x           = $leftTopBlack[0];
		$y           = $leftTopBlack[1];*/
		$inBlack = true;
		$transitions = 0;
		while ($x < $width && $y < $height) {
			if ($inBlack != $image->get($x, $y)) {
				if (++$transitions == 5) {
					break;
				}
				$inBlack = !$inBlack;
			}
			$x++;
			$y++;
		}
		if ($x == $width || $y == $height) {
			throw NotFoundException::getNotFoundInstance();
		}

		return ($x - $leftTopBlack[0]) / 7.0; //return ($x - $leftTopBlack[0]) / 7.0f;
	}

	public function reset(): void
	{
		// do nothing
	}

	final protected function getDecoder()
	{
		return $this->decoder;
	}
}
