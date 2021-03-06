<?php

declare(strict_types=1);

namespace WyriHaximus\React\Cache;

use React\Cache\CacheInterface;

use function array_filter;
use function array_keys;
use function count;
use function React\Promise\all;

final class Fallback implements CacheInterface
{
    private CacheInterface $primary;

    private CacheInterface $fallback;

    public function __construct(CacheInterface $primary, CacheInterface $fallback)
    {
        $this->primary  = $primary;
        $this->fallback = $fallback;
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function get($key, $default = null)
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         * @psalm-suppress MissingClosureParamType
         * @psalm-suppress MissingClosureReturnType
         */
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

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function set($key, $value, $ttl = null)
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         */
        return all([
            'primary' => $this->primary->set($key, $value, $ttl),
            'fallback' => $this->fallback->set($key, $value, $ttl),
        ])->then(static function (array $bool): bool {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, static function (): bool {
            return false;
        });
    }

    /**
     * @inheritDoc
     */
    public function delete($key)
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         */
        return all([
            'primary' => $this->primary->delete($key),
            'fallback' => $this->fallback->delete($key),
        ])->then(static function (array $bool): bool {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, static function (): bool {
            return false;
        });
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function getMultiple(array $keys, $default = null)
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         */
        return $this->primary->getMultiple($keys, $default)->then(function (array $items) use ($default) {
            $keys = array_keys(array_filter($items, static function ($value): bool {
                return $value === null;
            }));

            if (count($keys) === 0) {
                return $items;
            }

            return $this->fallback->getMultiple($keys, $default)->then(static function (array $fallbackItems) use ($items): array {
                foreach ($fallbackItems as $key => $value) {
                    $items[$key] = $value;
                }

                return $items;
            });
        });
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function setMultiple(array $values, $ttl = null)
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         */
        return all([
            'primary' => $this->primary->setMultiple($values, $ttl),
            'fallback' => $this->fallback->setMultiple($values, $ttl),
        ])->then(static function (array $bool): bool {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, static function (): bool {
            return false;
        });
    }

    /**
     * @inheritDoc
     */
    public function deleteMultiple(array $keys)
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         */
        return all([
            'primary' => $this->primary->deleteMultiple($keys),
            'fallback' => $this->fallback->deleteMultiple($keys),
        ])->then(static function (array $bool): bool {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, static function (): bool {
            return false;
        });
    }

    /**
     * @inheritDoc
     */
    public function clear()
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         */
        return all([
            'primary' => $this->primary->clear(),
            'fallback' => $this->fallback->clear(),
        ])->then(static function (array $bool): bool {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, static function (): bool {
            return false;
        });
    }

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        /**
         * @psalm-suppress TooManyTemplateParams
         */
        return all([
            'primary' => $this->primary->has($key),
            'fallback' => $this->fallback->has($key),
        ])->then(static function (array $bool): bool {
            return $bool['primary'] === true && $bool['fallback'] === true;
        }, static function (): bool {
            return false;
        });
    }
}
