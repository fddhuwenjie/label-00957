<?php

namespace tests\Unit;

use tests\TestCase;
use Mockery as m;
use think\facade\Cache;
use think\Request;

class RateLimitTest extends TestCase
{
    private string $testIp = '192.168.1.100';
    private int $maxRequests = 60;
    private int $windowSeconds = 60;

    public function rateLimitDataProvider(): array
    {
        return [
            'first request in window' => [
                0,
                true,
                1,
                60
            ],
            'normal request within limit' => [
                30,
                true,
                31,
                30
            ],
            'at the limit boundary' => [
                59,
                true,
                60,
                1
            ],
            'exceeding rate limit' => [
                60,
                false,
                60,
                0
            ],
            'way over the limit' => [
                100,
                false,
                100,
                0
            ]
        ];
    }

    /**
     * @dataProvider rateLimitDataProvider
     */
    public function testRateLimiting(int $currentCount, bool $expectedAllowed, int $expectedCount, int $expectedRemaining): void
    {
        $key = "rate_limit:{$this->testIp}:api";

        Cache::shouldReceive('get')->with($key, 0)->andReturn($currentCount);

        if ($expectedAllowed) {
            Cache::shouldReceive('inc')->with($key)->andReturn($expectedCount);
            if ($currentCount === 0) {
                Cache::shouldReceive('expire')->with($key, $this->windowSeconds)->andReturn(true);
            }
        }

        $result = $this->checkRateLimit($this->testIp);

        $this->assertEquals($expectedAllowed, $result['allowed']);
        $this->assertEquals($this->maxRequests, $result['limit']);

        if ($expectedAllowed) {
            $this->assertEquals($expectedCount, $result['current']);
            $this->assertGreaterThanOrEqual(0, $result['remaining']);
        } else {
            $this->assertEquals(0, $result['remaining']);
            $this->assertArrayHasKey('retry_after', $result);
        }
    }

    public function testDifferentIpsHaveSeparateCounters(): void
    {
        $ip1 = '10.0.0.1';
        $ip2 = '10.0.0.2';
        $key1 = "rate_limit:{$ip1}:api";
        $key2 = "rate_limit:{$ip2}:api";

        Cache::shouldReceive('get')->with($key1, 0)->andReturn(0);
        Cache::shouldReceive('inc')->with($key1)->andReturn(1);
        Cache::shouldReceive('expire')->with($key1, $this->windowSeconds)->andReturn(true);

        Cache::shouldReceive('get')->with($key2, 0)->andReturn(59);
        Cache::shouldReceive('inc')->with($key2)->andReturn(60);

        $result1 = $this->checkRateLimit($ip1);
        $result2 = $this->checkRateLimit($ip2);

        $this->assertTrue($result1['allowed']);
        $this->assertEquals(1, $result1['current']);

        $this->assertTrue($result2['allowed']);
        $this->assertEquals(60, $result2['current']);
    }

    public function testRateLimitResetAfterWindow(): void
    {
        $key = "rate_limit:{$this->testIp}:api";

        Cache::shouldReceive('get')->with($key, 0)->andReturn(0, 0);
        Cache::shouldReceive('inc')->with($key)->andReturn(1, 1);
        Cache::shouldReceive('expire')->with($key, $this->windowSeconds)->andReturn(true, true);

        $result1 = $this->checkRateLimit($this->testIp);
        $this->assertTrue($result1['allowed']);
        $this->assertEquals(1, $result1['current']);

        $result2 = $this->checkRateLimit($this->testIp);
        $this->assertTrue($result2['allowed']);
        $this->assertEquals(1, $result2['current']);
    }

    public function testGetClientIpFromRequest(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('server')->with('HTTP_X_FORWARDED_FOR')->andReturn('203.0.113.195, 10.0.0.1');
        $request->shouldReceive('server')->with('HTTP_X_REAL_IP')->andReturn(null);
        $request->shouldReceive('server')->with('REMOTE_ADDR')->andReturn('10.0.0.1');

        $ip = $this->getClientIp($request);
        $this->assertEquals('203.0.113.195', $ip);
    }

    public function testGetClientIpFromXRealIp(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('server')->with('HTTP_X_FORWARDED_FOR')->andReturn(null);
        $request->shouldReceive('server')->with('HTTP_X_REAL_IP')->andReturn('198.51.100.5');
        $request->shouldReceive('server')->with('REMOTE_ADDR')->andReturn('10.0.0.1');

        $ip = $this->getClientIp($request);
        $this->assertEquals('198.51.100.5', $ip);
    }

    public function testGetClientIpFromRemoteAddr(): void
    {
        $request = m::mock(Request::class);
        $request->shouldReceive('server')->with('HTTP_X_FORWARDED_FOR')->andReturn(null);
        $request->shouldReceive('server')->with('HTTP_X_REAL_IP')->andReturn(null);
        $request->shouldReceive('server')->with('REMOTE_ADDR')->andReturn('192.0.2.10');

        $ip = $this->getClientIp($request);
        $this->assertEquals('192.0.2.10', $ip);
    }

    public function testWhitelistedIpBypassesRateLimit(): void
    {
        $whitelist = ['127.0.0.1', '10.0.0.1'];
        $testIp = '10.0.0.1';

        Cache::shouldReceive('get')->never();
        Cache::shouldReceive('inc')->never();

        $result = $this->checkRateLimit($testIp, $whitelist);
        $this->assertTrue($result['allowed']);
        $this->assertEquals('whitelisted', $result['current']);
    }

    private function checkRateLimit(string $ip, array $whitelist = []): array
    {
        if (in_array($ip, $whitelist, true)) {
            return [
                'allowed' => true,
                'limit' => $this->maxRequests,
                'current' => 'whitelisted',
                'remaining' => $this->maxRequests
            ];
        }

        $key = "rate_limit:{$ip}:api";
        $current = Cache::get($key, 0);

        if ($current >= $this->maxRequests) {
            return [
                'allowed' => false,
                'limit' => $this->maxRequests,
                'current' => $current,
                'remaining' => 0,
                'retry_after' => $this->windowSeconds
            ];
        }

        $count = Cache::inc($key);

        if ($count === 1) {
            Cache::expire($key, $this->windowSeconds);
        }

        return [
            'allowed' => true,
            'limit' => $this->maxRequests,
            'current' => $count,
            'remaining' => $this->maxRequests - $count
        ];
    }

    private function getClientIp(Request $request): string
    {
        $forwardedFor = $request->server('HTTP_X_FORWARDED_FOR');
        if ($forwardedFor) {
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }

        $realIp = $request->server('HTTP_X_REAL_IP');
        if ($realIp) {
            return $realIp;
        }

        return $request->server('REMOTE_ADDR', '0.0.0.0');
    }
}
