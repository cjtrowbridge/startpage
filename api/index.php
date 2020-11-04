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
      return array('Router' => ping('192.168.86.1'));
    case 'NAS':
      return array('NAS' => ping('192.168.86.2'));
    
    case 'Surveillance':
      return array('Surveillance' => ping('192.168.86.4'));
    case 'Bridge':
      return array('Bridge' => ping('192.168.86.5'));
    case 'Server':
      return array('Server' => ping('192.168.86.6'));
    case 'Mycroft':
      return array('Mycroft' => ping('192.168.86.7'));
    case 'Kali':
      return array('Kali' => ping('192.168.86.8'));
    case 'Laptop':
      return array('Laptop' => ping('192.168.86.9'));
    
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
  if ($handle = opendir('../surveillance')) {
    while (false !== ($file = readdir($handle))) {
      if ($file != "." && $file != "..") {
        $Events[filemtime($file)] = '/surveillance/'.$file;
      }
    }
    closedir($handle);
    // sort
    ksort($Events);
  }
  return $Events;
}
