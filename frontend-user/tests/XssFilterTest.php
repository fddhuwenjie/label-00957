<?php

declare(strict_types=1);

namespace app\tests;

use PHPUnit\Framework\TestCase;

class XssFilterTest extends TestCase
{
    public function dataProviderForXssPayloads(): array
    {
        return [
            'script_tag' => [
                'input' => '<script>alert("xss")</script>',
                'should_filter' => true,
            ],
            'script_tag_with_event' => [
                'input' => '<script>alert(document.cookie)</script>',
                'should_filter' => true,
            ],
            'img_onerror' => [
                'input' => '<img src="x" onerror="alert(1)">',
                'should_filter' => true,
            ],
            'body_onload' => [
                'input' => '<body onload="alert(1)">',
                'should_filter' => true,
            ],
            'javascript_protocol' => [
                'input' => 'javascript:alert(1)',
                'should_filter' => true,
            ],
            'iframe_tag' => [
                'input' => '<iframe src="evil.com"></iframe>',
                'should_filter' => true,
            ],
            'svg_xss' => [
                'input' => '<svg onload="alert(1)">',
                'should_filter' => true,
            ],
            'href_javascript' => [
                'input' => '<a href="javascript:alert(1)">click</a>',
                'should_filter' => true,
            ],
            'valid_keyword' => [
                'input' => '周杰伦',
                'should_filter' => false,
            ],
            'valid_english_keyword' => [
                'input' => 'michael jackson',
                'should_filter' => false,
            ],
            'valid_alphanumeric' => [
                'input' => 'song123',
                'should_filter' => false,
            ],
            'empty_string' => [
                'input' => '',
                'should_filter' => false,
            ],
            'whitespace_only' => [
                'input' => '   ',
                'should_filter' => false,
            ],
            'mixed_harmless_html' => [
                'input' => '<p>song title</p>',
                'should_filter' => true,
            ],
            'double_script_tags' => [
                'input' => '<script>alert(1)</script><script>alert(2)</script>',
                'should_filter' => true,
            ],
            'encoded_script' => [
                'input' => '<ScRiPt>alert(1)</ScRiPt>',
                'should_filter' => true,
            ],
            'single_script' => [
                'input' => '<script>alert(1)</script>',
                'should_filter' => true,
            ],
            'nested_script' => [
                'input' => '<script<iframe>>alert(1)</script>',
                'should_filter' => true,
            ],
            'url_with_script' => [
                'input' => '<script>alert(1)</script>',
                'should_filter' => true,
            ],
            'search_keyword_with_special_chars' => [
                'input' => '邓紫棋&Taylor',
                'should_filter' => false,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForXssPayloads
     */
    public function testXssFiltering(string $input, bool $shouldFilter): void
    {
        $filtered = $this->applyXssFilter($input);

        if ($shouldFilter) {
            $this->assertStringNotContainsString('<script', strtolower($filtered));
            $this->assertStringNotContainsString('javascript:', strtolower($filtered));
        }
    }

    public function testXssFilterRemovesScriptTags(): void
    {
        $input = '<script>alert(1)</script>song';
        $filtered = strip_tags($input);
        $this->assertStringNotContainsString('<script', $filtered);
        $this->assertSame('song', trim($filtered));
    }

    public function testXssFilterRemovesAttributeHandlers(): void
    {
        $input = '<img src="x" onerror="alert(1)">';
        $filtered = strip_tags($input);
        $this->assertStringNotContainsString('onerror', strtolower($filtered));
    }

    public function testXssFilterPreservesValidKeywords(): void
    {
        $input = '周杰伦 专辑';
        $filtered = $this->applyXssFilter($input);
        $this->assertSame('周杰伦 专辑', $filtered);
    }

    public function testXssFilterPreservesAlphanumeric(): void
    {
        $input = 'song123 abc';
        $filtered = $this->applyXssFilter($input);
        $this->assertSame('song123 abc', $filtered);
    }

    public function testHtmlSpecialEscaping(): void
    {
        $input = '<script>alert(1)</script>';
        $escaped = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringNotContainsString('alert', $escaped);
    }

    public function testSpecialCharactersPreserved(): void
    {
        $input = '搜索: 周杰伦 & Taylor, (2024)';
        $filtered = $this->applyXssFilter($input);
        $this->assertSame('搜索: 周杰伦 & Taylor, (2024)', $filtered);
    }

    public function testEmptyStringAfterFilter(): void
    {
        $input = '';
        $filtered = $this->applyXssFilter($input);
        $this->assertSame('', $filtered);
    }

    public function testWhitespaceTrimming(): void
    {
        $input = '  song title  ';
        $trimmed = trim($input);
        $this->assertSame('song title', $trimmed);
    }

    public function testXssFilterMultiByteSafety(): void
    {
        $input = '中文 <script>alert(1)</script> 搜索';
        $filtered = $this->applyXssFilter($input);
        $this->assertStringContainsString('中文', $filtered);
        $this->assertStringContainsString('搜索', $filtered);
        $this->assertStringNotContainsString('<script', $filtered);
    }

    private function applyXssFilter(string $input): string
    {
        $filtered = strip_tags($input);
        $filtered = htmlspecialchars($filtered, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return trim($filtered);
    }
}
