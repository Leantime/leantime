#!/usr/bin/php
<?php
/**
 * mltranslate - Automatically translate text
 *
 * Usage: ./mltranslate src_lang dst_lang src_file dts_file tra_file
 * Configuration: Put DeepL.com API key in file KEYFILE as $apikey = 'KEY';
 */
const KEYFILE = '/var/lib/wwwrun/private/mltranslate-key.inc.php';

require('./vendor/autoload.php');

// Get API key
require(KEYFILE);

// Get command line arguments
$src_lang = isset($argv[1]) ? $argv[1] : die("Usage: $argv[0] src_lang dst_lang src_file dst_file tra_file".PHP_EOL);
$dst_lang = isset($argv[2]) ? $argv[2] : die("Usage: $argv[0] src_lang dst_lang src_file dst_file tra_file".PHP_EOL);
$src_file = isset($argv[3]) ? $argv[3] : die("Usage: $argv[0] src_lang dst_lang src_file dst_file tra_file".PHP_EOL);
$dst_file = isset($argv[4]) ? $argv[4] : die("Usage: $argv[0] src_lang dst_lang src_file dst_file tra_file".PHP_EOL);
$tra_file = isset($argv[5]) ? $argv[5] : die("Usage: $argv[0] src_lang dst_lang src_file dst_file tra_file".PHP_EOL);

// Check source and destination languages
$langs = [ 'en', 'de', 'es', 'fr', 'it', 'ja', 'nl', 'pt-BR', 'pr-PT', 'ru', 'sv', 'tr', 'zh' ];

in_array($src_lang, $langs) || die("Error: Source language '$src_lang' not supported".PHP_EOL);
if(!in_array($dst_lang, $langs)) {
	
    echo "Warning: Destination language '$dst_lang' not supported for translation. Not translating file.".PHP_EOL;

}

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
		isset($match[1]) ? $key = trim($match[1]) : die("Error: Cannot find text key in line '$line'".PHP_EOL);
		if(!$match[2]) die("Error: Cannot find text in line '$line'".PHP_EOL);
		preg_match('/"(.*?)"/', $match[2], $submatch);

		if(isset($submatch[1])) {

			$dst_text_ary[$key] = trim($submatch[1]);

		}

	}

	fclose($dst_stream);
	
}else {

	echo "Warning: cannot read file '$dst_file'. Ignoring\n";

}

// Open file to translate
$src_stream = fopen($src_file, 'r');
if($src_stream === false) die("Error: Cannot read file '$src_file'".PHP_EOL);

$tra_stream = fopen($tra_file, 'w');
if($tra_stream === false) die("Error: Cannot write file '$tra_file'".PHP_EOL);

// Translate line by line
$first_time_quota_error = true;
while(!feof($src_stream)) {
	
	$line = fgets($src_stream);

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
		
		echo "Warning: Cannot find text key in line '$line'".PHP_EOL;
		continue;
		
	}
    if(!$match[2]) {
		
		echo "Warning: Cannot find text in line '$line'".PHP_EOL;
		continue;
		
	}
	
	preg_match('/"(.*?)"/', $match[2], $submatch);
	if(isset($submatch[1])) {
		$src_text = trim($submatch[1]);

		// Translating
		echo $key.' = "'.$src_text.'"'.PHP_EOL;
		if(!isset($dst_text_ary[$key]) && !isset($dst_text_ary['MTR.'.$key])) {
			
			if(in_array($dst_lang, $langs) && $src_lang !== $dst_lang) {
				
				try {
					
					$result = $trAPI->translateText($src_text, $src_lang, $dst_lang);
					$tra_text = $result->text;
					
				}
				catch(\DeepL\QuotaExceededException) {
					
					if($first_time_quota_error) {
						
						echo "Warning: Translation quota exceeded for this month".PHP_EOL;
						$first_time_quota_error = false;
						
					}
					
					$tra_text = $src_text;
					
				}
				
			}else{
				
				$tra_text = $src_text;
				
			}
			
			fwrite($tra_stream, 'MTR.'.$key.' = "'.$tra_text.'"'.PHP_EOL);
			echo 'MTR.'.$key.' = "'.$tra_text.'"'.PHP_EOL;
			
		}else{

            if(isset($dst_text_ary[$key])) {
                
                $tra_text = $dst_text_ary[$key];

            }else{
                
                $tra_text = $dst_text_ary['MTR.'.$key];

            }
			fwrite($tra_stream, $key.' = "'.$tra_text.'"'.PHP_EOL);
			echo $key.' = "'.$tra_text.'"'.PHP_EOL;
			
		}

	}
}

// Close file and database connection
fclose($src_stream);
fclose($tra_stream);

?>
