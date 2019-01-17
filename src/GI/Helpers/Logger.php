<?php

/**
 *  Project: Generic Importer
 *  Contains the logger class.
 *  @since 0.0.1
 */

namespace GI\Helpers;

use Monolog\Logger as MonoLogger;
use Monolog\Handler\StreamHandler;

/**
 * Description of Logger
 *
 * @author Electric Studio
 */
class Logger {
    private $log;
    private $log_name = 'import_log';
    private $log_filename;
    private $log_levels;
    private $extra_data = array();
    
    const DEBUG = 'debug';
    const INFO = 'info';
    const NOTICE = 'notice';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';
    const ALERT = 'alert';
    const EMERGENCY = 'emergency';

    public function __construct( $log_location ) {
        // create a log channel
        $this->log = new MonoLogger( $this->log_name );
        $this->log->pushHandler(new StreamHandler( $log_location ));
        $this->log_levels = array(
          self::DEBUG,
          self::INFO,
          self::NOTICE,
          self::WARNING,
          self::ERROR,
          self::CRITICAL,
          self::ALERT,
          self::EMERGENCY,
        );
    }
    
    /**
     * Adds a message to the log.
     * 
     * @since 0.0.1
     * @param string $level   Use one of the constants.
     * @param string $message Message to be logged.
     * @param type $data      Any extra data to include.
     */
    public function add_log_message( $level, $message, $data = [] ) {
        if ( ! in_array( $level, $this->log_levels ) ) {
          $level = self::DEBUG;
          $message = $message . ' Originally sent at level ' . $level;
        }
        // add records to the log
        $this->log->$level( $message, $data );
    }
    
}
