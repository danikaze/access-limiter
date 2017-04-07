<?php

function outputInternalFile($path) {
  $handle = fopen($path, 'rb');
  $contents = fread($handle, filesize($path));
  $mime = mime_content_type($path);
  fclose($handle);

  header("content-type: $mime");
  echo $contents;
}

?>