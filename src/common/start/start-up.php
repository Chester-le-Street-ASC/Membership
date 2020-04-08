<?php

define('CRON_PROCESS', true);

require '../common.php';

$db = null;
try {
  $db = new PDO("mysql:host=" . env('DB_HOST') . ";dbname=" . env('DB_NAME') . ";charset=utf8mb4", env('DB_USER'), env('DB_PASS'));
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
  echo "\r\nUnable to connect to SQL database.";
  echo "\r\nQuitting...\r\n\r\n";
  exit();
}

/**
 * halt to stop execution
 *
 * @param integer $statusCode http response code
 * @param boolean $throwException throw an exception to be handled later
 * @return void
 */
function halt(int $statusCode, bool $throwException = true) {
  if ($statusCode == 500) {
    // Report the error
  }
  
  if ($throwException) {
    throw new \SCDS\HaltException('Status ' . $statusCode);
  }
}

include BASE_PATH . 'database.php';