<?php

namespace tests\Unit;

use PHPUnit\Framework\TestCase;

class SearchXssFilterTest extends TestCase
{
    public static function xssInputProvider(): array
    {
        return [
            'script_tag' => [
                'input' => '<script>alert("xss")</script>',
                'expectedSanitized' => 'alert("xss")',
            ],
            'img_onerror' => [
                'input' => '<img src=x onerror=alert(1)>',
                'expectedSanitized' => '',
            ],
            'svg_onload' => [
                'input' => '<svg onload=alert(1)>',
                'expectedSanitized' => '',
            ],
            'javascript_protocol' => [
                'input' => 'javascript:alert(1)',
                'expectedSanitized' => 'alert(1)',
            ],
            'event_handler' => [
                'input' => 'onclick=alert(1)',
                'expectedSanitized' => 'alert(1)',
            ],
            'html_entity_script' => [
                'input' => '&lt;script&gt;alert(1)&lt;/script&gt;',
                'expectedSanitized' => 'alert(1)',
            ],
            'nested_script' => [
                'input' => '<scr<script>ipt>alert(1)</scr</script>ipt>',
                'expectedSanitized' => 'alert(1)',
            ],
            'normal_keyword' => [
                'input' => '周杰伦',
                'expectedSanitized' => '周杰伦',
            ],
            'mixed_content' => [
                'input' => 'test<script>alert(1)</script>keyword',
                'expectedSanitized' => 'testkeyword',
            ],
            'style_tag' => [
                'input' => '<style>body{display:none}</style>',
                'expectedSanitized' => '',
            ],
            'iframe_tag' => [
                'input' => '<iframe src="evil.com"></iframe>',
                'expectedSanitized' => '',
            ],
            'data_uri' => [
                'input' => 'data:text/html,<script>alert(1)</script>',
                'expectedSanitized' => '',
            ],
        ];
    }

    public static function sqlInjectionProvider(): array
    {
        return [
            'basic_sql_injection' => [
                'input' => "' OR 1=1 --",
                'containsSqlPattern' => true,
            ],
            'union_injection' => [
                'input' => "' UNION SELECT * FROM users --",
                'containsSqlPattern' => true,
            ],
            'drop_table' => [
                'input' => "'; DROP TABLE users; --",
                'containsSqlPattern' => true,
            ],
            'normal_query' => [
                'input' => '平凡之路',
                'containsSqlPattern' => false,
            ],
            'english_query' => [
                'input' => 'hello world',
                'containsSqlPattern' => false,
            ],
        ];
    }

    public static function htmlEntityProvider(): array
    {
        return [
            'html_entities_decoded' => [
                'input' => '&#60;script&#62;alert(1)&#60;/script&#62;',
                'expectedSafe' => true,
            ],
            'hex_entities_decoded' => [
                'input' => '\x3cscript\x3ealert(1)\x3c/script\x3e',
                'expectedSafe' => true,
            ],
            'unicode_entities' => [
                'input' => '\u003cscript\u003e',
                'expectedSafe' => true,
            ],
        ];
    }

    private function sanitizeKeyword(string $input): string
    {
        $sanitized = strip_tags($input);
        $sanitized = html_entity_decode($sanitized, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sanitized = strip_tags($sanitized);
        $sanitized = preg_replace('/<[^>]*>/i', '', $sanitized);
        $sanitized = preg_replace('/javascript:/i', '', $sanitized);
        $sanitized = preg_replace('/on\w+\s*=/i', '', $sanitized);
        $sanitized = trim($sanitized);
        return $sanitized;
    }

    private function detectSqlInjection(string $input): bool
    {
        $patterns = [
            '/(\b(UNION|SELECT|INSERT|UPDATE|DELETE|DROP|ALTER|CREATE|TRUNCATE)\b)/i',
            "/('|\")(\s)*(OR|AND)(\s)*(\d|')/i",
            '/(--|#|\/\*|\*\/)/',
            '/;\s*(DROP|DELETE|UPDATE|INSERT)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @dataProvider xssInputProvider
     */
    public function testXssFilterRemovesDangerousContent(string $input, string $expectedSanitized): void
    {
        $result = $this->sanitizeKeyword($input);
        $this->assertEquals($expectedSanitized, $result);
    }

    /**
     * @dataProvider sqlInjectionProvider
     */
    public function testSqlInjectionDetection(string $input, bool $containsSqlPattern): void
    {
        $detected = $this->detectSqlInjection($input);
        $this->assertEquals($containsSqlPattern, $detected);
    }

    /**
     * @dataProvider htmlEntityProvider
     */
    public function testHtmlEntityXssPrevention(string $input, bool $expectedSafe): void
    {
        $decoded = html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $sanitized = $this->sanitizeKeyword($decoded);
        $containsScript = stripos($sanitized, '<script') !== false;
        $this->assertEquals($expectedSafe, !$containsScript);
    }

    public function testEmptyKeywordReturnsEmpty(): void
    {
        $result = $this->sanitizeKeyword('');
        $this->assertEquals('', $result);
    }

    public function testKeywordLengthLimit(): void
    {
        $maxLength = 100;
        $longKeyword = str_repeat('a', 200);
        $truncated = mb_substr($longKeyword, 0, $maxLength);
        $this->assertEquals($maxLength, mb_strlen($truncated));
    }
}
