<?php

use PeoplePerHour\YiiLogExtension\YiiLogWrapper;

class LogWrapperTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Yii::import('system.logging.CLogger', true);
    }

    public function testLog()
    {
        $logMessage = 'dummy message';
        $logCategory = 'dummy category';
        YiiLogWrapper::log($logMessage, \CLogger::LEVEL_ERROR, $logCategory);

        /**
        * Each array element represents one message with the following structure:
        * array(
        *     [0] => message (string)
        *     [1] => level (string)
        *     [2] => category (string)
        *     [3] => timestamp (float, obtained by microtime(true));.
        * );
        **/
        $logs = \Yii::getLogger()->getLogs();
        $lastLog = array_pop($logs);
        $this->assertEquals($logMessage, $lastLog[0]);
        $this->assertEquals(\CLogger::LEVEL_ERROR, $lastLog[1]);
        $this->assertEquals($logCategory, $lastLog[2]);
    }

    public function testLogArray()
    {
        $inputArray = ['foo' => 'bar'];
        $logCategory = 'dummy category';
        YiiLogWrapper::logArray(
            $inputArray,
            \CLogger::LEVEL_ERROR,
            $logCategory
        );
        $logs = \Yii::getLogger()->logs;
        $lastLog = array_pop($logs);
        $this->assertEquals(
            array_merge(
                $inputArray,
                [YiiLogWrapper::JSON_ENCODED => true]
            ),
            json_decode($lastLog[0], true)
        );
        $this->assertEquals(\CLogger::LEVEL_ERROR, $lastLog[1]);
        $this->assertEquals($logCategory, $lastLog[2]);
    }

    public function testLogException()
    {
        $exc = new Exception('Dummy Exception');
        $logCategory = 'dummy category';
        YiiLogWrapper::logException($exc, \CLogger::LEVEL_ERROR, $logCategory);
        $logs = \Yii::getLogger()->logs;
        $lastLog = array_pop($logs);
        $this->assertEquals(
            array_merge(
                array(
                    'message' => $exc->getMessage(),
                    'trace' => $exc->getTraceAsString()
                ),
                array(YiiLogWrapper::JSON_ENCODED => true)
            ),
            json_decode($lastLog[0], true)
        );
        $this->assertEquals(\CLogger::LEVEL_ERROR, $lastLog[1]);
        $this->assertEquals($logCategory, $lastLog[2]);
    }

}
