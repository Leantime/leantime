<?php

/*
 * This file is part of the overtrue/pinyin.
 *
 * (c) overtrue <i@overtrue.me>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Overtrue\Pinyin;

use InvalidArgumentException;

class Pinyin
{
    /**
     * Dict loader.
     *
     * @var \Overtrue\Pinyin\DictLoaderInterface
     */
    protected $loader;

    /**
     * Punctuations map.
     *
     * @var array
     */
    protected $punctuations = [
        '，' => ',',
        '。' => '.',
        '！' => '!',
        '？' => '?',
        '：' => ':',
        '“' => '"',
        '”' => '"',
        '‘' => "'",
        '’' => "'",
        '_' => '_',
    ];

    /**
     * Constructor.
     *
     * @param string $loaderName
     */
    public function __construct($loaderName = null)
    {
        $this->loader = $loaderName ?: 'Overtrue\\Pinyin\\FileDictLoader';
    }

    /**
     * Convert string to pinyin.
     *
     * @param string $string
     * @param int    $option
     *
     * @return array
     */
    public function convert($string, $option = PINYIN_DEFAULT)
    {
        $pinyin = $this->romanize($string, $option);

        return $this->splitWords($pinyin, $option);
    }

    /**
     * Convert string (person name) to pinyin.
     *
     * @param string $stringName
     * @param int    $option
     *
     * @return array
     */
    public function name($stringName, $option = PINYIN_NAME)
    {
        $option = $option | PINYIN_NAME;

        $pinyin = $this->romanize($stringName, $option);

        return $this->splitWords($pinyin, $option);
    }

    /**
     * Return a pinyin permalink from string.
     *
     * @param string $string
     * @param string $delimiter
     * @param int    $option
     *
     * @return string
     */
    public function permalink($string, $delimiter = '-', $option = PINYIN_DEFAULT)
    {
        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, '-'];
        }

        if (!in_array($delimiter, ['_', '-', '.', ''], true)) {
            throw new InvalidArgumentException("Delimiter must be one of: '_', '-', '', '.'.");
        }

        return implode($delimiter, $this->convert($string, $option | \PINYIN_KEEP_NUMBER | \PINYIN_KEEP_ENGLISH));
    }

    /**
     * Return first letters.
     *
     * @param string $string
     * @param string $delimiter
     * @param int    $option
     *
     * @return string
     */
    public function abbr($string, $delimiter = '', $option = PINYIN_DEFAULT)
    {
        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ''];
        }

        return implode($delimiter, array_map(function ($pinyin) {
            return \is_numeric($pinyin) || preg_match('/\d+/', $pinyin) ? $pinyin : mb_substr($pinyin, 0, 1);
        }, $this->convert($string, $option | PINYIN_NO_TONE)));
    }

    /**
     * Chinese phrase to pinyin.
     *
     * @param string $string
     * @param string $delimiter
     * @param int    $option
     *
     * @return string
     */
    public function phrase($string, $delimiter = ' ', $option = PINYIN_DEFAULT)
    {
        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ' '];
        }

        return implode($delimiter, $this->convert($string, $option));
    }

    /**
     * Chinese to pinyin sentence.
     *
     * @param string $string
     * @param string $delimiter
     * @param int    $option
     *
     * @return string
     */
    public function sentence($string, $delimiter = ' ', $option = \PINYIN_NO_TONE)
    {
        if (\is_int($delimiter)) {
            list($option, $delimiter) = [$delimiter, ' '];
        }

        return implode($delimiter, $this->convert($string, $option | \PINYIN_KEEP_PUNCTUATION | \PINYIN_KEEP_ENGLISH | \PINYIN_KEEP_NUMBER));
    }

    /**
     * Loader setter.
     *
     * @param \Overtrue\Pinyin\DictLoaderInterface $loader
     *
     * @return $this
     */
    public function setLoader(DictLoaderInterface $loader)
    {
        $this->loader = $loader;

        return $this;
    }

    /**
     * Return dict loader,.
     *
     * @return \Overtrue\Pinyin\DictLoaderInterface
     */
    public function getLoader()
    {
        if (!($this->loader instanceof DictLoaderInterface)) {
            $dataDir = dirname(__DIR__) . '/data/';

            $loaderName = $this->loader;
            $this->loader = new $loaderName($dataDir);
        }

        return $this->loader;
    }

    /**
     * Convert Chinese to pinyin.
     *
     * @param string $string
     * @param int    $option
     *
     * @return string
     */
    protected function romanize($string, $option = \PINYIN_DEFAULT)
    {
        $string = $this->prepare($string, $option);

        $dictLoader = $this->getLoader();

        if ($this->hasOption($option, \PINYIN_NAME)) {
            $string = $this->convertSurname($string, $dictLoader);
        }

        $dictLoader->map(function ($dictionary) use (&$string) {
            $string = strtr($string, $dictionary);
        });

        return $string;
    }

    /**
     * Convert Chinese Surname to pinyin.
     *
     * @param string                               $string
     * @param \Overtrue\Pinyin\DictLoaderInterface $dictLoader
     *
     * @return string
     */
    protected function convertSurname($string, $dictLoader)
    {
        $dictLoader->mapSurname(function ($dictionary) use (&$string) {
            foreach ($dictionary as $surname => $pinyin) {
                if (0 === strpos($string, $surname)) {
                    $string = $pinyin . mb_substr($string, mb_strlen($surname, 'UTF-8'), mb_strlen($string, 'UTF-8') - 1, 'UTF-8');

                    break;
                }
            }
        });

        return $string;
    }

    /**
     * Split pinyin string to words.
     *
     * @param string $pinyin
     * @param string $option
     *
     * @return array
     */
    protected function splitWords($pinyin, $option)
    {
        $split = array_filter(preg_split('/\s+/i', $pinyin));

        if (!$this->hasOption($option, PINYIN_TONE)) {
            foreach ($split as $index => $pinyin) {
                $split[$index] = $this->formatTone($pinyin, $option);
            }
        }

        return array_values($split);
    }

    /**
     * @param int $option
     * @param int $check
     *
     * @return bool
     */
    public function hasOption($option, $check)
    {
        return ($option & $check) === $check;
    }

    /**
     * Pre-process.
     *
     * @param string $string
     * @param int    $option
     *
     * @return string
     */
    protected function prepare($string, $option = \PINYIN_DEFAULT)
    {
        $string = preg_replace_callback('~[a-z0-9_-]+~i', function ($matches) {
            return "\t" . $matches[0];
        }, $string);

        $regex = ['\x{3007}\x{2E80}-\x{2FFF}\x{3100}-\x{312F}\x{31A0}-\x{31EF}\x{3400}-\x{4DBF}\x{4E00}-\x{9FFF}\x{F900}-\x{FAFF}', '\p{Z}', '\p{M}', "\t"];

        if ($this->hasOption($option, \PINYIN_KEEP_NUMBER)) {
            \array_push($regex, '0-9');
        }

        if ($this->hasOption($option, \PINYIN_KEEP_ENGLISH)) {
            \array_push($regex, 'a-zA-Z');
        }

        if ($this->hasOption($option, \PINYIN_KEEP_PUNCTUATION)) {
            $punctuations = array_merge($this->punctuations, ["\t" => ' ', '  ' => ' ']);
            $string = trim(str_replace(array_keys($punctuations), $punctuations, $string));

            \array_push($regex, preg_quote(implode(array_merge(array_keys($this->punctuations), $this->punctuations)), '~'));
        }

        return preg_replace(\sprintf('~[^%s]~u', implode($regex)), '', $string);
    }

    /**
     * Format.
     *
     * @param string $pinyin
     * @param int    $option
     *
     * @return string
     */
    protected function formatTone($pinyin, $option = \PINYIN_NO_TONE)
    {
        $replacements = [
            // mb_chr(593) => 'ɑ' 轻声中除了 `ɑ` 和 `ü` 以外，其它和字母一样
            'ɑ' => ['a', 5], 'ü' => ['yu', 5],
            'üē' => ['ue', 1], 'üé' => ['ue', 2], 'üě' => ['ue', 3], 'üè' => ['ue', 4],
            'ā' => ['a', 1], 'ē' => ['e', 1], 'ī' => ['i', 1], 'ō' => ['o', 1], 'ū' => ['u', 1], 'ǖ' => ['yu', 1],
            'á' => ['a', 2], 'é' => ['e', 2], 'í' => ['i', 2], 'ó' => ['o', 2], 'ú' => ['u', 2], 'ǘ' => ['yu', 2],
            'ǎ' => ['a', 3], 'ě' => ['e', 3], 'ǐ' => ['i', 3], 'ǒ' => ['o', 3], 'ǔ' => ['u', 3], 'ǚ' => ['yu', 3],
            'à' => ['a', 4], 'è' => ['e', 4], 'ì' => ['i', 4], 'ò' => ['o', 4], 'ù' => ['u', 4], 'ǜ' => ['yu', 4],
        ];

        foreach ($replacements as $unicode => $replacement) {
            if (false !== strpos($pinyin, $unicode)) {
                $umlaut = $replacement[0];

                // https://zh.wikipedia.org/wiki/%C3%9C
                if ($this->hasOption($option, \PINYIN_UMLAUT_V) && 'yu' == $umlaut) {
                    $umlaut = 'v';
                }

                $pinyin = str_replace($unicode, $umlaut, $pinyin) . ($this->hasOption($option, PINYIN_ASCII_TONE) ? $replacement[1] : '');
            }
        }

        return $pinyin;
    }
}
