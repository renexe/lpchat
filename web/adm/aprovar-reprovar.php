<?php

require 'session.php';
require '../conecta.php';

if ($_POST['aprovado'] == 0) {
    // REMOVENDO APROVAÇÃO OU REPROVAÇÃO
    $set = "aprovou = 0,
    data_aprovacao = '0000-00-00 00:00:00'";
} else {
    $set = "aprovou = '" . $_SESSION['id'] . "',
    data_aprovacao = NOW()";
}

$update = "
UPDATE
    comentarios
SET
    aprovado = '" . $_POST['aprovado'] . "',
    " . $set . "
WHERE
    id = '" . $_POST['id'] . "'";
if (mysqli_query($link, $update)) {
    $return = 1;
    $msg = '';
} else {
    $return = 0;
    $msg = mysqli_error($link);
}

$response = [
    'return' => $return,
    'msg' => $msg
];

die(json_encode($response));

?>