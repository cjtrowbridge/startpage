<?php

switch($_GET['command']){
  case 'checkInternet':
    $Result = pingIP('8.8.8.8');
    die(json_encode($Result));
    break;
}

function pingIP($ip) {
  $pingresult = exec("/bin/ping -n 3 $ip", $outcome, $status);
  if (0 == $status) {
    return true
  } else {
    return false
  }
}
