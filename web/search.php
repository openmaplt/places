<?php
$config = require('./config.php');
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

if (isset($_GET['f']))
{
  $filter = $_GET['f'];
  $x = $_GET['x'];
  $y = $_GET['y'];
}
else {
  exit;
}

function escapeJsonString($value) { # list from www.json.org: (\b backspace, \f formfeed)
  $escapers = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
  $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
  $result = str_replace($escapers, $replacements, $value);
  return $result;
}

$query = "SELECT st_x(st_transform(way, 4326)) as lat
                ,st_y(st_transform(way, 4326)) as lon
                ,name as name
                ,round(cast(st_distance(st_transform(way, 4326), ST_PointFromText('POINT(' || $1 || ' ' || $2 || ')', 4326)) * 100 as numeric), 3) as distance
                ,\"addr:city\" as city
                ,\"addr:street\" as street
                ,\"addr:housenumber\" as house
                ,case when historic = 'archaeological_site' and site_type = 'fortification' then 'HIL'
                    when historic = 'archaeological_site' and site_type = 'tumulus' then 'TUM'
                    when historic = 'manor' then 'MAN'
                    when historic = 'monastery' then 'MNS'
                    when historic in ('monument', 'memorial') then 'MON'
                    when historic is not null then 'HIS'
                    when \"ref:lt:kpd\" is not null then 'HER'
                    when man_made in ('tower', 'communications_tower') and \"tower:type\" is not null and tourism in ('attraction', 'viewpoint', 'museum') and coalesce(access, 'yes') != 'no' then 'TOW'
                    when tourism in ('attraction', 'theme_park', 'zoo', 'aquarium') then 'ATT'
                    when tourism = 'viewpoint' then 'VIE'
                    when tourism = 'museum' then 'MUS'
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
                    when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('catholic', 'roman_catholic') then 'CHU'
                    when amenity = 'place_of_worship' and religion = 'christian' and denomination in ('lutheran', 'evangelical', 'reformed') then 'LUT'
                    when amenity = 'place_of_worship' and religion = 'christian' and denomination = 'orthodox' then 'ORT'
                    when amenity = 'place_of_worship' and (religion != 'christian' or coalesce(denomination, '@') not in ('catholic', 'roman_catholic', 'lutheran', 'evangelical', 'reformed', 'orthodox')) then 'ORE'
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
                    when \"natural\" = 'stone' then 'STO'
                    when \"natural\" = 'tree' then 'TRE'
                    when \"natural\" = 'spring' then 'SPR'
                    else '???'
                 end as type
                ,st_asgeojson(st_transform(way, 4326)) as geojson
            FROM poif
           WHERE name_soundex like '%' || places_soundex(lower($3)) || '%'
              or alt_name_soundex like '%' || places_soundex(lower($3)) || '%'
           ORDER BY st_distance(st_transform(way, 4326), ST_PointFromText('POINT(' || $1 || ' ' || $2 || ')', 4326))
           LIMIT 10";
$res = pg_query_params($link, $query, array(floatval($x), floatval($y), $filter));

$output = '';
$rowOutput = '';

while ($row = pg_fetch_assoc($res)) {
//echo "${row['name']}";
    $rowOutput = (strlen($rowOutput) > 0 ? ',' : '') . '{"type": "Feature", "geometry": ' . $row['geojson'] . ', "properties": {';
    $props = '';
    $id    = '';
    foreach ($row as $key => $val) {
        if ($key != "geojson") {
            $props .= (strlen($props) > 0 ? ',' : '') . '"' . $key . '":"' . escapeJsonString($val) . '"';
        }
        if ($key == "id") {
            $id .= ',"id":"' . escapeJsonString($val) . '"';
        }
    }

    $rowOutput .= $props . '}';
    $rowOutput .= $id;
    $rowOutput .= '}';
    $output .= $rowOutput;
}
pg_close($link);
$output = '{ "type": "FeatureCollection", "features": [ ' . $output . ' ]}';
echo $output;
?>
