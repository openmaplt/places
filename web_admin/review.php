<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="cache-control" content="no-cache">
</head>
<body>
<div style="display: none;"><iframe name="josm"></iframe></div>
<h2>Lankytinų vietų peržiūra</h2>
<table border=1 cellspacing=0>
<tr><th>Lankytina vieta</th><th>Peržiūrų</th><th>Paskutinė peržiūra</th><th>Veiksmas</th></tr>
<?php
$config = require('./config.php');
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

if (isset($_GET['reviewed'])) {
    $query = "update poif set visit_count_reviewed = visit_count
                             ,visit_count_last_review = now()
              where uid = {$_GET['reviewed']}";
    echo "<p>{$query}</p>";
    $res = pg_query($link, $query);
    /*$err = pg_last_error($link);*/
    header('Location: http://patrulis.openmap.lt/poi/review.php');
    /*echo "<p>{$err}</p>";*/
}

$query = "select uid
                ,coalesce(name, '---') as name
                ,visit_count as total
                ,visit_count - coalesce(visit_count_reviewed, 0) as new
                ,to_char(visit_count_last_review, 'YYYY-MM-DD HH24:MI') as last
                ,lat
                ,lon
            from poif
           where visit_count - coalesce(visit_count_reviewed, 0) > 0
          order by visit_count - coalesce(visit_count_reviewed, 0) desc, visit_count_last_review asc nulls first
          limit 30";

$res_m = pg_query($link, $query);
while ($row_m = pg_fetch_assoc($res_m)) {
  $josm = "top=" . $row_m['lon'] * 1.000002 .
          "&bottom=" . $row_m['lon'] * 0.999998 .
          "&left=" . $row_m['lat'] * 0.99999 .
          "&right=" . $row_m['lat'] * 1.00001;
  echo "<tr><td><a href=\"http://localhost:8111/load_and_zoom?{$josm}\" target=\"josm\" title=\"JOSM\">{$row_m['name']}</a></td><td>{$row_m['new']} ({$row_m['total']})</td><td>{$row_m['last']}</td><td><a href=\"review.php?reviewed={$row_m['uid']}\">Peržiūrėta</a></td></tr>\n";
}
echo "</table>\n";

pg_close($link);
?>
</body>
