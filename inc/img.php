<?php
include('./AccessLimiter.php');

$limiter = new AccessLimiter(array(
  'maxViews' => 3,
  'file'     => 'img.jpg',
  'lockFile' => '../locks/img.jpg.lock',
  'logFile'  => '../log/log.txt',
));

function outputInternalFile($path) {
  $handle = fopen($path, 'rb');
  $contents = fread($handle, filesize($path));
  $mime = mime_content_type($path);
  fclose($handle);

  header("content-type: $mime");
  echo $contents;
}

if($limiter->isAllowed()) {
  //outputInternalFile('./img.jpg');
  echo "!";
} else {
  echo "Image blocked";
}
?>