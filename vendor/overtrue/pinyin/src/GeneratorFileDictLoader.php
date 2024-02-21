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

use Closure;
use SplFileObject;
use Generator;

class GeneratorFileDictLoader implements DictLoaderInterface
{
    /**
     * Data directory.
     *
     * @var string
     */
    protected $path;

    /**
     * Words segment name.
     *
     * @var string
     */
    protected $segmentName = 'words_%s';

    /**
     * SplFileObjects.
     *
     * @var array
     */
    protected static $handles = [];

    /**
     * surnames.
     *
     * @var SplFileObject
     */
    protected static $surnamesHandle;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;

        for ($i = 0; $i < 100; ++$i) {
            $segment = $this->path . '/' . sprintf($this->segmentName, $i);

            if (file_exists($segment) && is_file($segment)) {
                array_push(static::$handles, $this->openFile($segment));
            }
        }
    }

    /**
     * Construct a new file object.
     *
     * @param string $filename file path
     * @param string $mode     file open mode
     *
     * @return SplFileObject
     */
    protected function openFile($filename, $mode = 'r')
    {
        return new SplFileObject($filename, $mode);
    }

    /**
     * get Generator syntax.
     *
     * @param array $handles SplFileObjects
     *
     * @return Generator
     */
    protected function getGenerator(array $handles)
    {
        foreach ($handles as $handle) {
            $handle->seek(0);
            while (false === $handle->eof()) {
                $string = str_replace(['\'', ' ', PHP_EOL, ','], '', $handle->fgets());

                if (false === strpos($string, '=>')) {
                    continue;
                }

                list($string, $pinyin) = explode('=>', $string);

                yield $string => $pinyin;
            }
        }
    }

    /**
     * Traverse the stream.
     *
     * @param Generator $generator
     * @param Closure   $callback
     *
     * @author Seven Du <shiweidu@outlook.com>
     */
    protected function traversing(Generator $generator, Closure $callback)
    {
        foreach ($generator as $string => $pinyin) {
            $callback([$string => $pinyin]);
        }
    }

    /**
     * Load dict.
     *
     * @param Closure $callback
     */
    public function map(Closure $callback)
    {
        $this->traversing($this->getGenerator(static::$handles), $callback);
    }

    /**
     * Load surname dict.
     *
     * @param Closure $callback
     */
    public function mapSurname(Closure $callback)
    {
        if (!static::$surnamesHandle instanceof SplFileObject) {
            static::$surnamesHandle = $this->openFile($this->path . '/surnames');
        }

        $this->traversing($this->getGenerator([static::$surnamesHandle]), $callback);
    }
}
