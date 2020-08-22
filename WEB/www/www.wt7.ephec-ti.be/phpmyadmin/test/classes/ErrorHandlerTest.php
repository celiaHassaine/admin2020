<?php
/**
 * Tests for ErrorHandler
 *
 * @package PhpMyAdmin-test
 */
declare(strict_types=1);

namespace PhpMyAdmin\Tests;

use PhpMyAdmin\ErrorHandler;
use PhpMyAdmin\Tests\PmaTestCase;
use ReflectionClass;

/**
 * Test for PhpMyAdmin\ErrorHandler class.
 *
 * @package PhpMyAdmin-test
 */
class ErrorHandlerTest extends PmaTestCase
{
    /**
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     * @return void
     */
    protected function setUp(): void
    {
        $this->object = new ErrorHandler();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->object);
    }

    /**
     * Call protected functions by setting visibility to public.
     *
     * @param string $name   method name
     * @param array  $params parameters for the invocation
     *
     * @return mixed the output from the protected method.
     */
    private function _callProtectedFunction($name, $params)
    {
        $class = new ReflectionClass(ErrorHandler::class);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($this->object, $params);
    }

    /**
     * Data provider for testHandleError
     *
     * @return array data for testHandleError
     */
    public function providerForTestHandleError()
    {
        return [
            [
                E_RECOVERABLE_ERROR,
                'Compile Error',
                'error.txt',
                12,
                'Compile Error',
                '',
            ],
            [
                E_USER_NOTICE,
                'User notice',
                'error.txt',
                12,
                'User notice',
                'User notice',
            ],
        ];
    }

    /**
     * Test for getDispErrors when PHP errors are not shown
     *
     * @param integer $errno       error number
     * @param string  $errstr      error string
     * @param string  $errfile     error file
     * @param integer $errline     error line
     * @param string  $output_show expected output if showing of errors is
     *                             enabled
     * @param string  $output_hide expected output if showing of errors is
     *                             disabled and 'sendErrorReports' is set to 'never'
     *
     * @return void
     *
     * @dataProvider providerForTestHandleError
     */
    public function testGetDispErrorsForDisplayFalse(
        $errno,
        $errstr,
        $errfile,
        $errline,
        $output_show,
        $output_hide
    ) {
        // TODO: Add other test cases for all combination of 'sendErrorReports'
        $GLOBALS['cfg']['SendErrorReports'] = 'never';

        $this->object->handleError($errno, $errstr, $errfile, $errline);

        $output = $this->object->getDispErrors();

        if ($output_hide == '') {
            $this->assertEquals('', $output);
        } else {
            $this->assertStringContainsString($output_hide, $output);
        }
    }

    /**
     * Test for getDispErrors when PHP errors are shown
     *
     * @param integer $errno       error number
     * @param string  $errstr      error string
     * @param string  $errfile     error file
     * @param integer $errline     error line
     * @param string  $output_show expected output if showing of errors is
     *                             enabled
     * @param string  $output_hide expected output if showing of errors is
     *                             disabled
     *
     * @return void
     *
     * @dataProvider providerForTestHandleError
     */
    public function testGetDispErrorsForDisplayTrue(
        $errno,
        $errstr,
        $errfile,
        $errline,
        $output_show,
        $output_hide
    ) {
        $this->object->handleError($errno, $errstr, $errfile, $errline);

        $this->assertStringContainsString(
            $output_show,
            $this->object->getDispErrors()
        );
    }

    /**
     * Test for checkSavedErrors
     *
     * @return void
     */
    public function testCheckSavedErrors()
    {

        $_SESSION['errors'] = [];

        $this->_callProtectedFunction(
            'checkSavedErrors',
            []
        );
        $this->assertArrayNotHasKey('errors', $_SESSION);
    }

    /**
     * Test for countErrors
     *
     * @return void
     *
     * @group medium
     */
    public function testCountErrors()
    {
        $this->object->addError(
            'Compile Error',
            E_WARNING,
            'error.txt',
            15
        );
        $this->assertEquals(
            1,
            $this->object->countErrors()
        );
    }

    /**
     * Test for sliceErrors
     *
     * @return void
     *
     * @group medium
     */
    public function testSliceErrors()
    {
        $this->object->addError(
            'Compile Error',
            E_WARNING,
            'error.txt',
            15
        );
        $this->assertEquals(
            1,
            $this->object->countErrors()
        );
        $this->assertEquals(
            [],
            $this->object->sliceErrors(1)
        );
        $this->assertEquals(
            1,
            $this->object->countErrors()
        );
        $this->assertCount(
            1,
            $this->object->sliceErrors(0)
        );
        $this->assertEquals(
            0,
            $this->object->countErrors()
        );
    }

    /**
     * Test for countUserErrors
     *
     * @return void
     */
    public function testCountUserErrors()
    {
        $this->object->addError(
            'Compile Error',
            E_WARNING,
            'error.txt',
            15
        );
        $this->assertEquals(
            0,
            $this->object->countUserErrors()
        );
        $this->object->addError(
            'Compile Error',
            E_USER_WARNING,
            'error.txt',
            15
        );
        $this->assertEquals(
            1,
            $this->object->countUserErrors()
        );
    }

    /**
     * Test for hasUserErrors
     *
     * @return void
     */
    public function testHasUserErrors()
    {
        $this->assertFalse($this->object->hasUserErrors());
    }

    /**
     * Test for hasErrors
     *
     * @return void
     */
    public function testHasErrors()
    {
        $this->assertFalse($this->object->hasErrors());
    }

    /**
     * Test for countDisplayErrors
     *
     * @return void
     */
    public function testCountDisplayErrorsForDisplayTrue()
    {
        $this->assertEquals(
            0,
            $this->object->countDisplayErrors()
        );
    }

    /**
     * Test for countDisplayErrors
     *
     * @return void
     */
    public function testCountDisplayErrorsForDisplayFalse()
    {
        $this->assertEquals(
            0,
            $this->object->countDisplayErrors()
        );
    }

    /**
     * Test for hasDisplayErrors
     *
     * @return void
     */
    public function testHasDisplayErrors()
    {
        $this->assertFalse($this->object->hasDisplayErrors());
    }
}
