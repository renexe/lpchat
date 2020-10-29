<?php

require 'conecta.php';
require 'functions.php';

$cookie_id = cookie();

$select = "
SELECT
    id
FROM
    like_dislike
WHERE
    comentario = '" . $_POST['id'] . "'
    AND cookie = '" . $cookie_id . "'
LIMIT 1";
if ($sql = mysqli_query($link, $select)) {
    if ($_POST['like'] == 1) {
        $value = "1";
    } else if ($_POST['dislike'] == 1) {
        $value = "2";
    } else {
        $value = 0;
    }
    $total = mysqli_num_rows($sql);
    if ($_POST['like'] == 1 || $_POST['dislike'] == 1) {
        // DANDO LIKE OU DISLIKE
        if ($_POST['like'] == 1) {
            // DANDO LIKE
            $value = "1";
        } else {
            // DANDO DISLIKE
            $value = "2";
        }
        if ($total > 0) {
            // JÁ DEU LIKE OU DISLIKE NO COMENTÁRIO
            $update_insert = "
            UPDATE
                like_dislike
            SET
                like_dislike = '" . $value . "'
            WHERE
                comentario = '" . $_POST['id'] . "'
                AND cookie = '" . $cookie_id . "'
            LIMIT 1";
        } else {
            // NUNCA DEU LIKE OU DISLIKE NO COMENTÁRIO
            $update_insert = "
            INSERT INTO like_dislike(
                comentario,
                like_dislike,
                cookie
            )
            VALUES(
                '" . $_POST['id'] . "',
                '" . $value . "',
                '" . $cookie_id . "'
            )";
        }
        if (!mysqli_query($link, $update_insert)) {
            die(mysqli_error($link));
        }
    } else if ($_POST['like'] == -1 || $_POST['dislike'] == -1) {
        // REMOVENDO LIKE OU DISLIKE
        if ($total > 0) {
            // JÁ DEU LIKE OU DISLIKE
            $row = mysqli_fetch_assoc($sql);
            $delete = "DELETE FROM like_dislike WHERE id = '" . $row['id'] . "' LIMIT 1";
            if (!mysqli_query($link, $delete)) {
                die(mysqli_error($link));
            }
        }
    }
    
} else {
    die(mysqli_error($link));
}

?>