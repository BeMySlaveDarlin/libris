<?php

declare(strict_types=1);

namespace app\tests\Unit\Services;

use app\models\Author;
use app\models\Book;
use app\models\forms\ReportFilter;
use app\services\catalog\ReportService;
use Codeception\Test\Unit;
use Yii;

final class ReportServiceTest extends Unit
{
    private ReportService $_reports;

    protected function _before(): void
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        foreach (['book_author', 'book', 'author'] as $table) {
            Yii::$app->db->createCommand()->truncateTable("{{%{$table}}}")->execute();
        }
        Yii::$app->db->createCommand()->checkIntegrity(true)->execute();

        $this->_reports = new ReportService(Yii::$app->db, 10);
    }

    public function testCountsBooksPerAuthorForRequestedYear(): void
    {
        $prolific = $this->author('Прыткий Автор');
        $quiet = $this->author('Тихий Автор');

        $this->book('Первая', 2020, [$prolific]);
        $this->book('Вторая', 2020, [$prolific]);
        $this->book('Третья', 2020, [$quiet]);
        $this->book('Прошлогодняя', 2019, [$quiet]);

        $rows = $this->_reports->topAuthors($this->filter(2020));

        $this->assertCount(2, $rows);
        $this->assertSame('Прыткий Автор', $rows[0]['full_name']);
        $this->assertSame(2, (int) $rows[0]['books_count']);
        $this->assertSame(1, (int) $rows[1]['books_count']);
    }

    public function testCountsCoAuthoredBookForEveryAuthor(): void
    {
        $first = $this->author('Аркадий');
        $second = $this->author('Борис');
        $this->book('Совместная', 2021, [$first, $second]);

        $rows = $this->_reports->topAuthors($this->filter(2021));

        $this->assertCount(2, $rows);
        $this->assertSame(1, (int) $rows[0]['books_count']);
        $this->assertSame(1, (int) $rows[1]['books_count']);
    }

    public function testLimitCutsTail(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->book("Книга {$i}", 2022, [$this->author("Автор {$i}")]);
        }

        $filter = $this->filter(2022);
        $filter->limit = 25;
        $this->assertCount(5, $this->_reports->topAuthors($filter));

        $filter->limit = 10;
        $this->assertCount(5, $this->_reports->topAuthors($filter));
    }

    public function testFiltersByAuthorNamePart(): void
    {
        $this->book('Соляріс', 2023, [$this->author('Станислав Лем')]);
        $this->book('Обделённые', 2023, [$this->author('Урсула Ле Гуин')]);

        $filter = $this->filter(2023);
        $filter->author = 'Лем';
        $rows = $this->_reports->topAuthors($filter);

        $this->assertCount(1, $rows);
        $this->assertSame('Станислав Лем', $rows[0]['full_name']);
    }

    public function testMinBooksDropsAuthorsBelowThreshold(): void
    {
        $prolific = $this->author('Плодовитый');
        $this->book('Раз', 2024, [$prolific]);
        $this->book('Два', 2024, [$prolific]);
        $this->book('Одинокая', 2024, [$this->author('Скромный')]);

        $filter = $this->filter(2024);
        $filter->minBooks = 2;
        $rows = $this->_reports->topAuthors($filter);

        $this->assertCount(1, $rows);
        $this->assertSame('Плодовитый', $rows[0]['full_name']);
    }

    public function testReturnsEmptyListForYearWithoutBooks(): void
    {
        $this->assertSame([], $this->_reports->topAuthors($this->filter(1900)));
    }

    public function testAvailableYearsAreDistinctAndSortedDesc(): void
    {
        $author = $this->author('Автор');
        $this->book('А', 2001, [$author]);
        $this->book('Б', 2003, [$author]);
        $this->book('В', 2003, [$author]);

        $this->assertSame([2003, 2001], $this->_reports->availableYears());
        $this->assertSame(2003, $this->_reports->latestYear());
    }

    public function testLatestYearIsNullOnEmptyCatalogue(): void
    {
        $this->assertNull($this->_reports->latestYear());
    }

    private function filter(int $year): ReportFilter
    {
        $filter = new ReportFilter();
        $filter->year = $year;

        return $filter;
    }

    private function author(string $name): Author
    {
        $author = new Author(['full_name' => $name]);
        $author->save();

        return $author;
    }

    /**
     * @param Author[] $authors
     */
    private function book(string $title, int $year, array $authors): Book
    {
        $book = new Book(['title' => $title, 'year' => $year]);
        $book->save();
        foreach ($authors as $author) {
            $book->link('authors', $author);
        }

        return $book;
    }
}
