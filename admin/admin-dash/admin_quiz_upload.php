<?php

require_once("../../session_db/session_db.php");

global $_SESSION_DB;

if ($_SESSION_DB->session_db_status() === false) {
    $_SESSION_DB->session_db_start();
}
if(!isset($_SESSION_DB['authorized']) && !isset($_SESSION_DB['auth_id'])){
    header('Location: /admin');
    exit;
}

?>
<?php require_once "../../conn/conn.php"; global $conn; ?>


<div id="admindash-add">
    <h2 id="admin-add-title">Quiz feltöltése</h2>
    <form action="" method="POST" id="admindash-form">
        <div class="admindash-cat-container">
            <?php

            $sql_cat= "SELECT kategoria.id, kategoria.kategoria FROM kategoria order by kategoria.id ASC"; // kategoria adatok
            $query = $conn->prepare($sql_cat);
            $query->execute();
            $result = $query->get_result();

            echo '<select name="category" id="admindash-cat">';
            echo '<option>Kategória</option>';
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    echo '<option value="'.$row['id'].'">'.$row['kategoria'].'</option>';
                }
            }
            echo '</select>';
            ?>
        </div>
        <div class="admindash-question">
            <input type="text" name="exam-question" id="admindash-question" placeholder="Kérdés">
        </div>

        <div id="admindash-anss">
            <div id="admindash-ans-n">
                <div class="admindash-ans">
                    <input type="text" id="ans-1" name="ans-1" placeholder="Válasz1"><input type="checkbox" class="admindash-ans-sol" name="isSol-1" id="isSol-1"><label for="isSol-1">Megoldás</label>
                </div>
                <div class="admindash-ans"><input type="text" id="ans-2" name="ans-2" placeholder="Válasz2"><input type="checkbox" class="admindash-ans-sol" name="isSol-2" id="isSol-2"><label for="isSol-2">Megoldás</label>
                </div>
            </div>
            <div id="admindash-ans-addrm">
                <input type="button" id="admindash-ans-add" name="ans-add" value="Hozzáadás">
                <input type="button" id="admindash-ans-rm" name="ans-rm" value="Törlés">
            </div>
        </div>
        <input type="submit" name="exam-submit" id="admindash-submit" placeholder="Hozzáadás">
        <div id="admindash-upload-msg">

        </div>
    </form>
</div>
