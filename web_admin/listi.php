<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="cache-control" content="no-cache">
</head>
<body>
<div style="display: none;"><iframe name="josm"></iframe></div>
<h2>Lankytinos vietos pasikeitimas</h2>
<table border=1 cellspacing=0>
<tr><th>Lankytinos vietos</th></tr>
<?php
$config = require('./config.php');
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

if (isset($_GET['a_osm_id'])) {
  $a_osm_id = $_GET['a_osm_id'];
  $a_obj_type = $_GET['a_obj_type'];
  $a_change = $_GET['a_change'];
  if (!empty($a_osm_id)) {
    $query = "select accept_change({$a_osm_id}, '{$a_obj_type}', '{$a_change}')";
    /*echo "<p>{$query}</p>";*/
    $res = pg_query($link, $query);
    $err = pg_last_error($link);
    header('Location: http://patrulis.openmap.lt/poi/listi.php');
    /*echo "<p>{$err}</p>";*/
  }
}

if (isset($_GET['t_old_osm_id'])) {
  $t_old_osm_id = $_GET['t_old_osm_id'];
  $t_old_type = $_GET['t_old_type'];
  $t_new_osm_id = $_GET['t_new_osm_id'];
  $t_new_type = $_GET['t_new_type'];
  $t_change = $_GET['t_change'];
  $t_uid = $_GET['t_uid'];
  if (!empty($t_uid)) {
    $query = "select transfer_id({$t_old_osm_id}, '{$t_old_type}', {$t_new_osm_id}, '{$t_new_type}', '{$t_change}', {$t_uid})";
    /*echo "<p>{$query}</p>";*/
    $res = pg_query($link, $query);
    $err = pg_last_error($link);
    echo "<p>{$err}</p>";
  }
}

$query = "select obj_type
                ,osm_id
                ,x_type
                ,name
            from poi_change
           where (tourism is not null and tourism not in ('guest_house', 'hotel', 'motel', 'apartment'))
              or historic is not null
              or amenity in ('place_of_worship', 'theatre', 'cinema')
          order by case when x_type = 'D' then 1 when x_type = 'C' then 2 else 3 end, 2";

$res_m = pg_query($link, $query);
while ($row_m = pg_fetch_assoc($res_m)) {
  if ($row_m['x_type'] == 'D') {
    $q2 = "select name from poif where obj_type = '{$row_m['obj_type']}' and osm_id = {$row_m['osm_id']}";
    $r2 = pg_query($link, $q2);
    while ($row2 = pg_fetch_assoc($r2)) {
      $name = $row2['name'];
    }
  } else {
    $name = $row_m['name'];
  }
  echo "<tr><td><a href=\"poi.php?id={$row_m['obj_type']}{$row_m['osm_id']}&tp={$row_m['x_type']}\">{$row_m['obj_type']} {$row_m['osm_id']} {$row_m['x_type']} {$name}</a></td></tr>\n";
}
echo "</table>\n";
/*echo "<p>{$query}</p>";*/

pg_close($link);
?>
</body>