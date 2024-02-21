<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\Csv;

/**
 * Defines constants for common BOM sequences.
 */
interface ByteSequence
{
    const BOM_UTF8 = "\xEF\xBB\xBF";
    const BOM_UTF16_BE = "\xFE\xFF";
    const BOM_UTF16_LE = "\xFF\xFE";
    const BOM_UTF32_BE = "\x00\x00\xFE\xFF";
    const BOM_UTF32_LE = "\xFF\xFE\x00\x00";
}
