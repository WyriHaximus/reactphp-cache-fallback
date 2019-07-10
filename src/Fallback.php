<?php declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use React\Cache\CacheInterface;
use function React\Promise\all;

final class Fallback implements CacheInterface
{
    /** @var CacheInterface */
    private $primary;

    /** @var CacheInterface */
    private $fallback;

    /**
     * @param CacheInterface $primary
     * @param CacheInterface $fallback
     */
    public function __construct(CacheInterface $primary, CacheInterface $fallback)
    {
        $this->primary = $primary;
        $this->fallback = $fallback;
    }

    public function get($key, $default = null)
    {
        return $this->primary->get($key, $default)->then(function ($value) use ($key, $default) {
            if ($value === null || $value === $default) {
                return $this->fallback->get($key, $default);
            }

            return $value;
        })->then(function ($value) use ($key) {
            $this->primary->set($key, $value);

            return $value;
        });
    }

    public function set($key, $value, $ttl = null)
    {
        return all([
            'primary' => $this->primary->set($key, $value, $ttl),
            'fallback' => $this->fallback->set($key, $value, $ttl),
        ])->then(function (array $bool) {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }

    public function delete($key)
    {
        return all([
            'primary' => $this->primary->delete($key),
            'fallback' => $this->fallback->delete($key),
        ])->then(function (array $bool) {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }

    public function getMultiple(array $keys, $default = null)
    {
        return $this->primary->getMultiple($keys, $default)->then(function (array $items) use ($default) {
            $keys = \array_keys(\array_filter($items, function ($value) {
                return $value === null;
            }));

            if (\count($keys) == 0) {
                return $items;
            }

            return $this->fallback->getMultiple($keys, $default)->then(function (array $fallbackItems) use ($items) {
                foreach ($fallbackItems as $key => $value) {
                    $items[$key] = $value;
                }

                return $items;
            });
        });
    }

    public function setMultiple(array $values, $ttl = null)
    {
        return all([
            'primary' => $this->primary->setMultiple($values, $ttl),
            'fallback' => $this->fallback->setMultiple($values, $ttl),
        ])->then(function (array $bool) {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }

    public function deleteMultiple(array $keys)
    {
        return all([
            'primary' => $this->primary->deleteMultiple($keys),
            'fallback' => $this->fallback->deleteMultiple($keys),
        ])->then(function (array $bool) {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }

    public function clear()
    {
        return all([
            'primary' => $this->primary->clear(),
            'fallback' => $this->fallback->clear(),
        ])->then(function (array $bool) {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }

    public function has($key)
    {
        return all([
            'primary' => $this->primary->has($key),
            'fallback' => $this->fallback->has($key),
        ])->then(function (array $bool) {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }
}
