<?php
require_once 'Google/autoload.php';

$CLIENT_ID = '24411176895-gt1plgkpunja7lnsfghlgpabfngc0alk.apps.googleusercontent.com';
$client = new Google_Client(['client_id' => $CLIENT_ID]);
$client->setAuthConfigFile('/var/www/secret.json');
$payload = $client->verifyIdToken($_POST['idtoken']);
if ($payload) {
  $token = $payload->getAttributes()['payload']['sub'];
  echo $payload->getAttributes()['payload']['sub'];

  $config = require './config.php';
  $link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

  $query = "select count(1) as c from kol_naudotojai where token = '$token'";
  $res = pg_query($link, $query);
  $row = pg_fetch_assoc($res);
  if ($row['c'] == 0) {
    $query = "insert into kol_naudotojai (id, token, first_connection) values (nextval('seq_kol_naudotojai'), '$token', current_timestamp)";
    $res = pg_query($link, $query);
  }
  $query = "update kol_naudotojai set last_connection = current_timestamp where token = '$token'";
  $res = pg_query($link, $query);
} else {
  // Invalid ID token
  echo "ojoj";
}
?>
