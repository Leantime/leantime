<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Cache\Traits;

use Symfony\Component\VarExporter\LazyObjectInterface;
use Symfony\Component\VarExporter\LazyProxyTrait;
use Symfony\Contracts\Service\ResetInterface;

// Help opcache.preload discover always-needed symbols
class_exists(\Symfony\Component\VarExporter\Internal\Hydrator::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectRegistry::class);
class_exists(\Symfony\Component\VarExporter\Internal\LazyObjectState::class);

/**
 * @internal
 */
class Redis6Proxy extends \Redis implements ResetInterface, LazyObjectInterface
{
    use LazyProxyTrait {
        resetLazyObject as reset;
    }

    private const LAZY_OBJECT_PROPERTY_SCOPES = [];

    public function __construct($options = null)
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->__construct(...\func_get_args());
    }

    public function _compress($value): string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_compress(...\func_get_args());
    }

    public function _uncompress($value): string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_uncompress(...\func_get_args());
    }

    public function _prefix($key): string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_prefix(...\func_get_args());
    }

    public function _serialize($value): string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_serialize(...\func_get_args());
    }

    public function _unserialize($value): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_unserialize(...\func_get_args());
    }

    public function _pack($value): string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_pack(...\func_get_args());
    }

    public function _unpack($value): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->_unpack(...\func_get_args());
    }

    public function acl($subcmd, ...$args): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->acl(...\func_get_args());
    }

    public function append($key, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->append(...\func_get_args());
    }

    public function auth(#[\SensitiveParameter] $credentials): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->auth(...\func_get_args());
    }

    public function bgSave(): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bgSave(...\func_get_args());
    }

    public function bgrewriteaof(): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bgrewriteaof(...\func_get_args());
    }

    public function bitcount($key, $start = 0, $end = -1, $bybit = false): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bitcount(...\func_get_args());
    }

    public function bitop($operation, $deskey, $srckey, ...$other_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bitop(...\func_get_args());
    }

    public function bitpos($key, $bit, $start = 0, $end = -1, $bybit = false): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bitpos(...\func_get_args());
    }

    public function blPop($key_or_keys, $timeout_or_key, ...$extra_args): \Redis|array|false|null
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->blPop(...\func_get_args());
    }

    public function brPop($key_or_keys, $timeout_or_key, ...$extra_args): \Redis|array|false|null
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->brPop(...\func_get_args());
    }

    public function brpoplpush($src, $dst, $timeout): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->brpoplpush(...\func_get_args());
    }

    public function bzPopMax($key, $timeout_or_key, ...$extra_args): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bzPopMax(...\func_get_args());
    }

    public function bzPopMin($key, $timeout_or_key, ...$extra_args): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bzPopMin(...\func_get_args());
    }

    public function bzmpop($timeout, $keys, $from, $count = 1): \Redis|array|false|null
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->bzmpop(...\func_get_args());
    }

    public function zmpop($keys, $from, $count = 1): \Redis|array|false|null
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zmpop(...\func_get_args());
    }

    public function blmpop($timeout, $keys, $from, $count = 1): \Redis|array|false|null
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->blmpop(...\func_get_args());
    }

    public function lmpop($keys, $from, $count = 1): \Redis|array|false|null
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lmpop(...\func_get_args());
    }

    public function clearLastError(): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->clearLastError(...\func_get_args());
    }

    public function client($opt, ...$args): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->client(...\func_get_args());
    }

    public function close(): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->close(...\func_get_args());
    }

    public function command($opt = null, ...$args): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->command(...\func_get_args());
    }

    public function config($operation, $key_or_settings = null, $value = null): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->config(...\func_get_args());
    }

    public function connect($host, $port = 6379, $timeout = 0, $persistent_id = null, $retry_interval = 0, $read_timeout = 0, $context = null): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->connect(...\func_get_args());
    }

    public function copy($src, $dst, $options = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->copy(...\func_get_args());
    }

    public function dbSize(): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->dbSize(...\func_get_args());
    }

    public function debug($key): \Redis|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->debug(...\func_get_args());
    }

    public function decr($key, $by = 1): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->decr(...\func_get_args());
    }

    public function decrBy($key, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->decrBy(...\func_get_args());
    }

    public function del($key, ...$other_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->del(...\func_get_args());
    }

    public function delete($key, ...$other_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->delete(...\func_get_args());
    }

    public function discard(): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->discard(...\func_get_args());
    }

    public function dump($key): \Redis|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->dump(...\func_get_args());
    }

    public function echo($str): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->echo(...\func_get_args());
    }

    public function eval($script, $args = [], $num_keys = 0): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->eval(...\func_get_args());
    }

    public function eval_ro($script_sha, $args = [], $num_keys = 0): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->eval_ro(...\func_get_args());
    }

    public function evalsha($sha1, $args = [], $num_keys = 0): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->evalsha(...\func_get_args());
    }

    public function evalsha_ro($sha1, $args = [], $num_keys = 0): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->evalsha_ro(...\func_get_args());
    }

    public function exec(): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->exec(...\func_get_args());
    }

    public function exists($key, ...$other_keys): \Redis|bool|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->exists(...\func_get_args());
    }

    public function expire($key, $timeout, $mode = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->expire(...\func_get_args());
    }

    public function expireAt($key, $timestamp, $mode = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->expireAt(...\func_get_args());
    }

    public function failover($to = null, $abort = false, $timeout = 0): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->failover(...\func_get_args());
    }

    public function expiretime($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->expiretime(...\func_get_args());
    }

    public function pexpiretime($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pexpiretime(...\func_get_args());
    }

    public function fcall($fn, $keys = [], $args = []): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->fcall(...\func_get_args());
    }

    public function fcall_ro($fn, $keys = [], $args = []): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->fcall_ro(...\func_get_args());
    }

    public function flushAll($sync = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->flushAll(...\func_get_args());
    }

    public function flushDB($sync = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->flushDB(...\func_get_args());
    }

    public function function($operation, ...$args): \Redis|array|bool|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->function(...\func_get_args());
    }

    public function geoadd($key, $lng, $lat, $member, ...$other_triples_and_options): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geoadd(...\func_get_args());
    }

    public function geodist($key, $src, $dst, $unit = null): \Redis|false|float
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geodist(...\func_get_args());
    }

    public function geohash($key, $member, ...$other_members): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geohash(...\func_get_args());
    }

    public function geopos($key, $member, ...$other_members): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geopos(...\func_get_args());
    }

    public function georadius($key, $lng, $lat, $radius, $unit, $options = []): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->georadius(...\func_get_args());
    }

    public function georadius_ro($key, $lng, $lat, $radius, $unit, $options = []): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->georadius_ro(...\func_get_args());
    }

    public function georadiusbymember($key, $member, $radius, $unit, $options = []): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->georadiusbymember(...\func_get_args());
    }

    public function georadiusbymember_ro($key, $member, $radius, $unit, $options = []): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->georadiusbymember_ro(...\func_get_args());
    }

    public function geosearch($key, $position, $shape, $unit, $options = []): array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geosearch(...\func_get_args());
    }

    public function geosearchstore($dst, $src, $position, $shape, $unit, $options = []): \Redis|array|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->geosearchstore(...\func_get_args());
    }

    public function get($key): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->get(...\func_get_args());
    }

    public function getAuth(): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getAuth(...\func_get_args());
    }

    public function getBit($key, $idx): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getBit(...\func_get_args());
    }

    public function getEx($key, $options = []): \Redis|bool|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getEx(...\func_get_args());
    }

    public function getDBNum(): int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getDBNum(...\func_get_args());
    }

    public function getDel($key): \Redis|bool|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getDel(...\func_get_args());
    }

    public function getHost(): string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getHost(...\func_get_args());
    }

    public function getLastError(): ?string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getLastError(...\func_get_args());
    }

    public function getMode(): int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getMode(...\func_get_args());
    }

    public function getOption($option): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getOption(...\func_get_args());
    }

    public function getPersistentID(): ?string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getPersistentID(...\func_get_args());
    }

    public function getPort(): int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getPort(...\func_get_args());
    }

    public function getRange($key, $start, $end): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getRange(...\func_get_args());
    }

    public function lcs($key1, $key2, $options = null): \Redis|array|false|int|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lcs(...\func_get_args());
    }

    public function getReadTimeout(): float
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getReadTimeout(...\func_get_args());
    }

    public function getset($key, $value): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getset(...\func_get_args());
    }

    public function getTimeout(): false|float
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getTimeout(...\func_get_args());
    }

    public function getTransferredBytes(): array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->getTransferredBytes(...\func_get_args());
    }

    public function clearTransferredBytes(): void
    {
        ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->clearTransferredBytes(...\func_get_args());
    }

    public function hDel($key, $field, ...$other_fields): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hDel(...\func_get_args());
    }

    public function hExists($key, $field): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hExists(...\func_get_args());
    }

    public function hGet($key, $member): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hGet(...\func_get_args());
    }

    public function hGetAll($key): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hGetAll(...\func_get_args());
    }

    public function hIncrBy($key, $field, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hIncrBy(...\func_get_args());
    }

    public function hIncrByFloat($key, $field, $value): \Redis|false|float
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hIncrByFloat(...\func_get_args());
    }

    public function hKeys($key): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hKeys(...\func_get_args());
    }

    public function hLen($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hLen(...\func_get_args());
    }

    public function hMget($key, $fields): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hMget(...\func_get_args());
    }

    public function hMset($key, $fieldvals): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hMset(...\func_get_args());
    }

    public function hRandField($key, $options = null): \Redis|array|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hRandField(...\func_get_args());
    }

    public function hSet($key, $member, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hSet(...\func_get_args());
    }

    public function hSetNx($key, $field, $value): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hSetNx(...\func_get_args());
    }

    public function hStrLen($key, $field): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hStrLen(...\func_get_args());
    }

    public function hVals($key): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hVals(...\func_get_args());
    }

    public function hscan($key, &$iterator, $pattern = null, $count = 0): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->hscan($key, $iterator, ...\array_slice(\func_get_args(), 2));
    }

    public function incr($key, $by = 1): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->incr(...\func_get_args());
    }

    public function incrBy($key, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->incrBy(...\func_get_args());
    }

    public function incrByFloat($key, $value): \Redis|false|float
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->incrByFloat(...\func_get_args());
    }

    public function info(...$sections): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->info(...\func_get_args());
    }

    public function isConnected(): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->isConnected(...\func_get_args());
    }

    public function keys($pattern)
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->keys(...\func_get_args());
    }

    public function lInsert($key, $pos, $pivot, $value)
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lInsert(...\func_get_args());
    }

    public function lLen($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lLen(...\func_get_args());
    }

    public function lMove($src, $dst, $wherefrom, $whereto): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lMove(...\func_get_args());
    }

    public function blmove($src, $dst, $wherefrom, $whereto, $timeout): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->blmove(...\func_get_args());
    }

    public function lPop($key, $count = 0): \Redis|array|bool|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lPop(...\func_get_args());
    }

    public function lPos($key, $value, $options = null): \Redis|array|bool|int|null
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lPos(...\func_get_args());
    }

    public function lPush($key, ...$elements): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lPush(...\func_get_args());
    }

    public function rPush($key, ...$elements): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->rPush(...\func_get_args());
    }

    public function lPushx($key, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lPushx(...\func_get_args());
    }

    public function rPushx($key, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->rPushx(...\func_get_args());
    }

    public function lSet($key, $index, $value): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lSet(...\func_get_args());
    }

    public function lastSave(): int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lastSave(...\func_get_args());
    }

    public function lindex($key, $index): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lindex(...\func_get_args());
    }

    public function lrange($key, $start, $end): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lrange(...\func_get_args());
    }

    public function lrem($key, $value, $count = 0): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->lrem(...\func_get_args());
    }

    public function ltrim($key, $start, $end): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ltrim(...\func_get_args());
    }

    public function mget($keys): \Redis|array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->mget(...\func_get_args());
    }

    public function migrate($host, $port, $key, $dstdb, $timeout, $copy = false, $replace = false, #[\SensitiveParameter] $credentials = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->migrate(...\func_get_args());
    }

    public function move($key, $index): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->move(...\func_get_args());
    }

    public function mset($key_values): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->mset(...\func_get_args());
    }

    public function msetnx($key_values): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->msetnx(...\func_get_args());
    }

    public function multi($value = \Redis::MULTI): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->multi(...\func_get_args());
    }

    public function object($subcommand, $key): \Redis|false|int|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->object(...\func_get_args());
    }

    public function open($host, $port = 6379, $timeout = 0, $persistent_id = null, $retry_interval = 0, $read_timeout = 0, $context = null): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->open(...\func_get_args());
    }

    public function pconnect($host, $port = 6379, $timeout = 0, $persistent_id = null, $retry_interval = 0, $read_timeout = 0, $context = null): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pconnect(...\func_get_args());
    }

    public function persist($key): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->persist(...\func_get_args());
    }

    public function pexpire($key, $timeout, $mode = null): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pexpire(...\func_get_args());
    }

    public function pexpireAt($key, $timestamp, $mode = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pexpireAt(...\func_get_args());
    }

    public function pfadd($key, $elements): \Redis|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pfadd(...\func_get_args());
    }

    public function pfcount($key_or_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pfcount(...\func_get_args());
    }

    public function pfmerge($dst, $srckeys): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pfmerge(...\func_get_args());
    }

    public function ping($message = null): \Redis|bool|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ping(...\func_get_args());
    }

    public function pipeline(): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pipeline(...\func_get_args());
    }

    public function popen($host, $port = 6379, $timeout = 0, $persistent_id = null, $retry_interval = 0, $read_timeout = 0, $context = null): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->popen(...\func_get_args());
    }

    public function psetex($key, $expire, $value): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->psetex(...\func_get_args());
    }

    public function psubscribe($patterns, $cb): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->psubscribe(...\func_get_args());
    }

    public function pttl($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pttl(...\func_get_args());
    }

    public function publish($channel, $message): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->publish(...\func_get_args());
    }

    public function pubsub($command, $arg = null): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->pubsub(...\func_get_args());
    }

    public function punsubscribe($patterns): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->punsubscribe(...\func_get_args());
    }

    public function rPop($key, $count = 0): \Redis|array|bool|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->rPop(...\func_get_args());
    }

    public function randomKey(): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->randomKey(...\func_get_args());
    }

    public function rawcommand($command, ...$args): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->rawcommand(...\func_get_args());
    }

    public function rename($old_name, $new_name): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->rename(...\func_get_args());
    }

    public function renameNx($key_src, $key_dst): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->renameNx(...\func_get_args());
    }

    public function restore($key, $ttl, $value, $options = null): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->restore(...\func_get_args());
    }

    public function role(): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->role(...\func_get_args());
    }

    public function rpoplpush($srckey, $dstkey): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->rpoplpush(...\func_get_args());
    }

    public function sAdd($key, $value, ...$other_values): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sAdd(...\func_get_args());
    }

    public function sAddArray($key, $values): int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sAddArray(...\func_get_args());
    }

    public function sDiff($key, ...$other_keys): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sDiff(...\func_get_args());
    }

    public function sDiffStore($dst, $key, ...$other_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sDiffStore(...\func_get_args());
    }

    public function sInter($key, ...$other_keys): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sInter(...\func_get_args());
    }

    public function sintercard($keys, $limit = -1): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sintercard(...\func_get_args());
    }

    public function sInterStore($key, ...$other_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sInterStore(...\func_get_args());
    }

    public function sMembers($key): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sMembers(...\func_get_args());
    }

    public function sMisMember($key, $member, ...$other_members): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sMisMember(...\func_get_args());
    }

    public function sMove($src, $dst, $value): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sMove(...\func_get_args());
    }

    public function sPop($key, $count = 0): \Redis|array|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sPop(...\func_get_args());
    }

    public function sRandMember($key, $count = 0): \Redis|array|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sRandMember(...\func_get_args());
    }

    public function sUnion($key, ...$other_keys): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sUnion(...\func_get_args());
    }

    public function sUnionStore($dst, $key, ...$other_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sUnionStore(...\func_get_args());
    }

    public function save(): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->save(...\func_get_args());
    }

    public function scan(&$iterator, $pattern = null, $count = 0, $type = null): array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->scan($iterator, ...\array_slice(\func_get_args(), 1));
    }

    public function scard($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->scard(...\func_get_args());
    }

    public function script($command, ...$args): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->script(...\func_get_args());
    }

    public function select($db): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->select(...\func_get_args());
    }

    public function set($key, $value, $options = null): \Redis|bool|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->set(...\func_get_args());
    }

    public function setBit($key, $idx, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->setBit(...\func_get_args());
    }

    public function setRange($key, $index, $value): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->setRange(...\func_get_args());
    }

    public function setOption($option, $value): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->setOption(...\func_get_args());
    }

    public function setex($key, $expire, $value)
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->setex(...\func_get_args());
    }

    public function setnx($key, $value): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->setnx(...\func_get_args());
    }

    public function sismember($key, $value): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sismember(...\func_get_args());
    }

    public function slaveof($host = null, $port = 6379): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->slaveof(...\func_get_args());
    }

    public function replicaof($host = null, $port = 6379): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->replicaof(...\func_get_args());
    }

    public function touch($key_or_array, ...$more_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->touch(...\func_get_args());
    }

    public function slowlog($operation, $length = 0): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->slowlog(...\func_get_args());
    }

    public function sort($key, $options = null): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sort(...\func_get_args());
    }

    public function sort_ro($key, $options = null): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sort_ro(...\func_get_args());
    }

    public function sortAsc($key, $pattern = null, $get = null, $offset = -1, $count = -1, $store = null): array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sortAsc(...\func_get_args());
    }

    public function sortAscAlpha($key, $pattern = null, $get = null, $offset = -1, $count = -1, $store = null): array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sortAscAlpha(...\func_get_args());
    }

    public function sortDesc($key, $pattern = null, $get = null, $offset = -1, $count = -1, $store = null): array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sortDesc(...\func_get_args());
    }

    public function sortDescAlpha($key, $pattern = null, $get = null, $offset = -1, $count = -1, $store = null): array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sortDescAlpha(...\func_get_args());
    }

    public function srem($key, $value, ...$other_values): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->srem(...\func_get_args());
    }

    public function sscan($key, &$iterator, $pattern = null, $count = 0): array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sscan($key, $iterator, ...\array_slice(\func_get_args(), 2));
    }

    public function ssubscribe($channels, $cb): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ssubscribe(...\func_get_args());
    }

    public function strlen($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->strlen(...\func_get_args());
    }

    public function subscribe($channels, $cb): bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->subscribe(...\func_get_args());
    }

    public function sunsubscribe($channels): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->sunsubscribe(...\func_get_args());
    }

    public function swapdb($src, $dst): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->swapdb(...\func_get_args());
    }

    public function time(): \Redis|array
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->time(...\func_get_args());
    }

    public function ttl($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->ttl(...\func_get_args());
    }

    public function type($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->type(...\func_get_args());
    }

    public function unlink($key, ...$other_keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->unlink(...\func_get_args());
    }

    public function unsubscribe($channels): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->unsubscribe(...\func_get_args());
    }

    public function unwatch(): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->unwatch(...\func_get_args());
    }

    public function watch($key, ...$other_keys): \Redis|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->watch(...\func_get_args());
    }

    public function wait($numreplicas, $timeout): false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->wait(...\func_get_args());
    }

    public function xack($key, $group, $ids): false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xack(...\func_get_args());
    }

    public function xadd($key, $id, $values, $maxlen = 0, $approx = false, $nomkstream = false): \Redis|false|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xadd(...\func_get_args());
    }

    public function xautoclaim($key, $group, $consumer, $min_idle, $start, $count = -1, $justid = false): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xautoclaim(...\func_get_args());
    }

    public function xclaim($key, $group, $consumer, $min_idle, $ids, $options): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xclaim(...\func_get_args());
    }

    public function xdel($key, $ids): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xdel(...\func_get_args());
    }

    public function xgroup($operation, $key = null, $group = null, $id_or_consumer = null, $mkstream = false, $entries_read = -2): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xgroup(...\func_get_args());
    }

    public function xinfo($operation, $arg1 = null, $arg2 = null, $count = -1): mixed
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xinfo(...\func_get_args());
    }

    public function xlen($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xlen(...\func_get_args());
    }

    public function xpending($key, $group, $start = null, $end = null, $count = -1, $consumer = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xpending(...\func_get_args());
    }

    public function xrange($key, $start, $end, $count = -1): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xrange(...\func_get_args());
    }

    public function xread($streams, $count = -1, $block = -1): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xread(...\func_get_args());
    }

    public function xreadgroup($group, $consumer, $streams, $count = 1, $block = 1): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xreadgroup(...\func_get_args());
    }

    public function xrevrange($key, $end, $start, $count = -1): \Redis|array|bool
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xrevrange(...\func_get_args());
    }

    public function xtrim($key, $threshold, $approx = false, $minid = false, $limit = -1): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->xtrim(...\func_get_args());
    }

    public function zAdd($key, $score_or_options, ...$more_scores_and_mems): \Redis|false|float|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zAdd(...\func_get_args());
    }

    public function zCard($key): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zCard(...\func_get_args());
    }

    public function zCount($key, $start, $end): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zCount(...\func_get_args());
    }

    public function zIncrBy($key, $value, $member): \Redis|false|float
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zIncrBy(...\func_get_args());
    }

    public function zLexCount($key, $min, $max): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zLexCount(...\func_get_args());
    }

    public function zMscore($key, $member, ...$other_members): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zMscore(...\func_get_args());
    }

    public function zPopMax($key, $count = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zPopMax(...\func_get_args());
    }

    public function zPopMin($key, $count = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zPopMin(...\func_get_args());
    }

    public function zRange($key, $start, $end, $options = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRange(...\func_get_args());
    }

    public function zRangeByLex($key, $min, $max, $offset = -1, $count = -1): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRangeByLex(...\func_get_args());
    }

    public function zRangeByScore($key, $start, $end, $options = []): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRangeByScore(...\func_get_args());
    }

    public function zrangestore($dstkey, $srckey, $start, $end, $options = null): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zrangestore(...\func_get_args());
    }

    public function zRandMember($key, $options = null): \Redis|array|string
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRandMember(...\func_get_args());
    }

    public function zRank($key, $member): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRank(...\func_get_args());
    }

    public function zRem($key, $member, ...$other_members): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRem(...\func_get_args());
    }

    public function zRemRangeByLex($key, $min, $max): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRemRangeByLex(...\func_get_args());
    }

    public function zRemRangeByRank($key, $start, $end): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRemRangeByRank(...\func_get_args());
    }

    public function zRemRangeByScore($key, $start, $end): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRemRangeByScore(...\func_get_args());
    }

    public function zRevRange($key, $start, $end, $scores = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRevRange(...\func_get_args());
    }

    public function zRevRangeByLex($key, $max, $min, $offset = -1, $count = -1): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRevRangeByLex(...\func_get_args());
    }

    public function zRevRangeByScore($key, $max, $min, $options = []): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRevRangeByScore(...\func_get_args());
    }

    public function zRevRank($key, $member): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zRevRank(...\func_get_args());
    }

    public function zScore($key, $member): \Redis|false|float
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zScore(...\func_get_args());
    }

    public function zdiff($keys, $options = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zdiff(...\func_get_args());
    }

    public function zdiffstore($dst, $keys): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zdiffstore(...\func_get_args());
    }

    public function zinter($keys, $weights = null, $options = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zinter(...\func_get_args());
    }

    public function zintercard($keys, $limit = -1): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zintercard(...\func_get_args());
    }

    public function zinterstore($dst, $keys, $weights = null, $aggregate = null): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zinterstore(...\func_get_args());
    }

    public function zscan($key, &$iterator, $pattern = null, $count = 0): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zscan($key, $iterator, ...\array_slice(\func_get_args(), 2));
    }

    public function zunion($keys, $weights = null, $options = null): \Redis|array|false
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zunion(...\func_get_args());
    }

    public function zunionstore($dst, $keys, $weights = null, $aggregate = null): \Redis|false|int
    {
        return ($this->lazyObjectState->realInstance ??= ($this->lazyObjectState->initializer)())->zunionstore(...\func_get_args());
    }
}
