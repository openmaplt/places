<!DOCTYPE html>
<html style="height:100%" lang="lt">
<head>
  <title><?php
if (isset($_GET['id'])) {
  $id = $_GET['id'];
} else {
  exit;
}

if (substr($id, -1) == 'm') {
  $id = substr($id, 0, strlen($id) - 1);
  $frommap = 'Y';
}

$config = require('./config.php');
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));
$query = "SELECT name, osm_id, obj_type, \"attraction:type\" as type, type0, type1, lat, lon from poif where uid = $id";
$res = pg_query($link, $query);
#echo "{$query}\n";
while ($row = pg_fetch_assoc($res)) {
  $name = $row['name'];
  $obj_type = $row['obj_type'];
  $osm_id = $row['osm_id'];
  $type = $row['type'];
  $lat = $row['lat'];
  $lon = $row['lon'];
  $type0 = $row['type0'];
  $type1 = $row['type1'];
  echo $name;
}

if ($type == "hiking_route") {
  $query = "SELECT st_asgeojson(st_multi(st_union(way))) as geojson from planet_osm_line where name = '$name' and route = 'hiking' limit 1";
  $res = pg_query($link, $query);
  while ($row = pg_fetch_assoc($res)) {
    $geojson = $row['geojson'];
  }
}

pg_close($link);
?></title>
  <meta name="description" content="Lietuvos lankytinų vietų žemėlapis: turizmas, paveldas, apgyvendinimas, automobiliai, įstaigos, prekyba, pramogos, religija ir t.t.">
  <meta name="keywords" content="Lietuva lankytinos vietos piliakalniai stovyklavietės kempingai restoranai barai kavinės degalinės muziejai parduotuvės">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta property="og:title" content="Lietuvos lankytinos vietos" />
  <meta property="og:description" content="Interaktyvus Lietuvos lankytinų vietų žemėlapis: turizmas, prekyba, apgyvendinimas, paveldas, religija ir t.t." />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://places.openmap.lt/" />
  <meta property="og:locale" content="lt_LT" />
  <link rel="stylesheet" href="/js/ol.css" type="text/css">
  <link rel="stylesheet" href="/main.css" type="text/css">
  <link rel="stylesheet" href="/style.css" type="text/css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>.ol-control button {background-color: rgb(0,0,0)}</style>
  <script type="text/javascript" src="/js/ol.js"></script>
  <!--script type="text/javascript" src="/js/jquery.min.js"></script-->
  <!--script src="//code.jquery.com/ui/1.12.0/jquery-ui.js"></script-->
  <!--script type="text/javascript" src="styles.js"></script-->
  <!--script type="text/javascript" src="app.js"></script-->
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-18331326-3', 'auto');
  ga('send', 'pageview');
</script>
<script>
function initMap(lat, lon) {
  var map;
  var view;
  var source;
  var center = new ol.proj.fromLonLat([lat, lon]);
  var layer = new ol.layer.Tile({source: new ol.source.OSM({url:'https://dev.openmap.lt/tiles/{z}/{x}/{y}.png',crossOrigin:null}), visible: true});
  view = new ol.View({
          center: center,
          zoom: 16,
          minZoom: 1,
          maxZoom: 18
        });

  var marker = new ol.Feature({
        type: 'icon',
        geometry: new ol.geom.Point(center)
      });
  source = new ol.source.Vector({features: [marker]});
  var vectorLayer = new ol.layer.Vector({
    source: source,
    style: new ol.style.Style({
          image: new ol.style.Circle({
            radius: 7,
            snapToPixel: false,
            /*fill: new ol.style.Fill({color: 'black'}),*/
            stroke: new ol.style.Stroke({
              color: 'black', width: 2
            })
          })
        })
  });
<?php if (!empty($geojson)) { ?>
  var geojsonObject = '<?php echo $geojson ?>';
  var vectorSource = new ol.source.Vector({ features: (new ol.format.GeoJSON()).readFeatures(geojsonObject) });
  var routeLayer = new ol.layer.Vector({ source: vectorSource, style: new ol.style.Style({
          stroke: new ol.style.Stroke({
            color: 'rgba(255,0,255,0.3)',
            lineDash: [4],
            width: 10
          })
        })
         });
<?php } ?>
  map = new ol.Map({
        target: 'map',
        layers: [layer, vectorLayer<?php if (!empty($geojson)) { ?>, routeLayer<?php } ?>],
        view: view
      });
} // initMap
</script>
</head>
<body onLoad="initMap(<?php echo "$lat,$lon" ?>)">
<p class="meniu"><?php
if ($frommap == 'Y') {
  print "<a href=\"/\">Grįžti į žemėlapį...</a>";
} else {
  print "<a href=\"/#m=16/{$lat}/{$lon}/{$type0}/T\">Žiūrėti žemėlapyje</a> <a href=\"./\">Sąrašas</a>";
}
?></p>
<div id="map" class="map" style="max-height: 50%; max-width: 600px;"></div>
<?php
$external_id = $id;
include 'info.php';

echo "<small><i>OpenStreetMap object: $obj_type $osm_id</i></small>";
?>
</body>
</html>
