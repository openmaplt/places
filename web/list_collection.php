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

$config = require './config.php';
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));
if (!$link) {
  debug('Cannot connect to database');
  die;
}

$lat = $_GET['lat'];
$lon = $_GET['lon'];
$notfound = $_GET['nf'];
debug('notfound=' . $notfound);
$token = $_GET['t'];

$grupes = "";
$query = "select grupes from kol_naudotojai where token = '{$token}'";
debug('query=' . $query);
$res = pg_query($link, $query);
while ($row = pg_fetch_assoc($res)) {
  debug("kuku");
  $grupes = $row['grupes'];
}
debug('Grupes=' . $grupes);
if ($grupes == '') {
  die;
}

$filter = "0=1";
  for ($i=0; $i < strlen($grupes); $i++) {
    if ($i == 0) {
      $filter = "(0=1";
    }
    $type = $grupes[$i];
    debug("processing type={$type}");
    switch($type) {
      case 'a': // kiti istoriniai
        $filter .= " or (historic is not null and " .
                   "historic not in ('monument', 'memorial', 'wayside_cross', 'wayside_shrine', 'manor') and" .
                   "(historic != 'archaeological_site' or site_type not in ('fortification', 'tumulus')))";
        break;
      case 'b': // hillfort
        $filter .= " or (historic = 'archaeological_site' and site_type = 'fortification')";
        break;
      case 'c': // heritage
        $filter .= " or ((\"ref:lt:kpd\" is not null) and (coalesce(historic, '@') != 'archaeological_site' or coalesce(site_type, '@') != 'fortification'))";
        break;
      case 'd': // paminklas
        $filter .= " or (historic in ('monument', 'memorial'))";
        break;
      case 'e': // pilkapiai
        $filter .= " or (historic = 'archaeological_site' and site_type = 'tumulus')";
        break;
      case 'f': // dvarai
        $filter .= " or (historic = 'manor')";
        break;
      case 'g':
        $filter .= " or (man_made in ('tower', 'communications_tower') and \"tower:type\" is not null and tourism in ('attraction', 'viewpoint', 'museum') and coalesce(access, 'yes') != 'no')";
        break;
      case 'h':
        $filter .= " or (tourism in ('attraction', 'theme_park', 'zoo', 'aquarium') and historic is null and \"attraction:type\" is null)";
        break;
      case 'W':
        $filter .= " or (tourism = 'viewpoint' and historic is null)";
        break;
      case 'i':
        $filter .= " or (tourism = 'museum')";
        break;
      case 'j':
        $filter .= " or ((tourism = 'picnic_site' or amenity = 'shelter') and fireplace = 'yes')";
        break;
      case 'k':
        $filter .= " or ((tourism = 'picnic_site' or amenity = 'shelter') and (fireplace is null or fireplace = 'no'))";
        break;
      case 'l':
        $filter .= " or (tourism = 'camp_site')";
        break;
      case 'm':
        $filter .= " or (tourism in ('chalet', 'hostel', 'motel', 'guest_house'))";
        break;
      case 'n':
        $filter .= " or (amenity = 'fuel')";
        break;
      case 'o':
        $filter .= " or (amenity = 'cafe')";
        break;
      case 'p':
        $filter .= " or (amenity = 'fast_food')";
        break;
      case 'q':
        $filter .= " or (amenity = 'restaurant')";
        break;
      case 'r':
        $filter .= " or (amenity in ('pub', 'bar'))";
        break;
      case 's':
        $filter .= " or (tourism = 'hotel')";
        break;
      case 't':
        $filter .= " or (tourism = 'information')";
        break;
      case 'u':
        $filter .= " or (amenity = 'theatre')";
        break;
      case 'v':
        $filter .= " or (amenity = 'cinema')";
        break;
      case 'w':
        $filter .= " or (highway = 'speed_camera')";
        break;
      case 'x':
        $filter .= " or (amenity = 'arts_centre')";
        break;
      case 'y':
        $filter .= " or (amenity = 'library')";
        break;
      case 'z':
        $filter .= " or (amenity = 'hospital')";
        break;
      case 'A':
        $filter .= " or (amenity = 'clinic')";
        break;
      case 'B':
        $filter .= " or (amenity = 'dentist')";
        break;
      case 'C':
        $filter .= " or (amenity = 'doctors')";
        break;
      case 'D':
        $filter .= " or (amenity = 'pharmacy')";
        break;
      case 'E':
        $filter .= " or (shop in ('supermarket', 'mall'))";
        break;
      case 'F':
        $filter .= " or (shop = 'convenience')";
        break;
      case 'G':
        $filter .= " or (shop = 'car_repair')";
        break;
      case 'H':
        $filter .= " or (shop = 'kiosk')";
        break;
      case 'I':
        $filter .= " or (shop = 'doityourself')";
        break;
      case 'J':
        $filter .= " or (amenity = 'place_of_worship' and religion = 'christian' and denomination in ('catholic', 'roman_catholic'))";
        break;
      case 'K':
        $filter .= " or (amenity = 'place_of_worship' and religion = 'christian' and denomination in ('lutheran', 'evangelical', 'reformed'))";
        break;
      case 'L':
        $filter .= " or (amenity = 'place_of_worship' and religion = 'christian' and denomination = 'orthodox')";
        break;
      case 'M':
        $filter .= " or (amenity = 'place_of_worship' and (religion != 'christian' or coalesce(denomination, '@') not in ('catholic', 'roman_catholic', 'lutheran', 'evangelical', 'reformed', 'orthodox')))";
        break;
      case 'N':
        $filter .= " or (office = 'government') or (amenity = 'townhall')";
        break;
      case 'O':
        $filter .= " or (amenity = 'courthouse')";
        break;
      case 'P':
        $filter .= " or (office in ('notary', 'lawyer'))";
        break;
      case 'Y':
        $filter .= " or (office = 'insurance')";
        break;
      case 'Q':
        $filter .= " or (office is not null and office not in ('government', 'notary', 'lawyer', 'insurance'))";
        break;
      case 'R':
        $filter .= " or (shop is not null and shop not in ('supermarket', 'mall', 'convenience', 'car_repair', 'kiosk', 'doityourself'))";
        break;
      case 'S':
        $filter .= " or (amenity = 'post_office')";
        break;
      case 'T':
        $filter .= " or (amenity = 'car_wash')";
        break;
      case 'U':
        $filter .= " or (amenity = 'bank')";
        break;
      case 'V':
        $filter .= " or (amenity = 'atm')";
        break;
      case 'X':
        $filter .= " or (historic = 'monastery')";
        break;
      case '1':
        $filter .= " or (tourism = 'attraction' and \"attraction:type\" = 'hiking_route')";
        break;
      default:
        continue;
    }
    if ($i == (strlen($grupes) - 1)) {
      $filter .= ")";
    }
  } // while loop through all type values

  if ($notfound == 'yes') {
    $filter .= " and (uid not in (select uid from kol_lankymas where token = '{$token}'))";
  }
  $fields = "uid
            ,name
            ,case when obj_type = 'n' then osm_id * 10
                  else osm_id * 10 + 1
             end as id
            ,case when historic = 'archaeological_site' and site_type = 'fortification' then '/img/hillfort_.png'
                  when historic = 'archaeological_site' and site_type = 'tumulus' then '/img/tumulus_.png'
                  when historic = 'manor' then '/img/dvarai_.png'
                  when historic = 'monastery' then '/img/convent_.png'
                  when historic in ('monument', 'memorial') then '/img/memorial_.png'
                  when historic is not null then '/img/ruins_.png'
                  when \"ref:lt:kpd\" is not null then '/img/paveldas_.png'
                  when man_made in ('tower', 'communications_tower') and \"tower:type\" is not null and tourism in ('attraction', 'viewpoint', 'museum') and coalesce(access, 'yes') != 'no' then '/img/tower_.png'
                  when tourism = 'attraction' and \"attraction:type\" = 'hiking_route' then '/img/hiking_.png'
                  when tourism in ('attraction', 'theme_park', 'zoo', 'aquarium') then '/img/footprint_.png'
                  when tourism = 'viewpoint' then '/img/viewpoint_.png'
                  when tourism = 'museum' then '/img/museum_.png'
                  when (tourism = 'picnic_site' or amenity = 'shelter') and fireplace = 'yes' then 'PIF'
                  when (tourism = 'picnic_site' or amenity = 'shelter') and (fireplace is null or fireplace = 'no') then 'PIC'
                  when tourism = 'camp_site' then 'CAM'
                  when tourism in ('chalet', 'hostel', 'motel', 'guest_house') then 'GUE'
                  when amenity = 'fuel' then 'FUE'
                  when amenity = 'cafe' then 'CAF'
                  when amenity = 'fast_food' then 'FAS'
                  when amenity = 'restaurant' then 'RES'
                  when amenity in ('pub', 'bar') then 'PUB'
                  when tourism = 'hotel' then 'HOT'
                  when tourism = 'information' then 'INF'
                  when amenity = 'theatre' then 'THE'
                  when amenity = 'cinema' then 'CIN'
                  when highway = 'speed_camera' then 'SPE'
                  when amenity = 'arts_centre' then 'ART'
                  when amenity = 'library' then 'LIB'
                  when amenity = 'hospital' then 'HOS'
                  when amenity = 'clinic' then 'CLI'
                  when amenity = 'dentist' then 'DEN'
                  when amenity = 'doctors' then 'DOC'
                  when amenity = 'pharmacy' then 'PHA'
                  when shop in ('supermarket', 'mall') then 'SUP'
                  when shop = 'convenience' then 'CON'
                  when shop = 'car_repair' then 'CAR'
                  when shop = 'kiosk' then 'KIO'
                  when shop = 'doityourself' then 'DIY'
                  when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('catholic', 'roman_catholic') then '/img/cathedral_.png'
                  when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('lutheran', 'evangelical', 'reformed') then '/img/lutheran_.png'
                  when amenity = 'place_of_worship' and religion = 'christian' and denomination = 'orthodox' then '/img/orthodox_.png'
                  when amenity = 'place_of_worship' and (religion != 'christian' or coalesce(denomination, '@') not in ('catholic', 'roman_catholic', 'lutheran', 'evangelical', 'reformed', 'orthodox')) then '/img/prayer_.png'
                  when office = 'government' or amenity = 'townhall' then 'GOV'
                  when amenity = 'courthouse' then 'COU'
                  when office = 'notary' then 'NOT'
                  when office = 'insurance' then 'INS'
                  when office is not null and office not in ('government', 'notary') then 'COM'
                  when shop is not null and shop not in ('supermarket', 'mall', 'convenience', 'car_repair', 'kiosk', 'doityourself') then 'OSH'
                  when amenity = 'post_office' then 'POS'
                  when amenity = 'car_wash' then 'WAS'
                  when amenity = 'bank' then 'BAN'
                  when amenity = 'atm' then 'ATM'
                  else '???'
             end as image,
             round(cast(st_distance(way, st_setSRID(st_makepoint({$lat}, {$lon}),4326)) * 100 as numeric), 3) as distance";
  $query = "SELECT ST_X(way) lat, ST_Y(way) lon, {$fields}
              FROM poif
             WHERE {$filter}
             ORDER BY st_distance(way, st_setSRID(st_makepoint({$lat}, {$lon}),4326)) asc
             LIMIT 100";
  debug('Query is: ' . $query);
  $res = pg_query($link, $query);
  if (!$res) {
    debug(pg_last_error($link));
    throw new Exception(pg_last_error($link));
    exit;
  }

  echo "<table class=\"list\">";
  while ($row = pg_fetch_assoc($res)) {
    //debug("lat:{$row['lat']}, lon:{$row['lon']}, tags:{$row['name']}");
    //$row['tp'] = $tp;
    echo "<tr onClick=\"showPoi({$row['uid']}, {$row['lat']}, {$row['lon']})\" class=\"item\">" .
         "<td><img src=\"{$row['image']}\"></td>" .
         "<td>{$row['name']}</td>" .
         "<td>{$row['distance']}km.</td></tr>";
  }
  echo "</table>";

pg_close($link);
