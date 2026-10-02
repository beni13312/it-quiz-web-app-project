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

<div id="admindash-manage">
    <h2 id="admindash-manage-title"><span></span> Quizek kezelése</h2>

    <div id="admin-quiz-manage-container">
        <div id="admin-quiz-manage-category">
            <?php
            $sql = "SELECT kategoria.id, kategoria.kategoria FROM kategoria ORDER BY id ASC"; // kategóriák megjelenítése
            $query = $conn->prepare($sql);
            $query->execute();
            $result = $query->get_result();

            if($result->num_rows === 0){
                echo "Nem található elem!";
            }else{
                while($row = $result->fetch_assoc()){
                    echo "<div class='admin-quiz-manage-category-row' id='".$row['id']."'>".$row['kategoria']."</div>";
                }
            }

            ?>

        </div>
        <div id="admin-quiz-manage-table-wrapper">
            <table id="admin-quiz-manage-table">
                <thead id="admin-quiz-manage-table-head">
                    <tr>
                        <th>Kérdés</th>
                        <th>Válaszok</th>
                        <th>Megoldások</th>
                        <th>Törlés</th>
                    </tr>
                </thead>
                <tbody id="admin-quiz-manage-table-body">

                </tbody>

            </table>
        </div>

    </div>
    <div id="admin-quiz-manage-msg">

    </div>



</div>