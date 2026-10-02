use feladatbank;


/* kategóriák beszúrása */

insert into kategoria (kategoria) value ('HTML');
insert into kategoria (kategoria) value ('CSS');
insert into kategoria (kategoria) value ('JavaScript');
insert into kategoria (kategoria) value ('SQL');
insert into kategoria (kategoria) value ('PHP');
insert into kategoria (kategoria) value ('Python');


/* Példa:
   Python:
   Mi a jele az egysoros kommentnek?
   lehettséges válaszok: // # --
   megoldás: #
   */

set @kategoria = 'Python';
set @valasz1 = '//';
set @valasz2 = '#';
set @valasz3 = '--';
set @kerdes = 'Mi a jele az egysoros kommentnek?';
set @megoldas = '#';


insert into valaszok (valasz) values (@valasz1),(@valasz2),(@valasz3); -- választási lehetőségek

insert into megoldasok (megoldas) value (@megoldas); -- megoldás/megoldások

insert into feladat (kat_id, kerdes) values (
                (select kategoria.id from kategoria where kategoria.kategoria = @kategoria),@kerdes);

insert into feladat_valasz (feladat_id, valasz_id) VALUES
                        (
                        (select feladat.id from feladat where kerdes = @kerdes),
                        (select valaszok.id from valaszok where valasz = @valasz1)
                        ),                        (
                        (select feladat.id from feladat where kerdes = @kerdes),
                        (select valaszok.id from valaszok where valasz = @valasz2)
                        ),                        (
                        (select feladat.id from feladat where kerdes = @kerdes),
                        (select valaszok.id from valaszok where valasz = @valasz3)
                        );
insert into feladat_megoldas (feladat_id, megoldas_id) VALUES (
                        (select feladat.id from feladat where kerdes=@kerdes),
                        (select megoldasok.id from megoldasok where megoldas= @megoldas1)
                        );



-- Két megoldás, négy választási lehetőség

set @kategoria = 'PHP';
set @valasz1 = 'std::cout << "Hello, World!";';
set @valasz3 = 'echo "Hello, World!";';
set @valasz4 = 'print "Hello, World!";';
set @kerdes = 'Mi a helyes mód egy szöveg kiíratására PHP-ban?';
set @megoldas1 = 'echo "Hello, World!";';
set @megoldas2 = 'print "Hello, World!";';


insert into valaszok (valasz) values (@valasz1),(@valasz3),(@valasz4); -- választási lehetőségek

insert into megoldasok (megoldas) values (@megoldas1),(@megoldas2); -- megoldás/megoldások

insert into feladat (kat_id, kerdes) values (
                (select kategoria.id from kategoria where kategoria.kategoria = @kategoria),@kerdes);

insert into feladat_valasz (feladat_id, valasz_id) VALUES
                        (
                        (select feladat.id from feladat where kerdes = @kerdes),
                        (select valaszok.id from valaszok where valasz = @valasz1)
                        ),                        (
                        (select feladat.id from feladat where kerdes = @kerdes),
                        (select valaszok.id from valaszok where valasz = @valasz3)
                        ),                        (
                        (select feladat.id from feladat where kerdes = @kerdes),
                        (select valaszok.id from valaszok where valasz = @valasz4)
                                                  );
insert into feladat_megoldas (feladat_id, megoldas_id) VALUES (
                        (select feladat.id from feladat where kerdes=@kerdes),
                        (select megoldasok.id from megoldasok where megoldas= @megoldas1)
                        ),                        (
                        (select feladat.id from feladat where kerdes=@kerdes),
                        (select megoldasok.id from megoldasok where megoldas= @megoldas2)
                        );