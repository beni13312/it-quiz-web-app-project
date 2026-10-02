use feladatbank;

delete from feladat_valasz
where feladat_id = (select feladat.id from feladat where feladat.kerdes = 'Mi a helyes mód egy szöveg kiíratására PHP-ban?');

delete from feladat_megoldas
where feladat_id = (select feladat.id from feladat where feladat.kerdes = 'Mi a helyes mód egy szöveg kiíratására PHP-ban?');

delete from feladat
where kerdes = 'Mi a helyes mód egy szöveg kiíratására PHP-ban?';


START TRANSACTION;

-- Delete feladat (this auto-deletes from junction tables)
DELETE FROM feladat WHERE id = ?;

-- Now delete valaszok/megoldasok that are not used anymore
DELETE FROM valaszok
WHERE id NOT IN (SELECT valasz_id FROM feladat_valasz);

DELETE FROM megoldasok
WHERE id NOT IN (SELECT megoldas_id FROM feladat_megoldas);

COMMIT;
