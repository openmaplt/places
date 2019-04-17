<!DOCTYPE html>
<html style="height:100%" lang="lt">
<head>
  <title>Lietuvos lankytinos vietos</title>
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
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>.material-icons { font-size: 38px; color: white; }</style>
  <style>.ol-control button {background-color: rgb(0,0,0)}</style>
  <script type="text/javascript" src="/js/ol.js"></script>
  <script type="text/javascript" src="/js/jquery.min.js"></script>
  <script src="//code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-18331326-3', 'auto');
  ga('send', 'pageview');
</script>
</head>
<body>
<p><a href="/lankytinos.html">Grįžti...</a></p>
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
if (isset($_GET['debug'])) {
  $debug = $_GET['debug'];
} else {
  $debug = 'no';
}
define('DEBUG', ($debug == 'yes'));

if (DEBUG) {
  error_reporting(E_ALL ^ E_NOTICE);
  echo '<pre>';
} else {
  error_reporting(0);
}

$config = require './config.php';
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));
if (!$link) {
  debug('Cannot connect to database');
  die;
}

$query = "select coalesce(name, official_name, 'Be pavadinimo') as name, uid,
            case when historic = 'archaeological_site' and site_type = 'fortification' then '/img/hillfort_.png'
                  when historic = 'archaeological_site' and site_type = 'tumulus' then '/img/tumulus_.png'
                  when historic = 'manor' then '/img/dvarai_.png'
                  when historic = 'monastery' then '/img/convent_.png'
                  when historic in ('monument', 'memorial') then '/img/memorial_.png'
                  when historic is not null then '/img/ruins_.png'
                  when man_made in ('tower', 'communications_tower') and \"tower:type\" is not null and tourism in ('attraction', 'viewpoint', 'museum') and coalesce(access, 'yes') != 'no' then '/img/tower_.png'
                  when tourism = 'attraction' and \"attraction:type\" = 'hiking_route' then '/img/hiking_.png'
                  when tourism = 'viewpoint' then '/img/viewpoint_.png'
                  when tourism = 'museum' then '/img/museum_.png'
                  when (tourism = 'picnic_site' or amenity = 'shelter') and fireplace = 'yes' then '/img/fire_.png'
                  when (tourism = 'picnic_site' or amenity = 'shelter') and (fireplace is null or fireplace = 'no') then '/img/picnic_.png'
                  when tourism in ('camp_site', 'caravan_site') then '/img/camping_.png'
                  when tourism in ('chalet', 'hostel', 'motel', 'guest_house') then '/img/hostel_.png'
                  when amenity = 'fuel' then '/img/fillingstation_.png'
                  when amenity = 'cafe' then '/img/coffee_.png'
                  when amenity = 'fast_food' then '/img/burger_.png'
                  when amenity = 'restaurant' then '/img/restaurant_.png'
                  when amenity in ('pub', 'bar') then '/img/bar_.png'
                  when tourism = 'hotel' then '/img/hotel_.png'
                  when tourism = 'information' then '/img/information_.png'
                  when amenity = 'theatre' then '/img/theater_.png'
                  when amenity = 'cinema' then '/img/cinema_.png'
                  when highway = 'speed_camera' then '/img/speed_.png'
                  when amenity = 'arts_centre' then '/img/art-museum_.png'
                  when amenity = 'library' then '/img/library_.png'
                  when amenity = 'hospital' then '/img/hospital_.png'
                  when amenity = 'clinic' then '/img/firstaid_.png'
                  when amenity = 'dentist' then '/img/dentist_.png'
                  when amenity = 'doctors' then '/img/medicine_.png'
                  when amenity = 'pharmacy' then '/img/drugstore_.png'
                  when shop in ('supermarket', 'mall') then '/img/supermarket_.png'
                  when shop = 'convenience' then '/img/convenience_.png'
                  when shop = 'car_repair' then '/img/repair_.png'
                  when shop = 'kiosk' then '/img/market_.png'
                  when shop = 'doityourself' then '/img/workshop_.png'
                  when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('catholic', 'roman_catholic') then '/img/cathedral_.png'
                  when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('lutheran', 'evangelical', 'reformed') then '/img/lutheran_.png'
                  when amenity = 'place_of_worship' and religion = 'christian' and denomination = 'orthodox' then '/img/orthodox_.png'
                  when amenity = 'place_of_worship' and (religion != 'christian' or coalesce(denomination, '@') not in ('catholic', 'roman_catholic', 'lutheran', 'evangelical', 'reformed', 'orthodox')) then '/img/prayer_.png'
                  when office = 'government' or amenity = 'townhall' then '/img/congress_.png'
                  when amenity = 'courthouse' then '/img/court_.png'
                  when office = 'notary' then '/img/administration_.png'
                  when office = 'insurance' then '/img/umbrella_.png'
                  when office is not null and office not in ('government', 'notary') then '/img/office-building_.png'
                  when shop is not null and shop not in ('supermarket', 'mall', 'convenience', 'car_repair', 'kiosk', 'doityourself') then '/img/departmentstore_.png'
                  when amenity = 'post_office' then '/img/postal_.png'
                  when amenity = 'car_wash' then '/img/carwash_.png'
                  when amenity = 'bank' then '/img/bigcity_.png'
                  when amenity = 'atm' then '/img/euro_.png'
                  when amenity = 'police' then '/img/police_.png'
                  when \"natural\" = 'tree' then '/img/tree_.png'
                  when \"natural\" = 'stone' then '/img/stone_.png'
                  when \"natural\" = 'spring' then '/img/spring_.png'
                  when tourism in ('attraction', 'theme_park', 'zoo', 'aquarium') then '/img/footprint_.png'
                  when \"ref:lt:kpd\" is not null then '/img/paveldas_.png'
                  else '???'
             end as image
from poif where ";
$type = $_GET['t'];
switch ($type) {
      case 'a': // kiti istoriniai
        echo "<h1>Kiti istoriniai taškai</h1>\n";
        $query .= "(historic is not null and " .
                  "historic not in ('monument', 'memorial', 'wayside_cross', 'wayside_shrine', 'manor') and" .
                   "(historic != 'archaeological_site' or site_type not in ('fortification', 'tumulus')))";
        break;
      case 'b': // hillfort
        echo "<h1>Piliakalniai</h1>\n";
        $query .= "(historic = 'archaeological_site' and site_type = 'fortification')";
        break;
      case 'c': // heritage
        echo "<h1>Kultūros paveldas</h1>\n";
        $query .= "((\"ref:lt:kpd\" is not null) and (coalesce(historic, '@') != 'archaeological_site' or coalesce(site_type, '@') != 'fortification'))";
        break;
      case 'd': // paminklas
        echo "<h1>Paminklai</h1>\n";
        $query .= "(historic in ('monument', 'memorial'))";
        break;
      case 'e': // pilkapiai
        echo "<h1>Pilkapiai</h1>\n";
        $query .= "(historic = 'archaeological_site' and site_type = 'tumulus')";
        break;
      case 'f': // dvarai
        echo "<h1>Dvarai</h1>\n";
        $query .= "(historic = 'manor')";
        break;
      case 'g':
        echo "<h1>Apžvalgos bokštai</h1>\n";
        $query .= "(man_made in ('tower', 'communications_tower') and \"tower:type\" is not null and tourism in ('attraction', 'viewpoint', 'museum') and coalesce(access, 'yes') != 'no')";
        break;
      case 'h':
        echo "<h1>Lankytinos vietos</h1>\n";
        $query .= "(tourism in ('attraction', 'theme_park', 'zoo', 'aquarium') and historic is null and \"attraction:type\" is null)";
        break;
      case 'W':
        echo "<h1>Regyklos</h1>\n";
        $query .= "(tourism = 'viewpoint' and historic is null)";
        break;
      case 'i':
        echo "<h1>Muziejai</h1>\n";
        $query .= "(tourism = 'museum')";
        break;
      case 'j':
        echo "<h1>Stovyklavietės su laužaviete</h1>\n";
        $query .= "((tourism = 'picnic_site' or amenity = 'shelter') and fireplace = 'yes')";
        break;
      case 'k':
        echo "<h1>Stovyklavietės be laužavietės</h1>\n";
        $query .= "((tourism = 'picnic_site' or amenity = 'shelter') and (fireplace is null or fireplace = 'no'))";
        break;
      case 'l':
        echo "<h1>Kempingai</h1>\n";
        $query .= "(tourism in ('camp_site', 'caravan_site'))";
        break;
      case 'm':
        echo "<h1>Kaimo sodybos, svečių namai, moteliai</h1>\n";
        $query .= "(tourism in ('chalet', 'hostel', 'motel', 'guest_house'))";
        break;
      case 'n':
        echo "<h1>Degalinės</h1>\n";
        $query .= "(amenity = 'fuel')";
        break;
      case 'o':
        echo "<h1>Kavinės</h1>\n";
        $query .= "(amenity = 'cafe')";
        break;
      case 'p':
        echo "<h1>Greitas maistas</h1>\n";
        $query .= "(amenity = 'fast_food')";
        break;
      case 'q':
        echo "<h1>Restoranai</h1>\n";
        $query .= "(amenity = 'restaurant')";
        break;
      case 'r':
        echo "<h1>Aludės, barai</h1>\n";
        $query .= "(amenity in ('pub', 'bar'))";
        break;
      case 's':
        echo "<h1>Viešbučiai</h1>\n";
        $query .= "(tourism = 'hotel')";
        break;
      case 't':
        echo "<h1>Turizmo informacija (informaciniai centrai ir lentos)</h1>\n";
        $query .= "(tourism = 'information')";
        break;
      case 'u':
        echo "<h1>Teatrai</h1>\n";
        $query .= "(amenity = 'theatre')";
        break;
      case 'v':
        echo "<h1>Kino teatrai</h1>\n";
        $query .= "(amenity = 'cinema')";
        break;
      case 'w':
        echo "<h1>Greičio kameros</h1>\n";
        $query .= "(highway = 'speed_camera')";
        break;
      case 'x':
        echo "<h1>Menų centrai</h1>\n";
        $query .= "(amenity = 'arts_centre')";
        break;
      case 'y':
        echo "<h1>Bibliotekos</h1>\n";
        $query .= "(amenity = 'library')";
        break;
      case 'z':
        echo "<h1>Ligoninės</h1>\n";
        $query .= "(amenity = 'hospital')";
        break;
      case 'A':
        echo "<h1>Klinikos</h1>\n";
        $query .= "(amenity = 'clinic')";
        break;
      case 'B':
        echo "<h1>Odontologijos klinikos</h1>\n";
        $query .= "(amenity = 'dentist')";
        break;
      case 'C':
        echo "<h1>Daktarai</h1>\n";
        $query .= "(amenity = 'doctors')";
        break;
      case 'D':
        echo "<h1>Vaistinės</h1>\n";
        $query .= "(amenity = 'pharmacy')";
        break;
      case 'E':
        echo "<h1>Prekybos centrai</h1>\n";
        $query .= "(shop in ('supermarket', 'mall'))";
        break;
      case 'F':
        echo "<h1>Maisto ir kitų prekių parduotuvės</h1>\n";
        $query .= "(shop = 'convenience')";
        break;
      case 'G':
        echo "<h1>Mašinų remontas (servisai)</h1>\n";
        $query .= "(shop = 'car_repair')";
        break;
      case 'H':
        echo "<h1>Kioskai</h1>\n";
        $query .= "(shop = 'kiosk')";
        break;
      case 'I':
        echo "<h1>Parduotuvės „pasidaryk pats“</h1>\n";
        $query .= "(shop = 'doityourself')";
        break;
      case 'J':
        echo "<h1>Katalikų maldos namai</h1>\n";
        $query .= "(amenity = 'place_of_worship' and religion = 'christian' and denomination in ('catholic', 'roman_catholic'))";
        break;
      case 'K':
        echo "<h1>Evangelikų liuteronų maldos namai</h1>\n";
        $query .= "(amenity = 'place_of_worship' and religion = 'christian' and denomination in ('lutheran', 'evangelical', 'reformed'))";
        break;
      case 'L':
        echo "<h1>Provoslavų (stačiatikių ir sentikių) maldos namai</h1>\n";
        $query .= "(amenity = 'place_of_worship' and religion = 'christian' and denomination in ('orthodox', 'old_believers'))";
        break;
      case 'M':
        echo "<h1>Kitų religijų maldos namai</h1>\n";
        $query .= "(amenity = 'place_of_worship' and (religion != 'christian' or coalesce(denomination, '@') not in ('catholic', 'roman_catholic', 'lutheran', 'evangelical', 'reformed', 'orthodox', 'old_believers')))";
        break;
      case 'N':
        echo "<h1>Valstybinės įstaigos</h1>\n";
        $query .= "(office = 'government') or (amenity = 'townhall')";
        break;
      case 'O':
        echo "<h1>Teismai</h1>\n";
        $query .= "(amenity = 'courthouse')";
        break;
      case 'P':
        echo "<h1>Notarų, advokatų kontoros</h1>\n";
        $query .= "(office in ('notary', 'lawyer'))";
        break;
      case 'Y':
        echo "<h1>Draudimas</h1>\n";
        $query .= "(office = 'insurance')";
        break;
      case 'Q':
        echo "<h1>Kitos įstaigos</h1>\n";
        $query .= "(office is not null and office not in ('government', 'notary', 'lawyer', 'insurance'))";
        break;
      case 'R':
        echo "<h1>Kitos parduotuvės</h1>\n";
        $query .= "(shop is not null and shop not in ('supermarket', 'mall', 'convenience', 'car_repair', 'kiosk', 'doityourself'))";
        break;
      case 'S':
        echo "<h1>Paštai</h1>\n";
        $query .= "(amenity = 'post_office')";
        break;
      case 'T':
        echo "<h1>Mašinų plovyklos</h1>\n";
        $query .= "(amenity = 'car_wash')";
        break;
      case 'U':
        echo "<h1>Bankai</h1>\n";
        $query .= "(amenity = 'bank')";
        break;
      case 'V':
        echo "<h1>Bankomatai</h1>\n";
        $query .= "(amenity = 'atm')";
        break;
      case 'X':
        echo "<h1>Vienuolynai</h1>\n";
        $query .= "(historic = 'monastery')";
        break;
      case '1':
        echo "<h1>Pažintiniai takai</h1>\n";
        $query .= "(tourism = 'attraction' and \"attraction:type\" = 'hiking_route')";
        break;
      case '2':
        echo "<h1>Policijos nuovados</h1>\n";
        $query .= "(amenity = 'police')";
        break;
      case '3':
        echo "<h1>Gamtos objektai</h1>\n";
        $query .= "(\"natural\" in ('tree', 'stone', 'spring'))";
        break;
      default:
        continue;
}
$query .= " ORDER BY name";

debug('Query is: ' . $query);
$res = pg_query($link, $query);
if (!$res) {
  debug(pg_last_error($link));
  throw new Exception(pg_last_error($link));
  exit;
}

echo "<table style=\"border: 1px solid;\">\n<tr><th>Nr.</th><th>Pavadinimas</th></tr>\n";
$nr = 1;
while ($row = pg_fetch_assoc($res)) {
  $url = strtolower($row['name']) . '-' . $row['uid'];
  $url = str_replace(' ', '-', $url);
  $url = str_replace('ą', 'a', $url);
  $url = str_replace('č', 'c', $url);
  $url = str_replace('ę', 'e', $url);
  $url = str_replace('ė', 'e', $url);
  $url = str_replace('į', 'i', $url);
  $url = str_replace('š', 's', $url);
  $url = str_replace('Š', 's', $url);
  $url = str_replace('ų', 'u', $url);
  $url = str_replace('ū', 'u', $url);
  $url = str_replace('ž', 'z', $url);
  $url = str_replace('.', '', $url);
  $url = str_replace(',', '', $url);
  $url = str_replace('„', '', $url);
  $url = str_replace('“', '', $url);
  $url = str_replace('"', '', $url);
  $url = str_replace('\'', '', $url);
  $url = str_replace('(', '', $url);
  $url = str_replace(')', '', $url);
  echo "<tr><td>$nr</td><td><img src=\"" . $row['image'] . "\" alt=\"\"> <a href=\"$url\">" . $row['name'] . "</a></td></tr>\n";
  $nr += 1;
}
echo "</table>\n";

pg_close($link);
?>
</body>
</html>
