<?php

namespace PeoplePerHour\YiiLogExtension;

/**
 * LogWrapper extends the interface of Yii Logger, which only handles strings.
 * LogWrapper allows array logging.
 *
 * Usage:
 * LogWrapper::log('dummy message', 'info', 'application')
 * LogWrapper::logArray(['foo' => 'bar'], 'info', 'application')
 *
**/
class YiiLogWrapper
{

    CONST JSON_ENCODED = 'json_encoded';

    /**
    * Just a wrapper to Yii log
    **/
    public static function log($message, $level=\CLogger::LEVEL_INFO, $category='application')
    {
        \Yii::log($message, $level, $category);
    }


    /**
    * Performs array logging. The array will be json encoded and a
    * corresponding 'json_encoded' field will be appended in order to be
    * recognized by the log handler.
    *
    * @param data array should be associative
    * @param level string with the level of the errror as seen in CLogger
    * @param category string with the category of the log as used in logroutes
    *       (eg app.componenets.test)
    **/
    public static function logArray(array $data, $level=\CLogger::LEVEL_INFO, $category='application')
    {
        $data[self::JSON_ENCODED] = true;
        $jsonMessage = json_encode($data);
        \Yii::log($jsonMessage, $level , $category);
    }

    /**
    * Logs exception message and trace
    *
    * @param exc oject exception
    * @param level string with the level of the errror as seen in CLogger
    * @param category string with the category of the log as used in logroutes
    *       (eg app.componenets.test)
    **/
    public static function logException($exc, $level=\CLogger::LEVEL_ERROR, $category='exception')
    {
        $data = array(
            'message' => $exc->getMessage(),
            'trace'   => $exc->getTraceAsString()
        );
        static::logArray($data, $level, $category);
    }
}
