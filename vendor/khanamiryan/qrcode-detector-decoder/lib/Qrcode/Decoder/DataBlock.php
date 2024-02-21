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
 * <p>Encapsulates a block of data within a QR Code. QR Codes may split their data into
 * multiple blocks, each of which is a unit of data and error-correction codewords. Each
 * is represented by an instance of this class.</p>
 *
 * @author Sean Owen
 */
final class DataBlock
{
	//byte[]

	private function __construct(private $numDataCodewords, private $codewords)
 {
 }

	/**
	 * <p>When QR Codes use multiple data blocks, they are actually interleaved.
	 * That is, the first byte of data block 1 to n is written, then the second bytes, and so on. This
	 * method will separate the data into original blocks.</p>
	 *
	 * @param bytes $rawCodewords as read directly from the QR Code
	 * @param version      $version of the QR Code
	 * @param error      $ecLevel-correction level of the QR Code
	 *
	 * @return array DataBlocks containing original bytes, "de-interleaved" from representation in the
	 *         QR Code
	 */
	public static function getDataBlocks(
		$rawCodewords,
		$version,
		$ecLevel
	)
	{
		if ((is_countable($rawCodewords) ? count($rawCodewords) : 0) != $version->getTotalCodewords()) {
			throw new \InvalidArgumentException();
		}

		// Figure out the number and size of data blocks used by this version and
		// error correction level
		$ecBlocks = $version->getECBlocksForLevel($ecLevel);

		// First count the total number of data blocks
		$totalBlocks = 0;
		$ecBlockArray = $ecBlocks->getECBlocks();
		foreach ($ecBlockArray as $ecBlock) {
			$totalBlocks += $ecBlock->getCount();
		}

		// Now establish DataBlocks of the appropriate size and number of data codewords
		$result = [];//new DataBlock[$totalBlocks];
		$numResultBlocks = 0;
		foreach ($ecBlockArray as $ecBlock) {
			$ecBlockCount = $ecBlock->getCount();
			for ($i = 0; $i < $ecBlockCount; $i++) {
				$numDataCodewords = $ecBlock->getDataCodewords();
				$numBlockCodewords = $ecBlocks->getECCodewordsPerBlock() + $numDataCodewords;
				$result[$numResultBlocks++] = new DataBlock($numDataCodewords, fill_array(0, $numBlockCodewords, 0));
			}
		}

		// All blocks have the same amount of data, except that the last n
		// (where n may be 0) have 1 more byte. Figure out where these start.
		$shorterBlocksTotalCodewords = is_countable($result[0]->codewords) ? count($result[0]->codewords) : 0;
		$longerBlocksStartAt = count($result) - 1;
		while ($longerBlocksStartAt >= 0) {
			$numCodewords = is_countable($result[$longerBlocksStartAt]->codewords) ? count($result[$longerBlocksStartAt]->codewords) : 0;
			if ($numCodewords == $shorterBlocksTotalCodewords) {
				break;
			}
			$longerBlocksStartAt--;
		}
		$longerBlocksStartAt++;

		$shorterBlocksNumDataCodewords = $shorterBlocksTotalCodewords - $ecBlocks->getECCodewordsPerBlock();
		// The last elements of result may be 1 element longer;
		// first fill out as many elements as all of them have
		$rawCodewordsOffset = 0;
		for ($i = 0; $i < $shorterBlocksNumDataCodewords; $i++) {
			for ($j = 0; $j < $numResultBlocks; $j++) {
				$result[$j]->codewords[$i] = $rawCodewords[$rawCodewordsOffset++];
			}
		}
		// Fill out the last data block in the longer ones
		for ($j = $longerBlocksStartAt; $j < $numResultBlocks; $j++) {
			$result[$j]->codewords[$shorterBlocksNumDataCodewords] = $rawCodewords[$rawCodewordsOffset++];
		}
		// Now add in error correction blocks
		$max = is_countable($result[0]->codewords) ? count($result[0]->codewords) : 0;
		for ($i = $shorterBlocksNumDataCodewords; $i < $max; $i++) {
			for ($j = 0; $j < $numResultBlocks; $j++) {
				$iOffset = $j < $longerBlocksStartAt ? $i : $i + 1;
				$result[$j]->codewords[$iOffset] = $rawCodewords[$rawCodewordsOffset++];
			}
		}

		return $result;
	}

	public function getNumDataCodewords()
	{
		return $this->numDataCodewords;
	}

	public function getCodewords()
	{
		return $this->codewords;
	}
}
