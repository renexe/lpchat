<?php
ini_set('display_errors', 'ON');
error_reporting(E_ALL | E_STRICT);
require 'session.php';
$comentarios_por_pagina = 50;
if ($_SESSION['id'] == 26) {
    $nav_responder_tab = '';
//    $nav_responder_tab = '
//    <a class="nav-item nav-link" id="nav-responder-tab" data-toggle="tab" href="#nav-responder" role="tab" aria-controls="nav-profile" aria-selected="false">
//        <small>
//            <strong>Responder</strong>
//        </small>
//    </a>';
    $mostra_responder = True;
} else {
    $nav_responder_tab = '';
    $mostra_responder = False;
}

?>

<!doctype html>
<html lang="pt-br">
    <head>

        <title>Moderação de comentários</title>
        
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    </head>
    <body>

        <section class="container-fluid">
            <div class="row bg-light py-3">
                <div class="col-8 pr-0 my-auto text-center">
                    <nav>
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                            <a class="nav-item nav-link active" id="nav-moderar-tab" data-toggle="tab" href="#nav-moderar" role="tab" aria-controls="nav-home" aria-selected="true">
                                <small>
                                    <strong>Moderar</strong>
                                </small>
                            </a>
                            <?=$nav_responder_tab?>
                        </div>
                    </nav>
                </div>
                <div class="col-4 pl-0 text-right my-auto">
                    <a href="logout.php" class="btn btn-outline-danger">
                        <small>Logout</small>
                    </a>
                </div>
            </div>
        </section>
        
        <div class="tab-content" id="nav-tabContent">
            <div class="tab-pane fade show active" id="nav-moderar" role="tabpanel" aria-labelledby="nav-moderar-tab"></div>
            <div class="tab-pane fade" id="nav-responder" role="tabpanel" aria-labelledby="nav-responder-tab"></div>
        </div>
        
        <?php require '../footer.php'; ?>

    </body>
</html>