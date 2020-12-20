<?php

header("Content-type:application/json");

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
if(isset($_GET['checkMainPower'])){
  $Result = checkMainPower();
  die(json_encode($Result));
}
if(isset($_GET['checkBackupPower'])){
  $Result = checkBackupPower();
  die(json_encode($Result));
}
if(isset($_GET['checkEnginePower'])){
  $Result = checkEnginePower();
  die(json_encode($Result));
}
if(isset($_GET['checkShorePower'])){
  $Result = checkShorePower();
  die(json_encode($Result));
}
if(isset($_GET['update'])){
  die(shell_exec('cd /var/www/ && git reset --hard && git pull'));
}

function pingDevices($Device){
  switch($Device){
    case 'Internet':
      return array('Internet' => ping('8.8.8.8'));
    case 'Router':
      return array('Router'   => ping('192.168.0.1'));
    case 'NAS':
      return array('NAS'      => ping('192.168.0.2'));
    case 'Server':
      return array('Server'   => ping('192.168.0.3'));
    case 'Kali':
      return array('Kali'     => ping('192.168.0.4'));
    
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
function checkMainPower(){
  return array(
    'State'   => 'Online',
    'Percent' => '75'
  );
}
function checkBackupPower(){
  return array(
    'State'   => 'Online',
    'Percent' => '100'
  );
}
function checkEnginePower(){
  return array(
    'State' => true
  );
}
function checkShorePower(){
  return array(
    'State' => false
  );
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
