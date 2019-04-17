<?php
/***************************************************************
 * Print out debug information. This print out something only
 * if called with a parameter debug=yes
 ***************************************************************/
function debug($txt) {
  if (DEBUG) {
    echo $txt, PHP_EOL;
  }
} // debug

//***********************************************************************
// Main routine
//***********************************************************************
// respond to preflights
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  // return only the headers and not the content
  // only allow CORS if we're doing a GET - i.e. no saving for now.
  if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']) &&
            $_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] == 'GET') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: X-Requested-With');
  }
  exit;
} else { // simple scenario without preflight
  header('Access-Control-Allow-Origin: *');
}

//***********************************************************************
// Check if debugging is on/off
// When debugging is on, prior to geojson information you will get
// a number of "human readable" debug messages
define(DEBUG, ($_GET['debug'] == 'yes'));
if (DEBUG) {
  error_reporting(E_ALL ^ E_NOTICE);
  echo '<pre>';
} else {
  error_reporting(0);
}

$token = $_GET['t'];
debug("token=" . $token);
$uid = $_GET['uid'];
debug("uid=" . $uid);

$config = require './config.php';
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

if (!$link) {
  debug('Cannot connect to database');
  die;
}

if (isset($_GET['op'])) {
  $op = $_GET['op'];
  debug('op=' . $op);
  $query = "select kol_update_status({$uid}, '{$token}', '{$op}');";
  debug('query=' . $query);
  $res = pg_query($link, $query);
}

$query = "select status from kol_lankymas where token = '{$token}' and uid = '{$uid}'";
debug('query=' . $query);
$res = pg_query($link, $query);
$status = 'n';
while ($row = pg_fetch_assoc($res)) {
  $status = $row['status'];
}

debug('status=' . $status);
if ($status == 's') {
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'n\');" class="meniu">Nematyta</a> ';
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'s\');" class="meniua"><b>Pamatyta</b></a> ';
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'b\');" class="meniu">Neįdomi</a>';
} elseif ($status == 'b') {
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'n\');" class="meniu">Nematyta</a> ';
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'s\');" class="meniu">Pamatyta</a> ';
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'b\');" class="meniua"><b>Neįdomi</b></a>';
} else {
  debug('nematyta');
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'n\');" class="meniua"><b>Nematyta</b></a> ';
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'s\');" class="meniu">Pamatyta</a> ';
  echo '<a href="#" onClick="changeStatus(' . $uid . ', \'b\');" class="meniu">Neįdomi</a>';
}

pg_close($link);
