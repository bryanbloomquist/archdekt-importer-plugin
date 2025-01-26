<?php

namespace ArchidektImporter\Includes\DebugLogger;

/**
 * Class Logger
 * This class provides a way to write some debug logs
 */
class Logger
{
  private static $log_file = 'Includes/DebugLogger/logger.log';

  /**
   * Write a log message to the log file
   * @param string $type    the type of message
   * @param string $message the message to be logged
   */
  public static function write($type, $message)
  {
    $file_path = ADI_PLUGIN_PATH . self::$log_file;
    $file = fopen($file_path, "a");
    if ($file === false) {
      error_log("Unable to open log file: " . $file_path);
      return;
    }
    if (is_array($message)) {
      $message = json_encode($message);
    }
    fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: [" . $type . "] " . $message);
    fclose($file);
  }
}
