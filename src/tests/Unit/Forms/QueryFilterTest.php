<?php

declare(strict_types=1);

namespace app\tests\Unit\Forms;

use app\models\forms\AuthorSearch;
use app\models\forms\BookSearch;
use app\models\forms\ReportFilter;
use Codeception\Test\Unit;

final class QueryFilterTest extends Unit
{
    public function testEmptyStringsFromQueryDoNotBreakNullableIntegers(): void
    {
        $filter = new ReportFilter();
        $filter->load(['year' => '', 'minBooks' => '', 'author' => '', 'limit' => '']);

        $this->assertNull($filter->year);
        $this->assertNull($filter->minBooks);
        $this->assertSame('', $filter->author);
        $this->assertSame(10, $filter->limit);
    }

    public function testNonNumericValueForIntegerIsDropped(): void
    {
        $filter = new ReportFilter();
        $filter->load(['year' => 'abc', 'minBooks' => 'дюжина', 'limit' => 'все']);

        $this->assertNull($filter->year);
        $this->assertNull($filter->minBooks);
        $this->assertSame(10, $filter->limit);
    }

    public function testScalarInsteadOfArrayIsDropped(): void
    {
        $search = new BookSearch();
        $search->load(['genres' => 'детектив']);

        $this->assertSame([], $search->genres);
    }

    public function testArrayInsteadOfStringIsDropped(): void
    {
        $search = new BookSearch();
        $search->load(['q' => ['a', 'b']]);

        $this->assertSame('', $search->q);
    }

    public function testValuesAreStillLoaded(): void
    {
        $filter = new ReportFilter();
        $filter->load(['year' => '1974', 'minBooks' => '2', 'limit' => '25', 'author' => 'Лем']);

        $this->assertSame(1974, $filter->year);
        $this->assertSame(2, $filter->minBooks);
        $this->assertSame(25, $filter->limit);
        $this->assertSame('Лем', $filter->author);
    }

    public function testAuthorSearchSurvivesBlankQuery(): void
    {
        $search = new AuthorSearch();
        $search->load(['q' => '', 'genres' => ['']]);

        $this->assertSame('', $search->q);
        $this->assertFalse($search->isFiltered());
    }

    public function testFilteredFlagReactsToValues(): void
    {
        $search = new BookSearch();
        $search->load(['q' => 'Лем']);

        $this->assertTrue($search->isFiltered());
    }
}
