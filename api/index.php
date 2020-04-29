<?php

if(isset($_GET['checkInternet'])){
  $Result = pingIP('8.8.8.8');
  die(json_encode($Result));
}

function pingIP($ip) {
  $pingresult = exec("/bin/ping -n 3 $ip", $outcome, $status);
  if (0 == $status) {
    return true;
  } else {
    return false;
  }
}
