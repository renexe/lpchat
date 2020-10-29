<?php
ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

require 'session.php';
require '../conecta.php';
require '../functions.php';

//This is Cris
if ($_SESSION['id'] == 26) {
    $mostra_responder = True;
} else {
    $mostra_responder = False;
}

$titulo_menu = 'Moderação de comentários';
$dir_menu = '';

$html_pendentes = $html_aprovados = $html_reprovados = $display_sem_comentarios_pendentes = $display_sem_comentarios_aprovados = $display_sem_comentarios_reprovados = '';
$pendentes = $aprovados = $reprovados = 0;

// BUSCA OS COMENTÁRIOS
$select_comentarios = "
SELECT
    id,
    nome,
    comentario,
    aprovado,
    data
FROM
    comentarios
ORDER BY
    id DESC";
if ($sql_comentarios = mysqli_query($link, $select_comentarios)) {
    if (mysqli_num_rows($sql_comentarios) > 0) {
        while ($row_comentarios = mysqli_fetch_assoc($sql_comentarios)) {
            if ($row_comentarios['aprovado'] == 0) {
                $display_aprovar_reprovar = '';
                $display_cancelar = 'display: none;';
                $display_responder = '';
            }else if($row_comentarios['aprovado'] == 2){
                $display_responder = 'display: none;';
            }else {
                $display_aprovar_reprovar = 'display: none;';
                $display_cancelar = '';
                $display_responder = '';
            }
            $html = '
        <div class="row mt-3 rounded shadow bg-light py-2 mx-0 row-comentario" data-id="' . $row_comentarios['id'] . '">
            <div class="col">
                <div class="row">
                    <div class="col-auto text-primary">
                        <small>
                            <strong>' . $row_comentarios['nome'] . '</strong>
                        </small>
                    </div>
                    <div class="col">
                        <small>' . horas($row_comentarios['data']) . '</small>
                    </div>
                </div>
                <div class="row">
                    <div class="col text-left">' . $row_comentarios['comentario'] . '</div>
                </div>
                <div class="row">
                    <div class="col-auto px-2 text-muted" style="' . $display_aprovar_reprovar . '">
                        <div class="aprovar-reprovar cursor-pointer" data-id="' . $row_comentarios['id'] . '" data-aprovado="1">
                            <i class="fa fa-check text-success"></i> <small>Aprovar</small>
                        </div>
                    </div>
                    <div class="col-auto px-2 text-muted" style="' . $display_aprovar_reprovar . '">
                        <div class="aprovar-reprovar cursor-pointer" data-id="' . $row_comentarios['id'] . '" data-aprovado="2">
                            <i class="fa fa-check text-danger"></i> <small>Reprovar</small> 
                        </div>
                    </div>
                    ';
            if ($mostra_responder) {
                $html .= '<div class="col-auto px-2 text-muted" style="' . $display_responder . '">
                        <div class="responder cursor-pointer" id="responder' . $row_comentarios['id'] . '" data-aprovado="0">
                            <i class="text-primary fa fa-comment"></i> <small>Responder</small>
                        </div>
                    </div>';
            }
            $html .= '<div class="col-auto px-2 text-muted" style="' . $display_cancelar . '">
                        <div class="aprovar-reprovar cursor-pointer" data-id="' . $row_comentarios['id'] . '" data-aprovado="0">
                            <i class="fa fa-ban text-danger"></i> <small>Cancelar</small> 
                        </div>
                    </div>
                    <div class="col-auto px-2 text-muted loading-aprovar-reprovar" data-id="' . $row_comentarios['id'] . '" style="display: none;">
                        <span class="spinner-border spinner-border-sm"></span>
                    </div>';
            $html .= '
                </div>
                <div class="row mt-1">
                    <div class="col-xs-12 col-md-8">
                        <textarea id="responder-form' . $row_comentarios['id'] . '" hidden class="responder-form form-control form-control-sm" maxlength="200" placeholder="Escreva uma resposta aqui"></textarea>
                        <button id="responder-btn' . $row_comentarios['id'] . '" hidden class="responder-btn btn btn-secondary mt-1">Enviar</button>
                    </div>
                </div>
            </div>
        </div>
        ';
            if ($row_comentarios['aprovado'] == 0) {
                $html_pendentes .= $html;
                $pendentes++;
            } else if ($row_comentarios['aprovado'] == 1) {
                $html_aprovados .= $html;
                $aprovados++;
            } else if ($row_comentarios['aprovado'] == 2) {
                $html_reprovados .= $html;
                $reprovados++;
            }
        }
    }
} else {
    die(mysqli_error($link));
}

if ($pendentes > 0) {
    $display_sem_comentarios_pendentes = 'display: none;';
}

if ($aprovados > 0) {
    $display_sem_comentarios_aprovados = 'display: none;';
}

if ($reprovados > 0) {
    $display_sem_comentarios_reprovados = 'display: none;';
}
?>

<section class="container mt-3">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active px-1 py-2 p-sm-2" id="pendentes-tab" data-toggle="tab" href="#pendentes" role="tab" aria-controls="pendentes" aria-selected="true" data-aprovado="0">
                <small>Pendentes (<span class="total" data-aprovado="0"><?= $pendentes ?></span>)</small>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link px-1 py-2 p-sm-2" id="aprovados-tab" data-toggle="tab" href="#aprovados" role="tab" aria-controls="aprovados" aria-selected="false" data-aprovado="1">
                <small>Aprovados (<span class="total" data-aprovado="1"><?= $aprovados ?></span>)</small>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link px-1 py-2 p-sm-2" id="reprovados-tab" data-toggle="tab" href="#reprovados" role="tab" aria-controls="reprovados" aria-selected="false" data-aprovado="2">
                <small>Reprovados (<span class="total" data-aprovado="2"><?= $reprovados ?></span>)</small>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div class="tab-pane fade show active" id="pendentes" role="tabpanel" aria-labelledby="pendentes-tab" data-aprovado="0">
            <div class="html">
                <?= $html_pendentes ?>
            </div>
            <div class="row mt-3 rounded shadow bg-light py-2 mx-0 sem-comentarios" style="<?= $display_sem_comentarios_pendentes ?>">
                <div class="col">Não há comentários para aprovar.</div>
            </div>
        </div>
        <div class="tab-pane fade" id="aprovados" role="tabpanel" aria-labelledby="aprovados-tab" data-aprovado="1">
            <div class="html">
                <?= $html_aprovados ?>
            </div>
            <div class="row mt-3 rounded shadow bg-light py-2 mx-0 sem-comentarios" style="<?= $display_sem_comentarios_aprovados ?>">
                <div class="col">Não há comentários aprovados.</div>
            </div>
        </div>
        <div class="tab-pane fade" id="reprovados" role="tabpanel" aria-labelledby="reprovados-tab" data-aprovado="2">
            <div class="html">
                <?= $html_reprovados ?>
            </div>
            <div class="row mt-3 rounded shadow bg-light py-2 mx-0 sem-comentarios" style="<?= $display_sem_comentarios_reprovados ?>">
                <div class="col">Não há comentários reprovados.</div>
            </div>
        </div>
    </div>
</section>