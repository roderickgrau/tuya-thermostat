<?php
#$json1='{"id":"xxxxxx","value":"50"}';
#$post = json_decode($json1, true);

if ( $_SERVER['HTTP_REFERER'] != "https://xx.xx.xx/thermostat.php" ) {
  echo "<html><body><br></body></html>\n";
  exit;
}

$post = json_decode(file_get_contents('php://input'), true);

if ( $post["id"] == "" ) {
  echo "<html><body><br></body></html>\n";
} else {

  if ( $post["command"] == "set" ) {
    $json = json_encode($post);
    $command = "python3 /thermostat/setTemp.py set " . $post["id"] . " " . $post["dps"] . " " . $post["value"] ;
  } elseif ( $post["command"] == "get" ) {
    $json = json_encode($post);
    $command = "python3 /thermostat/setTemp.py get " . $post["id"] . " " . $post["dps"] ;
  } else {
    echo "<html><body>Wrong command</body></html>\n";
    exit;
  }

  $output = shell_exec($command);

  echo $output;
  #echo $json;
}

?>
