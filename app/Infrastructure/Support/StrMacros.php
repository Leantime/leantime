<?php

namespace Leantime\Infrastructure\Support;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class StrMacros
{
    /**
     * Cleans a string by removing special characters and optionally spaces.
     *
     * @param  bool  $removeSpaces  Whether to remove spaces from the string.
     * @return callable A function that cleans a string based on the given parameter.
     */
    public function alphaNumeric($removeSpaces = false)
    {
        return function ($value) use ($removeSpaces) {

            $cleaned = preg_replace('/[^A-Za-z0-9 ]/', '', (string) $value);

            if ($removeSpaces) {
                $cleaned = str_replace(' ', '', $cleaned);
            } else {
                // Step 2: Replace multiple spaces with a single space
                $cleaned = preg_replace('/\s+/', ' ', $cleaned);
            }

            // Step 3: Trim leading and trailing spaces
            $cleaned = trim($cleaned);

            return $cleaned;
        };
    }

    public function sanitizeFilename($beautify = true)
    {
        return function ($filename) use ($beautify) {
            // sanitize filename
            $filename = preg_replace(
                '~
                        [<>:"/\\\|?*]|           # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
                        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
                        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
                        [#\[\]@!$&\'()+,;=]|     # URI reserved https://www.rfc-editor.org/rfc/rfc3986#section-2.2
                        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
                        ~x',
                '-',
                $filename
            );
            // avoids ".", ".." or ".hiddenFiles"
            $filename = ltrim($filename, '.-');
            // optional beautification
            if ($beautify) {
                $filename = Str::beautifyFilename($filename);
            }
            // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = mb_strcut(
                pathinfo($filename, PATHINFO_FILENAME),
                0,
                255 - ($ext ? strlen($ext) + 1 : 0),
                mb_detect_encoding($filename)
            ).($ext ? '.'.$ext : '');

            return $filename;
        };
    }

    public function beautifyFilename()
    {
        return function ($filename) {
            // reduce consecutive characters
            $filename = preg_replace([
                // "file   name.zip" becomes "file-name.zip"
                '/ +/',
                // "file___name.zip" becomes "file-name.zip"
                '/_+/',
                // "file---name.zip" becomes "file-name.zip"
                '/-+/',
            ], '-', $filename);
            $filename = preg_replace([
                // "file--.--.-.--name.zip" becomes "file.name.zip"
                '/-*\.-*/',
                // "file...name..zip" becomes "file.name.zip"
                '/\.{2,}/',
            ], '.', $filename);
            // lowercase for windows/unix interoperability http://support.microsoft.com/kb/100625
            $filename = mb_strtolower($filename, mb_detect_encoding($filename));
            // ".file-name.-" becomes "file-name"
            $filename = trim($filename, '.-');

            return $filename;
        };
    }
}
