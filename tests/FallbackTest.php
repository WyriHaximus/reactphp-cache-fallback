<?php declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use React\Cache\CacheInterface;
use function React\Promise\reject;
use function React\Promise\resolve;
use WyriHaximus\AsyncTestUtilities\AsyncTestCase;
use WyriHaximus\React\Cache\Fallback;

/**
 * @internal
 */
final class FallbackTest extends AsyncTestCase
{
    public function testGetPrimaryHasItem(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $default = null;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->get($key, $default)->shouldBeCalled()->willReturn(resolve($json));
        $primary->set($key, $json)->shouldBeCalled()->willReturn(resolve($json));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->get($key)->shouldNotBeCalled();

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertSame($json, $this->await($fallbackCache->get($key)));
    }

    public function testGetFallbackHasItemAndWIllBeAddedToPrimary(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $default = null;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->get($key, $default)->shouldBeCalled()->willReturn(resolve($default));
        $primary->set($key, $json)->shouldBeCalled();

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->get($key, $default)->shouldBeCalled()->willReturn(resolve($json));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertSame($json, $this->await($fallbackCache->get($key)));
    }

    public function testSet(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->set($key, $json, $ttl));
        self::assertTrue($result);
    }

    public function testSetOneFails(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(false));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->set($key, $json, $ttl));
        self::assertFalse($result);
    }

    public function testSetException(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->set($key, $json, $ttl)->shouldBeCalled()->wilLReturn(reject(new \Exception('fail!')));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->set($key, $json, $ttl));
        self::assertFalse($result);
    }

    public function testRemove(): void
    {
        $key = 'sleutel';

        $primary = $this->prophesize(CacheInterface::class);
        $primary->delete($key)->shouldBeCalled()->wilLReturn(resolve(true));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->delete($key)->shouldBeCalled()->wilLReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertTrue($this->await($fallbackCache->delete($key)));
    }

    public function testRemoveException(): void
    {
        $key = 'sleutel';

        $primary = $this->prophesize(CacheInterface::class);
        $primary->delete($key)->shouldBeCalled()->wilLReturn(reject(new \Exception('fail!')));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->delete($key)->shouldBeCalled()->wilLReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertFalse($this->await($fallbackCache->delete($key)));
    }

    public function testHas(): void
    {
        $key = 'sleutel';

        $primary = $this->prophesize(CacheInterface::class);
        $primary->has($key)->shouldBeCalled()->wilLReturn(resolve(true));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->has($key)->shouldBeCalled()->wilLReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertTrue($this->await($fallbackCache->has($key)));
    }

    public function testHasException(): void
    {
        $key = 'sleutel';

        $primary = $this->prophesize(CacheInterface::class);
        $primary->has($key)->shouldBeCalled()->wilLReturn(reject(new \Exception('fail!')));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->has($key)->shouldBeCalled()->wilLReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertFalse($this->await($fallbackCache->has($key)));
    }

    public function testClear(): void
    {
        $primary = $this->prophesize(CacheInterface::class);
        $primary->clear()->shouldBeCalled()->wilLReturn(resolve(true));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->clear()->shouldBeCalled()->wilLReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertTrue($this->await($fallbackCache->clear()));
    }

    public function testClearException(): void
    {
        $primary = $this->prophesize(CacheInterface::class);
        $primary->clear()->shouldBeCalled()->wilLReturn(reject(new \Exception('fail!')));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->clear()->shouldBeCalled()->wilLReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        self::assertFalse($this->await($fallbackCache->clear()));
    }

    public function testSetMultiple(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->setMultiple([$key => $json], $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->setMultiple([$key => $json], $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->setMultiple([$key => $json], $ttl));
        self::assertTrue($result);
    }

    public function testSetMultipleOneFails(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->setMultiple([$key => $json], $ttl)->shouldBeCalled()->willReturn(resolve(false));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->setMultiple([$key => $json], $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->setMultiple([$key => $json], $ttl));
        self::assertFalse($result);
    }

    public function testSetMultipleException(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->setMultiple([$key => $json], $ttl)->shouldBeCalled()->wilLReturn(reject(new \Exception('fail!')));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->setMultiple([$key => $json], $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->setMultiple([$key => $json], $ttl));
        self::assertFalse($result);
    }

    public function testDeleteMultiple(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];

        $primary = $this->prophesize(CacheInterface::class);
        $primary->deleteMultiple([$key => $json])->shouldBeCalled()->willReturn(resolve(true));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->deleteMultiple([$key => $json])->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->deleteMultiple([$key => $json]));
        self::assertTrue($result);
    }

    public function testDeleteMultipleOneFails(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];

        $primary = $this->prophesize(CacheInterface::class);
        $primary->deleteMultiple([$key => $json])->shouldBeCalled()->willReturn(resolve(false));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->deleteMultiple([$key => $json])->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->deleteMultiple([$key => $json]));
        self::assertFalse($result);
    }

    public function testDeleteMultipleException(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];

        $primary = $this->prophesize(CacheInterface::class);
        $primary->deleteMultiple([$key => $json])->shouldBeCalled()->wilLReturn(reject(new \Exception('fail!')));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->deleteMultiple([$key => $json])->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->deleteMultiple([$key => $json]));
        self::assertFalse($result);
    }

    public function testGetMultiple(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $default = null;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->getMultiple([$key], $default)->shouldBeCalled()->willReturn(resolve([$key => $json]));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->getMultiple([$key], $default)->shouldNotBeCalled();

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->getMultiple([$key]));
        self::assertSame([$key => $json], $result);
    }

    public function testGetMultipleOneFails(): void
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $default = null;

        $primary = $this->prophesize(CacheInterface::class);
        $primary->getMultiple([$key], $default)->shouldBeCalled()->willReturn(resolve([$key => null]));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->getMultiple([$key], $default)->shouldBeCalled()->willReturn(resolve([$key => $json]));

        $fallbackCache = new Fallback($primary->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->getMultiple([$key]));
        self::assertSame([$key => $json], $result);
    }
}
