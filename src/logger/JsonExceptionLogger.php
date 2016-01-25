<?php

namespace PeoplePerHour\YiiLogExtension\Logger;

class JsonExceptionLogger extends JsonLogger
{
    public static $category = 'error';

    /**
    * Logger to log exceptions with message and prettified trace. Outputs logs
    * to file in JSON format
    *
    * @param string message
    * @return array with message and trace fields
    **/
    protected function extraData($message)
    {
        if (is_array($message)) {
                $logMessage = preg_replace('#^{([^}]+)}(.*)?$#iUs', '{$1} ', $message);
                $trace = preg_replace('#^{([^\)]+)}(.*)?$#iUm', '$2', $message);
            } else {
                $pos = strpos($message, "\n"); // Find where the new line is as it designates that a trace exists
                if ($pos == 0){ // no newline, so no trace exists in message (YII_LEVEL = 0)
                    $logMessage = $message;
                    $trace = '';
                } else { // Newline exists so trace follows in new lines
                    $logMessage = substr($message, 0, $pos);
                    $trace = substr($message, $pos, strlen($message));
                }
            }
            $extraData = array(
                'message' => $logMessage,
                'trace'   => $trace,
            );

            return $extraData;
    }
}
