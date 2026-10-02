<?php

namespace Unit\app\Language;

use Unit\TestCase;

/**
 * Every translation file must parse the way Language loads it (INI_SCANNER_RAW). A broken value
 * from a translation sync (the km-KH.ini multi-line entries) made parse_ini_file() return false,
 * so that language silently failed to load.
 */
class LanguageFilesParseTest extends TestCase
{
    public function test_every_language_file_parses(): void
    {
        $files = glob(dirname(__DIR__, 4).'/app/Language/*.ini');
        $this->assertNotEmpty($files);

        foreach ($files as $file) {
            $parsed = @parse_ini_file($file, false, INI_SCANNER_RAW);
            $this->assertIsArray($parsed, basename($file).' does not parse: '.(error_get_last()['message'] ?? 'unknown error'));
            $this->assertNotEmpty($parsed, basename($file).' parsed to an empty array');
        }
    }
}
