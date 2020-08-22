<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * tests for PhpMyAdmin\SqlQueryForm
 *
 * @package PhpMyAdmin-test
 */
declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\Core;
use PhpMyAdmin\Encoding;
use PhpMyAdmin\SqlQueryForm;
use PhpMyAdmin\Theme;
use PhpMyAdmin\Url;
use PhpMyAdmin\Util;
use PHPUnit\Framework\TestCase;

//the following definition should be used globally
$GLOBALS['server'] = 0;

/**
 * PhpMyAdmin\Tests\SqlQueryFormTest class
 *
 * this class is for testing PhpMyAdmin\SqlQueryForm methods
 *
 * @package PhpMyAdmin-test
 */
class SqlQueryFormTest extends TestCase
{
    /**
     * @var SqlQueryForm
     */
    private $sqlQueryForm;

    /**
     * Test for setUp
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->sqlQueryForm = new SqlQueryForm();

        //$GLOBALS
        $GLOBALS['max_upload_size'] = 100;
        $GLOBALS['PMA_PHP_SELF'] = Core::getenv('PHP_SELF');
        $GLOBALS['db'] = "PMA_db";
        $GLOBALS['table'] = "PMA_table";
        $GLOBALS['text_dir'] = "text_dir";

        $GLOBALS['cfg']['GZipDump'] = false;
        $GLOBALS['cfg']['BZipDump'] = false;
        $GLOBALS['cfg']['ZipDump'] = false;
        $GLOBALS['cfg']['ServerDefault'] = "default";
        $GLOBALS['cfg']['TextareaAutoSelect'] = true;
        $GLOBALS['cfg']['TextareaRows'] = 100;
        $GLOBALS['cfg']['TextareaCols'] = 11;
        $GLOBALS['cfg']['DefaultTabDatabase'] = "structure";
        $GLOBALS['cfg']['RetainQueryBox'] = true;
        $GLOBALS['cfg']['ActionLinksMode'] = 'both';
        $GLOBALS['cfg']['DefaultTabTable'] = 'browse';
        $GLOBALS['cfg']['CodemirrorEnable'] = true;
        $GLOBALS['cfg']['DefaultForeignKeyChecks'] = 'default';

        //_SESSION
        $_SESSION['relation'][0] = [
            'PMA_VERSION' => PMA_VERSION,
            'table_coords' => "table_name",
            'displaywork' => 'displaywork',
            'db' => "information_schema",
            'table_info' => 'table_info',
            'relwork' => 'relwork',
            'relation' => 'relation',
            'bookmarkwork' => false,
        ];
        //$GLOBALS
        $GLOBALS['cfg']['Server']['user'] = "user";
        $GLOBALS['cfg']['Server']['pmadb'] = "pmadb";
        $GLOBALS['cfg']['Server']['bookmarktable'] = "bookmarktable";

        //$_SESSION

        //Mock DBI
        $dbi = $this->getMockBuilder('PhpMyAdmin\DatabaseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $fetchResult = [
            "index1" => "table1",
            "index2" => "table2",
        ];
        $dbi->expects($this->any())
            ->method('fetchResult')
            ->will($this->returnValue($fetchResult));

        $getColumns = [
            [
                "Field" => "field1",
                "Comment" => "Comment1",
            ],
        ];
        $dbi->expects($this->any())
            ->method('getColumns')
            ->will($this->returnValue($getColumns));

        $GLOBALS['dbi'] = $dbi;
    }

    /**
     * Test for getHtmlForInsert
     *
     * @return void
     */
    public function testPMAGetHtmlForSqlQueryFormInsert()
    {
        //Call the test function
        $query = "select * from PMA";
        $html = $this->sqlQueryForm->getHtmlForInsert($query);

        //validate 1: query
        $this->assertStringContainsString(
            htmlspecialchars($query),
            $html
        );

        //validate 2: enable auto select text in textarea
        $auto_sel = ' onclick="Functions.selectContent(this, sqlBoxLocked, true);"';
        $this->assertStringContainsString(
            $auto_sel,
            $html
        );

        //validate 3: showMySQLDocu
        $this->assertStringContainsString(
            Util::showMySQLDocu('SELECT'),
            $html
        );

        //validate 4: $fields_list
        $this->assertStringContainsString(
            '<input type="button" value="DELETE" id="delete"',
            $html
        );
        $this->assertStringContainsString(
            '<input type="button" value="UPDATE" id="update"',
            $html
        );
        $this->assertStringContainsString(
            '<input type="button" value="INSERT" id="insert"',
            $html
        );
        $this->assertStringContainsString(
            '<input type="button" value="SELECT" id="select"',
            $html
        );
        $this->assertStringContainsString(
            '<input type="button" value="SELECT *" id="selectall"',
            $html
        );

        //validate 5: Clear button
        $this->assertStringContainsString(
            '<input type="button" value="DELETE" id="delete"',
            $html
        );
        $this->assertStringContainsString(
            __('Clear'),
            $html
        );
    }

    /**
     * Test for getHtml
     *
     * @return void
     */
    public function testPMAGetHtmlForSqlQueryForm()
    {
        //Call the test function
        $GLOBALS['is_upload'] = true;
        $GLOBALS['lang'] = 'ja';
        $query = "select * from PMA";
        $html = $this->sqlQueryForm->getHtml($query);

        //validate 1: query
        $this->assertStringContainsString(
            htmlspecialchars($query),
            $html
        );

        //validate 2: $enctype
        $enctype = ' enctype="multipart/form-data"';
        $this->assertStringContainsString(
            $enctype,
            $html
        );

        //validate 3: sqlqueryform
        $this->assertStringContainsString(
            'id="sqlqueryform" name="sqlform">',
            $html
        );

        //validate 4: $db, $table
        $table  = $GLOBALS['table'];
        $db     = $GLOBALS['db'];
        $this->assertStringContainsString(
            Url::getHiddenInputs($db, $table),
            $html
        );

        //validate 5: $goto
        $goto = empty($GLOBALS['goto']) ? 'tbl_sql.php' : $GLOBALS['goto'];
        $this->assertStringContainsString(
            htmlspecialchars($goto),
            $html
        );

        //validate 6: Kanji encoding form
        $this->assertStringContainsString(
            Encoding::kanjiEncodingForm(),
            $html
        );
        $GLOBALS['lang'] = 'en';
    }
}
