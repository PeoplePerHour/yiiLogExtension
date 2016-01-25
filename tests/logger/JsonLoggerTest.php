<?php

use PeoplePerHour\YiiLogExtension\Logger\JsonLogger;

\Yii::createWebApplication(array('id' => 'test', 'basePath' => ''));

class JsonLoggerTest extends \PHPUnit_Framework_TestCase
{

    public function testFormatLogMessageOverwritesDateWhenTimeIsArgumentPassed()
    {
        $jsonResult = $this->fixture('this is the message');
        $resultArray = json_decode($jsonResult, true);

        $expectedArray = array(
            'container'   => '',
            'environment' => 'test',
            'app_name'    => JsonLogger::$app_name,
            'build_id'    => '',
            'level'       => 'dummy_level',
            'category'    => 'dummy_category',
            'message'     => 'this is the message',
            '@timestamp'  => '2016-01-19T08:32:10Z'
        );
        $this->assertEquals(
            $expectedArray,
            array_intersect($expectedArray, $resultArray)
        );
        $this->assertArrayHasKey('host_ip', $resultArray);
    }

    public function testFormatLogMessageEndsWithNewLineCharacter()
    {
        $jsonResult = $this->fixture('this is the message');

        function endsWith($needle, $haystack) {
            return $needle === '' || (($temp = strlen($haystack) - strlen($needle)) >= 0
                && strpos($haystack, $needle, $temp) !== FALSE);
        }
        $this->assertTrue(endsWith("\n", $jsonResult));
    }

    public function testFormatMessageRemovesTrace()
    {
        $method = (new ReflectionClass('\\PeoplePerHour\YiiLogExtension\\Logger\\JsonLogger'))
            ->getMethod('extraData');
        $method->setAccessible(true);
        $strResult = $method->invokeArgs(
            new JsonLogger,
            ["foo\nin /path/to/file"]
        );
        $this->assertEquals(['message' => 'foo'], $strResult);
    }

    public function testJsonMessageHandlingExtractsReturnExtraDataMergedWithDecodedMessageData()
    {
        $dataArray = array(
            'foo' => 'bar',
            'message' => 'this is the message',
            'json_encoded' => true
        );

        // 2016-01-19T08:32:10Z
        $fixedTimestamp = '1453192330';

        $method = (new ReflectionClass('\\PeoplePerHour\YiiLogExtension\\Logger\\JsonLogger'))
            ->getMethod('jsonMessageHandling');
        $method->setAccessible(true);
        $arrayResult = $method->invokeArgs(
            new JsonLogger,
            array(['message' => json_encode($dataArray)])
        );
        $expectedArray = array(
            'message' => 'this is the message',
            'foo' => 'bar'
        );
        $this->assertEquals($expectedArray, $arrayResult);
    }

    protected function fixture($message)
    {
        $method = (new ReflectionClass('\\PeoplePerHour\YiiLogExtension\\Logger\\JsonLogger'))
            ->getMethod('formatLogMessage');
        $method->setAccessible(true);

        // 2016-01-19T08:32:10Z
        $fixedTimestamp = '1453192330';
        $jsonResult = $method->invokeArgs(
            new JsonLogger,
            [$message, 'dummy_level', 'dummy_category', $fixedTimestamp]
        );
        return $jsonResult;
    }

}
