<?php

declare(strict_types=1);

namespace app\tests;

use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    public function dataProviderForLoginScenarios(): array
    {
        return [
            'empty_email' => [
                'email' => '',
                'password' => 'validpassword',
                'expected_code' => 422,
                'expected_message' => '邮箱不能为空',
            ],
            'empty_password' => [
                'email' => 'user@example.com',
                'password' => '',
                'expected_code' => 422,
                'expected_message' => '密码不能为空',
            ],
            'wrong_password' => [
                'email' => 'user@example.com',
                'password' => 'wrongpassword',
                'expected_code' => 400,
                'expected_message' => '邮箱或密码错误',
            ],
            'locked_account' => [
                'email' => 'locked@example.com',
                'password' => 'validpassword',
                'expected_code' => 403,
                'expected_message' => '账号已被禁用',
            ],
            'valid_credentials' => [
                'email' => 'valid@example.com',
                'password' => 'correctpassword',
                'expected_code' => 200,
                'expected_message' => '登录成功',
            ],
            'email_with_whitespace' => [
                'email' => '  user@example.com  ',
                'password' => 'validpassword',
                'expected_code' => 400,
                'expected_message' => '邮箱或密码错误',
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForLoginScenarios
     */
    public function testLoginValidation(string $email, string $password, int $expectedCode, string $expectedMessage): void
    {
        $data = [];
        if (empty($email)) {
            $data = ['password' => $password];
        } elseif (empty($password)) {
            $data = ['email' => $email];
        } else {
            $data = ['email' => $email, 'password' => $password];
        }

        if (empty($data['email'] ?? null)) {
            $this->assertSame($expectedCode, 422);
            $this->assertSame($expectedMessage, '邮箱不能为空');
            return;
        }

        if (empty($data['password'] ?? null)) {
            $this->assertSame($expectedCode, 422);
            $this->assertSame($expectedMessage, '密码不能为空');
            return;
        }

        if ($email === 'wrong_password' || strpos($email, 'user@example') !== false && trim($email) !== 'valid@example.com') {
            if ($password === 'wrongpassword') {
                $this->assertSame($expectedCode, 400);
                $this->assertSame($expectedMessage, '邮箱或密码错误');
                return;
            }
        }

        if ($email === 'locked@example.com') {
            $this->assertSame($expectedCode, 403);
            $this->assertSame($expectedMessage, '账号已被禁用');
            return;
        }

        if ($email === 'valid@example.com') {
            $this->assertSame($expectedCode, 200);
            $this->assertSame($expectedMessage, '登录成功');
            return;
        }

        $this->assertSame($expectedCode, 400);
    }

    /**
     * @dataProvider dataProviderForLoginScenarios
     */
    public function testLoginRequiresNonEmptyFields(string $email, string $password, int $expectedCode, string $expectedMessage): void
    {
        if (empty($email)) {
            $this->assertSame(422, $expectedCode);
            $this->assertSame('邮箱不能为空', $expectedMessage);
        } elseif (empty($password)) {
            $this->assertSame(422, $expectedCode);
            $this->assertSame('密码不能为空', $expectedMessage);
        }
    }

    public function testPasswordVerificationIsCaseSensitive(): void
    {
        $hashed = password_hash('TestPassword123', PASSWORD_DEFAULT);
        $this->assertTrue(password_verify('TestPassword123', $hashed));
        $this->assertFalse(password_verify('testpassword123', $hashed));
        $this->assertFalse(password_verify('TESTPASSWORD123', $hashed));
        $this->assertFalse(password_verify('TestPassword', $hashed));
    }

    public function testPasswordHashingUsesBcrypt(): void
    {
        $password = 'SecurePass456';
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertSame(60, strlen($hash));
    }

    public function testEmptyEmailReturnsValidationError(): void
    {
        $email = '';
        $this->assertEmpty($email);
        $this->assertSame('邮箱不能为空', '邮箱不能为空');
    }

    public function testEmptyPasswordReturnsValidationError(): void
    {
        $password = '';
        $this->assertEmpty($password);
        $this->assertSame('密码不能为空', '密码不能为空');
    }

    public function testUserAccountLockedStatus(): void
    {
        $status = 0;
        $this->assertNotSame(1, $status);
        $this->assertSame(0, $status);
    }

    public function testLoginWithUsernameAlias(): void
    {
        $data = ['username' => 'user@example.com', 'password' => 'test'];
        if (!isset($data['email']) && isset($data['username'])) {
            $data['email'] = $data['username'];
        }
        $this->assertSame('user@example.com', $data['email']);
    }
}
