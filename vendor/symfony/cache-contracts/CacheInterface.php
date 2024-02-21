<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Contracts\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\InvalidArgumentException;

/**
 * Covers most simple to advanced caching needs.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface CacheInterface
{
    /**
     * Fetches a value from the pool or computes it if not found.
     *
     * On cache misses, a callback is called that should return the missing value.
     * This callback is given a PSR-6 CacheItemInterface instance corresponding to the
     * requested key, that could be used e.g. for expiration control. It could also
     * be an ItemInterface instance when its additional features are needed.
     *
     * @template T
     *
     * @param string $key The key of the item to retrieve from the cache
     * @param (callable(CacheItemInterface,bool):T)|(callable(ItemInterface,bool):T)|CallbackInterface<T> $callback
     * @param float|null $beta      A float that, as it grows, controls the likeliness of triggering
     *                              early expiration. 0 disables it, INF forces immediate expiration.
     *                              The default (or providing null) is implementation dependent but should
     *                              typically be 1.0, which should provide optimal stampede protection.
     *                              See https://en.wikipedia.org/wiki/Cache_stampede#Probabilistic_early_expiration
     * @param array      &$metadata The metadata of the cached item {@see ItemInterface::getMetadata()}
     *
     * @return T
     *
     * @throws InvalidArgumentException When $key is not valid or when $beta is negative
     */
    public function get(string $key, callable $callback, float $beta = null, array &$metadata = null): mixed;

    /**
     * Removes an item from the pool.
     *
     * @param string $key The key to delete
     *
     * @return bool True if the item was successfully removed, false if there was any error
     *
     * @throws InvalidArgumentException When $key is not valid
     */
    public function delete(string $key): bool;
}
