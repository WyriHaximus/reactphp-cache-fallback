<?php declare(strict_types=1);

namespace WyriHaximus\Tests\React\Cache;

use ApiClients\Tools\TestUtilities\TestCase;
use React\Cache\CacheInterface;
use WyriHaximus\React\Cache\Fallback;
use WyriHaximus\React\Cache\Json;
use function React\Promise\resolve;

final class FallbackTest extends TestCase
{
    public function testGetPrimairyHasItem()
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $default = null;

        $primairy = $this->prophesize(CacheInterface::class);
        $primairy->get($key, $default)->shouldBeCalled()->willReturn(resolve($json));
        $primairy->set($key, $json)->shouldBeCalled()->willReturn(resolve($json));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->get($key)->shouldNotBeCalled();

        $fallbackCache = new Fallback($primairy->reveal(), $fallback->reveal());
        self::assertSame($json, $this->await($fallbackCache->get($key)));
    }

    public function testGetFallbackHasItemAndWIllBeAddedToPrimairy()
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $default = null;

        $primairy = $this->prophesize(CacheInterface::class);
        $primairy->get($key, $default)->shouldBeCalled()->willReturn(resolve($default));
        $primairy->set($key, $json)->shouldBeCalled();

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->get($key, $default)->shouldBeCalled()->willReturn(resolve($json));

        $fallbackCache = new Fallback($primairy->reveal(), $fallback->reveal());
        self::assertSame($json, $this->await($fallbackCache->get($key)));
    }

    public function testSet()
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primairy = $this->prophesize(CacheInterface::class);
        $primairy->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primairy->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->set($key, $json, $ttl));
        self::assertTrue($result);
    }

    public function testSetOneFails()
    {
        $key = 'sleutel';
        $json = [
            'foo' => 'bar',
        ];
        $ttl = 123;

        $primairy = $this->prophesize(CacheInterface::class);
        $primairy->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(false));

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->set($key, $json, $ttl)->shouldBeCalled()->willReturn(resolve(true));

        $fallbackCache = new Fallback($primairy->reveal(), $fallback->reveal());
        $result = $this->await($fallbackCache->set($key, $json, $ttl));
        self::assertFalse($result);
    }

    public function testRemove()
    {
        $key = 'sleutel';

        $primairy = $this->prophesize(CacheInterface::class);
        $primairy->delete($key)->shouldBeCalled();

        $fallback = $this->prophesize(CacheInterface::class);
        $fallback->delete($key)->shouldBeCalled();

        $fallbackCache = new Fallback($primairy->reveal(), $fallback->reveal());
        $fallbackCache->delete($key);
    }
}
