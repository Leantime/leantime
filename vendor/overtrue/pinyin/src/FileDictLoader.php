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

class FileDictLoader implements DictLoaderInterface
{
    /**
     * Words segment name.
     *
     * @var string
     */
    protected $segmentName = 'words_%s';

    /**
     * Dict path.
     *
     * @var string
     */
    protected $path;

    /**
     * Constructor.
     *
     * @param string $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Load dict.
     *
     * @param Closure $callback
     */
    public function map(Closure $callback)
    {
        for ($i = 0; $i < 100; ++$i) {
            $segment = $this->path . '/' . sprintf($this->segmentName, $i);

            if (file_exists($segment)) {
                $dictionary = (array) include $segment;
                $callback($dictionary);
            }
        }
    }

    /**
     * Load surname dict.
     *
     * @param Closure $callback
     */
    public function mapSurname(Closure $callback)
    {
        $surnames = $this->path . '/surnames';

        if (file_exists($surnames)) {
            $dictionary = (array) include $surnames;
            $callback($dictionary);
        }
    }
}
