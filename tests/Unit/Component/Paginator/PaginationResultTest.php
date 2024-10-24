<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Paginator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class PaginationResultTest extends TestCase
{
    public static function getTestPageCountData()
    {
        return [
            [1, 10, 40, [], 4],
            [1, 10, 41, [], 5],
            [1, 10, 49, [], 5],
            [1, 10, 50, [], 5],
            [1, 10, 51, [], 6],
            [1, 10, 5, [], 1],
            [1, 0, 0, [], 0],
            [1, null, 5, [], 1],
            [1, null, 0, [], 0],
        ];
    }

    /**
     * @param mixed $page
     * @param mixed $pageSize
     * @param mixed $totalCount
     * @param mixed $results
     * @param mixed $expectedPageCount
     */
    #[DataProvider('getTestPageCountData')]
    public function testGetPageCount($page, $pageSize, $totalCount, $results, $expectedPageCount)
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, $results);

        $this->assertSame($expectedPageCount, $paginationResult->getPageCount());
    }

    public static function getTestIsFirstPageData()
    {
        yield [1, 10, 20, true];

        yield [2, 10, 20, false];

        yield [1, null, 20, true];
    }

    /**
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param bool $expectedIsFirst
     */
    #[DataProvider('getTestIsFirstPageData')]
    public function testIsFirstPage(int $page, ?int $pageSize, int $totalCount, bool $expectedIsFirst)
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedIsFirst, $paginationResult->isFirstPage());
    }

    public static function getTestIsLastPageData()
    {
        yield [1, 10, 20, false];

        yield [2, 10, 20, true];

        yield [1, 10, 21, false];

        yield [2, 10, 21, false];

        yield [3, 10, 21, true];

        yield [1, null, 20, true];
    }

    /**
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param bool $expectedIsLast
     */
    #[DataProvider('getTestIsLastPageData')]
    public function testIsLastPage(int $page, ?int $pageSize, int $totalCount, bool $expectedIsLast)
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedIsLast, $paginationResult->isLastPage());
    }

    public static function getTestGetPreviousPageData()
    {
        yield [1, 10, 20, null];

        yield [2, 10, 20, 1];

        yield [3, 10, 21, 2];

        yield [1, null, 20, null];
    }

    /**
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param int|null $expectedPrevious
     */
    #[DataProvider('getTestGetPreviousPageData')]
    public function testGetPreviousPage(int $page, ?int $pageSize, int $totalCount, ?int $expectedPrevious)
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedPrevious, $paginationResult->getPreviousPage());
    }

    public static function getTestGetNextPageData()
    {
        yield [1, 10, 20, 2];

        yield [2, 10, 20, null];

        yield [2, 10, 21, 3];

        yield [3, 10, 21, null];

        yield [1, null, 20, null];
    }

    /**
     * @param int $page
     * @param int|null $pageSize
     * @param int $totalCount
     * @param int|null $expectedNext
     */
    #[DataProvider('getTestGetNextPageData')]
    public function testGetNextPage(int $page, ?int $pageSize, int $totalCount, ?int $expectedNext)
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, []);

        $this->assertSame($expectedNext, $paginationResult->getNextPage());
    }
}
