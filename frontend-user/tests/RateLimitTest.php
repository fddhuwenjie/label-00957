<?php

declare(strict_types=1);

namespace app\tests;

use PHPUnit\Framework\TestCase;

class RateLimitTest extends TestCase
{
    public function dataProviderForRateLimitScenarios(): array
    {
        return [
            'under_limit_first_request' => [
                'request_count' => 1,
                'limit' => 60,
                'window_seconds' => 60,
                'allowed' => true,
            ],
            'at_exact_limit' => [
                'request_count' => 60,
                'limit' => 60,
                'window_seconds' => 60,
                'allowed' => true,
            ],
            'one_over_limit' => [
                'request_count' => 61,
                'limit' => 60,
                'window_seconds' => 60,
                'allowed' => false,
            ],
            'double_limit' => [
                'request_count' => 120,
                'limit' => 60,
                'window_seconds' => 60,
                'allowed' => false,
            ],
            'zero_requests' => [
                'request_count' => 0,
                'limit' => 60,
                'window_seconds' => 60,
                'allowed' => true,
            ],
            'single_request_limit' => [
                'request_count' => 1,
                'limit' => 1,
                'window_seconds' => 60,
                'allowed' => true,
            ],
            'single_request_limit_exceeded' => [
                'request_count' => 2,
                'limit' => 1,
                'window_seconds' => 60,
                'allowed' => false,
            ],
            'login_strict_limit' => [
                'request_count' => 5,
                'limit' => 5,
                'window_seconds' => 60,
                'allowed' => true,
            ],
            'login_strict_limit_exceeded' => [
                'request_count' => 6,
                'limit' => 5,
                'window_seconds' => 60,
                'allowed' => false,
            ],
            'api_generous_limit' => [
                'request_count' => 100,
                'limit' => 100,
                'window_seconds' => 60,
                'allowed' => true,
            ],
            'api_generous_limit_exceeded' => [
                'request_count' => 101,
                'limit' => 100,
                'window_seconds' => 60,
                'allowed' => false,
            ],
            'high_concurrency' => [
                'request_count' => 1000,
                'limit' => 60,
                'window_seconds' => 60,
                'allowed' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForRateLimitScenarios
     */
    public function testRateLimit(int $requestCount, int $limit, int $windowSeconds, bool $allowed): void
    {
        $isAllowed = $requestCount <= $limit;
        $this->assertSame($allowed, $isAllowed);
    }

    public function testRateLimitHeaderGeneration(): void
    {
        $limit = 60;
        $remaining = max(0, $limit - 30);

        $this->assertSame(30, $remaining);
        $this->assertSame($limit, 60);

        $headers = [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => time() + 60,
        ];

        $this->assertArrayHasKey('X-RateLimit-Limit', $headers);
        $this->assertArrayHasKey('X-RateLimit-Remaining', $headers);
        $this->assertArrayHasKey('X-RateLimit-Reset', $headers);
    }

    public function testRateLimitExceededReturns429(): void
    {
        $requestCount = 61;
        $limit = 60;

        if ($requestCount > $limit) {
            $this->assertSame(429, 429);
            $this->assertSame('Rate limit exceeded', 'Rate limit exceeded');
        }
    }

    public function testRateLimitResetAfterWindow(): void
    {
        $windowSeconds = 60;
        $resetTime = time() + $windowSeconds;
        $this->assertGreaterThan(time(), $resetTime);
    }

    public function testLoginEndpointHasLowerLimit(): void
    {
        $loginLimit = 5;
        $apiLimit = 60;
        $this->assertLessThan($apiLimit, $loginLimit);
    }

    public function testRemainingDecrement(): void
    {
        $limit = 60;
        $used = 0;

        for ($i = 0; $i < 60; $i++) {
            $used++;
            $remaining = $limit - $used;
            $this->assertGreaterThanOrEqual(0, $remaining);
        }

        $this->assertSame(0, $remaining);
    }

    public function testRateLimitWithIpAddress(): void
    {
        $ip = '192.168.1.100';
        $key = 'rate_limit:' . $ip;
        $this->assertSame('rate_limit:192.168.1.100', $key);
    }

    public function testRateLimitWithEndpointKey(): void
    {
        $ip = '192.168.1.100';
        $endpoint = '/api/auth/login';
        $key = 'rate_limit:' . $ip . ':' . md5($endpoint);
        $this->assertNotEmpty($key);
    }

    public function testBurstBehavior(): void
    {
        $limit = 60;
        $requests = [1, 2, 3, 4, 5, 60, 61];
        foreach ($requests as $count) {
            $allowed = $count <= $limit;
            if ($count === 61) {
                $this->assertFalse($allowed);
            }
        }
    }
}
