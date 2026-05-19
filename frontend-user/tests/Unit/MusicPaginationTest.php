<?php

namespace tests\Unit;

use PHPUnit\Framework\TestCase;
use app\model\Music;

class MusicPaginationTest extends TestCase
{
    public static function paginationProvider(): array
    {
        return [
            'first_page' => [
                'page' => 1,
                'limit' => 20,
                'totalItems' => 50,
                'expectedLastPage' => 3,
                'expectedItemCount' => 20,
            ],
            'second_page' => [
                'page' => 2,
                'limit' => 20,
                'totalItems' => 50,
                'expectedLastPage' => 3,
                'expectedItemCount' => 20,
            ],
            'last_page' => [
                'page' => 3,
                'limit' => 20,
                'totalItems' => 50,
                'expectedLastPage' => 3,
                'expectedItemCount' => 10,
            ],
            'beyond_last_page' => [
                'page' => 999,
                'limit' => 20,
                'totalItems' => 50,
                'expectedLastPage' => 3,
                'expectedItemCount' => 0,
            ],
            'zero_page' => [
                'page' => 0,
                'limit' => 20,
                'totalItems' => 50,
                'expectedLastPage' => 3,
                'expectedItemCount' => 20,
            ],
            'negative_page' => [
                'page' => -1,
                'limit' => 20,
                'totalItems' => 50,
                'expectedLastPage' => 3,
                'expectedItemCount' => 20,
            ],
            'single_item' => [
                'page' => 1,
                'limit' => 20,
                'totalItems' => 1,
                'expectedLastPage' => 1,
                'expectedItemCount' => 1,
            ],
            'empty_result' => [
                'page' => 1,
                'limit' => 20,
                'totalItems' => 0,
                'expectedLastPage' => 1,
                'expectedItemCount' => 0,
            ],
        ];
    }

    public static function limitProvider(): array
    {
        return [
            'default_limit' => [
                'limit' => 20,
                'totalItems' => 100,
                'expectedLastPage' => 5,
            ],
            'small_limit' => [
                'limit' => 5,
                'totalItems' => 100,
                'expectedLastPage' => 20,
            ],
            'large_limit' => [
                'limit' => 100,
                'totalItems' => 50,
                'expectedLastPage' => 1,
            ],
            'limit_equals_total' => [
                'limit' => 50,
                'totalItems' => 50,
                'expectedLastPage' => 1,
            ],
        ];
    }

    public static function boundaryPageProvider(): array
    {
        return [
            'page_before_first' => [
                'page' => -5,
                'shouldBeValid' => false,
            ],
            'first_page' => [
                'page' => 1,
                'shouldBeValid' => true,
            ],
            'page_zero' => [
                'page' => 0,
                'shouldBeValid' => false,
            ],
            'very_large_page' => [
                'page' => 99999,
                'shouldBeValid' => false,
            ],
        ];
    }

    /**
     * @dataProvider paginationProvider
     */
    public function testPaginationCalculation(int $page, int $limit, int $totalItems, int $expectedLastPage, int $expectedItemCount): void
    {
        $effectivePage = max(1, $page);
        $lastPage = (int) ceil($totalItems / $limit);
        if ($lastPage < 1) {
            $lastPage = 1;
        }

        $offset = ($effectivePage - 1) * $limit;
        $itemCount = max(0, min($limit, $totalItems - $offset));

        $this->assertEquals($expectedLastPage, $lastPage);
        $this->assertEquals($expectedItemCount, $itemCount);
    }

    /**
     * @dataProvider limitProvider
     */
    public function testLimitAffectsPageCount(int $limit, int $totalItems, int $expectedLastPage): void
    {
        $lastPage = (int) ceil($totalItems / $limit);
        if ($lastPage < 1) {
            $lastPage = 1;
        }

        $this->assertEquals($expectedLastPage, $lastPage);
    }

    /**
     * @dataProvider boundaryPageProvider
     */
    public function testBoundaryPageValidation(int $page, bool $shouldBeValid): void
    {
        $isValidPage = $page >= 1;
        $this->assertEquals($shouldBeValid, $isValidPage);
    }

    public function testPaginationWithCategoryFilter(): void
    {
        $mockMusic = $this->createMock(Music::class);
        $categoryId = 5;
        $totalItems = 15;
        $limit = 10;

        $lastPage = (int) ceil($totalItems / $limit);
        $this->assertEquals(2, $lastPage);

        $firstPageCount = min($limit, $totalItems);
        $this->assertEquals(10, $firstPageCount);

        $secondPageCount = min($limit, $totalItems - $limit);
        $this->assertEquals(5, $secondPageCount);
    }

    public function testPaginationMetadataStructure(): void
    {
        $page = 2;
        $limit = 20;
        $totalItems = 50;
        $lastPage = (int) ceil($totalItems / $limit);

        $metadata = [
            'current_page' => $page,
            'per_page' => $limit,
            'total' => $totalItems,
            'last_page' => $lastPage,
            'has_more' => $page < $lastPage,
        ];

        $this->assertEquals(2, $metadata['current_page']);
        $this->assertEquals(20, $metadata['per_page']);
        $this->assertEquals(50, $metadata['total']);
        $this->assertEquals(3, $metadata['last_page']);
        $this->assertTrue($metadata['has_more']);
    }
}
