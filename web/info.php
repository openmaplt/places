<?php
$config = require('./config.php');
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

if (isset($external_id)) {
    $id = $external_id;
} else {
  if (isset($_GET['id']))
  {
    $id = $_GET['id'];
    if ($id < 0) {
      $id -= 2;
    }
  }
  else {
    exit;
  }
}

if (isset($_GET['map'])) {
  $map = 'Y';
}

$query = "SELECT case when historic = 'archaeological_site' and site_type = 'fortification' then 'Piliakalnis'
                      when historic = 'archaeological_site' and site_type = 'tumulus' then 'Pilkapiai'
                      when historic = 'manor' then 'Dvaras'
                      when historic in ('monument', 'memorial') then 'Monumentas/paminklas'
                      when historic is not null then 'Istorinė vieta'
                      when \"ref:lt:kpd\" is not null then 'Kultūros paveldas'
                      when tourism = 'information' and information = 'board' then 'Turizmo informacijos lenta'
                      when tourism = 'information' and information = 'map' then 'Turizmo informacijos žemėlapis'
                      when tourism = 'information' and information = 'guidepost' then 'Turizmo informacijos rodyklė'
                      when tourism = 'information' and information = 'terminal' then 'Turizmo informacijos terminalas'
                      when tourism = 'viewpoint' then 'Regykla'
                      when highway = 'speed_camera' then 'Greičio kamera'
                      when amenity = 'library' then 'Biblioteka'
                      when amenity = 'place_of_worship' then 'Maldos namai'
                      when amenity = 'hospital' then 'Ligoninė'
                      when amenity = 'dentist' then 'Odontologijos kabinetas'
                      when amenity = 'pharmacy' then 'Vaistinė'
                      when shop = 'supermarket' then 'Prekybos centras'
                      when shop = 'convenience' then 'Maisto ir kitų prekių parduotuvė'
                      when shop = 'kiosk' then 'Kioskas'
                      when shop = 'doityourself' then 'Ūkinių prekių parduotuvė'
                      when shop is not null then 'Parduotuvė'
                      when office = 'government' then 'Valstybinė įstaiga'
                      when amenity = 'townhall' then 'Rotušė'
                      when amenity = 'post_office' then 'Paštas'
                      when office = 'lawyer' then 'Advokatų kontora'
                      when amenity = 'police' then 'Policija'
                      when amenity = 'bank' then 'Bankas'
                      when office = 'insurance' then 'Draudimas'
                      when amenity = 'atm' then 'Bankomatas'
                      else '???'
                 end as type
                ,case when tourism = 'attraction' and \"attraction:type\" = 'hiking_route' then 'pazintiniai-takai'
                 else 'detales'
                 end as path
                ,name
                ,alt_name
                ,description
                ,information
                ,opening_hours
                ,\"addr:city\" city
                ,\"addr:postcode\" postcode
                ,\"addr:street\" street
                ,\"addr:housenumber\" housenumber
                ,phone
                ,email
                ,website
                ,url
                ,wikipedia
                ,\"wikipedia:en\" wikipedia_en
                ,\"wikipedia:lt\" wikipedia_lt
                ,\"ref:lt:kpd\" kpd
                ,height
                ,image
                ,distance
                ,maxspeed
                ,lat
                ,lon
                ,uid
            FROM poif
           WHERE uid = $id";
$res = pg_query($link, $query);

//echo "{$query}\n";
while ($row = pg_fetch_assoc($res)) {
  if (!empty($row['name'])) {
    echo "<h4>{$row['name']}</h4>\n";
  } else {
    echo "<h4>{$row['type']}</h4>\n";
  }

  if (!empty($row['alt_name'])) {
    echo "<p><i>Kiti pavadinimai:</i> {$row['alt_name']}</p>\n";
  }

  if (!empty($row['official_name'])) {
    echo "<p><i>Oficialus pavadinimas:</i> {$row['official_name']}</p>\n";
  }

  if (!empty($row['description'])) {
    echo "<p><i class=\"material-icons-black\">info_outline</i> {$row['description']}</p>\n";
  }

  if (!empty($row['information'])) {
    echo "<p><i class=\"material-icons-black\">content_paste</i> {$row['information']}</p>\n";
  }

  if (!empty($row['opening_hours'])) {
    echo "<p><i class=\"material-icons-black\">schedule</i> {$row['opening_hours']}</p>\n";
  }

  if (!empty($row['city']) || !empty($row['street']) || !empty($row['housenumber'])) {
    echo "<p><i class=\"material-icons-black\">contact_mail</i> {$row['city']} {$row['postcode']} {$row['street']} {$row['housenumber']}</p>\n";
  }

  if (!empty($row['phone'])) {
    echo "<p><i class=\"material-icons-black\">call</i> {$row['phone']}</p>\n";
  }

  if (!empty($row['email'])) {
    echo "<p><i class=\"material-icons-black\">mail_outline</i> {$row['email']}</p>\n";
  }

  // Website (according to OSM wiki url tag is deprecated, website tag should be used)
  if (!empty($row['website'])) {
    if (strpos($row['website'], 'http') !== 0) {
      $website = 'http://' . $row['website'];
    } else {
      $website = $row['website'];
    }

    if (strlen($website) > 30) {
      $url_name = 'Svetainė';
    } else {
      $url_name = $website;
    }
    echo "<p><i class=\"material-icons-black\">language</i> <a href=\"{$website}\" target=\" blank\">{$url_name}</a></p>\n";
  }

  if (!empty($row['url'])) {
    if (strpos($row['url'], 'http') !== 0) {
      $website = 'http://' . $row['url'];
    } else {
      $website = $row['url'];
    }

    if (strlen($website) > 30) {
      $url_name = 'Svetainė';
    } else {
      $url_name = $website;
    }
    echo "<p><i class=\"material-icons-black\">language</i> <a href=\"{$website}\" target=\" blank\">{$url_name}</a></p>\n";
  }

  // Wikipedia lt
  if (!empty($row['wikipedia_lt'])) {
    echo "<p><i class=\"material-icons-black\">open_in_browser</i> <a href=\"http://lt.wikipedia.org/wiki/{$row['wikipedia_lt']}\" target=\" blank\">Vikipedija (LT)</a></p>";
  }

  // Wikipedia en
  if (!empty($row['wikipedia_en'])) {
    echo "<p><i class=\"material-icons-black\">open_in_browser</i> <a href=\"http://en.wikipedia.org/wiki/{$row['wikipedia_en']}\" target=\" blank\">Vikipedija (EN)</a></p>";
  }

  // Wikipedia default
  if (!empty($row['wikipedia'])) {
    if (substr($row['wikipedia'], 2, 1) == ":") {
      echo "<p><i class=\"material-icons-black\">open_in_browser</i> <a href=\"http://" .
        substr($row['wikipedia'], 0, 2) . ".wikipedia.org/wiki/" .
        str_replace(' ', '_', substr($row['wikipedia'], 3)) . "\" target=\" blank\">Vikipedija (" .
        substr($row['wikipedia'], 0, 2) . ")</a></p>";
    } else {
      echo "<p><i class=\"material-icons-black\">open_in_browser</i> <a href=\"http://en.wikipedia.org/wiki/{$row['wikipedia']}\" target=\" blank\">Vikipedija (EN)</a></p>";
    }
  }

  // Cultural heritage
  if (!empty($row['kpd'])) {
    echo "<p><i class=\"material-icons-black\">filter_vintage</i> <a href=\"http://kvr.kpd.lt/heritage/Pages/KVRDetail.aspx?lang=lt&MC={$row['kpd']}\" target=\" blank\">Kultūros paveldas</a></p>";
  }

  // Height
  if (!empty($row['height'])) {
    echo "<p><i class=\"material-icons-black\">format_line_spacing</i> <i>Aukštis:</i> {$row['height']}m.</p>";
  }

  // Distance
  if (!empty($row['distance'])) {
    echo "<p><i class=\"material-icons-black\">settings_ethernet</i> <i>Atstumas/ilgis:</i> {$row['distance']} km.</p>";
  }

  // Max-speed
  if (!empty($row['maxspeed'])) {
    echo "<p><i class=\"material-icons-black\">update</i> <i>Maksimalus leidžiamas greitis:</i> {$row['maxspeed']} km/h.</p>";
  }

  // Fee
  if (!empty($row['fee'])) {
        if ($fee != 'no') {
            echo "<p><i class=\"material-icons-black\">attach_modey</i>Apsilankymas mokamas</p>";
        } else {
            echo "<p><i class=\"material-icons-black\">money_off</i>Apsilankymas nemokamas</p>";
        }
    }

/*
    // Reference number
    if ($ref) {
        $description[] = "<i>Reg. Nr.:</i> {$ref}";
    }
*/

  $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
  if (stripos($ua, 'android') !== false) { // && stripos($ua,'mobile') !== false) {
    echo "<p><i class=\"material-icons-black\">room</i> <a href=\"geo:{$row['lon']},{$row['lat']}\">{$row['lon']},{$row['lat']}</a></p>";
  } elseif ((stripos($ua, 'ipad') !== false) || (stripos($ua, 'iphone') !== false)) {
    echo "<p><i class=\"material-icons-black\">room</i> <a href=\"http://maps.apple.com/?ll={$row['lon']},{$row['lat']}\">{$row['lon']},{$row['lat']}</a></p>";
  } else {
    echo "<p><i class=\"material-icons-black\">room</i> {$row['lon']},{$row['lat']}</p>";
  }

  // Image
  if (!empty($row['image'])) {
    echo "<p><img src=\"{$row['image']}\" style=\"max-width: 300px; max-height: 250px;\" alt=\"Iliustracija\"></p>";
  }

  if ($map == "Y") {
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
    $url = $row['path'] . '/' . $url . 'm';
    echo "<p><a href=\"https://places.openmap.lt/$url\">Detalės</a></p>";
  }
}

$ua = strtolower($_SERVER['HTTP_USER_AGENT']);
if ((strpos($ua, 'googlebot') == false) &&
    (strpos($ua, 'bingbot') == false) &&
    (strpos($ua, 'seznambot') == false) &&
    (strpos($ua, 'yandexbot') == false) &&
    (strpos($ua, '12bot') == false) &&
    (strpos($ua, 'dotbot') == false) &&
    (strpos($ua, 'semrushbot') == false)
   ) {
  $query = "update poif set visit_count = coalesce(visit_count, 0) + 1 where uid = $id";
  $res = pg_query($link, $query);
}
pg_close($link);
?>
