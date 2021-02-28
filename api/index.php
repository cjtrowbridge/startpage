<?php

header("Content-type:application/json");

function hotspotData(){
  $General = file_get_contents('http://192.168.1.1/cgi-bin/general_monitor.cgi');
  $Extended = shell_exec("curl 'http://192.168.1.1/cgi-bin/home.index.cgi' \
  -H 'Connection: keep-alive' \
  -H 'Accept: application/json, text/javascript, */*; q=0.01' \
  -H 'X-Requested-With: XMLHttpRequest' \
  -H 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.141 Safari/537.36' \
  -H 'Content-Type: application/json; charset=UTF-8' \
  -H 'Origin: http://192.168.1.1' \
  -H 'Referer: http://192.168.1.1/' \
  -H 'Accept-Language: en-US,en;q=0.9' \
  -H 'Cookie: frkrouter=3d2fb48bfa8e2ab539dd69f7e1a5cd15' \
  --data-binary '{\"command\":\"load\",\"params\":null}' \
  --compressed \
  --insecure");
  return array(
    'General'  => json_decode($General,true),
    'Extended' => json_decode($Extended,true)
  );
}

function downlinkSpeed($Duration){
  $First = hotspotData();
  sleep($Duration);
  $Second = hotspotData();
  if(!(strpos($First['Extended']['data']['size_used']," MB") === false)){
    $Downloaded = trim($Second['Extended']['data']['size_used'], " MB") - trim($First['Extended']['data']['size_used'], " MB");
  }else{
    $Downloaded = trim($Second['Extended']['data']['size_used'], " GB") - trim($First['Extended']['data']['size_used'], " GB");
    $Downloaded = $Downloaded * 1000;
  }
  $Speed = $Downloaded / 10;
  if($Speed > 1){
    $Speed = round($Speed*100)/100;
    return $Speed.' mb/sec';
  }else{
    $Speed = round($Speed*100)/100;
    $Speed = ($Speed*1000);
    return $Speed.' kb/sec';
  }

}

if(isset($_GET['hotspotSpeed'])){
  $Duration = intval($_GET['hotspotSpeed']);
  if($Duration == 0){
    $Duration = 10;
  }
  echo downlinkSpeed($Duration);
  exit;
}

if(isset($_GET['hotspot'])){
  echo json_encode(hotspotData(), JSON_PRETTY_PRINT);
  exit;
}

if(isset($_GET['device'])){
  $DF  = intval(shell_exec("df | grep -oP '/root.* \K\d+(?=%)'"));
  $RAM = shell_exec('free -m');
  $RAM = explode(PHP_EOL, $RAM);
  $RAM = $RAM[1];
  $RAM = preg_replace('/\s+/', ' ', $RAM);
  $RAM = explode(' ',$RAM);
  $RAM = array(
    'Total' => $RAM[1],
    'Free'  => $RAM[3]
  );
  $RAM = round($RAM['Free'] / $RAM['Total']*100);
  die(json_encode(array(
    'SSD Free'     => $DF.'%',
    'RAM Free'     => $RAM.'%',
    'Uplink Speed' => downlinkSpeed(5)
  ),true));
}


if(isset($_GET['scene'])){
  include_once('webhooks.php');
  if(isset($Webhooks[$_GET['scene']])){
    echo file_get_contents($Webhooks[$_GET['scene']]);
  }
  exit;
}

if(isset($_GET['getSurveillanceEvents'])){
  header('Content-Type: application/json');
  $Data = getSurveillanceEvents();
  $JSON = json_encode($Data, JSON_PRETTY_PRINT);
  echo $JSON;
  exit;
}

if(isset($_GET['checkConnectivity'])){
  $Result = pingDevices($_GET['checkConnectivity']);
  die(json_encode($Result));
}
if(isset($_GET['update'])){
  die(shell_exec('cd /var/www/ && git reset --hard && git pull'));
}

function pingDevices($Device){
  switch($Device){
    case 'FiHotspot':
      return array('FiHotspot' => ping('192.168.1.1'));
    case 'Internet':
      return array('Internet' => ping('8.8.8.8'));
    case 'Router':
      return array('Router'   => ping('192.168.0.1'));
    case 'NAS':
      return array('NAS'      => ping('192.168.0.2'));
    case 'Server':
      return array('Server'   => ping('192.168.0.3'));
    case 'Bridge':
      return array('Bridge'   => ping('192.168.1.2'));
    case 'Inside':
      return array('Inside'   => ping('192.168.0.10'));
    case 'Outside':
      return array('Outside'  => ping('192.168.0.11'));
  }
}
function ping($ip){
  $pingresult = exec("/bin/ping -n -c1 -W1 $ip", $outcome, $status);
  if(0 == $status){
    return true;
  }else{
    return false;
  }
}
function getSurveillanceEvents(){
  $Events = array();
  $Path = '../surveillance/';
  if ($handle = opendir($Path)) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != "..") {
        $Events[filemtime($Path.$file)] = '/surveillance/'.$file;
      }
    }
    closedir($handle);
    ksort($Events);
  }
  $Ret = array();
  foreach($Events as $Time => $Event){
    $Age = time() - $Time;
    $Size = filesize('..'.$Event)/1000000;
    $Size = round($Size,3);
    $Ret[$Age] = array(
      'Ago'  => ago($Time),
      'Time' => date('Y-m-d H:i:s',$Time),
      'Link' => $Event,
      'Size' => $Size
    );
  }
  ksort($Ret);
  $Ret = array_slice($Ret,0,5);
  return $Ret;
}

function ago($time){
  /*
    Ago accepts any date or time and returns a string explaining how long ago that was.
    For example, "6 days ago" or "8 seconds ago"
  */
  if(intval($time)==0){
    $time=strtotime($time);
  }
  if(($time==0)||(empty($time))){
    return 'Never';
  }
  $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
  $lengths = array("60","60","24","7","4.35","12","10");
  $now = time();
  $difference     = $now - $time;
  $tense         = "ago";
  for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
    $difference /= $lengths[$j];
  }
  $difference = round($difference);
  if($difference != 1) {
    $periods[$j].= "s";
  }
  return "$difference $periods[$j] ago";
}

if(isset($_GET['FiModem'])){
  switch($_GET['FiModem']){
    case 'connect':
      $url = 'http://192.168.1.1/cgi-bin/general_monitor.cgi';
      $options = array(
        'http' => array(
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST',
          'content' => http_build_query(array('command' => 'connect', 'params' => null))
        )
      );
      $context  = stream_context_create($options);
      $result = file_get_contents($url, false, $context);
      var_dump($result);
      break;
    case 'disconnect':
      $url = 'http://192.168.1.1/cgi-bin/general_monitor.cgi';
      $options = array(
        'http' => array(
          'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
          'method'  => 'POST',
          'content' => http_build_query(array('command' => 'disconnect', 'params' => null))
        )
      );
      $context  = stream_context_create($options);
      $result = file_get_contents($url, false, $context);
      var_dump($result);
      break;
  }

  $Data = file_get_contents('http://192.168.1.1/cgi-bin/general_monitor.cgi');
  echo $Data;
  exit;
}
