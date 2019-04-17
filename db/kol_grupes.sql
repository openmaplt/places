drop table kol_grupes;
create table kol_grupes (
  id integer
 ,pavadinimas text
 ,image text
 ,kodas text
 ,sort integer
);
insert into kol_grupes values (1,  'Lankytinos vietos',          'footprint_.png', 'h', 1);
insert into kol_grupes values (2,  'Vaizdingos vietos',          'viewpoint_.png', 'W', 2);
insert into kol_grupes values (14, 'Pažintiniai takai',          'hiking_.png', '1',    3);
insert into kol_grupes values (3,  'Piliakalniai',               'hillfort_.png', 'b',  4);
insert into kol_grupes values (4,  'Paveldas',                   'paveldas_.png', 'c',  5);
insert into kol_grupes values (5,  'Dvarai',                     'dvarai_.png', 'f',    6);
insert into kol_grupes values (6,  'Kiti istoriniai',            'ruins_.png', 'a',     7);
insert into kol_grupes values (7,  'Bokštai',                    'tower_.png', 'g',     8);
insert into kol_grupes values (8,  'Muziejai',                   'museum_.png', 'i',    9);
insert into kol_grupes values (9,  'Katalikų bažnyčios',         'cathedral_.png', 'J', 10);
insert into kol_grupes values (10, 'Evangelikų bažnyčios',       'lutheran_.png', 'K',  11);
insert into kol_grupes values (11, 'Cerkvės',                    'orthodox_.png', 'L',  12);
insert into kol_grupes values (12, 'Kitų religijų maldos namai', 'prayer_.png', 'M',    13);
insert into kol_grupes values (13, 'Vienuolynai',                'convent_.png', 'X',   14);
