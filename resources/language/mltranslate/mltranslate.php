#!/usr/bin/php
<?php
/**
 * mltranslate - Automatically translate text
 *
 * Usage: ./mltranslate src_filename src_lang dst_lan [tra_filename] >out_filename
 * Configuration: Put DeepL.com API key in file KEYFILE as $apikey = 'KEY';
 */
const KEYFILE = '/var/lib/wwwrun/private/mltranslate-key.inc.php';

require('../../../vendor/autoload.php');

// Get API key
require(KEYFILE);

// Get command line arguments
$src_file = isset($argv[1]) ? $argv[1] : die("Usage: $argv[0] src_filename src_lang dst_lan [tra_filename] >dst_filename\n");
$src_lang = isset($argv[2]) ? $argv[2] : die("Usage: ./mltranslate src_filename src_lang dst_lan [tra_filename] >dst_filename\n");
$dst_lang = isset($argv[3]) ? $argv[3] : die("Usage: ./mltranslate src_filename src_lang dst_lan [tra_filename] >dst_filename\n");
$tra_file = isset($argv[4]) ? $argv[4] : '';

// Check source and destination languages
in_array($src_lang, [ 'en' ]) || die("Error: Source language '$src_lang' not supported\n");
$dst_langs = [ 'de', 'es', 'fr', 'it', 'ja', 'nl', 'pt-BR', 'pr-PT', 'ru', 'zh' ];
in_array($dst_lang, $dst_langs) ||
    die("Error: Destination language '$dst_lang' not supported\n"."Supported languages are: ".implode(', ', $dst_langs));

// Create a new translation engine
$trAPI = new \DeepL\Translator($apikey);

// Read already tanslated file
$tra_text_ary = [];
if(file_exists($tra_file)) {
	$tra_stream = fopen($tra_file, 'r');
	
	while(!feof($tra_stream)) {
		$line = fgets($tra_stream);

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
			$tra_text_ary[$key] = trim($submatch[1]);
		}
	}

	fclose($tra_stream);
}

// Open file to translate
$src_stream = fopen($src_file, 'r');
if($src_stream === false) die("Error: Cannot open file '$src_file'\n");

// Translate line by line
while(!feof($src_stream)) {
	$line = fgets($src_stream);

	// Remove comment
	$comment_pos = strpos($line, '#');
	if($comment_pos === 0) {
		echo $line.PHP_EOL;
		continue;
	}

	// Remove white spaces
	$line = trim($line);
	if(empty($line)) {
		echo PHP_EOL;
		continue;
	}

	// Extract key and text
	preg_match('/([^=]*)=(.*)/', $line, $match);
	isset($match[1]) ? $key = trim($match[1]) : die("Error: Cannot find text key in line '$line'\n");
    if(!$match[2]) die("Error: Cannot find text in line '$line'\n");
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
			$dst_text = $result->text;
		}
		else {
			$dst_text = $tra_text_ary[$key];
		}
		
		// Output result
		echo $key.' = "'.$dst_text.'"'.PHP_EOL;
	}
}

// Close file and database connection
fclose($src_stream);

?>
