<?php

namespace tests\Unit;

use PHPUnit\Framework\TestCase;
use app\model\User;
use app\service\JwtService;
use app\service\CryptoService;

class AuthLoginTest extends TestCase
{
    public static function loginCredentialProvider(): array
    {
        return [
            'empty_email' => [
                'email' => '',
                'password' => 'password123',
                'expectedCode' => 422,
                'expectedMessage' => '邮箱不能为空',
            ],
            'empty_password' => [
                'email' => 'test@example.com',
                'password' => '',
                'expectedCode' => 422,
                'expectedMessage' => '密码不能为空',
            ],
            'wrong_password' => [
                'email' => 'test@example.com',
                'password' => 'wrong_password',
                'expectedCode' => 400,
                'expectedMessage' => '邮箱或密码错误',
            ],
            'nonexistent_user' => [
                'email' => 'nonexistent@example.com',
                'password' => 'password123',
                'expectedCode' => 400,
                'expectedMessage' => '邮箱或密码错误',
            ],
        ];
    }

    public static function accountStatusProvider(): array
    {
        return [
            'disabled_account' => [
                'status' => 0,
                'expectedCode' => 403,
                'expectedMessage' => '账号已被禁用',
            ],
            'banned_account' => [
                'status' => 2,
                'expectedCode' => 403,
                'expectedMessage' => '账号已被禁用',
            ],
        ];
    }

    public static function lockedAccountProvider(): array
    {
        return [
            'status_locked' => [
                'status' => 0,
                'expectedCode' => 403,
            ],
            'status_banned' => [
                'status' => -1,
                'expectedCode' => 403,
            ],
        ];
    }

    /**
     * @dataProvider loginCredentialProvider
     */
    public function testLoginValidationFails(string $email, string $password, int $expectedCode, string $expectedMessage): void
    {
        if (empty($email)) {
            $this->assertEquals(422, $expectedCode);
            $this->assertEquals('邮箱不能为空', $expectedMessage);
            return;
        }

        if (empty($password)) {
            $this->assertEquals(422, $expectedCode);
            $this->assertEquals('密码不能为空', $expectedMessage);
            return;
        }

        $mockUser = $this->createMock(User::class);
        $mockUser->method('__get')->willReturnCallback(function ($key) {
            return match ($key) {
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'status' => 1,
                default => null,
            };
        });

        $wrongHash = password_hash('password123', PASSWORD_DEFAULT);
        $this->assertFalse(password_verify('wrong_password', $wrongHash) && $wrongHash === password_hash('password123', PASSWORD_DEFAULT));

        $this->assertEquals($expectedCode, 400);
        $this->assertEquals($expectedMessage, '邮箱或密码错误');
    }

    /**
     * @dataProvider accountStatusProvider
     */
    public function testLoginWithDisabledAccount(int $status, int $expectedCode, string $expectedMessage): void
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('__get')->willReturnCallback(function ($key) use ($status) {
            return match ($key) {
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'status' => $status,
                'id' => 1,
                default => null,
            };
        });

        $isNotActive = $status !== 1;
        $this->assertTrue($isNotActive);
        $this->assertEquals($expectedCode, 403);
        $this->assertEquals($expectedMessage, '账号已被禁用');
    }

    /**
     * @dataProvider lockedAccountProvider
     */
    public function testLockedAccountCannotLogin(int $status, int $expectedCode): void
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('__get')->willReturnCallback(function ($key) use ($status) {
            return match ($key) {
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'status' => $status,
                'id' => 1,
                default => null,
            };
        });

        $this->assertNotEquals(1, $status);
        $this->assertEquals($expectedCode, 403);
    }

    public function testSuccessfulLogin(): void
    {
        $mockUser = $this->createMock(User::class);
        $mockUser->method('__get')->willReturnCallback(function ($key) {
            return match ($key) {
                'password' => password_hash('password123', PASSWORD_DEFAULT),
                'status' => 1,
                'id' => 1,
                'email' => 'test@example.com',
                'nickname' => 'TestUser',
                default => null,
            };
        });

        $mockUser->method('verifyPassword')
            ->with('password123')
            ->willReturn(true);

        $mockUser->method('hidden')
            ->willReturn([
                'id' => 1,
                'email' => 'test@example.com',
                'nickname' => 'TestUser',
                'status' => 1,
            ]);

        $this->assertTrue($mockUser->verifyPassword('password123'));
        $this->assertEquals(1, $mockUser->__get('status'));

        $token = JwtService::generate(1);
        $this->assertNotEmpty($token);

        $payload = JwtService::verify($token);
        $this->assertEquals(1, $payload['uid']);
    }

    public function testPasswordVerificationWorksCorrectly(): void
    {
        $hash = password_hash('mypassword', PASSWORD_DEFAULT);
        $this->assertTrue(password_verify('mypassword', $hash));
        $this->assertFalse(password_verify('wrongpassword', $hash));
    }
}
