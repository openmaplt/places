create or replace function transfer_id(p_old_osm_id bigint, p_old_type text, p_new_osm_id bigint, p_new_type text, p_new_change text, p_uid int) returns void as $$
declare
l_dummy text;
begin
  select accept_change(p_old_osm_id, p_old_type, 'D') into l_dummy;
  select accept_change(p_new_osm_id, p_new_type, p_new_change) into l_dummy;
  update poif set uid = p_uid where osm_id = p_new_osm_id and obj_type = p_new_type;
end
$$ language plpgsql;

create or replace function accept_change(p_osm_id bigint, p_obj_type text, p_change text) returns void as $$
declare
l_poi poi_change%rowtype;
begin
  raise notice 'accepting change % % %', p_osm_id, p_obj_type, p_change;
  if (p_change = 'D') then
    raise notice 'deleting poi';
    delete from poif where osm_id = p_osm_id and obj_type = p_obj_type;
  elsif (p_change = 'N') then
    raise notice 'inserting new poi';
    select * into l_poi from poi_change where obj_type = p_obj_type and osm_id = p_osm_id;
    insert into poif (osm_id
                    ,obj_type
                    ,uid
                    ,name
                    ,description
                    ,information
                    ,image
                    ,opening_hours
                    ,phone
                    ,email
                    ,website
                    ,url
                    ,"addr:city"
                    ,"addr:street"
                    ,"addr:postcode"
                    ,"addr:housenumber"
                    ,real_ale
                    ,historic
                    ,man_made
                    ,"tower:type"
                    ,fee
                    ,ref
                    ,wikipedia
                    ,"wikipedia:lt"
                    ,"wikipedia:en"
                    ,height
                    ,alt_name
                    ,"ref:lt:kpd"
                    ,maxspeed
                    ,operator
                    ,tourism
                    ,site_type
                    ,amenity
                    ,fireplace
                    ,highway
                    ,access
                    ,shop
                    ,whitewater
                    ,milestone
                    ,religion
                    ,denomination
                    ,office
                    ,official_name
                    ,distance
                    ,"attraction:type"
                    ,"natural"
                    ,lat
                    ,lon
                    ,way)
              values
                    (l_poi.osm_id
                    ,l_poi.obj_type
                    ,nextval('poi_uid')
                    ,l_poi.name
                    ,l_poi.description
                    ,l_poi.information
                    ,l_poi.image
                    ,l_poi.opening_hours
                    ,l_poi.phone
                    ,l_poi.email
                    ,l_poi.website
                    ,l_poi.url
                    ,l_poi."addr:city"
                    ,l_poi."addr:street"
                    ,l_poi."addr:postcode"
                    ,l_poi."addr:housenumber"
                    ,l_poi.real_ale
                    ,l_poi.historic
                    ,l_poi.man_made
                    ,l_poi."tower:type"
                    ,l_poi.fee
                    ,l_poi.ref
                    ,l_poi.wikipedia
                    ,l_poi."wikipedia:lt"
                    ,l_poi."wikipedia:en"
                    ,l_poi.height
                    ,l_poi.alt_name
                    ,l_poi."ref:lt:kpd"
                    ,l_poi.maxspeed
                    ,l_poi.operator
                    ,l_poi.tourism
                    ,l_poi.site_type
                    ,l_poi.amenity
                    ,l_poi.fireplace
                    ,l_poi.highway
                    ,l_poi.access
                    ,l_poi.shop
                    ,l_poi.whitewater
                    ,l_poi.milestone
                    ,l_poi.religion
                    ,l_poi.denomination
                    ,l_poi.office
                    ,l_poi.official_name
                    ,l_poi.distance
                    ,l_poi."attraction:type"
                    ,l_poi."natural"
                    ,l_poi.lat
                    ,l_poi.lon
                    ,l_poi.way
                    );
  elsif (p_change = 'C') then
    raise notice 'updating poif';
    select * into l_poi from poi_change where obj_type = p_obj_type and osm_id = p_osm_id;
    update poif
       set name = l_poi.name
          ,description = l_poi.description
          ,information = l_poi.information
          ,image = l_poi.image
          ,opening_hours = l_poi.opening_hours
          ,phone = l_poi.phone
          ,email = l_poi.email
          ,website = l_poi.website
          ,url = l_poi.url
          ,"addr:city" = l_poi."addr:city"
          ,"addr:street" = l_poi."addr:street"
          ,"addr:postcode" = l_poi."addr:postcode"
          ,"addr:housenumber" = l_poi."addr:housenumber"
          ,real_ale = l_poi.real_ale
          ,historic = l_poi.historic
          ,man_made = l_poi.man_made
          ,"tower:type" = l_poi."tower:type"
          ,fee = l_poi.fee
          ,ref = l_poi.ref
          ,wikipedia = l_poi.wikipedia
          ,"wikipedia:lt" = l_poi."wikipedia:lt"
          ,"wikipedia:en" = l_poi."wikipedia:en"
          ,height = l_poi.height
          ,alt_name = l_poi.alt_name
          ,"ref:lt:kpd" = l_poi."ref:lt:kpd"
          ,maxspeed = l_poi.maxspeed
          ,operator = l_poi.operator
          ,tourism = l_poi.tourism
          ,site_type = l_poi.site_type
          ,amenity = l_poi.amenity
          ,fireplace = l_poi.fireplace
          ,highway = l_poi.highway
          ,access = l_poi.access
          ,shop = l_poi.shop
          ,whitewater = l_poi.whitewater
          ,milestone = l_poi.milestone
          ,religion = l_poi.religion
          ,denomination = l_poi.denomination
          ,office = l_poi.office
          ,official_name = l_poi.official_name
          ,"attraction:type" = l_poi."attraction:type"
          ,distance = l_poi.distance
          ,"natural" = l_poi."natural"
          ,lat = l_poi.lat
          ,lon = l_poi.lon
          ,way = l_poi.way
     where obj_type = p_obj_type
       and osm_id = p_osm_id;
  end if;
  raise notice ' Deleting processed poi_change';
  delete from poi_change where osm_id = p_osm_id and obj_type = p_obj_type;
end
$$ language plpgsql;

create or replace function process_poi_change() returns text as $$
declare
c record;
l_count integer := 0;
l_poi poif%rowtype;
l_existing integer;
begin
  raise notice 'Starting process_poi_change %', clock_timestamp();
  delete from poi_change;
  update poif set x = null;

  for c in (select osm_id
                  ,'n' obj_type
                  ,name
                  ,description
                  ,information
                  ,image
                  ,opening_hours
                  ,coalesce("contact:phone", phone) phone
                  ,coalesce("contact:email", email) email
                  ,coalesce("contact:website", website) website
                  ,url
                  ,coalesce("contact:city", "addr:city") "addr:city"
                  ,coalesce("contact:street", "addr:street") "addr:street"
                  ,coalesce("contact:postcode", "addr:postcode") "addr:postcode"
                  ,coalesce("contact:housenumber", "addr:housenumber") "addr:housenumber"
                  ,real_ale
                  ,historic
                  ,man_made
                  ,"tower:type"
                  ,fee
                  ,ref
                  ,wikipedia
                  ,"wikipedia:lt"
                  ,"wikipedia:en"
                  ,height
                  ,alt_name
                  ,"ref:lt:kpd"
                  ,maxspeed
                  ,operator
                  ,tourism
                  ,site_type
                  ,amenity
                  ,fireplace
                  ,highway
                  ,access
                  ,shop
                  ,whitewater
                  ,"waterway:milestone" milestone
                  ,religion
                  ,denomination
                  ,office
                  ,official_name
                  ,"attraction:type"
                  ,distance
                  ,"natural"
                  ,round(cast(st_x(st_transform(way, 4326)) as numeric), 4) lat
                  ,round(cast(st_y(st_transform(way, 4326)) as numeric), 4) lon
                  ,st_transform(way, 4326) as way
              from planet_osm_point
             where ((tourism is not null and tourism not in ('artwork') and (tourism != 'information' or information = 'office'))
                or (historic is not null and
                    historic not in ('wayside_cross', 'wayside_shrine', 'boundary_stone', 'yes', 'building', 'tomb', 'memorial') and
                    coalesce(memorial, '!@#') != 'plaque' and coalesce(subitem, 'no') != 'yes')
                or "ref:lt:kpd" is not null
                or (amenity is not null and amenity not in ('parking',
                                                            'parking_space',
                                                            'parking_entrance',
                                                            'bicycle_parking',
                                                            'recycling',
                                                            'waste_disposal',
                                                            'bench',
                                                            'grave_yard',
                                                            'waste_basket',
                                                            'fountain',
                                                            'toilets',
                                                            'post_box',
                                                            'telephone',
                                                            'taxi',
                                                            'drinking_water',
                                                            'stripclub',
                                                            'public_bath',
                                                            'social_centre',
                                                            'vending_machine',
                                                            'marketplace',
                                                            'fire_station',
                                                            'nursing_home',
                                                            'water_point',
                                                            'compressed_air',
                                                            'charging_station',
                                                            'casino',
                                                            'swimming_pool',
                                                            'social_facility',
                                                            'bus_station',
                                                            'customs',
                                                            'public_bookcase',
                                                            'clock',
                                                            'hunting_stand',
                                                            'grit_bin',
                                                            'smoking_area',
                                                            'ferry_terminal',
                                                            'bureau_de_change',
                                                            'ice_cream',
                                                            'shelter',
                                                            'bbq',
                                                            'festival_grounds',
                                                            'dressing_room',
                                                            'waste_transfer_station',
                                                            'ranger_station',
                                                            'driving_school',
                                                            'veterinary',
                                                            'community_centre')
                    and (shelter_type is null or (shelter_type not in ('public_transport', 'lean_to')))
                   )
                or highway = 'speed_camera'
                or (shop is not null and shop != 'pawnbroker')
                or whitewater is not null
                or office is not null
                or "waterway:milestone" is not null
                or ("natural" in ('stone', 'spring', 'tree') and name is not null)
                )
            union all
            select osm_id
                  ,'p'
                  ,name
                  ,description
                  ,information
                  ,image
                  ,opening_hours
                  ,coalesce("contact:phone", phone) phone
                  ,coalesce("contact:email", email) email
                  ,coalesce("contact:website", website) website
                  ,url
                  ,coalesce("contact:city", "addr:city") "addr:city"
                  ,coalesce("contact:street", "addr:street") "addr:street"
                  ,coalesce("contact:postcode", "addr:postcode") "addr:postcode"
                  ,coalesce("contact:housenumber", "addr:housenumber") "addr:housenumber"
                  ,real_ale
                  ,historic
                  ,man_made
                  ,"tower:type"
                  ,fee
                  ,ref
                  ,wikipedia
                  ,"wikipedia:lt"
                  ,"wikipedia:en"
                  ,height
                  ,alt_name
                  ,"ref:lt:kpd"
                  ,maxspeed
                  ,operator
                  ,tourism
                  ,site_type
                  ,amenity
                  ,fireplace
                  ,highway
                  ,access
                  ,shop
                  ,null
                  ,null
                  ,religion
                  ,denomination
                  ,office
                  ,official_name
                  ,null
                  ,null
                  ,"natural"
                  ,round(cast(st_x(st_transform(st_centroid(way), 4326)) as numeric), 4)
                  ,round(cast(st_y(st_transform(st_centroid(way), 4326)) as numeric), 4)
                  ,st_centroid(st_transform(way,4326)) as way
              from planet_osm_polygon
             where ((tourism is not null and tourism not in ('artwork') and (tourism != 'information' or information = 'office'))
                or (historic is not null and
                    historic not in ('wayside_cross', 'wayside_shrine', 'boundary_stone', 'yes', 'building', 'tomb', 'memorial'))
                or "ref:lt:kpd" is not null
                or (amenity is not null and amenity not in ('parking',
                                                            'parking_space',
                                                            'parking_entrance',
                                                            'bicycle_parking',
                                                            'recycling',
                                                            'waste_disposal',
                                                            'bench',
                                                            'grave_yard',
                                                            'waste_basket',
                                                            'fountain',
                                                            'toilets',
                                                            'post_box',
                                                            'telephone',
                                                            'taxi',
                                                            'drinking_water',
                                                            'stripclub',
                                                            'public_bath',
                                                            'social_centre',
                                                            'vending_machine',
                                                            'marketplace',
                                                            'fire_station',
                                                            'nursing_home',
                                                            'water_point',
                                                            'compressed_air',
                                                            'charging_station',
                                                            'casino',
                                                            'swimming_pool',
                                                            'social_facility',
                                                            'bus_station',
                                                            'customs',
                                                            'clock',
                                                            'hunting_stand',
                                                            'grit_bin',
                                                            'smoking_area',
                                                            'ferry_terminal',
                                                            'bureau_de_change',
                                                            'ice_cream',
                                                            'shelter',
                                                            'bbq',
                                                            'festival_grounds',
                                                            'dressing_room',
                                                            'waste_transfer_station',
                                                            'ranger_station',
                                                            'driving_school',
                                                            'veterinary',
                                                            'community_centre')
                    and (shelter_type is null or (shelter_type not in ('public_transport', 'lean_to')))
                   )
                or highway = 'speed_camera'
                or (shop is not null and shop != 'pawnbroker')
                or office is not null
                or ("natural" in ('stone', 'spring', 'tree') and name is not null)
                )
            union all
            select osm_id
                  ,'p'
                  ,name
                  ,description
                  ,information
                  ,image
                  ,opening_hours
                  ,coalesce("contact:phone", phone) phone
                  ,coalesce("contact:email", email) email
                  ,coalesce("contact:website", website) website
                  ,url
                  ,coalesce("contact:city", "addr:city") "addr:city"
                  ,coalesce("contact:street", "addr:street") "addr:street"
                  ,coalesce("contact:postcode", "addr:postcode") "addr:postcode"
                  ,coalesce("contact:housenumber", "addr:housenumber") "addr:housenumber"
                  ,real_ale
                  ,historic
                  ,man_made
                  ,"tower:type"
                  ,fee
                  ,ref
                  ,wikipedia
                  ,"wikipedia:lt"
                  ,"wikipedia:en"
                  ,height
                  ,alt_name
                  ,"ref:lt:kpd"
                  ,maxspeed
                  ,operator
                  ,tourism
                  ,site_type
                  ,amenity
                  ,fireplace
                  ,highway
                  ,access
                  ,shop
                  ,null
                  ,null
                  ,religion
                  ,denomination
                  ,office
                  ,official_name
                  ,null
                  ,null
                  ,"natural"
                  ,round(cast(st_x(st_transform(st_centroid(way), 4326)) as numeric), 4)
                  ,round(cast(st_y(st_transform(st_centroid(way), 4326)) as numeric), 4)
                  ,st_centroid(st_transform(way,4326)) as way
              from planet_osm_line
             where ((tourism is not null and tourism not in ('artwork') and (tourism != 'information' or information = 'office'))
                or (historic is not null and
                    historic not in ('wayside_cross', 'wayside_shrine', 'boundary_stone', 'yes', 'building', 'tomb', 'memorial'))
                or "ref:lt:kpd" is not null
                or (amenity is not null and amenity not in ('parking',
                                                            'parking_space',
                                                            'parking_entrance',
                                                            'bicycle_parking',
                                                            'recycling',
                                                            'waste_disposal',
                                                            'bench',
                                                            'grave_yard',
                                                            'waste_basket',
                                                            'fountain',
                                                            'toilets',
                                                            'post_box',
                                                            'telephone',
                                                            'taxi',
                                                            'drinking_water',
                                                            'stripclub',
                                                            'public_bath',
                                                            'social_centre',
                                                            'vending_machine',
                                                            'marketplace',
                                                            'fire_station',
                                                            'nursing_home',
                                                            'water_point',
                                                            'compressed_air',
                                                            'charging_station',
                                                            'casino',
                                                            'swimming_pool',
                                                            'social_facility',
                                                            'bus_station',
                                                            'customs',
                                                            'clock',
                                                            'hunting_stand',
                                                            'grit_bin',
                                                            'smoking_area',
                                                            'ferry_terminal',
                                                            'bureau_de_change',
                                                            'ice_cream',
                                                            'shelter',
                                                            'bbq',
                                                            'festival_grounds',
                                                            'dressing_room',
                                                            'waste_transfer_station',
                                                            'ranger_station',
                                                            'driving_school',
                                                            'veterinary',
                                                            'community_centre')
                    and (shelter_type is null or (shelter_type not in ('public_transport', 'lean_to')))
                   )
                or highway = 'speed_camera'
                or (shop is not null and shop != 'pawnbroker')
                or office is not null)
            ) loop
    --raise notice 'Found osm_id % of type %', c.obj_type, c.osm_id;
    l_count := l_count + 1;
    if l_count % 1000 = 0 then
      raise notice '%', l_count;
    end if;

    select count(1)
      into l_existing
      from poif
     where osm_id = c.osm_id
       and obj_type = c.obj_type;
    --raise notice 'Found existing poi type %, count %', c.obj_type, l_existing;

    if l_existing = 1 then
      update poif set x = 'Y' where osm_id = c.osm_id and obj_type = c.obj_type;

      --raise notice 'Pre name=% obj=% %', l_poi.name, c.obj_type, c.osm_id;
      select p.*
        into l_poi
        from poif p
       where p.osm_id = c.osm_id
         and p.obj_type = c.obj_type;
      --raise notice 'Existing name=% new name=%', l_poi.name, c.name;

      if coalesce(c.name, '@') != coalesce(l_poi.name, '@') or
         coalesce(c.description, '@') != coalesce(l_poi.description, '@') or
         coalesce(c.information, '@') != coalesce(l_poi.information, '@') or
         coalesce(c.image, '@') != coalesce(l_poi.image, '@') or
         coalesce(c.opening_hours, '@') != coalesce(l_poi.opening_hours, '@') or
         coalesce(c.phone, '@') != coalesce(l_poi.phone, '@') or
         coalesce(c.email, '@') != coalesce(l_poi.email, '@') or
         coalesce(c.website, '@') != coalesce(l_poi.website, '@') or
         coalesce(c.url, '@') != coalesce(l_poi.url, '@') or
         coalesce(c."addr:city", '@') != coalesce(l_poi."addr:city", '@') or
         coalesce(c."addr:street", '@') != coalesce(l_poi."addr:street", '@') or
         coalesce(c."addr:postcode", '@') != coalesce(l_poi."addr:postcode", '@') or
         coalesce(c."addr:housenumber", '@') != coalesce(l_poi."addr:housenumber", '@') or
         coalesce(c.real_ale, '@') != coalesce(l_poi.real_ale, '@') or
         coalesce(c.historic, '@') != coalesce(l_poi.historic, '@') or
         coalesce(c.man_made, '@') != coalesce(l_poi.man_made, '@') or
         coalesce(c."tower:type", '@') != coalesce(l_poi."tower:type", '@') or
         coalesce(c.fee, '@') != coalesce(l_poi.fee, '@') or
         coalesce(c.ref, '@') != coalesce(l_poi.ref, '@') or
         coalesce(c.wikipedia, '@') != coalesce(l_poi.wikipedia, '@') or
         coalesce(c."wikipedia:lt", '@') != coalesce(l_poi."wikipedia:lt", '@') or
         coalesce(c."wikipedia:en", '@') != coalesce(l_poi."wikipedia:en", '@') or
         coalesce(c.height, '@') != coalesce(l_poi.height, '@') or
         coalesce(c.alt_name, '@') != coalesce(l_poi.alt_name, '@') or
         coalesce(c."ref:lt:kpd", '@') != coalesce(l_poi."ref:lt:kpd", '@') or
         coalesce(c.maxspeed, '@') != coalesce(l_poi.maxspeed, '@') or
         coalesce(c.operator, '@') != coalesce(l_poi.operator, '@') or
         coalesce(c.tourism, '@') != coalesce(l_poi.tourism, '@') or
         coalesce(c.site_type, '@') != coalesce(l_poi.site_type, '@') or
         coalesce(c.amenity, '@') != coalesce(l_poi.amenity, '@') or
         coalesce(c.fireplace, '@') != coalesce(l_poi.fireplace, '@') or
         coalesce(c.highway, '@') != coalesce(l_poi.highway, '@') or
         coalesce(c.access, '@') != coalesce(l_poi.access, '@') or
         coalesce(c.shop, '@') != coalesce(l_poi.shop, '@') or
         coalesce(c.whitewater, '@') != coalesce(l_poi.whitewater, '@') or
         coalesce(c.milestone, '@') != coalesce(l_poi.milestone, '@') or
         coalesce(c.religion, '@') != coalesce(l_poi.religion, '@') or
         coalesce(c.denomination, '@') != coalesce(l_poi.denomination, '@') or
         coalesce(c.office, '@') != coalesce(l_poi.office, '@') or
         coalesce(c.official_name, '@') != coalesce(l_poi.official_name, '@') or
         coalesce(c."attraction:type", '@') != coalesce(l_poi."attraction:type", '@') or
         coalesce(c.distance, '@') != coalesce(l_poi.distance, '@') or
         coalesce(c."natural", '@') != coalesce(l_poi."natural", '@') or
         st_distance(c.way::geography, l_poi.way::geography) > 10
      then
        raise notice 'POI % % data has changed! Distance: %m', c.obj_type, c.osm_id, round(st_distance(c.way::geography, l_poi.way::geography));
        insert into poi_change(osm_id
                              ,obj_type
                              ,uid
                              ,name
                              ,description
                              ,information
                              ,image
                              ,opening_hours
                              ,phone
                              ,email
                              ,website
                              ,url
                              ,"addr:city"
                              ,"addr:street"
                              ,"addr:postcode"
                              ,"addr:housenumber"
                              ,real_ale
                              ,historic
                              ,man_made
                              ,"tower:type"
                              ,fee
                              ,ref
                              ,wikipedia
                              ,"wikipedia:lt"
                              ,"wikipedia:en"
                              ,height
                              ,alt_name
                              ,"ref:lt:kpd"
                              ,maxspeed
                              ,operator
                              ,tourism
                              ,site_type
                              ,amenity
                              ,fireplace
                              ,highway
                              ,access
                              ,shop
                              ,whitewater
                              ,milestone
                              ,religion
                              ,denomination
                              ,office
                              ,official_name
                              ,"attraction:type"
                              ,distance
                              ,"natural"
                              ,lat
                              ,lon
                              ,x_type
                              ,way
                              )
                       values (c.osm_id
                              ,c.obj_type
                              ,l_poi.uid
                              ,c.name
                              ,c.description
                              ,c.information
                              ,c.image
                              ,c.opening_hours
                              ,c.phone
                              ,c.email
                              ,c.website
                              ,c.url
                              ,c."addr:city"
                              ,c."addr:street"
                              ,c."addr:postcode"
                              ,c."addr:housenumber"
                              ,c.real_ale
                              ,c.historic
                              ,c.man_made
                              ,c."tower:type"
                              ,c.fee
                              ,c.ref
                              ,c.wikipedia
                              ,c."wikipedia:lt"
                              ,c."wikipedia:en"
                              ,c.height
                              ,c.alt_name
                              ,c."ref:lt:kpd"
                              ,c.maxspeed
                              ,c.operator
                              ,c.tourism
                              ,c.site_type
                              ,c.amenity
                              ,c.fireplace
                              ,c.highway
                              ,c.access
                              ,c.shop
                              ,c.whitewater
                              ,c.milestone
                              ,c.religion
                              ,c.denomination
                              ,c.office
                              ,c.official_name
                              ,c."attraction:type"
                              ,c.distance
                              ,c."natural"
                              ,c.lat
                              ,c.lon
                              ,'C'
                              ,c.way
                              );
      end if;
    else
      raise notice 'NEW POI % %!', c.obj_type, c.osm_id;
        insert into poi_change(osm_id
                              ,obj_type
                              ,name
                              ,description
                              ,information
                              ,image
                              ,opening_hours
                              ,phone
                              ,email
                              ,website
                              ,url
                              ,"addr:city"
                              ,"addr:street"
                              ,"addr:postcode"
                              ,"addr:housenumber"
                              ,real_ale
                              ,historic
                              ,man_made
                              ,"tower:type"
                              ,fee
                              ,ref
                              ,wikipedia
                              ,"wikipedia:lt"
                              ,"wikipedia:en"
                              ,height
                              ,alt_name
                              ,"ref:lt:kpd"
                              ,maxspeed
                              ,operator
                              ,tourism
                              ,site_type
                              ,amenity
                              ,fireplace
                              ,highway
                              ,access
                              ,shop
                              ,whitewater
                              ,milestone
                              ,religion
                              ,denomination
                              ,office
                              ,official_name
                              ,"attraction:type"
                              ,distance
                              ,"natural"
                              ,lat
                              ,lon
                              ,x_type
                              ,way
                              )
                       values (c.osm_id
                              ,c.obj_type
                              ,c.name
                              ,c.description
                              ,c.information
                              ,c.image
                              ,c.opening_hours
                              ,c.phone
                              ,c.email
                              ,c.website
                              ,c.url
                              ,c."addr:city"
                              ,c."addr:street"
                              ,c."addr:postcode"
                              ,c."addr:housenumber"
                              ,c.real_ale
                              ,c.historic
                              ,c.man_made
                              ,c."tower:type"
                              ,c.fee
                              ,c.ref
                              ,c.wikipedia
                              ,c."wikipedia:lt"
                              ,c."wikipedia:en"
                              ,c.height
                              ,c.alt_name
                              ,c."ref:lt:kpd"
                              ,c.maxspeed
                              ,c.operator
                              ,c.tourism
                              ,c.site_type
                              ,c.amenity
                              ,c.fireplace
                              ,c.highway
                              ,c.access
                              ,c.shop
                              ,c.whitewater
                              ,c.milestone
                              ,c.religion
                              ,c.denomination
                              ,c.office
                              ,c.official_name
                              ,c."attraction:type"
                              ,c.distance
                              ,c."natural"
                              ,c.lat
                              ,c.lon
                              ,'N'
                              ,c.way
                              );
    end if;
  end loop;
  raise notice 'Done, total number of POI - %', l_count;

  for c in (select *
              from poif
             where x is null) loop
    raise notice 'DELTED POI % %', c.obj_type, c.osm_id;
    insert into poi_change(osm_id
                          ,obj_type
                          ,uid
                          ,x_type
                          ,way
                          )
                   values (c.osm_id
                          ,c.obj_type
                          ,c.uid
                          ,'D'
                          ,c.way
                          );
  end loop;

  return 'OK';
end
$$ language plpgsql;

select process_poi_change();
