<?php

header("Content-type:application/json");

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

function pingDevices(){
  switch($Device){
      case 'Internet';
        return array('Internet' => ping('8.8.8.8'));
        break;
      case 'Router';
        return array('Router' => ping('192.168.86.1'));
        break;
      case 'NAS';
        return array('NAS' => ping('192.168.86.2'));
        break;
      case 'Server';
        return array('Server' => ping('192.168.86.3'));
        break;
      case 'Surveillance';
        return array('Surveillance' => ping('192.168.86.4'));
        break;
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
