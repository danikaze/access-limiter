<?php
include('./inc/AccessLimiter.php');
include('./inc/outputFile.php');

$limiter = new AccessLimiter(array(
  'maxViews'  => 3,
  'file'      => 'img.jpg',
  'lockFile'  => __DIR__ . '/locks/img.jpg.lock',
  'logFile'   => __DIR__ . '/log/log.txt',
  'bypassKey' => 'bypass',
));

if($limiter->isAllowed()) {
  outputInternalFile('./inc/img.jpg');
} else {
  echo "Image blocked";
}

// send an email regardless the status in each access
$limiter->sendMail('watcher@mail.com', array(
  'timezone'   => 'Asia/Tokyo',
  'timeformat' => 'Y-m-d H:i:s',
  'logUrl'     => 'http://url.com/log/',
));

?>