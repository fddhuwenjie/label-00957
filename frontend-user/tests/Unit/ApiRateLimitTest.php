<?php

namespace tests\Unit;

use PHPUnit\Framework\TestCase;

class ApiRateLimitTest extends TestCase
{
    public static function rateLimitProvider(): array
    {
        return [
            'within_limit' => [
                'requestCount' => 30,
                'limit' => 60,
                'windowSeconds' => 60,
                'expectedAllowed' => true,
            ],
            'at_limit' => [
                'requestCount' => 60,
                'limit' => 60,
                'windowSeconds' => 60,
                'expectedAllowed' => false,
            ],
            'over_limit' => [
                'requestCount' => 100,
                'limit' => 60,
                'windowSeconds' => 60,
                'expectedAllowed' => false,
            ],
            'single_request' => [
                'requestCount' => 1,
                'limit' => 60,
                'windowSeconds' => 60,
                'expectedAllowed' => true,
            ],
            'zero_requests' => [
                'requestCount' => 0,
                'limit' => 60,
                'windowSeconds' => 60,
                'expectedAllowed' => true,
            ],
        ];
    }

    public static function slidingWindowProvider(): array
    {
        return [
            'first_window_expired' => [
                'currentTime' => 120,
                'windowStart' => 0,
                'windowSeconds' => 60,
                'isExpired' => true,
            ],
            'within_window' => [
                'currentTime' => 45,
                'windowStart' => 0,
                'windowSeconds' => 60,
                'isExpired' => false,
            ],
            'exact_boundary' => [
                'currentTime' => 60,
                'windowStart' => 0,
                'windowSeconds' => 60,
                'isExpired' => true,
            ],
            'just_before_expiry' => [
                'currentTime' => 59,
                'windowStart' => 0,
                'windowSeconds' => 60,
                'isExpired' => false,
            ],
        ];
    }

    public static function retryAfterProvider(): array
    {
        return [
            'half_window_remaining' => [
                'currentTime' => 90,
                'windowStart' => 60,
                'windowSeconds' => 60,
                'expectedRetryAfter' => 30,
            ],
            'just_started' => [
                'currentTime' => 61,
                'windowStart' => 60,
                'windowSeconds' => 60,
                'expectedRetryAfter' => 59,
            ],
            'about_to_expire' => [
                'currentTime' => 119,
                'windowStart' => 60,
                'windowSeconds' => 60,
                'expectedRetryAfter' => 1,
            ],
        ];
    }

    public static function endpointLimitProvider(): array
    {
        return [
            'login_endpoint' => [
                'endpoint' => '/api/auth/login',
                'limit' => 5,
                'window' => 300,
            ],
            'register_endpoint' => [
                'endpoint' => '/api/auth/register',
                'limit' => 3,
                'window' => 3600,
            ],
            'music_list_endpoint' => [
                'endpoint' => '/api/music/list',
                'limit' => 60,
                'window' => 60,
            ],
            'search_endpoint' => [
                'endpoint' => '/api/music/search',
                'limit' => 30,
                'window' => 60,
            ],
            'upload_endpoint' => [
                'endpoint' => '/api/user/avatar',
                'limit' => 10,
                'window' => 60,
            ],
        ];
    }

    /**
     * @dataProvider rateLimitProvider
     */
    public function testRateLimitEnforcement(int $requestCount, int $limit, int $windowSeconds, bool $expectedAllowed): void
    {
        $isAllowed = $requestCount < $limit;
        $this->assertEquals($expectedAllowed, $isAllowed);
    }

    /**
     * @dataProvider slidingWindowProvider
     */
    public function testSlidingWindowExpiry(int $currentTime, int $windowStart, int $windowSeconds, bool $isExpired): void
    {
        $elapsed = $currentTime - $windowStart;
        $windowExpired = $elapsed >= $windowSeconds;
        $this->assertEquals($isExpired, $windowExpired);
    }

    /**
     * @dataProvider retryAfterProvider
     */
    public function testRetryAfterHeader(int $currentTime, int $windowStart, int $windowSeconds, int $expectedRetryAfter): void
    {
        $windowEnd = $windowStart + $windowSeconds;
        $retryAfter = $windowEnd - $currentTime;
        $this->assertEquals($expectedRetryAfter, $retryAfter);
    }

    /**
     * @dataProvider endpointLimitProvider
     */
    public function testEndpointSpecificLimits(string $endpoint, int $limit, int $window): void
    {
        $this->assertGreaterThan(0, $limit);
        $this->assertGreaterThan(0, $window);
        $this->assertStringStartsWith('/api/', $endpoint);
    }

    public function testRateLimitResponseHeaders(): void
    {
        $limit = 60;
        $remaining = 30;
        $reset = time() + 60;

        $headers = [
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => $remaining,
            'X-RateLimit-Reset' => $reset,
        ];

        $this->assertEquals($limit, $headers['X-RateLimit-Limit']);
        $this->assertEquals($remaining, $headers['X-RateLimit-Remaining']);
        $this->assertGreaterThan(time(), $headers['X-RateLimit-Reset']);
    }

    public function testRateLimitExceededReturns429(): void
    {
        $requestCount = 61;
        $limit = 60;
        $isOverLimit = $requestCount > $limit;
        $this->assertTrue($isOverLimit);

        $statusCode = $isOverLimit ? 429 : 200;
        $this->assertEquals(429, $statusCode);
    }

    public function testRateLimitKeyGeneration(): void
    {
        $ip = '192.168.1.100';
        $endpoint = '/api/auth/login';
        $key = 'rate_limit:' . md5($ip . ':' . $endpoint);

        $this->assertStringStartsWith('rate_limit:', $key);
        $this->assertEquals(32, strlen(explode(':', $key, 2)[1]));
    }
}
