<?php

class AccessLimiter {
  const CASE_BYPASS = 'Bypass';
  const CASE_ALLOW = 'Shown';
  const CASE_BLOCK = 'Blocked';

  private $case;
  public $settings;

  /**
   *
   */
  public function __construct($options) {
    $this->reset($options);
  }

  /**
   *
   */
  public function isAllowed() {
    $done = $this->case;
    $isPassThrough = $this->isPassThrough();
    $views = $this->getAndUpdateViews($this->case || $isPassThrough);

    if ($isPassThrough) {
      $this->case = self::CASE_BYPASS;
    } else {
      if ($views > $this->settings['maxViews']) {
        $this->case = self::CASE_BLOCK;
      } else {
        $this->case = self::CASE_ALLOW;
      }
    }

    if (!$done) {
      $this->log($this->case, $views);
    }
    return $isPassThrough ? true : ($views <= $this->settings['maxViews']);
  }

  /**
   *
   */
  public function getCase() {
    return $this->case;
  }

  /**
   *
   */
  public function sendMail($to, $options) {
    $case = $this->getCase();
    $file = $this->settings['file'] ? $this->settings['file'] : $this->settings['lockFile'];
    $subject = "File accessed ($file > $case)";
    $ip = $this->getClientIp();

    $date = new DateTime('now', new DateTimeZone('GMT'));
    if (isset($options) && isset($options['timezone'])) {
      $date->setTimezone(new DateTimeZone($options['timezone']));
    }
    if (isset($options) && isset($options['timeformat'])) {
      $time = $date->format($options['timeformat']);
    } else {
      $time = $date->format('c');
    }

    $msg = "There was an access to $file from $ip at $time.";
    $msg .= "\nThe result was [$case].";
    $msg .= "\n\nFor more information, check the logs.";

    if (isset($options['logUrl'])) {
      $msg .= "\n" . $options['logUrl'];
    }


    mail($to, $subject, $msg);
  }

  /*
   *
   */
  public function reset($options) {
    $this->case = null;
    $this->settings = array_merge(array(
      'file'       => null,
      'lockFile'   => $_SERVER['SCRIPT_FILENAME'] . '.lock',
      'logFile'    => dirname($_SERVER['SCRIPT_FILENAME']) . '/log.txt',
      'logEnabled' => true,
      'logFormat'  => '%TIME% %IP% > %FILE% %VIEWS% [%CASE%] %CLIENT%',
      'maxViews'   => 1,
      'bypassKey'  => null,
    ), $options);
  }

  /**
   *
   */
  private function getAndUpdateViews($dontUpdate) {
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

      if (!$dontUpdate) {
        $views++;
        file_put_contents($this->settings['lockFile'], "$views\n");
      }

      return $views;
    } catch (Exception $e) {
      return PHP_INT_MAX;
    }
  }

  /**
   *
   */
  private function isPassThrough() {
    return isset($this->settings['bypassKey'])
      && $this->settings['bypassKey'] != null
      && isset($_GET[$this->settings['bypassKey']]);
  }

  /**
   *
   */
  private function getLogInfo($case, $views) {
    $date = new DateTime('now', new DateTimeZone('GMT'));
    $placeholders = array(
      '%TIME%'   => $date->format('c'),
      '%IP%'     => $this->getClientIp(),
      '%CASE%'   => $case,
      '%VIEWS%'  => $views,
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
  private function log($case, $views) {
    if (!$this->settings['logEnabled']) {
      return;
    }
    try {
      $handle = fopen($this->settings['logFile'], 'a');
      fwrite($handle, $this->getLogInfo($case, $views) . "\n");
      fclose($handle);
    } catch (Exception $e) {}
  }

  /**
   *
   */
  public static function getClientIp() {
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