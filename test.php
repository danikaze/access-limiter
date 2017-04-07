<?php
include('./inc/AccessLimiter.php');

$limiter = new AccessLimiter(array(
  'maxViews'  => 3,
  'file'      => 'test.html',
  'lockFile'  => __DIR__ . '/locks/test.html.lock',
  'logFile'   => __DIR__ . '/log/log.txt',
  'bypassKey' => 'bypass',
));

if ($limiter->isAllowed()) {
  echo "Allowed";
} else {
  echo "Blocked";
}

// send an email regardless the status in each access
$limiter->sendMail('watcher@mail.com', array(
  'timezone'   => 'Asia/Tokyo',
  'timeformat' => 'Y-m-d H:i:s',
  'logUrl'     => 'http://url.com/log/',
));

if (!$limiter->isAllowed()) {
  exit;
}
?>

This content should only be shown 3 times unless it's bypassed using the admin key (appending ?bypass to the url).