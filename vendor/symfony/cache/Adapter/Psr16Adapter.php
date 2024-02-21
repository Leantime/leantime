<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Adapter;

use Psr\SimpleCache\CacheInterface;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Component\Cache\Traits\ProxyTrait;

/**
 * Turns a PSR-16 cache into a PSR-6 one.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
class Psr16Adapter extends AbstractAdapter implements PruneableInterface, ResettableInterface
{
    use ProxyTrait;

    /**
     * @internal
     */
    protected const NS_SEPARATOR = '_';

    private object $miss;

    public function __construct(CacheInterface $pool, string $namespace = '', int $defaultLifetime = 0)
    {
        parent::__construct($namespace, $defaultLifetime);

        $this->pool = $pool;
        $this->miss = new \stdClass();
    }

    protected function doFetch(array $ids): iterable
    {
        foreach ($this->pool->getMultiple($ids, $this->miss) as $key => $value) {
            if ($this->miss !== $value) {
                yield $key => $value;
            }
        }
    }

    protected function doHave(string $id): bool
    {
        return $this->pool->has($id);
    }

    protected function doClear(string $namespace): bool
    {
        return $this->pool->clear();
    }

    protected function doDelete(array $ids): bool
    {
        return $this->pool->deleteMultiple($ids);
    }

    protected function doSave(array $values, int $lifetime): array|bool
    {
        return $this->pool->setMultiple($values, 0 === $lifetime ? null : $lifetime);
    }
}
