-- összess mező
select kategoria.kategoria, feladat.kerdes, valaszok.valasz, megoldasok.megoldas
from feladatbank.kategoria,feladatbank.feladat, feladatbank.valaszok, feladatbank.megoldasok, feladatbank.feladat_valasz, feladatbank.feladat_megoldas
where kategoria.id = feladat.kat_id and
      feladat.id = feladat_valasz.feladat_id and
      feladat.id = feladat_megoldas.feladat_id and
      feladat_valasz.valasz_id = valaszok.id and
      feladat_megoldas.megoldas_id = megoldasok.id;

-- feladat lekérdezés
-- 6 = Python
select feladat.id, feladat.kerdes, valaszok.valasz, megoldasok.megoldas
from feladatbank.feladat, feladatbank.valaszok, feladatbank.megoldasok, feladatbank.feladat_valasz, feladatbank.feladat_megoldas
where feladat.kat_id=6 and
      feladat.id = feladat_valasz.feladat_id and
      feladat.id = feladat_megoldas.feladat_id and
      feladat_valasz.valasz_id = valaszok.id and
      feladat_megoldas.megoldas_id = megoldasok.id
order by feladat.id ASC;

-- kerdések
select feladat.id, feladat.kerdes
from feladatbank.feladat
where feladat.kat_id=6
order by feladat.id ASC;

-- válaszok
select feladat.id,valaszok.valasz
from feladatbank.feladat, feladatbank.valaszok, feladatbank.feladat_valasz
where feladat.kat_id=6 and
      feladat.id = feladat_valasz.feladat_id and
      feladat_valasz.valasz_id = valaszok.id
order by feladat.id ASC;

-- megoldások
select feladat.id, megoldasok.megoldas
from feladatbank.feladat, feladatbank.megoldasok, feladatbank.feladat_megoldas
where feladat.kat_id=6 and
      feladat.id = feladat_megoldas.feladat_id and
      feladat_megoldas.megoldas_id = megoldasok.id
order by feladat.id ASC;


-- a megoldások válasz id ja

select valaszok.id as 'valasz_id', megoldasok.megoldas as 'megoldas_str'
from feladat
join feladat_valasz on feladat.id = feladat_valasz.feladat_id
join feladat_megoldas on feladat.id = feladat_megoldas.feladat_id
join valaszok on feladat_valasz.valasz_id = valaszok.id
join megoldasok on feladat_megoldas.megoldas_id = megoldasok.id
where feladat.id = 6 and
      valaszok.valasz = megoldasok.megoldas;


select *
from feladat
join feladat_valasz on feladat.id = feladat_valasz.feladat_id
join feladat_megoldas on feladat.id = feladat_megoldas.feladat_id
join valaszok on feladat_valasz.valasz_id = valaszok.id
join megoldasok on feladat_megoldas.megoldas_id = megoldasok.id
where feladat.id = 6 and
      valaszok.valasz = megoldasok.megoldas;


SELECT COUNT(megoldasok.id) as 'szam' -- megoldások száma
        FROM feladat
        JOIN feladat_megoldas on feladat.id = feladat_megoldas.feladat_id
        JOIN megoldasok on feladat_megoldas.megoldas_id = megoldasok.id
        WHERE feladat.id = 15;

SELECT valaszok.id, valaszok.valasz -- válaszok kiírása
        FROM feladat
        JOIN feladat_valasz on feladat_valasz.feladat_id = feladat.id
        JOIN valaszok on valaszok.id = feladat_valasz.valasz_id
        WHERE feladat.id = 15;

-- feladat elemeinek lekérdezése

SELECT feladat.id, feladat.kerdes,
    JSON_ARRAYAGG(valaszok.valasz) AS valaszok,
    JSON_ARRAYAGG(megoldasok.megoldas) AS megoldasok
            FROM feladat
            JOIN feladat_valasz ON feladat_valasz.feladat_id = feladat.id
            JOIN feladat_megoldas ON feladat_megoldas.feladat_id = feladat.id
            JOIN valaszok ON valaszok.id = feladat_valasz.valasz_id
            JOIN megoldasok ON megoldasok.id = feladat_megoldas.megoldas_id
            WHERE feladat.kat_id = 6
            GROUP BY feladat.id, feladat.kerdes ORDER BY feladat.id;

SELECT feladat.id FROM feladat WHERE feladat.kat_id = 6 ORDER BY feladat.id; -- összes feladat id egy kategóriában



SELECT session_db.expires_at -- ahol a session lejárati idejéből eltelt 40 perc 60-20
FROM session_db
WHERE TIMESTAMPDIFF(SECOND , NOW(), DATE_SUB(expires_at, INTERVAL 20 MINUTE)) <= 0; -- kisebb vagy egyenlő 0-val
-- vagyis pontosan egyenlő a mostani idővel vagy már több idő eltelt másodperc külömbséggel, differencia a jelenlegi időtől