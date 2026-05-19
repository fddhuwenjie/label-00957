<?php

namespace tests\Unit;

use tests\TestCase;
use app\controller\api\MusicController;
use app\model\Music;
use Mockery as m;

class XssFilterTest extends TestCase
{
    private $musicController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->musicController = m::mock(MusicController::class)->makePartial();
    }

    public function xssKeywordDataProvider(): array
    {
        return [
            'basic script tag' => [
                '<script>alert("xss")</script>',
                'alert("xss")',
                false
            ],
            'script with src' => [
                '<script src="http://evil.com/xss.js"></script>',
                '',
                false
            ],
            'img onerror' => [
                '<img src="x" onerror="alert(\'xss\')">',
                '',
                false
            ],
            'svg onload' => [
                '<svg onload="alert(\'xss\')">',
                '',
                false
            ],
            'javascript href' => [
                '<a href="javascript:alert(\'xss\')">click</a>',
                'click',
                false
            ],
            'event handler' => [
                '<div onclick="alert(\'xss\')">test</div>',
                'test',
                false
            ],
            'iframe injection' => [
                '<iframe src="http://evil.com"></iframe>',
                '',
                false
            ],
            'style expression' => [
                '<div style="expression(alert(\'xss\'))">test</div>',
                'test',
                false
            ],
            'mixed case script' => [
                '<SCRIPT>alert("xss")</SCRIPT>',
                'alert("xss")',
                false
            ],
            'encoded script' => [
                '&#60;script&#62;alert(1)&#60;/script&#62;',
                'alert(1)',
                false
            ],
            'normal search keyword' => [
                '周杰伦',
                '周杰伦',
                true
            ],
            'keyword with special chars' => [
                'rock & roll',
                'rock &amp; roll',
                true
            ],
            'keyword with html entities' => [
                'hello < world',
                'hello &lt; world',
                true
            ],
            'empty keyword' => [
                '',
                '',
                true
            ],
            'sql injection attempt' => [
                "' OR '1'='1",
                "' OR '1'='1",
                true
            ],
            'polyglot XSS' => [
                'javascript:/*--></title></style></textarea></script></xmp><svg/onload=\'+/"/+/onmouseover=1/+/[*/[]/+alert(1)//\'>',
                '/*-->',
                false
            ]
        ];
    }

    /**
     * @dataProvider xssKeywordDataProvider
     */
    public function testXssFilter(string $input, string $expectedOutput, bool $expectedIsSafe): void
    {
        $sanitized = $this->sanitizeKeyword($input);

        if ($expectedIsSafe) {
            $this->assertEquals($expectedOutput, $sanitized);
        } else {
            $this->assertStringNotContainsStringIgnoringCase('<script', $sanitized);
            $this->assertStringNotContainsStringIgnoringCase('javascript:', $sanitized);
            $this->assertStringNotContainsStringIgnoringCase('onerror', $sanitized);
            $this->assertStringNotContainsStringIgnoringCase('onload', $sanitized);
            $this->assertStringNotContainsStringIgnoringCase('onclick', $sanitized);
        }

        $this->assertMatchesRegularExpression('/^[^\<\>]*$/', preg_replace('/&[a-zA-Z]+;/', '', $sanitized),
            'Sanitized output should not contain HTML tags');
    }

    public function testSearchWithXssKeyword(): void
    {
        $_GET = ['keyword' => '<script>alert("xss")</script>', 'page' => 1, 'limit' => 10];

        $paginatorMock = m::mock('overload:think\Paginator');
        $paginatorMock->shouldReceive('toArray')->andReturn([
            'total' => 0,
            'per_page' => 10,
            'current_page' => 1,
            'data' => []
        ]);

        $queryMock = m::mock();
        $queryMock->shouldReceive('where')->with('status', 1)->andReturnSelf();
        $queryMock->shouldReceive('where')->andReturnUsing(function ($callback) use ($queryMock) {
            $subQueryMock = m::mock();
            $subQueryMock->shouldReceive('whereLike')->andReturnSelf();
            $subQueryMock->shouldReceive('whereOr')->andReturnSelf();
            $callback($subQueryMock);
            return $queryMock;
        });
        $queryMock->shouldReceive('with')->with(['category'])->andReturnSelf();
        $queryMock->shouldReceive('order')->with('play_count', 'desc')->andReturnSelf();
        $queryMock->shouldReceive('paginate')->andReturn($paginatorMock);

        Music::shouldReceive('where')->with('status', 1)->andReturn($queryMock);

        $response = $this->musicController->search();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $responseData['code']);
    }

    public function testEmptyKeywordReturnsError(): void
    {
        $_GET = ['keyword' => '', 'page' => 1, 'limit' => 10];

        $response = $this->musicController->search();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(422, $responseData['code']);
        $this->assertStringContainsString('请输入搜索关键词', $responseData['message']);
    }

    private function sanitizeKeyword(string $keyword): string
    {
        $keyword = trim($keyword);

        if (empty($keyword)) {
            return '';
        }

        $keyword = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $keyword);
        $keyword = preg_replace('/<iframe[^>]*>.*?<\/iframe>/is', '', $keyword);
        $keyword = preg_replace('/<svg[^>]*>.*?<\/svg>/is', '', $keyword);

        $keyword = preg_replace('/\s+on\w+\s*=\s*"[^"]*"/i', '', $keyword);
        $keyword = preg_replace("/\s+on\w+\s*=\s*'[^']*'/i", '', $keyword);
        $keyword = preg_replace('/\s+on\w+\s*=\s*[^\s>]+/i', '', $keyword);

        $keyword = preg_replace('/javascript\s*:\s*/i', '', $keyword);
        $keyword = preg_replace('/expression\s*\(/i', '', $keyword);

        $keyword = htmlspecialchars($keyword, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        return $keyword;
    }
}
