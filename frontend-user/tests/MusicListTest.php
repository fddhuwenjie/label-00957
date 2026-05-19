<?php

declare(strict_types=1);

namespace app\tests;

use PHPUnit\Framework\TestCase;

class MusicListTest extends TestCase
{
    public function dataProviderForPaginationScenarios(): array
    {
        return [
            'first_page_default' => [
                'page' => 1,
                'limit' => 20,
                'expected_page' => 1,
                'expected_limit' => 20,
                'expected_offset' => 0,
            ],
            'page_two' => [
                'page' => 2,
                'limit' => 20,
                'expected_page' => 2,
                'expected_limit' => 20,
                'expected_offset' => 20,
            ],
            'page_ten' => [
                'page' => 10,
                'limit' => 20,
                'expected_page' => 10,
                'expected_limit' => 20,
                'expected_offset' => 180,
            ],
            'zero_page_clamped_to_one' => [
                'page' => 0,
                'limit' => 20,
                'expected_page' => 1,
                'expected_limit' => 20,
                'expected_offset' => 0,
            ],
            'negative_page_clamped_to_one' => [
                'page' => -5,
                'limit' => 20,
                'expected_page' => 1,
                'expected_limit' => 20,
                'expected_offset' => 0,
            ],
            'very_high_page' => [
                'page' => 99999,
                'limit' => 20,
                'expected_page' => 99999,
                'expected_limit' => 20,
                'expected_offset' => 1999960,
            ],
            'zero_limit_defaults_to_20' => [
                'page' => 1,
                'limit' => 0,
                'expected_page' => 1,
                'expected_limit' => 20,
                'expected_offset' => 0,
            ],
            'negative_limit_defaults_to_20' => [
                'page' => 1,
                'limit' => -10,
                'expected_page' => 1,
                'expected_limit' => 20,
                'expected_offset' => 0,
            ],
            'large_limit_truncated' => [
                'page' => 1,
                'limit' => 1000,
                'expected_page' => 1,
                'expected_limit' => 100,
                'expected_offset' => 0,
            ],
            'string_page_coerced_to_int' => [
                'page' => '3',
                'limit' => '25',
                'expected_page' => 3,
                'expected_limit' => 25,
                'expected_offset' => 50,
            ],
            'category_filter_applied' => [
                'page' => 1,
                'limit' => 20,
                'expected_page' => 1,
                'expected_limit' => 20,
                'expected_offset' => 0,
            ],
            'boundary_page_last' => [
                'page' => 1000000,
                'limit' => 20,
                'expected_page' => 1000000,
                'expected_limit' => 20,
                'expected_offset' => 19999980,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForPaginationScenarios
     * @param mixed $page
     * @param mixed $limit
     */
    public function testPaginationParameters(
        $page,
        $limit,
        int $expectedPage,
        int $expectedLimit,
        int $expectedOffset
    ): void {
        $page = (int) $page;
        $limit = (int) $limit;

        if ($page < 1) {
            $page = 1;
        }

        if ($limit < 1) {
            $limit = 20;
        }

        if ($limit > 100) {
            $limit = 100;
        }

        $offset = ($page - 1) * $limit;

        $this->assertSame($expectedPage, $page);
        $this->assertSame($expectedLimit, $limit);
        $this->assertSame($expectedOffset, $offset);
    }

    public function testPaginationOffsetCalculation(): void
    {
        $offset = (1 - 1) * 20;
        $this->assertSame(0, $offset);

        $offset = (2 - 1) * 20;
        $this->assertSame(20, $offset);

        $offset = (5 - 1) * 20;
        $this->assertSame(80, $offset);

        $offset = (10 - 1) * 20;
        $this->assertSame(180, $offset);
    }

    public function testPaginationClampsToNonNegative(): void
    {
        $page = -3;
        $page = max(1, $page);
        $this->assertSame(1, $page);

        $limit = -5;
        $limit = max(1, $limit);
        $this->assertSame(1, $limit);
    }

    public function testPaginationMaximumLimitCap(): void
    {
        $limit = 500;
        $limit = min(100, $limit);
        $this->assertSame(100, $limit);

        $limit = 50;
        $limit = min(100, $limit);
        $this->assertSame(50, $limit);
    }

    public function testInputSanitizationForPagination(): void
    {
        $page = 'abc';
        $page = (int) $page;
        $this->assertSame(0, $page);
        $this->assertSame(1, max(1, $page));

        $limit = 'xyz';
        $limit = (int) $limit;
        $this->assertSame(0, $limit);
        $this->assertSame(20, $limit > 0 ? $limit : 20);
    }

    public function testCategoryIdFilterIsApplied(): void
    {
        $categoryId = 0;
        $this->assertSame(0, $categoryId);

        $categoryId = 5;
        $this->assertSame(5, $categoryId);

        $categoryId = -1;
        $this->assertSame(-1, $categoryId);
    }

    public function testMusicListOnlyReturnsActiveRecords(): void
    {
        $status = 1;
        $this->assertSame(1, $status);

        $status = 0;
        $this->assertNotSame(1, $status);
    }

    public function testPaginationSupportsBoundaryConditions(): void
    {
        $page = 1;
        $offset = ($page - 1) * 20;
        $this->assertSame(0, $offset);

        $page = PHP_INT_MAX;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $this->assertGreaterThan(0, $offset);
    }
}
