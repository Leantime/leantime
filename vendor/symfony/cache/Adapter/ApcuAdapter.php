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

use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\Exception\CacheException;
use Symfony\Component\Cache\Marshaller\MarshallerInterface;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
class ApcuAdapter extends AbstractAdapter
{
    private ?MarshallerInterface $marshaller;

    /**
     * @throws CacheException if APCu is not enabled
     */
    public function __construct(string $namespace = '', int $defaultLifetime = 0, string $version = null, MarshallerInterface $marshaller = null)
    {
        if (!static::isSupported()) {
            throw new CacheException('APCu is not enabled.');
        }
        if ('cli' === \PHP_SAPI) {
            ini_set('apc.use_request_time', 0);
        }
        parent::__construct($namespace, $defaultLifetime);

        if (null !== $version) {
            CacheItem::validateKey($version);

            if (!apcu_exists($version.'@'.$namespace)) {
                $this->doClear($namespace);
                apcu_add($version.'@'.$namespace, null);
            }
        }

        $this->marshaller = $marshaller;
    }

    /**
     * @return bool
     */
    public static function isSupported()
    {
        return \function_exists('apcu_fetch') && filter_var(\ini_get('apc.enabled'), \FILTER_VALIDATE_BOOL);
    }

    protected function doFetch(array $ids): iterable
    {
        $unserializeCallbackHandler = ini_set('unserialize_callback_func', __CLASS__.'::handleUnserializeCallback');
        try {
            $values = [];
            foreach (apcu_fetch($ids, $ok) ?: [] as $k => $v) {
                if (null !== $v || $ok) {
                    $values[$k] = null !== $this->marshaller ? $this->marshaller->unmarshall($v) : $v;
                }
            }

            return $values;
        } catch (\Error $e) {
            throw new \ErrorException($e->getMessage(), $e->getCode(), \E_ERROR, $e->getFile(), $e->getLine());
        } finally {
            ini_set('unserialize_callback_func', $unserializeCallbackHandler);
        }
    }

    protected function doHave(string $id): bool
    {
        return apcu_exists($id);
    }

    protected function doClear(string $namespace): bool
    {
        return isset($namespace[0]) && class_exists(\APCUIterator::class, false) && ('cli' !== \PHP_SAPI || filter_var(\ini_get('apc.enable_cli'), \FILTER_VALIDATE_BOOL))
            ? apcu_delete(new \APCUIterator(sprintf('/^%s/', preg_quote($namespace, '/')), \APC_ITER_KEY))
            : apcu_clear_cache();
    }

    protected function doDelete(array $ids): bool
    {
        foreach ($ids as $id) {
            apcu_delete($id);
        }

        return true;
    }

    protected function doSave(array $values, int $lifetime): array|bool
    {
        if (null !== $this->marshaller && (!$values = $this->marshaller->marshall($values, $failed))) {
            return $failed;
        }

        try {
            if (false === $failures = apcu_store($values, null, $lifetime)) {
                $failures = $values;
            }

            return array_keys($failures);
        } catch (\Throwable $e) {
            if (1 === \count($values)) {
                // Workaround https://github.com/krakjoe/apcu/issues/170
                apcu_delete(array_key_first($values));
            }

            throw $e;
        }
    }
}
