<html>

<?php
$remote = $_SERVER['REMOTE_ADDR'];
$DISABLED = "disabled";
$ALLOWED = array("192.168.1.1", "192.168.0.1");

if ( ! in_array($remote, $ALLOWED) ) {
  print "Your IP [$remote] is not allowed.";
  exit;
}

?>

<head><link rel='stylesheet' href='thermostat.css'></head>
<body>

<script language="JavaScript" type="text/javascript">
var devices = [];
var secs;
var delay = 1000;
var TimerTxt;

function InitTimer() {

  TimerTxt = document.getElementById("timer");

  if ( TimerTxt ) {
    secs = TimerTxt.innerHTML;
    StartTimer();
  }
}

function StartTimer(){
  if ( secs == 0 ) {
    devices.forEach( function(id) {
      getStatus(id,29); 
      getStatus(id,17);
      getStatus(id,3);
    });
    
    TimerTxt.innerHTML = 60;
    InitTimer();

  } else {
    secs = secs -1;
    TimerTxt.innerHTML = secs;
  
    self.setTimeout("StartTimer()", delay);
  } 

}

function change(id, action) {
  const min=45;
  const max=70;
  let changed = 0;

  setTemp = document.getElementById(id+':setTemp').value;

  if ( setTemp > min && action == 'min' ) {
    document.getElementById(id+':setTemp').value = min;
    changed = 1;
  }

  if ( setTemp < max && action == 'max' ) {
    document.getElementById(id+':setTemp').value = max;
    changed = 1;
  }

  if ( setTemp > min && action == 'dec' ) {
    document.getElementById(id+':setTemp').value --;
    changed = 1;
  }

  if ( setTemp < max && action == 'inc' ) {
    document.getElementById(id+':setTemp').value ++;
    changed = 1;
  }

  if ( action == '50' ) {
    document.getElementById(id+':setTemp').value = action;
    changed = 1;
  }

  if ( action == '60' ) {
    document.getElementById(id+':setTemp').value = action;
    changed = 1;
  }

  if ( action == '70' ) {
    document.getElementById(id+':setTemp').value = action;
    changed = 1;
  }

  if ( changed == 1 ) {
    submitChange(id, document.getElementById(id+':setTemp').value); 
  }
  
}

function submitChange(id, value ) {
  newTemp = {};
  newTemp['command'] = 'set';
  newTemp['id'] = id;
  newTemp['dps'] = '17';
  newTemp['value'] = value;

  fetch('https://xx.xx.xx/thermostat2.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(newTemp)
  })
  .then( (response) => response.json() )
  .then( (json) => {
    console.log(json)
    d = document.getElementById("alert")
    d.innerHTML = JSON.stringify(json)
    d.style = "display: true"
    setTimeout(function(){d.style = "display: none" }, 3000);

  });
}

function getStatus(id, dps ) {
  newStatus = {};
  newStatus['command'] = 'get';
  newStatus['id'] = id;
  newStatus['dps'] = dps;

  fetch('https://xx.xx.xx/thermostat2.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(newStatus)
  })
  .then( (response) => response.json() )
  .then( (json) => {
    //console.log(id, json)

    if ( dps == 29 ) {
      document.getElementById(id).innerHTML = json['value'];
    }

    else if ( dps == 17 ) {
      document.getElementById(id+':setTemp').value = json['value'];
    }

    else if ( dps == 3 ) {
      document.getElementById(id+':status').innerHTML = "Status:" + json['value'];
      if ( json['value'] == "Heat" ){
        document.getElementById(id+':status').style.backgroundColor = "red";
        document.getElementById(id+':status').style.color = "white";
      } else {
        document.getElementById(id+':status').style.backgroundColor = "white";
        document.getElementById(id+':status').style.color = "black";
      }
    }

  });
}

</script>

<div id="alert" class="alert" style="display: none" >
This is an alert box.
</div>

<span id='timer'>60</span>
<span id='thermostats'></span>
<script>InitTimer();</script>

<div class="bigBox">
<?php

$filename='/var/www/html/weather/data.json';

if( file_exists($filename) ) {

  $data = file_get_contents($filename);
  $decoded = json_decode($data, true);

  print('<div class="container" id="weather">');
  foreach ( $decoded['properties']['periods'] as $k => $v ) {
    print('<img src="' . $v['icon'] . '"><br>');
    print($v['name'] . '<br>');
    print('Temp: ' . $v['temperature'] . '<br>');
    print('Wind: ' . $v['windSpeed'] . '<br>');
    print($v['shortForecast'] . '<br>');
    #print($v['detailedForecast'] . '<br>');
    print('<a href="/weather/weather.php">Weekly Weather</a>');
    print('</div>');

   break;
 
  }
}

$devicesFile = '/thermostat/devices.json';
$devices = json_decode( file_get_contents($devicesFile),true );
$count=0;

foreach($devices as $device){
  $name = $device['name'];
  $id = $device['id'];

  $currentTemp = json_decode(shell_exec("python3 /thermostat/setTemp.py get " . $id . " 29"), true);
  $setTemp = json_decode(shell_exec("python3 /thermostat/setTemp.py get " . $id . " 17" ), true);
  $status = json_decode(shell_exec("python3 /thermostat/setTemp.py get " . $id . " 3" ), true);

  print"<script>devices[".$count."] = '".$id."'</script>\n";
  print"<div class='box'>\n";
  print"<h1>".$name."</h1>\n";

  print"<p><div class='container'>Current Temp:\n";
  print"<div id='".$id."' class='currentTemp'>". $currentTemp['value'] ."</div>\n";
  print"</div></p>\n";

  print"<p><div class='container'>Set Temp:<br>\n";
  print"<button title='Set temp to min' class='setTemp' id='decrement' onclick=\"change('".$id."', 'min')\">&lt;</button>\n";
  print"<button title='Decrease temp' class='setTemp' id='decrement' onclick=\"change('".$id."', 'dec')\">-</button>\n";
  print"<input class='setTemp' id='".$id.":setTemp' value='".$setTemp['value']."' id='".$id."' readonly>\n";
  print"<button title='Increase temp' class='setTemp' id='increment' onclick=\"change('".$id."', 'inc')\">+</button>\n";
  print"<button title='Set temp to max' class='setTemp' id='increment' onclick=\"change('".$id."', 'max')\">&gt;</button>\n";
  print"<br><div id='".$id.":status'>Status: ".$status['value']."</div>\n";
  print"</div></p>\n";
  print"<button title='set temp to 50' class='setTemp' id='set' onclick=\"change('".$id."', '50')\">50</button>\n";
  print"<button title='set temp to 60' class='setTemp' id='set' onclick=\"change('".$id."', '60')\">60</button>\n";
  print"<button title='set temp to 70' class='setTemp' id='set' onclick=\"change('".$id."', '70')\">70</button>\n";
  print"</div>\n";
  $count++;

}
?>

</div>
</body></html>
