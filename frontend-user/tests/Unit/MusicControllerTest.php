<?php

namespace tests\Unit;

use tests\TestCase;
use app\controller\api\MusicController;
use app\model\Music;
use Mockery as m;
use think\Request;

class MusicControllerTest extends TestCase
{
    private $musicController;

    protected function setUp(): void
    {
        parent::setUp();

        $this->musicController = m::mock(MusicController::class)->makePartial();
    }

    public function paginationDataProvider(): array
    {
        return [
            'default page and limit' => [
                [],
                1,
                20
            ],
            'first page' => [
                ['page' => 1, 'limit' => 10],
                1,
                10
            ],
            'second page' => [
                ['page' => 2, 'limit' => 10],
                2,
                10
            ],
            'boundary - page 0 should become 1' => [
                ['page' => 0, 'limit' => 10],
                1,
                10
            ],
            'boundary - negative page should become 1' => [
                ['page' => -5, 'limit' => 10],
                1,
                10
            ],
            'large limit' => [
                ['page' => 1, 'limit' => 100],
                1,
                100
            ],
            'boundary - limit 0 should use default' => [
                ['page' => 1, 'limit' => 0],
                1,
                20
            ],
            'boundary - negative limit should use default' => [
                ['page' => 1, 'limit' => -10],
                1,
                20
            ],
            'with category filter' => [
                ['page' => 1, 'limit' => 20, 'category_id' => 3],
                1,
                20
            ]
        ];
    }

    /**
     * @dataProvider paginationDataProvider
     */
    public function testMusicListPagination(array $input, int $expectedPage, int $expectedLimit): void
    {
        $_GET = $input;

        $paginatorMock = m::mock('overload:think\Paginator');
        $paginatorMock->shouldReceive('toArray')->andReturn([
            'total' => 100,
            'per_page' => $expectedLimit,
            'current_page' => $expectedPage,
            'data' => []
        ]);

        $queryMock = m::mock();
        $queryMock->shouldReceive('where')->with('status', 1)->andReturnSelf();

        if (isset($input['category_id']) && $input['category_id'] > 0) {
            $queryMock->shouldReceive('where')->with('category_id', $input['category_id'])->andReturnSelf();
        }

        $queryMock->shouldReceive('with')->with(['category'])->andReturnSelf();
        $queryMock->shouldReceive('order')->with('created_at', 'desc')->andReturnSelf();
        $queryMock->shouldReceive('paginate')->with([
            'page' => $expectedPage,
            'list_rows' => $expectedLimit
        ])->andReturn($paginatorMock);

        Music::shouldReceive('where')->with('status', 1)->andReturn($queryMock);

        $response = $this->musicController->list();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $responseData['code']);
        $this->assertEquals($expectedPage, $responseData['data']['current_page']);
        $this->assertEquals($expectedLimit, $responseData['data']['per_page']);
    }

    public function testMusicListWithCategoryFilter(): void
    {
        $_GET = ['page' => 1, 'limit' => 10, 'category_id' => 5];

        $paginatorMock = m::mock('overload:think\Paginator');
        $paginatorMock->shouldReceive('toArray')->andReturn([
            'total' => 20,
            'per_page' => 10,
            'current_page' => 1,
            'data' => []
        ]);

        $queryMock = m::mock();
        $queryMock->shouldReceive('where')->with('status', 1)->andReturnSelf();
        $queryMock->shouldReceive('where')->with('category_id', 5)->andReturnSelf();
        $queryMock->shouldReceive('with')->with(['category'])->andReturnSelf();
        $queryMock->shouldReceive('order')->with('created_at', 'desc')->andReturnSelf();
        $queryMock->shouldReceive('paginate')->andReturn($paginatorMock);

        Music::shouldReceive('where')->with('status', 1)->andReturn($queryMock);

        $response = $this->musicController->list();
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(200, $responseData['code']);
    }
}
