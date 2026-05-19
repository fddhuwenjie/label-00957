<?php

namespace tests\Unit;

use tests\TestCase;
use app\controller\api\AuthController;
use app\model\User;
use Mockery as m;
use think\Request;
use think\facade\Log;

class AuthControllerTest extends TestCase
{
    private $authController;
    private $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->request = m::mock(Request::class);
        $this->authController = m::mock(AuthController::class)->makePartial();
        $this->authController->request = $this->request;
    }

    public function loginDataProvider(): array
    {
        return [
            'empty email' => [
                ['email' => '', 'password' => 'password123'],
                422,
                '邮箱不能为空'
            ],
            'empty password' => [
                ['email' => 'test@example.com', 'password' => ''],
                422,
                '密码不能为空'
            ],
            'user not found' => [
                ['email' => 'nonexistent@example.com', 'password' => 'password123'],
                400,
                '邮箱或密码错误'
            ],
            'wrong password' => [
                ['email' => 'user@example.com', 'password' => 'wrongpassword'],
                400,
                '邮箱或密码错误'
            ],
            'account locked' => [
                ['email' => 'locked@example.com', 'password' => 'password123'],
                403,
                '账号已被禁用'
            ],
            'successful login' => [
                ['email' => 'user@example.com', 'password' => 'password123'],
                200,
                '登录成功'
            ]
        ];
    }

    /**
     * @dataProvider loginDataProvider
     */
    public function testLogin(array $inputData, int $expectedCode, string $expectedMessage): void
    {
        $this->request->shouldReceive('param')->andReturn($inputData);
        $this->request->shouldReceive('getContent')->andReturn(json_encode($inputData));

        $userMock = m::mock(User::class);
        $userMock->shouldReceive('hidden')->with(['password'])->andReturnSelf();
        $userMock->shouldReceive('toArray')->andReturn([
            'id' => 1,
            'email' => 'user@example.com',
            'nickname' => 'testuser',
            'status' => 1
        ]);

        $userLockedMock = m::mock(User::class);
        $userLockedMock->status = 0;
        $userLockedMock->shouldReceive('verifyPassword')->andReturn(true);

        $userNormalMock = m::mock(User::class);
        $userNormalMock->status = 1;
        $userNormalMock->shouldReceive('verifyPassword')->with('password123')->andReturn(true);
        $userNormalMock->shouldReceive('verifyPassword')->with('wrongpassword')->andReturn(false);

        User::shouldReceive('where')
            ->with('email', 'nonexistent@example.com')
            ->andReturnSelf();
        User::shouldReceive('where')
            ->with('email', 'user@example.com')
            ->andReturnSelf();
        User::shouldReceive('where')
            ->with('email', 'locked@example.com')
            ->andReturnSelf();

        if ($inputData['email'] === 'nonexistent@example.com') {
            User::shouldReceive('find')->andReturn(null);
        } elseif ($inputData['email'] === 'locked@example.com') {
            User::shouldReceive('find')->andReturn($userLockedMock);
        } else {
            User::shouldReceive('find')->andReturn($userNormalMock);
        }

        if ($expectedCode === 200) {
            $userNormalMock->id = 1;
            $this->assertTrue(true);
        }

        $response = $this->authController->login();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals($expectedCode, $responseData['code']);
        $this->assertStringContainsString($expectedMessage, $responseData['message']);
    }

    public function testLoginWithUsernameField(): void
    {
        $inputData = ['username' => 'user@example.com', 'password' => 'password123'];
        $this->request->shouldReceive('param')->andReturn($inputData);
        $this->request->shouldReceive('getContent')->andReturn(json_encode($inputData));

        $userMock = m::mock(User::class);
        $userMock->status = 1;
        $userMock->id = 1;
        $userMock->shouldReceive('verifyPassword')->with('password123')->andReturn(true);
        $userMock->shouldReceive('hidden')->with(['password'])->andReturnSelf();
        $userMock->shouldReceive('toArray')->andReturn(['id' => 1]);

        User::shouldReceive('where')->with('email', 'user@example.com')->andReturnSelf();
        User::shouldReceive('find')->andReturn($userMock);

        $response = $this->authController->login();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $responseData['code']);
    }
}
