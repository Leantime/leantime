<?php

namespace LasseRafn;

class StringScript
{
    const REGEX_COMMON = '/\p{Common}/u';
    const REGEX_ARABIC = '/\p{Arabic}/u';
    const REGEX_ARMENIAN = '/\p{Armenian}/u';
    const REGEX_BENGALI = '/\p{Bengali}/u';
    const REGEX_BOPOMOFO = '/\p{Bopomofo}/u';
    const REGEX_BRAILLE = '/\p{Braille}/u';
    const REGEX_BUHID = '/\p{Buhid}/u';
    const REGEX_CANADIAN_ABORIGINAL = '/\p{Canadian_Aboriginal}/u';
    const REGEX_CHEROKEE = '/\p{Cherokee}/u';
    const REGEX_CYRILLIC = '/\p{Cyrillic}/u';
    const REGEX_DEVANAGARI = '/\p{Devanagari}/u';
    const REGEX_ETHIOPIC = '/\p{Ethiopic}/u';
    const REGEX_GEORGIAN = '/\p{Georgian}/u';
    const REGEX_GREEK = '/\p{Greek}/u';
    const REGEX_GUJARATI = '/\p{Gujarati}/u';
    const REGEX_GURMUKHI = '/\p{Gurmukhi}/u';
    const REGEX_HAN = '/\p{Han}/u';
    const REGEX_HANGUL = '/\p{Hangul}/u';
    const REGEX_HANUNOO = '/\p{Hanunoo}/u';
    const REGEX_HEBREW = '/\p{Hebrew}/u';
    const REGEX_HIRAGANA = '/\p{Hiragana}/u';
    const REGEX_INHERITED = '/\p{Inherited}/u';
    const REGEX_KANNADA = '/\p{Kannada}/u';
    const REGEX_KATAKANA = '/\p{Katakana}/u';
    const REGEX_KHMER = '/\p{Khmer}/u';
    const REGEX_LAO = '/\p{Lao}/u';
    const REGEX_LATIN = '/\p{Latin}/u';
    const REGEX_LIMBU = '/\p{Limbu}/u';
    const REGEX_MALAYALAM = '/\p{Malayalam}/u';
    const REGEX_MONGOLIAN = '/\p{Mongolian}/u';
    const REGEX_MYANMAR = '/\p{Myanmar}/u';
    const REGEX_OGHAM = '/\p{Ogham}/u';
    const REGEX_ORIYA = '/\p{Oriya}/u';
    const REGEX_RUNIC = '/\p{Runic}/u';
    const REGEX_SINHALA = '/\p{Sinhala}/u';
    const REGEX_SYRIAC = '/\p{Syriac}/u';
    const REGEX_TAGALOG = '/\p{Tagalog}/u';
    const REGEX_TAGBANWA = '/\p{Tagbanwa}/u';
    const REGEX_TAILE = '/\p{TaiLe}/u';
    const REGEX_TAMIL = '/\p{Tamil}/u';
    const REGEX_TELUGU = '/\p{Telugu}/u';
    const REGEX_THAANA = '/\p{Thaana}/u';
    const REGEX_THAI = '/\p{Thai}/u';
    const REGEX_TIBETAN = '/\p{Tibetan}/u';
    const REGEX_YI = '/\p{Yi}/u';

    public static function isCommon($string)
    {
        return preg_match(self::REGEX_COMMON, $string) > 0;
    }

    public static function isArabic($string)
    {
        return preg_match(self::REGEX_ARABIC, $string) > 0;
    }

    public static function isArmenian($string)
    {
        return preg_match(self::REGEX_ARMENIAN, $string) > 0;
    }

    public static function isBengali($string)
    {
        return preg_match(self::REGEX_BENGALI, $string) > 0;
    }

    public static function isBopomofo($string)
    {
        return preg_match(self::REGEX_BOPOMOFO, $string) > 0;
    }

    public static function isBraille($string)
    {
        return preg_match(self::REGEX_BRAILLE, $string) > 0;
    }

    public static function isBuhid($string)
    {
        return preg_match(self::REGEX_BUHID, $string) > 0;
    }

    public static function isCanadian_Aboriginal($string)
    {
        return preg_match(self::REGEX_CANADIAN_ABORIGINAL, $string) > 0;
    }

    public static function isCherokee($string)
    {
        return preg_match(self::REGEX_CHEROKEE, $string) > 0;
    }

    public static function isCyrillic($string)
    {
        return preg_match(self::REGEX_CYRILLIC, $string) > 0;
    }

    public static function isDevanagari($string)
    {
        return preg_match(self::REGEX_DEVANAGARI, $string) > 0;
    }

    public static function isEthiopic($string)
    {
        return preg_match(self::REGEX_ETHIOPIC, $string) > 0;
    }

    public static function isGeorgian($string)
    {
        return preg_match(self::REGEX_GEORGIAN, $string) > 0;
    }

    public static function isGreek($string)
    {
        return preg_match(self::REGEX_GREEK, $string) > 0;
    }

    public static function isGujarati($string)
    {
        return preg_match(self::REGEX_GUJARATI, $string) > 0;
    }

    public static function isGurmukhi($string)
    {
        return preg_match(self::REGEX_GURMUKHI, $string) > 0;
    }

    public static function isHan($string)
    {
        return preg_match(self::REGEX_HAN, $string) > 0;
    }

    public static function isHangul($string)
    {
        return preg_match(self::REGEX_HANGUL, $string) > 0;
    }

    public static function isHanunoo($string)
    {
        return preg_match(self::REGEX_HANUNOO, $string) > 0;
    }

    public static function isHebrew($string)
    {
        return preg_match(self::REGEX_HEBREW, $string) > 0;
    }

    public static function isHiragana($string)
    {
        return preg_match(self::REGEX_HIRAGANA, $string) > 0;
    }

    public static function isInherited($string)
    {
        return preg_match(self::REGEX_INHERITED, $string) > 0;
    }

    public static function isKannada($string)
    {
        return preg_match(self::REGEX_KANNADA, $string) > 0;
    }

    public static function isKatakana($string)
    {
        return preg_match(self::REGEX_KATAKANA, $string) > 0;
    }

    public static function isKhmer($string)
    {
        return preg_match(self::REGEX_KHMER, $string) > 0;
    }

    public static function isLao($string)
    {
        return preg_match(self::REGEX_LAO, $string) > 0;
    }

    public static function isLatin($string)
    {
        return preg_match(self::REGEX_LATIN, $string) > 0;
    }

    public static function isLimbu($string)
    {
        return preg_match(self::REGEX_LIMBU, $string) > 0;
    }

    public static function isMalayalam($string)
    {
        return preg_match(self::REGEX_MALAYALAM, $string) > 0;
    }

    public static function isMongolian($string)
    {
        return preg_match(self::REGEX_MONGOLIAN, $string) > 0;
    }

    public static function isMyanmar($string)
    {
        return preg_match(self::REGEX_MYANMAR, $string) > 0;
    }

    public static function isOgham($string)
    {
        return preg_match(self::REGEX_OGHAM, $string) > 0;
    }

    public static function isOriya($string)
    {
        return preg_match(self::REGEX_ORIYA, $string) > 0;
    }

    public static function isRunic($string)
    {
        return preg_match(self::REGEX_RUNIC, $string) > 0;
    }

    public static function isSinhala($string)
    {
        return preg_match(self::REGEX_SINHALA, $string) > 0;
    }

    public static function isSyriac($string)
    {
        return preg_match(self::REGEX_SYRIAC, $string) > 0;
    }

    public static function isTagalog($string)
    {
        return preg_match(self::REGEX_TAGALOG, $string) > 0;
    }

    public static function isTagbanwa($string)
    {
        return preg_match(self::REGEX_TAGBANWA, $string) > 0;
    }

    public static function isTaiLe($string)
    {
        return preg_match(self::REGEX_TAILE, $string) > 0;
    }

    public static function isTamil($string)
    {
        return preg_match(self::REGEX_TAMIL, $string) > 0;
    }

    public static function isTelugu($string)
    {
        return preg_match(self::REGEX_TELUGU, $string) > 0;
    }

    public static function isThaana($string)
    {
        return preg_match(self::REGEX_THAANA, $string) > 0;
    }

    public static function isThai($string)
    {
        return preg_match(self::REGEX_THAI, $string) > 0;
    }

    public static function isTibetan($string)
    {
        return preg_match(self::REGEX_TIBETAN, $string) > 0;
    }

    public static function isYi($string)
    {
        return preg_match(self::REGEX_YI, $string) > 0;
    }

    /* --------------------------------------------------------
     * Proxies for the common person
     * ----------------------------------------------------- */
    public static function isChinese($string)
    {
        return self::isHan($string);
    }

    public static function isJapanese($string)
    {
        return self::isHiragana($string) || self::isKatakana($string);
    }
}
