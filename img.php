<?php
include('./inc/AccessLimiter.php');

$limiter = new AccessLimiter(array(
  'maxViews' => 3,
  'file'     => 'img.jpg',
  'lockFile' => __DIR__ . '/locks/img.jpg.lock',
  'logFile'  => __DIR__ . '/log/log.txt',
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
  outputInternalFile('./inc/img.jpg');
} else {
  echo "Image blocked";
}
?>