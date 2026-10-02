-- Step 1: Add temporary column
ALTER TABLE kategoria ADD COLUMN new_id INT;

-- Step 2: Assign new sequential IDs
SET @row_number = 0;
UPDATE kategoria
SET new_id = (@row_number := @row_number + 1)
ORDER BY id;

-- Step 3: Update foreign key in child table `feladat`
ALTER TABLE feladat DROP FOREIGN KEY feladat_ibfk_1; -- Drop FK constraint
UPDATE feladat f
JOIN kategoria k ON f.kat_id = k.id
SET f.kat_id = k.new_id;

-- Step 4: Replace old ID with new ID
ALTER TABLE kategoria DROP COLUMN id;
ALTER TABLE kategoria CHANGE COLUMN new_id id INT AUTO_INCREMENT PRIMARY KEY;

-- Step 5: Restore foreign key
ALTER TABLE feladat
ADD FOREIGN KEY (kat_id) REFERENCES kategoria(id) ON DELETE CASCADE;

-- Step 1: Add temporary column
ALTER TABLE feladat ADD COLUMN new_id INT;

-- Step 2: Assign new sequential IDs
SET @row_number = 0;
UPDATE feladat
SET new_id = (@row_number := @row_number + 1)
ORDER BY id;

-- Step 3: Update junction tables
-- Drop foreign keys first
ALTER TABLE feladat_valasz DROP FOREIGN KEY feladat_valasz_ibfk_1;
ALTER TABLE feladat_megoldas DROP FOREIGN KEY feladat_megoldas_ibfk_1;

-- Update IDs in junction tables
UPDATE feladat_valasz fv
JOIN feladat f ON fv.feladat_id = f.id
SET fv.feladat_id = f.new_id;

UPDATE feladat_megoldas fm
JOIN feladat f ON fm.feladat_id = f.id
SET fm.feladat_id = f.new_id;

-- Step 4: Replace old ID with new ID
ALTER TABLE feladat DROP COLUMN id;
ALTER TABLE feladat CHANGE COLUMN new_id id INT AUTO_INCREMENT PRIMARY KEY;

-- Step 5: Restore foreign keys
ALTER TABLE feladat_valasz
ADD FOREIGN KEY (feladat_id) REFERENCES feladat(id) ON DELETE CASCADE;

ALTER TABLE feladat_megoldas
ADD FOREIGN KEY (feladat_id) REFERENCES feladat(id) ON DELETE CASCADE;

-- Step 1: Add temporary column
ALTER TABLE valaszok ADD COLUMN new_id INT;

-- Step 2: Assign new sequential IDs
SET @row_number = 0;
UPDATE valaszok
SET new_id = (@row_number := @row_number + 1)
ORDER BY id;

-- Step 3: Update junction table
ALTER TABLE feladat_valasz DROP FOREIGN KEY feladat_valasz_ibfk_2;
UPDATE feladat_valasz fv
JOIN valaszok v ON fv.valasz_id = v.id
SET fv.valasz_id = v.new_id;

-- Step 4: Replace old ID with new ID
ALTER TABLE valaszok DROP COLUMN id;
ALTER TABLE valaszok CHANGE COLUMN new_id id INT AUTO_INCREMENT PRIMARY KEY;

-- Step 5: Restore foreign key
ALTER TABLE feladat_valasz
ADD FOREIGN KEY (valasz_id) REFERENCES valaszok(id) ON DELETE CASCADE;

-- Step 1: Add temporary column
ALTER TABLE megoldasok ADD COLUMN new_id INT;

-- Step 2: Assign new sequential IDs
SET @row_number = 0;
UPDATE megoldasok
SET new_id = (@row_number := @row_number + 1)
ORDER BY id;

-- Step 3: Update junction table
ALTER TABLE feladat_megoldas DROP FOREIGN KEY feladat_megoldas_ibfk_2;
UPDATE feladat_megoldas fm
JOIN megoldasok m ON fm.megoldas_id = m.id
SET fm.megoldas_id = m.new_id;

-- Step 4: Replace old ID with new ID
ALTER TABLE megoldasok DROP COLUMN id;
ALTER TABLE megoldasok CHANGE COLUMN new_id id INT AUTO_INCREMENT PRIMARY KEY;

-- Step 5: Restore foreign key
ALTER TABLE feladat_megoldas
ADD FOREIGN KEY (megoldas_id) REFERENCES megoldasok(id) ON DELETE CASCADE;

ALTER TABLE kategoria AUTO_INCREMENT = (SELECT MAX(id) + 1 FROM kategoria);
ALTER TABLE feladat AUTO_INCREMENT = (SELECT MAX(id) + 1 FROM feladat);
ALTER TABLE valaszok AUTO_INCREMENT = (SELECT MAX(id) + 1 FROM valaszok);
ALTER TABLE megoldasok AUTO_INCREMENT = (SELECT MAX(id) + 1 FROM megoldasok);