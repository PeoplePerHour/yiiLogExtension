<?php

namespace PeoplePerHour\YiiLogExtension\Logger;

use PeoplePerHour\YiiLogExtension\YiiLogWrapper;

/**
 * A logger component that outputs the logs in file in JSON format.
 * Provides flexibility for variable fields to be logged.
 *
 * Inputs:
 * LogWrapper::log('dummy Message', 'info', 'application')
 * LogWrapper::logArray(array('foo' => 'bar'), 'info', 'application')
 * Yii::log('dummy Message', 'info', 'application')
 *
 * Outputs:
 * A json formatted string which includes user enterred fields
 * ("array with fields") together with some predefined fields. This message is
 * ouputed to file. Also handles file rotation.
 *
 * Usage:
 * Include the JsonLogger class in your log configs as such:
 * array(
 *       'class'       => 'JsonLogger',
 *       'categories'  => 'app.somecategory',
 *       'logFile'     => 'outputfile.log',
 *       'logPath'     =>  dirname(__FILE__).'/../../logs',
 *   )
 *
 * Extend:
 * To extend overwrite function extraData
**/
class JsonLogger extends \CFileLogRoute
{

    public static $app_name = 'pph';

    /**
    * Override the parent function in order to massage the trace messages
    * from the dev populated message
    **/
    protected function processLogs($logs)
    {
        $logtext = '';

        foreach ($logs as $log) {
            $logtext .= $this->formatLogMessage($log[0], $log[1], $log[2], $log[3]);
        }
        $this->logFile($logtext);
    }

    /**
    * Output logtext to file and handle rotation if file size exceeds threshold
    *
    * @param logtext string the json encoded text to be logged
    **/
    protected function logFile($logtext)
    {
        $logFile = $this->getLogPath() . DIRECTORY_SEPARATOR . $this->getLogFile();
        $fp = @fopen($logFile, 'a');
        @flock($fp, LOCK_EX);
        if (@filesize($logFile) > $this->getMaxFileSize() * 1024) {
            $this->rotateFiles();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($logFile, $logtext, FILE_APPEND|LOCK_EX);
        } else {
            @fwrite($fp, $logtext);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }

    /**
    * Overide default formatLogMessage in order to append necessary fields and
    * also format in a json format for logstash to use
    *
    * @param string message which will handled as text or json
    * @param level string with the level of the errror as seen in CLogger
    * @param category string with the category of the log as used in logroutes
    *       (eg app.componenets.test)
    * @param time timestamp
    * @return string with json encoded log
    **/
    protected function formatLogMessage($message, $level, $category, $time)
    {
        $extraData = $this->jsonMessageHandling($this->extraData($message));
        $logArray = array_merge(
            static::defaultData($level, $category, $time),
            $extraData
        );
        $messageStr = json_encode($logArray) . "\n";

        return $messageStr;
    }

    /**
    * @param time timestamp
    * @param level string with the level of the errror as seen in CLogger
    * @param category string with the category of the log as used in logroutes
    *       (eg app.componenets.test)
    * @param time timestamp
    * @return array with the default values to be logged
    **/
    static protected function defaultData($level, $category, $time)
    {
        $datetime = $time ? (new \DateTime)->setTimestamp($time) : new \DateTime;
        $formatedDate = $datetime->format('Y-m-d\TH:i:s\Z');
        $hostIp = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : \
            gethostbyname(gethostname());
        $defaultData = array(
            '@timestamp'    => $formatedDate,
            'category'      => $category,
            'host_ip'       => $hostIp,
            'container'     => self::getContainerName(),
            'build_id'      => self::getBuildID(),
            'environment'   => \Yii::app()->id,
            'app_name'      => self::$app_name,
            'level'         => $level
        );

        return $defaultData;
    }

    /**
    * Sets a message field with text and removes the stack trace if YII_DEBUG
    * is enabled
    *
    * @param string message
    * @return array with message field
    **/
    protected function extraData($message)
    {
        $tracePos = strpos($message, "\nin ");
        $message = $tracePos ? substr($message, 0, $tracePos) : $message;
        return ['message' => $message];
    }

    /**
    * If the message contains json_encoded the message is assumed to be json
    * so it will be decoded and extra fields will be merged with extraData.
    * Common fields will be overwritten by json data
    *
    * @param string message
    * @return array with data fields to be logged
    **/
    protected function jsonMessageHandling($extraData)
    {
        if (strpos($extraData['message'], YiiLogWrapper::JSON_ENCODED)) {
            $jsonDecodedData = json_decode($extraData['message'], true);
            $extraData = array_merge($extraData, $jsonDecodedData);
            unset($extraData[YiiLogWrapper::JSON_ENCODED]);
        }

        return $extraData;
    }

    /**
    * Placeholder function
    **/
    protected static function getBuildID()
    {
        return '';
    }

    /**
    * Placeholder function
    **/
    protected static function getContainerName()
    {
        return '';
    }

}
