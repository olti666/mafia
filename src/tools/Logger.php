<?php

namespace Tools;

require_once 'vendor/autoload.php'; 

class Logger{

  /**
     * Used when an action is successful or is an information which is not related to any error.
     *
     * @param string $message
     * @param string|array $data
     * @return void
     */
    public static function info(string $message, $data = NULL){
        static::log('INFO', $message, $data);
    }

    /**
     * Used when an action is showing a warning
     *
     * @param string $message
     * @param string|array $data
     * @return void
     */
    public static function warning(string $message, $data = NULL){
        static::log('WARNING', $message, $data);
    }

    /**
     * Used when an action is unsuccessful
     *
     * @param string $message
     * @param string|array $data
     * @return void
     */
    public static function error(string $message, $data = NULL){
        static::log('ERROR', $message, $data);
    }

    /**
     * Used to stored log message/data in the log file
     *
     * @param string $type
     * @param string $message
     * @param string|array $data
     * @return void
     */
    protected static function log(string $type, string $message, $data =  NULL){
        if(!is_scalar($data) && $data !== NULL){
            $data = json_encode($data);
        }else{
            $data = (string)$data; 
        }
        $row = '['.date('Y-m-d H:i:s').'] local.'.$type.' '.$message.' '.$data."\n";
        
        if($type == 'ERROR')
        {
            file_put_contents('logs/app_logs.log', $row, FILE_APPEND);
        }else
        {
            file_put_contents('logs/game_logs.log', $row, FILE_APPEND);
        }

    }
}


?>