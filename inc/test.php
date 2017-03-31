<?php
include('./AccessLimiter.php');

$limiter = new AccessLimiter(array(
  'maxViews' => 3,
  'file'     => 'test.html',
  'lockFile' => '../locks/test.html.lock',
  'logFile'  => '../log/log.txt',
));

if($limiter->isAllowed()) {
  echo "Allowed";
} else {
  echo "Blocked";
}
?>