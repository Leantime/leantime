#!/usr/bin/php
<?php
/**
 * mltranslate - Automatically translate text
 *
 * Usage: ./mltranslate src_lang dst_lang src_file dts_file tra_file
 * Configuration: Put DeepL.com API key in file KEYFILE as $apikey = 'KEY';
 */
const KEYFILE = '/var/lib/wwwrun/private/mltranslate-key.inc.php';

require('../../../vendor/autoload.php');

// Get API key
require(KEYFILE);

// Get command line arguments
$src_lang = isset($argv[1]) ? $argv[1] : die("Usage: $argv[0] /mltranslate src_lang dst_lang src_file dts_file tra_file\n");
$dst_lang = isset($argv[2]) ? $argv[2] : die("Usage: $argv[0] /mltranslate src_lang dst_lang src_file dts_file tra_file\n");
$src_file = isset($argv[3]) ? $argv[3] : die("Usage: $argv[0] /mltranslate src_lang dst_lang src_file dts_file tra_file\n");
$dst_file = isset($argv[4]) ? $argv[4] : die("Usage: $argv[0] /mltranslate src_lang dst_lang src_file dts_file tra_file\n");
$tra_file = isset($argv[5]) ? $argv[5] : die("Usage: $argv[0] /mltranslate src_lang dst_lang src_file dts_file tra_file\n");

// Check source and destination languages
in_array($src_lang, [ 'en' ]) || die("Error: Source language '$src_lang' not supported\n");
$dst_langs = [ 'de', 'es', 'fr', 'it', 'ja', 'nl', 'pt-BR', 'pr-PT', 'ru', 'tr', 'zh' ];
in_array($dst_lang, $dst_langs) ||
    die("Error: Destination language '$dst_lang' not supported\n"."Supported languages are: ".implode(', ', $dst_langs));

// Create a new translation engine
$trAPI = new \DeepL\Translator($apikey);

// Read already tanslated file
$dst_text_ary = [];
if(file_exists($dst_file)) {
	$dst_stream = fopen($dst_file, 'r');
	
	while(!feof($dst_stream)) {
		$line = fgets($dst_stream);

		// Remove comment
		$comment_pos = strpos($line, '#');
		if($comment_pos === 0) continue;

		// Remove white spaces
		$line = trim($line);
		if(empty($line)) continue;

		// Extract key and text
		preg_match('/([^=]*)=(.*)/', $line, $match);
		isset($match[1]) ? $key = trim($match[1]) : die("Error: Cannot find text key in line '$line'\n");
		if(!$match[2]) die("Error: Cannot find text in line '$line'\n");
		preg_match('/"(.*?)"/', $match[2], $submatch);
		if(isset($submatch[1])) {
			$dst_text_ary[$key] = trim($submatch[1]);
		}
	}

	fclose($dst_stream);
}

// Open file to translate
$src_stream = fopen($src_file, 'r');
if($src_stream === false) die("Error: Cannot open file '$src_file'\n");
$tra_stream = fopen($tra_file, 'w');
if($tra_stream === false) die("Error: Cannot open file '$tra_file'\n");

// Translate line by line
while(!feof($tra_stream)) {
	$line = fgets($tra_stream);

	// Remove comment
	$comment_pos = strpos($line, '#');
	if($comment_pos === 0) {
		fwrite($tra_stream, $line.PHP_EOL);
		continue;
	}

	// Remove white spaces
	$line = trim($line);
	if(empty($line)) {
		fwrite($tra_stream, PHP_EOL);
		continue;
	}

	// Extract key and text
	preg_match('/([^=]*)=(.*)/', $line, $match);
	if(isset($match[1])) {
		$key = trim($match[1]);
	}else{
		echo "Warning: Cannot find text key in line '$line'\n";
		continue;
	}
    if(!$match[2]) {
		echo "Warning: Cannot find text in line '$line'\n";
		continue;
	}
	preg_match('/"(.*?)"/', $match[2], $submatch);
	if(isset($submatch[1])) {
		$src_text = trim($submatch[1]);

		// Translating
		if(!isset($tra_text_ary[$key])) {
			try {
				$result = $trAPI->translateText($src_text, $src_lang, $dst_lang);
			}
			catch(\DeepL\QuotaExceededException) {
				die("Error: Translation quota exceeded for this month\n");
			}
			$tra_text = $result->text;
		}
		else {
			$tra_text = $dst_text_ary[$key];
		}
		
		// Output result
		fwrite($tra_stream, $key.' = "'.$tra_text.'"'.PHP_EOL);

		// Show on screen
		echo $key.' = "'.$tra_text.'"'.PHP_EOL;
		echo $key.' = "'.$src_text.'"'.PHP_EOL;
	}
}

// Close file and database connection
fclose($src_stream);
fclose($tra_stream);

?>
