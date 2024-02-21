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

/**
 * Dict loader interface.
 */
interface DictLoaderInterface
{
    /**
     * Load dict.
     *
     * <pre>
     * [
     *     '响应时间' => "[\t]xiǎng[\t]yìng[\t]shí[\t]jiān",
     *     '长篇连载' => '[\t]cháng[\t]piān[\t]lián[\t]zǎi',
     *     //...
     * ]
     * </pre>
     *
     * @param Closure $callback
     */
    public function map(Closure $callback);

    /**
     * Load surname dict.
     *
     * @param Closure $callback
     */
    public function mapSurname(Closure $callback);
}
