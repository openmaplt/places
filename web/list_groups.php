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

/*************************************************************
 ************************************************************/
function fetch_group($token) {
  global $link;

  debug('Start');
  $query = "select grupes from kol_naudotojai where token = '{$token}'";
  $res = pg_query($link, $query);
  if (!$res) {
    debug(pg_last_error($link));
    throw new Exception(pg_last_error($link));
    exit;
  }

  $grupes = '---';
  while ($row = pg_fetch_assoc($res)) {
    $grupes = $row['grupes'];
  }
  debug('Grupes=' . $grupes);
  if ($grupes == '---') {
    $query = "insert into kol_naudotojai values (null, '{$token}', null)";
    $res = pg_query($link, $query);
    $grupes == '';
  }

  echo "<table class=\"list\">";
  $query = "select pavadinimas, kodas, image from kol_grupes order by sort";
  $res = pg_query($link, $query);
  while ($row = pg_fetch_assoc($res)) {
    $pos = strpos($grupes, $row['kodas']);
    if ($pos === false) {
      echo "<tr class=\"item\"><td><img src=\"/img/{$row['image']}\"></td><td>{$row['pavadinimas']}</td><td><a href=\"#\" onClick=\"groupOn('{$row['kodas']}')\">Įjungti</a></td></tr>";
    } else {
      echo "<tr class=\"item\"><td><img src=\"/img/{$row['image']}\"></td><td><b>{$row['pavadinimas']}</b></td><td><a href=\"#\" onClick=\"groupOff('{$row['kodas']}')\">Įšjungti</a></td></tr>";
    }
  }
  echo "</table>";
} // fetch_poi

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

// Check the bounding box
$token = $_GET['t'];

debug("token=" . $token);

$config = require './config.php';
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

if (!$link) {
  debug('Cannot connect to database');
  die;
}

if (isset($_GET['on'])) {
  debug('on=' . $_GET['on']);
  debug('query=' . "update kol_naudotojai set grupes = coalesce(grupes, '') || '{$_GET['on']}' where token = '{$token}'");
  $query = "update kol_naudotojai set grupes = coalesce(grupes, '') || '{$_GET['on']}' where token = '{$token}'";
  $res = pg_query($link, $query);
} else if (isset($_GET['off'])) {
  debug('off=' . $_GET['off']);
  $query = "update kol_naudotojai set grupes = translate(grupes, '{$_GET['off']}', '') where token = '{$token}'";
  $res = pg_query($link, $query);
}
fetch_group($token);

pg_close($link);
