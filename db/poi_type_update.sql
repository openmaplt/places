create or replace function process_type_update() returns text as $$
declare
c record;
l_count integer := 0;
l_type0 text;
l_type1 text;
begin
  raise notice 'Starting process_type_update %', clock_timestamp();
  for c in (select * from poif) loop
    l_type0 = '';
    l_type1 = '';
    -- Kiti istoriniai
    if c.historic is not null and
       c.historic not in ('monument', 'memorial', 'wayside_cross', 'wayside_shrine', 'manor') and
       (c.historic != 'archaeological_site' or c.site_type not in ('fortification', 'tumulus')) then
      l_type0 = l_type0 || 'a';
    end if;

    -- Piliakalniai
    if c.historic = 'archaeological_site' and c.site_type = 'fortification' then
      l_type0 = l_type0 || 'b';
    end if;

    -- Paveldas
    if c."ref:lt:kpd" is not null and
       (coalesce(c.historic, '@') != 'archaeological_site' or coalesce(c.site_type, '@') != 'fortification') then
      l_type0 = l_type0 || 'c';
    end if;

    -- Paminklas
    if c.historic in ('monument', 'memorial') then
      l_type0 = l_type0 || 'd';
    end if;

    -- Pilkapiai
    if c.historic = 'archaeological_site' and c.site_type = 'tumulus' then
      l_type0 = l_type0 || 'e';
    end if;

    -- Dvarai
    if c.historic = 'manor' then
      l_type0 = l_type0 || 'f';
    end if;

    -- Bokštai
    if c.man_made = 'tower' and
       c."tower:type" is not null and
       c.tourism in ('attraction', 'viewpoint', 'museum') and
       coalesce(c.access, 'yes') != 'no' then
      l_type0 = l_type0 || 'g';
    end if;

    -- Zooparkai
    if c.tourism in ('attraction', 'theme_park', 'zoo') and
       c.historic is null and
       c."attraction:type" is null then
      l_type0 = l_type0 || 'h';
    end if;

    -- Vaizdinga vieta
    if c.tourism = 'viewpoint' and c.historic is null then
      l_type0 = l_type0 || 'W';
    end if;

    -- Muziejus
    if c.tourism = 'museum' then
      l_type0 = l_type0 || 'i';
    end if;

    -- Poilsiavietė su laužaviete
    if (c.tourism = 'picnic_site' or c.amenity = 'shelter') and c.fireplace = 'yes' then
      l_type0 = l_type0 || 'j';
    end if;

    -- Poilsiavietė be laužavietės
    if (c.tourism = 'picnic_site' or c.amenity = 'shelter') and (c.fireplace is null or c.fireplace = 'no') then
      l_type0 = l_type0 || 'k';
    end if;

    -- Stovyklavietė
    if c.tourism in ('camp_site', 'caravan_site') then
      l_type0 = l_type0 || 'l';
    end if;

    -- Kaimo sodyba ir pan.
    if c.tourism in ('chalet', 'hostel', 'motel', 'guest_house') then
      l_type0 = l_type0 || 'm';
    end if;

    -- Degalinės
    if c.amenity = 'fuel' then
      l_type0 = l_type0 || 'n';
    end if;

    -- Kavinės
    if c.amenity = 'cafe' then
      l_type0 = l_type0 || 'o';
    end if;

    -- Greitas maistas
    if c.amenity = 'fast_food' then
      l_type0 = l_type0 || 'p';
    end if;

    -- Restoranai
    if c.amenity = 'restaurant' then
      l_type0 = l_type0 || 'q';
    end if;

    -- Barai
    if c.amenity in ('pub', 'bar') then
      l_type0 = l_type0 || 'r';
    end if;

    -- Viešbučiai
    if c.tourism = 'hotel' then
      l_type0 = l_type0 || 's';
    end if;

    -- Informacija
    if c.tourism = 'information' then
      l_type0 = l_type0 || 't';
    end if;

    -- Teatrai
    if c.amenity = 'theatre' then
      l_type0 = l_type0 || 'u';
    end if;

    -- Kinoteatrai
    if c.amenity = 'cinema' then
      l_type0 = l_type0 || 'v';
    end if;

    -- Greičio kameros
    if c.highway = 'speed_camera' then
      l_type0 = l_type0 || 'w';
    end if;

    -- Meno centras
    if c.amenity = 'arts_centre' then
      l_type0 = l_type0 || 'x';
    end if;

    -- Bibliotekos
    if c.amenity = 'library' then
      l_type0 = l_type0 || 'y';
    end if;

    -- Ligoninės
    if c.amenity = 'hospital' then
      l_type0 = l_type0 || 'z';
    end if;

    -- Klinikos
    if c.amenity = 'clinic' then
      l_type0 = l_type0 || 'A';
    end if;

    -- Dantistai
    if c.amenity = 'dentist' then
      l_type0 = l_type0 || 'B';
    end if;

    -- Daktarai
    if c.amenity = 'doctors' then
      l_type0 = l_type0 || 'C';
    end if;

    -- Vaistinės
    if c.amenity = 'pharmacy' then
      l_type0 = l_type0 || 'D';
    end if;

    -- Didelės parduotuvės
    if c.shop in ('supermarket', 'mall') then
      l_type0 = l_type0 || 'E';
    end if;

    -- Mažos parduotuvės
    if c.shop = 'convenience' then
      l_type0 = l_type0 || 'F';
    end if;

    -- Servisai
    if c.shop = 'car_repair' then
      l_type0 = l_type0 || 'G';
    end if;

    -- Kioskai
    if c.shop = 'kiosk' then
      l_type0 = l_type0 || 'H';
    end if;

    -- Pasidaryk pats
    if c.shop = 'doityourself' then
      l_type0 = l_type0 || 'I';
    end if;

    -- Katalikų bažnyčios
    if c.amenity = 'place_of_worship' and
       c.religion = 'christian' and
       c.denomination in ('catholic', 'roman_catholic') then
      l_type0 = l_type0 || 'J';
    end if;

    -- Liuteronų bažnyčios
    if c.amenity = 'place_of_worship' and
       c.religion = 'christian' and
       c.denomination in ('lutheran', 'evangelical', 'reformed') then
      l_type0 = l_type0 || 'K';
    end if;

    -- Stačiatikių bažnyčios
    if c.amenity = 'place_of_worship' and
       c.religion = 'christian' and
       c.denomination in ('orthodox', 'old_believers') then
      l_type0 = l_type0 || 'L';
    end if;

    -- Kiti maldos namai
    if c.amenity = 'place_of_worship' and
       (c.religion != 'christian' or
        coalesce(c.denomination, '@') not in ('catholic',
                                              'roman_catholic',
                                              'lutheran',
                                              'evangelical',
                                              'reformed',
                                              'orthodox',
                                              'old_believers')) then
      l_type0 = l_type0 || 'M';
    end if;

    -- Valstybiniai
    if c.office = 'government' or
       c.amenity = 'townhall' then
      l_type0 = l_type0 || 'N';
    end if;

    -- Teismai
    if c.amenity = 'courthouse' then
      l_type0 = l_type0 || 'O';
    end if;

    -- Notarai/advokatai
    if c.office in ('notary', 'lawyer') then
      l_type0 = l_type0 || 'P';
    end if;

    -- Draudikai
    if c.office = 'insurance' then
      l_type0 = l_type0 || 'Y';
    end if;

    -- Kitos kontoros
    if c.office is not null and
       c.office not in ('government', 'notary', 'lawyer', 'insurance') then
      l_type0 = l_type0 || 'Q';
    end if;

    -- Kitos parduotuvės
    if c.shop is not null and
       c.shop not in ('supermarket',
                      'mall',
                      'convenience',
                      'car_repair',
                      'kiosk',
                      'doityourself') then
      l_type0 = l_type0 || 'R';
    end if;

    -- Paštas
    if c.amenity = 'post_office' then
      l_type0 = l_type0 || 'S';
    end if;

    -- Plovykla
    if c.amenity = 'car_wash' then
      l_type0 = l_type0 || 'T';
    end if;

    -- Bankai
    if c.amenity = 'bank' then
      l_type0 = l_type0 || 'U';
    end if;

    -- Bankomatai
    if c.amenity = 'atm' then
      l_type0 = l_type0 || 'V';
    end if;

    -- Vienuolynai
    if c.historic = 'monastery' then
      l_type0 = l_type0 || 'X';
    end if;

    -- Pažintiniai takai
    if c.tourism = 'attraction' and c."attraction:type" = 'hiking_route' then
      l_type0 = l_type0 || '1';
    end if;

    -- Policija
    if c.amenity = 'police' then
      l_type0 = l_type0 || '2';
    end if;

    -- Gamta
    if c."natural" in ('tree', 'stone', 'spring') then
      l_type0 = l_type0 || '3';
    end if;

    --raise notice 'type0=%, type1=%', l_type0, l_type1;
    if l_type0 != coalesce(c.type0, '') or
       l_type1 != coalesce(c.type1, '') then
      l_count = l_count + 1;
      if l_type0 = '' then l_type0 = null; end if;
      if l_type1 = '' then l_type1 = null; end if;
      update poif set type0 = l_type0, type1 = l_type1 where uid = c.uid;
    end if;

  end loop;
  raise notice 'Done, total number updated types - %', l_count;

  return 'OK';
end
$$ language plpgsql;

select process_type_update();
