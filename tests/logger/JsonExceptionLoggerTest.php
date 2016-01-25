<?php

use PeoplePerHour\YiiLogExtension\Logger\JsonExceptionLogger;

class JsonExceptionLoggerTest  extends \PHPUnit_Framework_TestCase
{

    function testExtraDataReturnArrayWithMessageAndTrace()
    {
        $reflectionClass = new ReflectionClass('PeoplePerHour\\YiiLogExtension\\Logger\\JsonExceptionLogger');
        $method = $reflectionClass->getMethod('extraData');
        $method->setAccessible(true);
        $resultArray = $method->invokeArgs(
            new JsonExceptionLogger,
            ["error\nin /path/to/file1.php (53)\nin /path/to/file2.php (251)"]
        );
        $expectedArray = array(
            'message' => 'error',
            'trace' => "\nin /path/to/file1.php (53)\nin /path/to/file2.php (251)"
        );
        $this->assertEquals($expectedArray, $resultArray);
    }

}
