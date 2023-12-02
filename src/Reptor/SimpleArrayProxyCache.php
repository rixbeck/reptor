<?php

/**
 * @author Rix Beck <rix@neologik.hu>
 */
declare(strict_types=1);

namespace brix\Reptor;

use Psr\SimpleCache\CacheInterface;

/**
 * Class SimpleArrayProxyCache
 *
 * @deprecated
 * @author Rix Beck <rix@neologik.hu>
 */
class SimpleArrayProxyCache implements CacheInterface
{
    protected \ArrayObject $cache;

    public function __construct()
    {
        $this->cache = new \ArrayObject();
    }

    public function clear(): bool
    {
        $this->cache->exchangeArray([]);

        return true;
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        foreach ($keys as $key) {
            yield $key => $this->get($key, $default);
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->cache->offsetExists($key)) {
            return $this->cache[$key];
        }
        $value = $default($key, $this);

        return $this->set($key, $value) ? $value : null;
    }

    public function set(string $key, mixed $value, \DateInterval|int|null $ttl = null): bool
    {
        $this->cache[$key] = $value;

        return true;
    }

    public function setMultiple(iterable $values, \DateInterval|int|null $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->cache[$key]);

        return true;
    }

    public function has(string $key): bool
    {
        return $this->cache->offsetExists($key);
    }
}
