create database if not exists feladatbank character set utf8mb4 collate utf8mb4_hungarian_ci;

ALTER DATABASE feladatbank
CHARACTER SET = utf8mb4
COLLATE = utf8mb4_hungarian_ci;

SHOW CREATE database feladatbank;

use feladatbank;


create table if not exists admin_felhasznalok( -- admin felhasználók
    id int primary key auto_increment,
    fnev varchar(50) not null unique,
    jelszo varchar(150) not null,
    ido timestamp default current_timestamp
);


create table session_db( -- session tábla létrehozása, recordok eltárolása memoriában a gyorsabb lekérdezésért
    session_id varchar(256) primary key not null unique,
    session_data varchar(1024),
    created_at timestamp default current_timestamp,
    expires_at timestamp not null
) engine = memory; -- recordok tárolása memóriában a gyorsabb lekérdezésért


create table if not exists kategoria( -- kategória pl.: Python, feladatok csoportja
    id int primary key auto_increment,
    kategoria varchar(50) not null unique,
    ido timestamp default current_timestamp
);




create table if not exists feladat( -- kérdés + válaszok + megoldások csoportja
    id int primary key auto_increment,
    kat_id int not null,
    kerdes text not null,
    ido timestamp default current_timestamp,
    foreign key (kat_id) references kategoria(id) on delete cascade


);


create table if not exists valaszok( -- egy adott feladat válasza/válaszai
    id int primary key auto_increment,
    valasz text not null,
    ido timestamp default current_timestamp

);

create table if not exists megoldasok( -- megoldások
    id int primary key auto_increment,
    megoldas text not null,
    ido timestamp default current_timestamp
);

create table if not exists feladat_valasz( -- válaszok kapcsoló tábla, egy feladat több válasz
    feladat_id int not null,
    valasz_id int not null,
    primary key (feladat_id,valasz_id),
    foreign key (feladat_id) references feladat(id) on delete cascade,
    foreign key (valasz_id) references valaszok(id) on delete cascade

);

create table if not exists feladat_megoldas( -- megoldások kapcsoló tábla, egy feladat több jó megoldás
    feladat_id int not null,
    megoldas_id int not null,
    primary key (feladat_id, megoldas_id),
    foreign key (feladat_id) references feladat(id) on delete cascade,
    foreign key (megoldas_id) references megoldasok(id) on delete cascade
);
