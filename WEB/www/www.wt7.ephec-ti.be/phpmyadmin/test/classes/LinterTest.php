<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Tests for Linter.php.
 *
 * @package PhpMyAdmin-test
 */
declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\Linter;
use PhpMyAdmin\Tests\PmaTestCase;

/**
 * Tests for PhpMyAdmin\Linter
 *
 * @package PhpMyAdmin-test
 */
class LinterTest extends PmaTestCase
{
    /**
     * Test for Linter::getLines
     *
     * @return void
     */
    public function testGetLines()
    {
        $this->assertEquals([0], Linter::getLines(''));
        $this->assertEquals([0, 2], Linter::getLines("a\nb"));
        $this->assertEquals([0, 4, 7], Linter::getLines("abc\nde\n"));
    }

    /**
     * Test for Linter::findLineNumberAndColumn
     *
     * @return void
     */
    public function testFindLineNumberAndColumn()
    {
        // Let the analyzed string be:
        //      ^abc$
        //      ^de$
        //      ^$
        //
        // Where `^` is the beginning of the line and `$` the end of the line.
        //
        // Positions of each character (by line):
        //      ( a, 0), ( b, 1), ( c, 2), (\n, 3),
        //      ( d, 4), ( e, 5), (\n, 6),
        //      (\n, 7).
        $this->assertEquals(
            [
                1,
                0,
            ],
            Linter::findLineNumberAndColumn([0, 4, 7], 4)
        );
        $this->assertEquals(
            [
                1,
                1,
            ],
            Linter::findLineNumberAndColumn([0, 4, 7], 5)
        );
        $this->assertEquals(
            [
                1,
                2,
            ],
            Linter::findLineNumberAndColumn([0, 4, 7], 6)
        );
        $this->assertEquals(
            [
                2,
                0,
            ],
            Linter::findLineNumberAndColumn([0, 4, 7], 7)
        );
    }

    /**
     * Test for Linter::lint
     *
     * @dataProvider lintProvider
     *
     * @param array  $expected The expected result.
     * @param string $query    The query to be analyzed.
     *
     * @return void
     */
    public function testLint($expected, $query): void
    {
        $this->assertEquals($expected, Linter::lint($query));
    }

    /**
     * Provides data for `testLint`.
     *
     * @return array
     */
    public static function lintProvider()
    {
        return [
            [
                [],
                '',
            ],
            [
                [],
                'SELECT * FROM tbl',
            ],
            [
                [
                    [
                        'message' => 'Unrecognized data type. (near ' .
                            '<code>IN</code>)',
                        'fromLine' => 0,
                        'fromColumn' => 22,
                        'toLine' => 0,
                        'toColumn' => 24,
                        'severity' => 'error',
                    ],
                    [
                        'message' => 'A closing bracket was expected. (near ' .
                            '<code>IN</code>)',
                        'fromLine' => 0,
                        'fromColumn' => 22,
                        'toLine' => 0,
                        'toColumn' => 24,
                        'severity' => 'error',
                    ],
                ],
                'CREATE TABLE tbl ( id IN',
            ],
            [
                [
                    [
                        'message' => 'Linting is disabled for this query because ' .
                            'it exceeds the maximum length.',
                        'fromLine' => 0,
                        'fromColumn' => 0,
                        'toLine' => 0,
                        'toColumn' => 0,
                        'severity' => 'warning',
                    ],
                ],
                str_repeat(";", 10001),
            ],
        ];
    }
}
