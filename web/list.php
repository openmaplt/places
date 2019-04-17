<?php
/*****************************************************************
 * Parameters:
 *  debug=yes|no - turn on debugging (default=no)
 *  bbox=L,B,R,T - bounding box in EPSG:4326
 *  type=[]
 ****************************************************************/

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
 * Fetch poi list for a given bbox
 * @left, @top, @right, @bottom - bbox boundaries
 * @p_type:
 * a string of letters, for example "abc", "acgdf" etc.
 * a - historic
 * b - hillfort
 * c - heritage
 ************************************************************/
function fetch_poi($left, $top, $right, $bottom, $p_type, Poi_Format_Geojson $format) {
  global $link;
  // Construct a query part filtering out only required POI's
  debug("poi type is " . $p_type);
  if (strlen($p_type) == 0) {
    debug("ERROR: Incorrect (empty) type parameter");
    die;
  }

  $filter = "0=1";
  for ($i=0; $i < strlen($p_type); $i++) {
    $type = $p_type[$i];
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
        $filter .= " or (tourism in ('camp_site', 'caravan_site'))";
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
        $filter .= " or (amenity = 'place_of_worship' and religion = 'christian' and denomination in ('orthodox', 'old_believers'))";
        break;
      case 'M':
        $filter .= " or (amenity = 'place_of_worship' and (religion != 'christian' or coalesce(denomination, '@') not in ('catholic', 'roman_catholic', 'lutheran', 'evangelical', 'reformed', 'orthodox', 'old_believers')))";
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
      case '2':
        $filter .= " or (amenity = 'police')";
        break;
      case '3':
        $filter .= " or (\"natural\" in ('tree', 'stone', 'spring'))";
        break;
      default:
        continue;
    }
    $fields = "/*case when obj_type = 'n' then osm_id * 10
                    else osm_id * 10 + 1
               end*/ uid as id
              ,case when historic = 'archaeological_site' and site_type = 'fortification' then 'HIL'
                    when historic = 'archaeological_site' and site_type = 'tumulus' then 'TUM'
                    when historic = 'manor' then 'MAN'
                    when historic = 'monastery' then 'MNS'
                    when historic in ('monument', 'memorial') then 'MON'
                    when historic is not null then 'HIS'
                    when man_made in ('tower', 'communications_tower') and \"tower:type\" is not null and tourism in ('attraction', 'viewpoint', 'museum') and coalesce(access, 'yes') != 'no' then 'TOW'
                    when tourism = 'attraction' and \"attraction:type\" = 'hiking_route' then 'HIK'
                    when tourism = 'viewpoint' then 'VIE'
                    when tourism = 'museum' then 'MUS'
                    when (tourism = 'picnic_site' or amenity = 'shelter') and fireplace = 'yes' then 'PIF'
                    when (tourism = 'picnic_site' or amenity = 'shelter') and (fireplace is null or fireplace = 'no') then 'PIC'
                    when tourism in ('camp_site', 'caravan_site') then 'CAM'
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
                    when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('catholic', 'roman_catholic') then 'CHU'
                    when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('lutheran', 'evangelical', 'reformed') then 'LUT'
                    when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('orthodox', 'old_believers') then 'ORT'
                    when amenity = 'place_of_worship' and (religion != 'christian' or coalesce(denomination, '@') not in ('catholic', 'roman_catholic', 'lutheran', 'evangelical', 'reformed', 'orthodox', 'old_believers')) then 'ORE'
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
                    when amenity = 'police' then 'POL'
                    when \"natural\" = 'tree' then 'TRE'
                    when \"natural\" = 'stone' then 'STO'
                    when \"natural\" = 'spring' then 'SPR'
                    when tourism in ('attraction', 'theme_park', 'zoo', 'aquarium') then 'ATT'
                    when \"ref:lt:kpd\" is not null then 'HER'
                    else '???'
               end as type";
    $query = "SELECT ST_X(way) lat, ST_Y(way) lon, {$fields}
                FROM poif
               WHERE way && st_SetSRID('BOX3D({$left} {$top},{$right} {$bottom})'::box3d,4326)
                 AND ({$filter})";
    debug('Query is: ' . $query);
    $res = pg_query($link, $query);
    if (!$res) {
      debug(pg_last_error($link));
      throw new Exception(pg_last_error($link));
      exit;
    }

    while ($row = pg_fetch_assoc($res)) {
      debug("lat:{$row['lat']}, lon:{$row['lon']}, tags:{$row['name']}");
      //$row['tp'] = $tp;
      $poi['id'] = $row['id'];
      $poi['lat'] = round($row['lat'], 5);
      $poi['lon'] = round($row['lon'], 5);
      $poi['tp'] = $row['type'];
      $poi['oid'] = $row['id'];
      // add data to output
      $format->addRow($poi);
    }
  } // while loop through all type values
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

// Check the bounding box
$cl = explode(',', $_GET['bbox']);
if (!is_array($cl) or count($cl) != 4) {
  print "incorrect bbox parameter";
  exit;
}

list($left, $bottom, $right, $top) = $cl;

// Try fixing some common mistakes
if ($left > $right)
{
    $temp  = $left;
    $left  = $right;
    $right = $temp;
}

if ($bottom > $top)
{
    $temp   = $top;
    $top    = $bottom;
    $bottom = $temp;
}
debug("left="   . $left);
debug("top="    . $top);
debug("right="  . $right);
debug("bottom=" . $bottom);

// Check the type of poi to be fetched
$type = $_GET["type"];
if ($type === null) {
    $type = "fuel"; // default poi type is fuel
}

$bbox = (object)compact('left', 'bottom', 'right', 'top');
$format = new Poi_Format_Geojson($bbox);

$config = require './config.php';
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));
if (!$link) {
  debug('Cannot connect to database');
  die;
}

fetch_poi($left, $top, $right, $bottom, $type, $format);
$format->output();

pg_close($link);

/**
 * Class files
 */

class Poi_Format_Geojson
{
    /**
     * Data array
     * @var array
     */
    protected $_data = array();

    protected $_bbox;

    public function __construct($bbox = null)
    {
        $this->_bbox = $bbox;
    }

    /**
     * Add row to formated output
     * @param array $row
     */
    public function addRow(array $row)
    {
        // convert to object
        $row = (object)$row;
        $row->id = (float)$row->id;
        $row->lat = (float)$row->lat;
        $row->lon = (float)$row->lon;
        $this->_data[$row->id] = $row;
    }
    public function output()
    {
        $features = array();
        foreach($this->_data as $row){
            // add data to future json
            $latlon = array($row->lat, $row->lon);

            $features[] = array(
                'geometry' => array(
                    'type' => 'Point',
                    'coordinates' => $latlon,
                ),
                'type' => 'Feature',
                'properties' => $this->_getProperties($row),
                'id' => $row->id,
            );
        }
        @header('Content-type: application/json; charset=UTF-8');
        echo json_encode(array(
            'type' => 'FeatureCollection',
            'bbox' => array($this->_bbox->left, $this->_bbox->bottom, $this->_bbox->right, $this->_bbox->top),
            'features' => $features,
        ));
    }

    protected function _getProperties($row)
    {
        $properties = array();
        foreach($row as $prop => $value){
            if(empty($value)){
                continue;
            }
            switch($prop){
                case 'id':
                    continue;
                case 'name':
                    $properties['title'] = $value;
                    continue;
                case 'housenumber';
                case 'street':
                case 'city':
                case 'postcode':
                    if(!isset($properties['address'])){
                        $properties['address'] = array();
                    }
                    $properties['address'][$prop] = $value;
                    break;
                default:
                    $properties[$prop] = $value;
            }
        }
        return $properties;
    }
}
