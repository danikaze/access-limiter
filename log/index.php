<?php
const FILE_PATH = './log.txt';
const CLEAR_LOG_PARAM = 'clearLog';
const LOCKS_PATH = '../locks';
const CLEAR_LOCKS_PARAM = 'clearLocks';
$LOG_FIELDS = array('time', 'ip', 'file', 'views', 'case', 'client');
$FIELD_VIEWER = array(
  'time'   => 'showTime',
  'ip'     => 'showIp',
);

/*
 *
 */
function clearLocks() {
  $files = glob(LOCKS_PATH . '/*');
  foreach ($files as $file) {
    if (is_file($file)) {
      unlink($file);
    }
  }
}

/**
 *
 */
function clearLog() {
  $f = @fopen(FILE_PATH, "r+");
  if ($f !== false) {
    ftruncate($f, 0);
    fclose($f);
  }
}

/**
 *
 */
function parseFile($path) {
  global $LOG_FIELDS;
  $data = array();
  try {
    if (file_exists($path)) {
      $entries = file($path);
      $pattern = '/([^ ]+) ([^ ]+) > ([^ ]+) (-?[0-9]+) \[([^ ]+)\] (.+)/';
      foreach ($entries as $entry) {
        preg_match($pattern, $entry, $matches);
        $row = array();
        foreach ($LOG_FIELDS as $i => $field) {
          $row[$field] = $matches[$i + 1];
        }
        $data[] = $row;
      }
    }
  } catch (Exception $e) {}

  return $data;
}

/**
 *
 */
function showTime($txt) {
  $date = new DateTime($txt);
  $date->setTimezone(new DateTimeZone('Asia/Tokyo'));
  return $date->format('Y-m-d H:i:s');
}

/**
 *
 */
function showIp($txt) {
  return '<a href="http://ip-api.com/#' . $txt . '">' . $txt . '</a>';
}

if (isset($_GET[CLEAR_LOCKS_PARAM])) {
  clearLocks();
}
if (isset($_GET[CLEAR_LOG_PARAM])) {
  clearLog();
}

$data = parseFile(FILE_PATH);

?><!DOCTYPE html>
<html lang="ja">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

    <title>Log Viewer</title>
    <style>
      table {
        border-collapse: collapse;
        text-align: center;
        table-layout: fixed;
        white-space: nowrap;
        border: 1px solid #757575;
        border-right: 0;
        font-size: smaller;
      }
      th, td { border-right: 1px solid #757575; padding: 0 1em; }
      th { background: #efefef; border-bottom: 1px solid #757575; }
      .tr0 { background: #f3f3f3; }
      .tr1 { background: #dedede; }
      .tr0.case-blocked { background: #ffdcdc; }
      .tr1.case-blocked { background: #ffcbcb; }
      .tr0.case-shown { background: #e0f3e3; }
      .tr1.case-shown { background: #bee6c4; }
      .tr0.case-bypass { background: #e4ecf9; }
      .tr1.case-bypass { background: #d0ddf1; }
    </style>
    <script>
      history.pushState(null, null, location.pathname);
    </script>
</head>
<body>
  <p><b>Total:</b> <?php echo count($data); ?> entries</p>
  <p><a href='?<?php echo CLEAR_LOG_PARAM; ?>'>Clear logs</a></p>
  <p><a href='?<?php echo CLEAR_LOCKS_PARAM; ?>'>Clear all file locks</a></p>
  <table>
    <thead>
      <tr>
        <?php
          echo "<th>#</th>";
          foreach ($LOG_FIELDS as $fieldName) {
            echo "<th>$fieldName</td>";
          }
        ?>
      </tr>
    </thead>
    <tbody>
    <?php
      foreach ($data as $i => $entry) {
        echo '<tr class="tr' . ($i%2) . ' case-' . strtolower($entry['case']) . '">';
        echo '<td>' . ($i + 1) . '</td>';
        foreach ($LOG_FIELDS as $fieldName) {
          if (isset($FIELD_VIEWER[$fieldName])) {
            $contents = $FIELD_VIEWER[$fieldName]($entry[$fieldName]);
          } else {
            $contents = $entry[$fieldName];
          }
          echo '<td class="' . $fieldName . '">' . $contents . '</td>';
        }
        echo '</tr>';
      }
    ?>
    </tbody>
  </table>
</body>
</html>