<?php
function tag($tag, $old, $new) {
  if (!empty($old) || !empty($new)) {
    if ($old != $new) {
      $style = " style=\"background-color: #ffff88;\"";
    } else {
      $style = "";
    }
    echo "<tr {$style}><td>{$tag}</td><td>{$old}</td><td>{$new}</td></tr>\n";
  }
}
?><html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
.map {
  height: 200px;
  width: 100%;
  border: 1px solid black;
}
</style>
<script src="http://dev.openmap.lt/js2/ol.js" type="text/javascript"></script>
<link rel="stylesheet" href="http://dev.openmap.lt/js2/ol.css" type="text/css">
</head>
<body>
<div style="display: none;"><iframe name="josm"></iframe></div>
<p><a href="list.php">Atgal į sąrašą</a></p>
<h2>Lankytinos vietos pasikeitimas</h2>
<table border=1 cellspacing=0>
<tr><th>Žyma</th><th>Senas</th><th>Naujas</th></tr>
<?php
$id = $_GET['id'];
$tp = $_GET['tp'];
$obj_type = substr($id, 0, 1);
$osm_id = substr($id, 1);

$config = require('./config.php');
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

if ($tp == "D") {
$query = "select p.lat old_lat
                ,null new_lat
                ,p.lon old_lon
                ,null new_lon
                ,p.name old_name
                ,null new_name
                ,p.description old_description
                ,null new_description
                ,p.information old_information
                ,null new_information
                ,p.image old_image
                ,null new_image
                ,p.opening_hours old_opening_hours
                ,null new_opening_hours
                ,p.phone old_phone
                ,null new_phone
                ,p.email old_email
                ,null new_email
                ,p.website old_website
                ,null new_website
                ,p.url old_url
                ,null new_url
                ,p.\"addr:city\" old_addrcity
                ,null new_addrcity
                ,p.\"addr:street\" old_addrstreet
                ,null new_addrstreet
                ,p.\"addr:postcode\" old_addrpostcode
                ,null new_addrpostcode
                ,p.\"addr:housenumber\" old_addrhousenumber
                ,null new_addrhousenumber
                ,p.real_ale old_real_ale
                ,null new_real_ale
                ,p.historic old_historic
                ,null new_historic
                ,p.man_made old_man_made
                ,null new_man_made
                ,p.\"tower:type\" old_towertype
                ,null new_towertype
                ,p.fee old_fee
                ,null new_fee
                ,p.ref old_ref
                ,null new_ref
                ,p.wikipedia old_wikipedia
                ,null new_wikipedia
                ,p.\"wikipedia:lt\" old_wikipedialt
                ,null new_wikipedialt
                ,p.\"wikipedia:en\" old_wikipediaen
                ,null new_wikipediaen
                ,p.height old_height
                ,null new_height
                ,p.alt_name old_alt_name
                ,null new_alt_name
                ,p.\"ref:lt:kpd\" old_refltkpd
                ,null new_refltkpd
                ,p.maxspeed old_maxspeed
                ,null new_maxspeed
                ,p.operator old_operator
                ,null new_operator
                ,p.tourism old_tourism
                ,null new_tourism
                ,p.site_type old_site_type
                ,null new_site_type
                ,p.amenity old_amenity
                ,null new_amenity

                ,p.historic old_historic
                ,null new_historic
                ,p.tourism old_tourism
                ,null new_tourism
                ,p.shop old_shop
                ,null new_shop
                ,p.religion old_religion
                ,null new_religion
                ,p.denomination old_denomination
                ,null new_denomination
                ,p.official_name old_official_name
                ,null new_official_name
                ,p.\"attraction:type\" old_attraction_type
                ,null new_attraction_type
                ,p.distance old_distance
                ,null new_distance
                ,p.\"natural\" old_natural
                ,null new_natural
            from poif p
           where p.osm_id = $osm_id
             and p.obj_type = '$obj_type'";
} elseif ($tp == "N") {
$query = "select null old_lat
                ,to_char(c.lat, '99D99999') new_lat
                ,null old_lon
                ,to_char(c.lon, '99D99999') new_lon
                ,null old_name
                ,c.name new_name
                ,null old_description
                ,c.description new_description
                ,null old_information
                ,c.information new_information
                ,null old_image
                ,c.image new_image
                ,null old_opening_hours
                ,c.opening_hours new_opening_hours
                ,null old_phone
                ,c.phone new_phone
                ,null old_email
                ,c.email new_email
                ,null old_website
                ,c.website new_website
                ,null old_url
                ,c.url new_url
                ,null old_addrcity
                ,c.\"addr:city\" new_addrcity
                ,null old_addrstreet
                ,c.\"addr:street\" new_addrstreet
                ,null old_addrpostcode
                ,c.\"addr:postcode\" new_addrpostcode
                ,null old_addrhousenumber
                ,c.\"addr:housenumber\" new_addrhousenumber
                ,null old_real_ale
                ,c.real_ale new_real_ale
                ,null old_historic
                ,c.historic new_historic
                ,null old_man_made
                ,c.man_made new_man_made
                ,null old_towertype
                ,c.\"tower:type\" new_towertype
                ,null old_fee
                ,c.fee new_fee
                ,null old_ref
                ,c.ref new_ref
                ,null old_wikipedia
                ,c.wikipedia new_wikipedia
                ,null old_wikipedialt
                ,c.\"wikipedia:lt\" new_wikipedialt
                ,null old_wikipediaen
                ,c.\"wikipedia:en\" new_wikipediaen
                ,null old_height
                ,c.height new_height
                ,null old_alt_name
                ,c.alt_name new_alt_name
                ,null old_refltkpd
                ,c.\"ref:lt:kpd\" new_refltkpd
                ,null old_maxspeed
                ,c.maxspeed new_maxspeed
                ,null old_operator
                ,c.operator new_operator
                ,null old_tourism
                ,c.tourism new_tourism
                ,null old_site_type
                ,c.site_type new_site_type
                ,null old_amenity
                ,c.amenity new_amenity

                ,null old_fireplace
                ,c.fireplace new_fireplace
                ,null old_highway
                ,c.highway new_highway
                ,null old_access
                ,c.access new_access
                ,null old_shop
                ,c.shop new_shop
                ,null old_whitewater
                ,c.whitewater new_whitewater
                ,null old_milestone
                ,c.milestone new_milestone
                ,null old_religion
                ,c.religion new_religion
                ,null old_denomination
                ,c.denomination new_denomination
                ,null old_office
                ,c.office new_office
                ,null old_official_name
                ,c.official_name new_official_name
                ,null old_attraction_type
                ,c.\"attraction:type\" new_attraction_type
                ,null old_distance
                ,c.distance new_distance
                ,null old_natural
                ,c.\"natural\" new_natural
            from poi_change c
           where c.osm_id = $osm_id
             and c.obj_type = '$obj_type'";
} elseif ($tp == "C") {
$query = "select to_char(p.lat, '999D9999') old_lat
                ,to_char(c.lat, '999D9999') new_lat
                ,to_char(p.lon, '999D9999') old_lon
                ,to_char(c.lon, '999D9999') new_lon
                ,p.name old_name
                ,c.name new_name
                ,p.description old_description
                ,c.description new_description
                ,p.information old_information
                ,c.information new_information
                ,p.image old_image
                ,c.image new_image
                ,p.opening_hours old_opening_hours
                ,c.opening_hours new_opening_hours
                ,p.phone old_phone
                ,c.phone new_phone
                ,p.email old_email
                ,c.email new_email
                ,p.website old_website
                ,c.website new_website
                ,p.url old_url
                ,c.url new_url
                ,p.\"addr:city\" old_addrcity
                ,c.\"addr:city\" new_addrcity
                ,p.\"addr:street\" old_addrstreet
                ,c.\"addr:street\" new_addrstreet
                ,p.\"addr:postcode\" old_addrpostcode
                ,c.\"addr:postcode\" new_addrpostcode
                ,p.\"addr:housenumber\" old_addrhousenumber
                ,c.\"addr:housenumber\" new_addrhousenumber
                ,p.real_ale old_real_ale
                ,c.real_ale new_real_ale
                ,p.historic old_historic
                ,c.historic new_historic
                ,p.man_made old_man_made
                ,c.man_made new_man_made
                ,p.\"tower:type\" old_towertype
                ,c.\"tower:type\" new_towertype
                ,p.fee old_fee
                ,c.fee new_fee
                ,p.ref old_ref
                ,c.ref new_ref
                ,p.wikipedia old_wikipedia
                ,c.wikipedia new_wikipedia
                ,p.\"wikipedia:lt\" old_wikipedialt
                ,c.\"wikipedia:lt\" new_wikipedialt
                ,p.\"wikipedia:en\" old_wikipediaen
                ,c.\"wikipedia:en\" new_wikipediaen
                ,p.height old_height
                ,c.height new_height
                ,p.alt_name old_alt_name
                ,c.alt_name new_alt_name
                ,p.\"ref:lt:kpd\" old_refltkpd
                ,c.\"ref:lt:kpd\" new_refltkpd
                ,p.maxspeed old_maxspeed
                ,c.maxspeed new_maxspeed
                ,p.operator old_operator
                ,c.operator new_operator
                ,p.tourism old_tourism
                ,c.tourism new_tourism
                ,p.site_type old_site_type
                ,c.site_type new_site_type
                ,p.amenity old_amenity
                ,c.amenity new_amenity

                ,p.fireplace old_fireplace
                ,c.fireplace new_fireplace
                ,p.highway old_highway
                ,c.highway new_highway
                ,p.access old_access
                ,c.access new_access
                ,p.shop old_shop
                ,c.shop new_shop
                ,p.office old_office
                ,c.office new_office
                ,p.whitewater old_whitewater
                ,c.whitewater new_whitewater
                ,p.milestone old_milestone
                ,c.milestone new_milestone
                ,p.religion old_religion
                ,c.religion new_religion
                ,p.denomination old_denomination
                ,c.denomination new_denomination
                ,p.official_name old_official_name
                ,c.official_name new_official_name
                ,p.\"attraction:type\" old_attraction_type
                ,c.\"attraction:type\" new_attraction_type
                ,p.distance old_distance
                ,c.distance new_distance
                ,p.\"natural\" old_natural
                ,c.\"natural\" new_natural
            from poi_change c
                ,poif p
           where c.osm_id = $osm_id
             and c.obj_type = '$obj_type'
             and p.osm_id = $osm_id
             and p.obj_type = '$obj_type'";
} else {
$query = "select 1 from poif where 1 = 0";
}
/*echo "<p>{$query}</p>\n";*/
$res_m = pg_query($link, $query);
while ($row_m = pg_fetch_assoc($res_m)) {
  tag("lat", $row_m['old_lat'], $row_m['new_lat']);
  tag("lon", $row_m['old_lon'], $row_m['new_lon']);
  tag("name", $row_m['old_name'], $row_m['new_name']);
  tag("description", $row_m['old_description'], $row_m['new_description']);
  tag("information", $row_m['old_information'], $row_m['new_information']);
  tag("image", $row_m['old_image'], $row_m['new_image']);
  tag("opening hours", $row_m['old_opening_hours'], $row_m['new_opening_hours']);
  tag("phone", $row_m['old_phone'], $row_m['new_phone']);
  tag("email", $row_m['old_email'], $row_m['new_email']);
  tag("website", $row_m['old_website'], $row_m['new_website']);
  tag("url", $row_m['old_url'], $row_m['new_url']);
  tag("addr:city", $row_m['old_addrcity'], $row_m['new_addrcity']);
  tag("addr:street", $row_m['old_addrstreet'], $row_m['new_addrstreet']);
  tag("addr:postcode", $row_m['old_addrpostcode'], $row_m['new_addrpostcode']);
  tag("addr:housenumber", $row_m['old_addrhousenumber'], $row_m['new_addrhousenumber']);
  tag("real_ale", $row_m['old_real_ale'], $row_m['new_real_ale']);
  tag("historic", $row_m['old_historic'], $row_m['new_historic']);
  tag("man_made", $row_m['old_man_made'], $row_m['new_man_made']);
  tag("tower:type", $row_m['old_towertype'], $row_m['new_towertype']);
  tag("fee", $row_m['old_fee'], $row_m['new_fee']);
  tag("ref", $row_m['old_ref'], $row_m['new_ref']);
  tag("wikipedia", $row_m['old_wikipedia'], $row_m['new_wikipedia']);
  tag("wikipedia:lt", $row_m['old_wikipedialt'], $row_m['new_wikipedialt']);
  tag("wikipedia:en", $row_m['old_wikipediaen'], $row_m['new_wikipediaen']);
  tag("height", $row_m['old_height'], $row_m['new_height']);
  tag("alt_name", $row_m['old_alt_name'], $row_m['new_alt_name']);
  tag("ref:lt:kpd", $row_m['old_refltkpd'], $row_m['new_refltkpd']);
  tag("maxspeed", $row_m['old_maxspeed'], $row_m['new_maxspeed']);
  tag("operator", $row_m['old_operator'], $row_m['new_operator']);
  tag("tourism", $row_m['old_tourism'], $row_m['new_tourism']);
  tag("site_type", $row_m['old_site_type'], $row_m['new_site_type']);
  tag("amenity", $row_m['old_amenity'], $row_m['new_amenity']);
  tag("fireplace", $row_m['old_fireplace'], $row_m['new_fireplace']);
  tag("highway", $row_m['old_highway'], $row_m['new_highway']);
  tag("access", $row_m['old_access'], $row_m['new_access']);
  tag("shop", $row_m['old_shop'], $row_m['new_shop']);
  tag("whitewater", $row_m['old_whitewater'], $row_m['new_whitewater']);
  tag("waterway:milestone", $row_m['old_milestone'], $row_m['new_milestone']);
  tag("religion", $row_m['old_religion'], $row_m['new_religion']);
  tag("denomination", $row_m['old_denomination'], $row_m['new_denomination']);
  tag("office", $row_m['old_office'], $row_m['new_office']);
  tag("official_name", $row_m['old_official_name'], $row_m['new_official_name']);
  tag("attraction:type", $row_m['old_attraction_type'], $row_m['new_attraction_type']);
  tag("distance", $row_m['old_distance'], $row_m['new_distance']);
  tag("natural", $row_m['old_natural'], $row_m['new_natural']);

    /*$qjosm = "select 'top='     || cast(st_y(st_transform(way, 4267)) * 1.000002 as text) ||
                     '&bottom=' || cast(st_y(st_transform(way, 4267)) * 0.999998 as text) ||
                     '&left='   || cast(st_x(st_transform(way, 4267)) * 0.99999  as text) ||
                     '&right='  || cast(st_x(st_transform(way, 4267)) * 1.00001  as text) josm
                from planet_osm_point
               where osm_id = {$row_m['osm_id']}";
    $res_josm = pg_query($link, $qjosm);
    $row_josm = pg_fetch_assoc($res_josm);
    $josm = $row_josm['josm'];
    echo "<tr style=\"font-size: 80%\"><td><a href=\"http://www.openstreetmap.org/browse/node/{$row_m['osm_id']}\" target=\"_blank\">{$row_m['miestas']}</a></td>" .
      "<td>{$row_m['busena']}</td>" .
      "<td><a href=\"http://localhost:8111/load_and_zoom?{$josm}\" target=\"josm\" title=\"JOSM\">JOSM</a></td></tr>\n";*/

  if (!empty($row_m['new_lat'])) {
    $lat = $row_m['new_lat'];
  } else {
    $lat = $row_m['old_lat'];
  }
  $lat = str_replace(",", ".", $lat);
  if (!empty($row_m['new_lon'])) {
    $lon = $row_m['new_lon'];
  } else {
    $lon = $row_m['old_lon'];
  }
  $lon = str_replace(",", ".", $lon);
}
echo "</table>\n";
/*echo "<p>{$query}</p>";*/

echo "<div id=\"map\" class=\"map\"></div>
<script>
map = new ol.Map({
  target: 'map',
  layers: [
    new ol.layer.Tile({source: new ol.source.OSM({url:'https://dev.openmap.lt/tiles/{z}/{x}/{y}.png',crossOrigin: null}),
                         visible: true})
  ],
  view: new ol.View({
    center: ol.proj.fromLonLat([{$lat}, {$lon}]),
    zoom: 16
  })
});
</script>
";
/*echo "<p>select accept_change({$osm_id}, '{$obj_type}', '{$tp}');</p>\n";*/
echo "<p><a href=\"list.php?a_osm_id={$osm_id}&a_obj_type={$obj_type}&a_change={$tp}\">[Patvirtinti]</a> \n";
$ot = $obj_type;
$osm_id2 = $osm_id;
if ($osm_id < 0) {
  $obj_type = "relation";
  $osm_id2 = -$osm_id;
} elseif ($obj_type == "n") {
  $obj_type = "node";
} elseif ($obj_type == "p") {
  $obj_type = "way";
}
echo "<a href=\"http://www.openstreetmap.org/{$obj_type}/{$osm_id2}/history\">[OSM info]</a></p>\n";

if ($tp == 'D') {
  echo "<h3>Potencialūs perėmėjai</h3>\n";
  $query = "select round(st_distance(del.way::geography, pos.way::geography)) atstumas
                  ,pos.name
                  ,pos.osm_id
                  ,pos.obj_type
                  ,del.uid
                  ,del.osm_id old_id
                  ,del.obj_type old_type
                  ,pos.x_type
              from poi_change pos
                  ,poi_change del
             where del.osm_id = $osm_id
               and del.obj_type = '$ot'
               and pos.x_type != 'D'
               and st_distance(del.way::geography, pos.way::geography) < 1000
            order by 1";
  /*echo "<p>$query</p>";*/

  $res_m = pg_query($link, $query);
  while ($row_m = pg_fetch_assoc($res_m)) {
    echo "<p>#{$row_m['uid']} {$row_m['obj_type']} {$row_m['osm_id']} {$row_m['name']} {$row_m['atstumas']}m. <a href=\"list.php?t_old_osm_id={$row_m['old_id']}&t_old_type={$row_m['old_type']}&t_new_osm_id={$row_m['osm_id']}&t_new_type={$row_m['obj_type']}&t_change={$row_m['x_type']}&t_uid={$row_m['uid']}\">[perkelti]</a></p>\n";
  }
}

pg_close($link);
?>
</body>
