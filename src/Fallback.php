<?php declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use React\Cache\CacheInterface;
use function React\Promise\all;

final class Fallback implements CacheInterface
{
    /** @var CacheInterface */
    private $primairy;

    /** @var CacheInterface */
    private $fallback;

    /**
     * @param CacheInterface $primairy
     * @param CacheInterface $fallback
     */
    public function __construct(CacheInterface $primairy, CacheInterface $fallback)
    {
        $this->primairy = $primairy;
        $this->fallback = $fallback;
    }

    public function get($key, $default = null)
    {
        return $this->primairy->get($key, $default)->then(function ($value) use ($key, $default) {
            if ($value === null || $value === $default) {
                return $this->fallback->get($key, $default);
            }

            return $value;
        })->then(function ($value) use ($key) {
            $this->primairy->set($key, $value);

            return $value;
        });
    }

    public function set($key, $value, $ttl = null)
    {
        return all([
            'primairy' => $this->primairy->set($key, $value, $ttl),
            'fallback' => $this->fallback->set($key, $value, $ttl),
        ])->then(function (array $bool) {
            return $bool['primairy'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }

    public function delete($key)
    {
        return all([
            'primairy' => $this->primairy->delete($key),
            'fallback' => $this->fallback->delete($key),
        ])->then(function (array $bool) {
            return $bool['primairy'] === true && $bool['fallback'] === true;
        }, function () {
            return false;
        });
    }
}
