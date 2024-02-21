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

namespace Zxing\Common;

/**
 * <p>Encapsulates the result of decoding a matrix of bits. This typically
 * applies to 2D barcode formats. For now it contains the raw bytes obtained,
 * as well as a String interpretation of those bytes, if applicable.</p>
 *
 * @author Sean Owen
 */
final class DecoderResult
{
	/**
  * @var mixed|null
  */
 private $errorsCorrected;
	/**
  * @var mixed|null
  */
 private $erasures;
	/**
  * @var mixed|null
  */
 private $other;


	public function __construct(private $rawBytes, private $text, private $byteSegments, private $ecLevel, private $structuredAppendSequenceNumber = -1, private $structuredAppendParity = -1)
 {
 }

	public function getRawBytes()
	{
		return $this->rawBytes;
	}

	public function getText()
	{
		return $this->text;
	}

	public function getByteSegments()
	{
		return $this->byteSegments;
	}

	public function getECLevel()
	{
		return $this->ecLevel;
	}

	public function getErrorsCorrected()
	{
		return $this->errorsCorrected;
	}

	public function setErrorsCorrected($errorsCorrected): void
	{
		$this->errorsCorrected = $errorsCorrected;
	}

	public function getErasures()
	{
		return $this->erasures;
	}

	public function setErasures($erasures): void
	{
		$this->erasures = $erasures;
	}

	public function getOther()
	{
		return $this->other;
	}

	public function setOther($other): void
	{
		$this->other = $other;
	}

	public function hasStructuredAppend()
	{
		return $this->structuredAppendParity >= 0 && $this->structuredAppendSequenceNumber >= 0;
	}

	public function getStructuredAppendParity()
	{
		return $this->structuredAppendParity;
	}

	public function getStructuredAppendSequenceNumber()
	{
		return $this->structuredAppendSequenceNumber;
	}
}
