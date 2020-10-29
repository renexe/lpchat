<?php
// Função utilizada pelo Ajax para gravar uma resposta feita por um usuário logado
require 'session.php';
require '../functions.php';
require '../conecta.php';

$resposta = $_REQUEST['resposta'];
$comentario = $_REQUEST['comentario'];
$usuario = $_SESSION['id'];
$nome = $_SESSION['usuario'];
$email = $_SESSION['email'];
$foto = $_SESSION['foto'];
$insert = "
INSERT INTO comentarios(
    nome,
    usuario,
    email,
    comentario,
    resposta,
    ip,
    cookie,
    aprovado,
    data
)
VALUES(
    '" . trim($nome) . "',
    '" . $usuario . "',
    '" . trim($email) . "',
    '" . ucwords(trim($comentario)) . "',
    '" . $resposta . "',
    '" . $_SERVER['REMOTE_ADDR'] . "',
    '" . cookie() . "',
    '1',
    NOW()
)";

if (!mysqli_query($link, $insert)) {
    $return = 0;
    $msg = mysqli_error($link);
} else {
    $return = 1;
    $msg = '';
}

$response = [
    'return' => $return,
    'msg' => $msg
];

$json = json_encode($response);
echo $json;
