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

namespace Zxing;

/**
 * Thrown when a barcode was successfully detected, but some aspect of
 * the content did not conform to the barcode's format rules. This could have
 * been due to a mis-detection.
 *
 * @author Sean Owen
 */
final class FormatException extends ReaderException
{
	private static ?\Zxing\FormatException $instance = null;

	public function __construct($cause = null)
	{
		if ($cause) {
			parent::__construct($cause);
		}
	}

	public static function getFormatInstance($cause = null)
	{
		if (!self::$instance) {
			self::$instance = new FormatException();
		}
		if (self::$isStackTrace) {
			return new FormatException($cause);
		} else {
			return self::$instance;
		}
	}
}
