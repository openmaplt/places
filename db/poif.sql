drop table poif;
create table poif (
osm_id bigint,
obj_type text,
uid int,
name text,
description text,
information text,
image text,
opening_hours text,
phone text,
email text,
website text,
url text,
"addr:city" text,
"addr:street" text,
"addr:postcode" text,
"addr:housenumber" text,
real_ale text,
historic text,
man_made text,
"tower:type" text,
fee text,
ref text,
wikipedia text,
"wikipedia:lt" text,
"wikipedia:en" text,
height text,
alt_name text,
"ref:lt:kpd" text,
maxspeed text,
operator text,
tourism text,
site_type text,
amenity text,
fireplace text,
highway text,
access text,
shop text,
whitewater text,
milestone text,
religion text,
denomination text,
office text,
type0 text,
type1 text,
lat real,
lon real,
x text
);

select addgeometrycolumn('poi', 'way', 3857, 'POINT', 2);
grant select on poif to tomas;

insert into poif (
  select osm_id
        ,'n'
        ,nextval('poi_uid')
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
        ,null -- maxspeed
        ,operator
        ,tourism
        ,site_type
        ,amenity
        ,fireplace
        ,highway
        ,access
        ,shop
        ,whitewater
        ,"waterway:milestone"
        ,religion
        ,denomination
        ,office
        ,st_x(st_transform(way, 4267))
        ,st_y(st_transform(way, 4267))
        ,null
        ,way
    from planet_osm_point
   where tourism is not null
      or (historic is not null and historic not in ('wayside_cross'))
      or "ref:lt:kpd" is not null
      or (amenity is not null and amenity not in ('parking', 'recycling', 'waste_disposal', 'bench', 'grave_yard', 'waste_basket', 'fountain'))
      or highway = 'speed_camera'
      or shop is not null
      or whitewater is not null
      or office is not null
  union all
  select osm_id
        ,'p'
        ,nextval('poi_uid')
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
        ,null
        ,null
        ,religion
        ,denomination
        ,office
        ,st_x(st_transform(st_centroid(way), 4267))
        ,st_y(st_transform(st_centroid(way), 4267))
        ,null
        ,st_centroid(way)
    from planet_osm_polygon
   where tourism is not null
      or (historic is not null and historic not in ('wayside_cross'))
      or "ref:lt:kpd" is not null
      or (amenity is not null and amenity not in ('parking', 'recycling', 'waste_disposal', 'bench', 'grave_yard', 'waste_basket', 'fountain'))
      or highway = 'speed_camera'
      or shop is not null
      or office is not null
);
create unique index poi_index on poif (osm_id, obj_type);
