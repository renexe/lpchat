<?php

session_start();

require 'conecta.php';
require 'functions.php';

if (!isset($_POST['resposta'])) {
    $resposta = 0;
} else {
    $resposta = trim($_POST['resposta']);
}

if (isset($_SESSION['id']) && $_SESSION['id'] == 26) {
    // CRIS FRANKLIN
    $usuario = $_SESSION['id'];
} else {
    $usuario = 0;
}

$insert = "
INSERT INTO comentarios(
    nome,
    usuario,
    email,
    comentario,
    resposta,
    ip,
    cookie,
    data
)
VALUES(
    '" . trim($_POST['nome']) . "',
    '" . $usuario . "',
    '" . trim($_POST['email']) . "',
    '" . ucwords(trim($_POST['comentario'])) . "',
    '" . $resposta . "',
    '" . $_SERVER['REMOTE_ADDR'] . "',
    '" . cookie() . "',
    NOW()
)";

if (!mysqli_query($link, $insert)) {
    $return = 0;
    $msg = mysqli_error($link);
} else {
    $return = 1;
    $msg = mysqli_insert_id($link);
}

$response = [
    'return' => $return,
    'msg' => $msg
];

die(json_encode($response));

?>