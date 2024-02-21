<?php

namespace Zxing\Common;

/**
 * Encapsulates a Character Set ECI, according to "Extended Channel
 * Interpretations" 5.3.1.1 of ISO 18004.
 */
final class CharacterSetECI
{
	/**#@+
	 * Character set constants.
	 */
	public const CP437 = 0;
	public const ISO8859_1 = 1;
	public const ISO8859_2 = 4;
	public const ISO8859_3 = 5;
	public const ISO8859_4 = 6;
	public const ISO8859_5 = 7;
	public const ISO8859_6 = 8;
	public const ISO8859_7 = 9;
	public const ISO8859_8 = 10;
	public const ISO8859_9 = 11;
	public const ISO8859_10 = 12;
	public const ISO8859_11 = 13;
	public const ISO8859_12 = 14;
	public const ISO8859_13 = 15;
	public const ISO8859_14 = 16;
	public const ISO8859_15 = 17;
	public const ISO8859_16 = 18;
	public const SJIS = 20;
	public const CP1250 = 21;
	public const CP1251 = 22;
	public const CP1252 = 23;
	public const CP1256 = 24;
	public const UNICODE_BIG_UNMARKED = 25;
	public const UTF8 = 26;
	public const ASCII = 27;
	public const BIG5 = 28;
	public const GB18030 = 29;
	public const EUC_KR = 30;
	/**
  * Map between character names and their ECI values.
  */
 private static array $nameToEci = [
		'ISO-8859-1' => self::ISO8859_1,
		'ISO-8859-2' => self::ISO8859_2,
		'ISO-8859-3' => self::ISO8859_3,
		'ISO-8859-4' => self::ISO8859_4,
		'ISO-8859-5' => self::ISO8859_5,
		'ISO-8859-6' => self::ISO8859_6,
		'ISO-8859-7' => self::ISO8859_7,
		'ISO-8859-8' => self::ISO8859_8,
		'ISO-8859-9' => self::ISO8859_9,
		'ISO-8859-10' => self::ISO8859_10,
		'ISO-8859-11' => self::ISO8859_11,
		'ISO-8859-12' => self::ISO8859_12,
		'ISO-8859-13' => self::ISO8859_13,
		'ISO-8859-14' => self::ISO8859_14,
		'ISO-8859-15' => self::ISO8859_15,
		'ISO-8859-16' => self::ISO8859_16,
		'SHIFT-JIS' => self::SJIS,
		'WINDOWS-1250' => self::CP1250,
		'WINDOWS-1251' => self::CP1251,
		'WINDOWS-1252' => self::CP1252,
		'WINDOWS-1256' => self::CP1256,
		'UTF-16BE' => self::UNICODE_BIG_UNMARKED,
		'UTF-8' => self::UTF8,
		'ASCII' => self::ASCII,
		'GBK' => self::GB18030,
		'EUC-KR' => self::EUC_KR,
	];
	/**#@-*/
 /**
  * Additional possible values for character sets.
  */
 private static array $additionalValues = [
		self::CP437 => 2,
		self::ASCII => 170,
	];
	private static int|string|null $name = null;

	/**
	 * Gets character set ECI by value.
	 *
	 * @param  string $value
	 *
	 * @return CharacterSetEci|null
	 */
	public static function getCharacterSetECIByValue($value)
	{
		if ($value < 0 || $value >= 900) {
			throw new \InvalidArgumentException('Value must be between 0 and 900');
		}
		if (false !== ($key = array_search($value, self::$additionalValues))) {
			$value = $key;
		}
		array_search($value, self::$nameToEci);
		try {
			self::setName($value);

			return new self($value);
		} catch (\UnexpectedValueException) {
			return null;
		}
	}

	private static function setName($value)
	{
		foreach (self::$nameToEci as $name => $key) {
			if ($key == $value) {
				self::$name = $name;

				return true;
			}
		}
		if (self::$name == null) {
			foreach (self::$additionalValues as $name => $key) {
				if ($key == $value) {
					self::$name = $name;

					return true;
				}
			}
		}
	}

	/**
	 * Gets character set ECI name.
	 *
	 * @return character set ECI name|null
	 */
	public static function name()
	{
		return self::$name;
	}

	/**
	 * Gets character set ECI by name.
	 *
	 * @param  string $name
	 *
	 * @return CharacterSetEci|null
	 */
	public static function getCharacterSetECIByName($name)
	{
		$name = strtoupper($name);
		if (isset(self::$nameToEci[$name])) {
			return new self(self::$nameToEci[$name]);
		}

		return null;
	}
}
