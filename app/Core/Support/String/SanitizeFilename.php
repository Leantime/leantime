<?php

namespace Leantime\Core\Support\String;

use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class SanitizeFilename
{
    /**
     * Sanitizes a filename by removing or replacing unsafe characters.
     *
     * @param  bool  $beautify  Whether to beautify the filename
     * @return callable A function that sanitizes a filename
     */
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
}
