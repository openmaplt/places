<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta http-equiv="cache-control" content="no-cache">
</head>
<body>
<h2>Lankytinų vietų skaičius</h2>
<table border=1 cellspacing=0>
<tr><th>Tipas</th><th>Skaičius</th></tr>
<?php
$config = require('./config.php');
$link = pg_connect(vsprintf('host=%s port=%u dbname=%s user=%s password=%s', $config['resource']['db']));

$query = "select 'Iš viso' as type
                ,count(1) as count
            from poif
           where historic is not null
              or man_made in ('tower', 'communications_tower', 'windmill')
              or tourism in ('theme_park', 'viewpoint', 'attraction', 'zoo', 'museum', 'caravan_site', 'camp_site', 'picnic_site', 'information')
              or \"natural\" in ('tree', 'stone', 'spring')
              or amenity in ('place_of_worship', 'theatre', 'cinema')
           union all
          select 'Piliakalniai'
                ,count(1)
            from poif
           where historic = 'archaeological_site' and site_type = 'fortification'
           union all
          select 'Alkakalniai'
                ,count(1)
            from poif
           where historic = 'archaeological_site' and site_type = 'sacrificial_site'
           union all
          select 'Pilkapiai'
                ,count(1)
            from poif
           where historic = 'archaeological_site' and site_type = 'tumulus'
           union all
          select 'Senovės gyvenvietės'
                ,count(1)
            from poif
           where historic = 'archaeological_site' and site_type = 'settlement'
           union all
          select 'Dvarai'
                ,count(1)
            from poif
           where historic = 'manor'
           union all
          select 'Kiti istoriniai'
                ,count(1)
            from poif
           where historic is not null
             and not (historic = 'archaeological_site' and site_type in ('fortification', 'sacrificial_site', 'tumulus', 'settlement'))
             and not (historic = 'manor')
           union all
          select 'Bokštai'
                ,count(1)
            from poif
           where man_made in ('tower', 'communications_tower')
           union all
          select 'Vėjo malūnai'
                ,count(1)
            from poif
           where man_made = 'windmill'
           union all
          select 'Vandens malūnai'
                ,count(1)
            from poif
           where man_made = 'watermill'
           union all
          select 'Teminiai parkai'
                ,count(1)
            from poif
           where tourism = 'theme_park'
           union all
          select 'Apžvalgos vietos'
                ,count(1)
            from poif
           where tourism = 'viewpoint'
           union all
          select 'Įdomios vietos'
                ,count(1)
            from poif
           where tourism = 'attraction'
             and coalesce(\"attraction:type\", '!@#') != 'hiking_route'
           union all
          select 'Pažintiniai takai'
                ,count(1)
            from poif
           where tourism = 'attraction'
             and \"attraction:type\" = 'hiking_route'
           union all
          select 'Zoologijos sodai'
                ,count(1)
            from poif
           where tourism = 'zoo'
           union all
          select 'Teatrai'
                ,count(1)
            from poif
           where amenity = 'theatre'
           union all
          select 'Kinoteatrai'
                ,count(1)
            from poif
           where amenity = 'cinema'
           union all
          select 'Muziejai'
                ,count(1)
            from poif
           where tourism = 'museum'
           union all
          select 'Stovyklavietės/poilsiavietės'
                ,count(1)
            from poif
           where tourism in ('caravan_site', 'camp_site', 'picnic_site')
           union all
          select 'Maldos namai'
                ,count(1)
            from poif
           where amenity = 'place_of_worship'
           union all
          select 'Kaimo sodybos, viešbučiai'
                ,count(1)
            from poif
           where tourism in ('guest_house', 'chalet', 'hostel', 'motel', 'hotel')
           union all
          select 'Turizmo informacijos centrai'
                ,count(1)
            from poif
           where tourism in ('information')
           union all
          select 'Medžiai'
                ,count(1)
            from poif
           where \"natural\" = 'tree'
           union all
          select 'Akmenys'
                ,count(1)
            from poif
           where \"natural\" = 'stone'
           union all
          select 'Šaltiniai'
                ,count(1)
            from poif
           where \"natural\" = 'spring'
           union all
          select 'Kultūros vertybių registras'
                ,count(1)
            from poif
           where \"ref:lt:kpd\" is not null
           union all
          select 'Iš viso bet kokių lankytinų vietų'
                ,count(1)
            from poif
         ";

$res_m = pg_query($link, $query);
while ($row_m = pg_fetch_assoc($res_m)) {
  echo "<tr><td>{$row_m['type']}</td><td>{$row_m['count']}</td></tr>\n";
}
echo "</table>\n";

pg_close($link);
?>
</body>
