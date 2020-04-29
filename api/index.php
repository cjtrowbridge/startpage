<?php

header("Content-type:application/json");

if(isset($_GET['checkConnectivity'])){
  $Result = pingDevices();
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
 return array(
   'Internet'     => pingIP('8.8.8.8'),
   'Router'       => pingIP('192.168.86.1'),
   'NAS'          => pingIP('192.168.86.2'),
   'Server'       => pingIP('192.168.86.3'),
   'Surveillance' => pingIP('192.168.86.4')
 );
}
function pingIP($ip){
  $pingresult = exec("/bin/ping -n -W 1 $ip", $outcome, $status);
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
