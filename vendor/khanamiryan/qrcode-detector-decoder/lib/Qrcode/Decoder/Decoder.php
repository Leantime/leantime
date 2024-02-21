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

use Zxing\ChecksumException;
use Zxing\Common\BitMatrix;
use Zxing\Common\Reedsolomon\GenericGF;
use Zxing\Common\Reedsolomon\ReedSolomonDecoder;
use Zxing\Common\Reedsolomon\ReedSolomonException;
use Zxing\FormatException;

/**
 * <p>The main class which implements QR Code decoding -- as opposed to locating and extracting
 * the QR Code from an image.</p>
 *
 * @author Sean Owen
 */
final class Decoder
{
	private readonly \Zxing\Common\Reedsolomon\ReedSolomonDecoder $rsDecoder;

	public function __construct()
	{
		$this->rsDecoder = new ReedSolomonDecoder(GenericGF::$QR_CODE_FIELD_256);
	}

	public function decode($variable, $hints = null)
	{
		if (is_array($variable)) {
			return $this->decodeImage($variable, $hints);
		} elseif ($variable instanceof BitMatrix) {
			return $this->decodeBits($variable, $hints);
		} elseif ($variable instanceof BitMatrixParser) {
			return $this->decodeParser($variable, $hints);
		}
		die('decode error Decoder.php');
	}

	/**
	 * <p>Convenience method that can decode a QR Code represented as a 2D array of booleans.
	 * "true" is taken to mean a black module.</p>
	 *
	 * @param array $image booleans representing white/black QR Code modules
	 * @param       decoding  $hints hints that should be used to influence decoding
	 *
	 * @return text and bytes encoded within the QR Code
	 * @throws FormatException if the QR Code cannot be decoded
	 * @throws ChecksumException if error correction fails
	 */
	public function decodeImage($image, $hints = null)
	{
		$dimension = count($image);
		$bits = new BitMatrix($dimension);
		for ($i = 0; $i < $dimension; $i++) {
			for ($j = 0; $j < $dimension; $j++) {
				if ($image[$i][$j]) {
					$bits->set($j, $i);
				}
			}
		}

		return $this->decode($bits, $hints);
	}


	/**
	 * <p>Decodes a QR Code represented as a {@link BitMatrix}. A 1 or "true" is taken to mean a black module.</p>
	 *
	 * @param BitMatrix $bits booleans representing white/black QR Code modules
	 * @param           decoding $hints hints that should be used to influence decoding
	 *
	 * @return text and bytes encoded within the QR Code
	 * @throws FormatException if the QR Code cannot be decoded
	 * @throws ChecksumException if error correction fails
	 */
	public function decodeBits($bits, $hints = null)
	{

// Construct a parser and read version, error-correction level
		$parser = new BitMatrixParser($bits);
		$fe = null;
		$ce = null;
		try {
			return $this->decode($parser, $hints);
		} catch (FormatException $e) {
			$fe = $e;
		} catch (ChecksumException $e) {
			$ce = $e;
		}

		try {

			// Revert the bit matrix
			$parser->remask();

			// Will be attempting a mirrored reading of the version and format info.
			$parser->setMirror(true);

			// Preemptively read the version.
			$parser->readVersion();

			// Preemptively read the format information.
			$parser->readFormatInformation();

			/*
			* Since we're here, this means we have successfully detected some kind
			* of version and format information when mirrored. This is a good sign,
			* that the QR code may be mirrored, and we should try once more with a
			* mirrored content.
			*/
			// Prepare for a mirrored reading.
			$parser->mirror();

			$result = $this->decode($parser, $hints);

			// Success! Notify the caller that the code was mirrored.
			$result->setOther(new QRCodeDecoderMetaData(true));

			return $result;
		} catch (FormatException $e) {// catch (FormatException | ChecksumException e) {
			// Throw the exception from the original reading
			if ($fe != null) {
				throw $fe;
			}
			if ($ce != null) {
				throw $ce;
			}
			throw $e;
		}
	}

	private function decodeParser($parser, $hints = null)
	{
		$version = $parser->readVersion();
		$ecLevel = $parser->readFormatInformation()->getErrorCorrectionLevel();

		// Read codewords
		$codewords = $parser->readCodewords();
		// Separate into data blocks
		$dataBlocks = DataBlock::getDataBlocks($codewords, $version, $ecLevel);

		// Count total number of data bytes
		$totalBytes = 0;
		foreach ($dataBlocks as $dataBlock) {
			$totalBytes += $dataBlock->getNumDataCodewords();
		}
		$resultBytes = fill_array(0, $totalBytes, 0);
		$resultOffset = 0;

		// Error-correct and copy data blocks together into a stream of bytes
		foreach ($dataBlocks as $dataBlock) {
			$codewordBytes = $dataBlock->getCodewords();
			$numDataCodewords = $dataBlock->getNumDataCodewords();
			$this->correctErrors($codewordBytes, $numDataCodewords);
			for ($i = 0; $i < $numDataCodewords; $i++) {
				$resultBytes[$resultOffset++] = $codewordBytes[$i];
			}
		}

		// Decode the contents of that stream of bytes
		return DecodedBitStreamParser::decode($resultBytes, $version, $ecLevel, $hints);
	}

	/**
	 * <p>Given data and error-correction codewords received, possibly corrupted by errors, attempts to
	 * correct the errors in-place using Reed-Solomon error correction.</p>
	 *
	 * @param data    $codewordBytes and error correction codewords
	 * @param number $numDataCodewords of codewords that are data bytes
	 *
	 * @throws ChecksumException if error correction fails
	 */
	private function correctErrors(&$codewordBytes, $numDataCodewords)
	{
		$numCodewords = is_countable($codewordBytes) ? count($codewordBytes) : 0;
		// First read into an array of ints
		$codewordsInts = fill_array(0, $numCodewords, 0);
		for ($i = 0; $i < $numCodewords; $i++) {
			$codewordsInts[$i] = $codewordBytes[$i] & 0xFF;
		}
		$numECCodewords = (is_countable($codewordBytes) ? count($codewordBytes) : 0) - $numDataCodewords;
		try {
			$this->rsDecoder->decode($codewordsInts, $numECCodewords);
		} catch (ReedSolomonException) {
			throw ChecksumException::getChecksumInstance();
		}
		// Copy back into array of bytes -- only need to worry about the bytes that were data
		// We don't care about errors in the error-correction codewords
		for ($i = 0; $i < $numDataCodewords; $i++) {
			$codewordBytes[$i] = $codewordsInts[$i];
		}
	}
}
