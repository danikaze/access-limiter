<?php
include('./inc/AccessLimiter.php');

$limiter = new AccessLimiter(array(
  'maxViews' => 3,
  'file'     => 'test.html',
  'lockFile' => __DIR__ . '/locks/test.html.lock',
  'logFile'  => __DIR__ . '/log/log.txt',
));

if($limiter->isAllowed()) {
  echo "Allowed";
} else {
  echo "Blocked";
}
?>