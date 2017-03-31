<?php

class AccessLimiter {
  const CASE_BYPASS = 'Bypass';
  const CASE_ALLOW = 'Shown';
  const CASE_BLOCK = 'Blocked';

  public $settings;

  /**
   *
   */
  public function __construct($options) {
    $this->settings = array_merge(array(
      'file'       => null,
      'lockFile'   => $_SERVER['SCRIPT_FILENAME'] . '.lock',
      'logFile'    => dirname($_SERVER['SCRIPT_FILENAME']) . '/log.txt',
      'logEnabled' => true,
      'logFormat'  => '%TIME% %IP% > %FILE% [%CASE%] %CLIENT%',
      'maxViews'   => 1,
      'bypassKey'  => 'bypass',
    ), $options);
  }

  /**
   *
   */
  public function isAllowed() {
    $isPassThrough = $this->isPassThrough();
    $views = $isPassThrough ? -1 : $this->getAndUpdateViews();

    if ($isPassThrough) {
      $this->log(self::CASE_BYPASS);
    } else {
      if ($views > $this->settings['maxViews']) {
        $this->log(self::CASE_BLOCK);
      } else {
        $this->log(self::CASE_ALLOW);
      }
    }

    return $views <= $this->settings['maxViews'];
  }

  /**
   *
   */
  private function getAndUpdateViews() {
    $views = 0;
    try {
      $folder = dirname($this->settings['lockFile']);
      if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
      }

      $fileExists = file_exists($this->settings['lockFile']);
      if ($fileExists) {
        $views = file_get_contents($this->settings['lockFile']);
        $views = intval($views);
      }

      $views++;
      file_put_contents($this->settings['lockFile'], "$views\n");

      return $views;
    } catch (Exception $e) {
      return PHP_INT_MAX;
    }
  }

  /**
   *
   */
  private function isPassThrough() {
    return isset($this->settings['bypassKey']) && isset($_GET[$this->settings['bypassKey']]);
  }

  /**
   *
   */
  private function getLogInfo($case) {
    $placeholders = array(
      '%TIME%'   => date('c'),
      '%IP%'     => $this->getClientIp(),
      '%CASE%'   => $case,
      '%FILE%'   => $this->settings['file'] ? $this->settings['file'] : $this->settings['lockFile'],
      '%CLIENT%' => $_SERVER['HTTP_USER_AGENT'],
    );
    $res = $this->settings['logFormat'];
    foreach ($placeholders as $key => $value) {
      $res = str_replace($key, $value, $res);
    }

    return $res;
  }

  /**
   *
   */
  private function log($case) {
    if (!$this->settings['logEnabled']) {
      return;
    }
    try {
      $handle = fopen($this->settings['logFile'], 'a');
      fwrite($handle, $this->getLogInfo($case) . "\n");
      fclose($handle);
    } catch (Exception $e) {}
  }

  /**
  *
  */
  private static function getClientIp() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
      $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
      $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
      $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
      $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
      $ipaddress = getenv('REMOTE_ADDR');
    else
      $ipaddress = 'UNKNOWN';
    return $ipaddress;
  }
}
?>