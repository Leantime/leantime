<?php

namespace Leantime\Core\Support\String;

/**
 * @mixin \Illuminate\Support\Stringable
 */
class BeautifyFilename
{
    /**
     * Beautifies a filename by normalizing characters and formatting.
     *
     * @return callable A function that beautifies a filename
     */
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
