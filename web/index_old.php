<?php

session_start();

require 'conecta.php';
require 'functions.php';

if (isset($_GET['login']) && $_GET['login'] == 1) {
    $login_index = true;
    require 'adm/session.php';
}

// NÃO FUNCIONA COM CORS
if (!isset($_COOKIE['id'])) {
    // SETA UM COOKIE
    $cookie_id = sha1($_SERVER['REMOTE_ADDR'] . ' ' . date('d-m-Y H:i:s') . ' ' . mt_rand());
    setcookie('id', $cookie_id, time() + (10 * 365 * 24 * 60 * 60));
    $_COOKIE['id'] = $cookie_id;
}

$cookie_id = cookie();

$html_comentarios = $div_mais_comentarios = $where = $total_comentarios_div  = $where_resposta = '';
$comentarios_por_pagina = 20;

if (isset($_GET['id'])) {
    $where .= "AND c.id = '" . $_GET['id'] . "'";
}

if (isset($_GET['ultimo_id'])) {
    $ultimo_id = $_GET['ultimo_id'];
    $where .= "AND c.id < " . $ultimo_id;
}

if (isset($_GET['resposta']) && $_GET['resposta'] != 0) {
    $resposta = $_GET['resposta'];
    $where_resposta = "
    AND resposta = '" . $resposta . "'";
    $where .= "
    AND c.resposta = '" . $resposta . "'";;
} else {
    $resposta = 0;
    if (!isset($_GET['id'])) {
        $where .= "
        AND c.resposta = 0";
    }
}

$server = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';

// BUSCA OS COMENTÁRIOS
$select_comentarios = "
SELECT
    c.id,
    c.nome,
    c.usuario,
    a.foto,
    c.comentario,
    DATE_SUB(c.data, INTERVAL 3 HOUR) data
FROM
    comentarios c
    LEFT JOIN
        adm a ON c.usuario = a.id
WHERE
    aprovado = 1
    " . $where . "
ORDER BY
    c.id DESC
LIMIT " . $comentarios_por_pagina;
if ($sql_comentarios = mysqli_query($link, $select_comentarios)) {
    if (mysqli_num_rows($sql_comentarios) > 0) {
        while ($row_comentarios = mysqli_fetch_assoc($sql_comentarios)) {
            $ultimo_id = $row_comentarios['id'];
            $select_respostas = "
            SELECT
                id
            FROM
                comentarios
            WHERE
                resposta = '" . $row_comentarios['id'] . "'
                AND aprovado = 1";
            if ($sql_respostas = mysqli_query($link, $select_respostas)) {
                $total_respostas = mysqli_num_rows($sql_respostas);
            } else {
                die(mysqli_error($link, $sql_respostas));
            }
            if ($total_respostas > 0) {
                $total_respostas_display = '';
            } else {
                $total_respostas_display = 'display: none';
            }
            $total_respostas_txt = '<span class="total-respostas" style="' . $total_respostas_display . '"> (' . $total_respostas . ')</span>';
            $select_likes_dislikes = "
            SELECT
                like_dislike,
                cookie
            FROM
                like_dislike
            WHERE
                comentario = '" . $row_comentarios['id'] . "'";
            $avaliou_like = $avaliou_dislike = '';
            if ($sql_likes_dislikes = mysqli_query($link, $select_likes_dislikes)) {
                $likes = 0;
                $dislikes = 0;
                if (mysqli_num_rows($sql_likes_dislikes) > 0) {
                    while ($row_likes_dislikes = mysqli_fetch_assoc($sql_likes_dislikes)) {
                        if ($row_likes_dislikes['like_dislike'] == 1) {
                            $likes++;
                            if ($row_likes_dislikes['cookie'] == $cookie_id) {
                                $avaliou_like = 'text-primary avaliou';
                            }
                        } else if ($row_likes_dislikes['like_dislike'] == 2) {
                            $dislikes++;
                            if ($row_likes_dislikes['cookie'] == $cookie_id) {
                                $avaliou_dislike = 'text-primary avaliou';
                            }
                        }
                    }
                }
            } else {
                die(mysqli_error($link));
            }
            $html_comentarios .= '
    <div class="row mb-3 rounded shadow bg-light py-2 mx-0 row-comentario" data-id="' . $row_comentarios['id'] . '">';
            $html_comentarios .= '
        <div class="col">
            <div class="row">';
            if ($row_comentarios['usuario'] == 26) {
                $foto = $server . 'img/' . $row_comentarios['foto'];
                //if (file_exists($foto)) {
                    $foto_26 = true;
                    $html_comentarios .= '
                    <div class="profile-img-col">
                        <img class="mw-100 rounded-circle" src="' . $foto . '" title="' . $row_comentarios['nome'] . '" alt="' . $row_comentarios['nome'] . '">
                    </div>';
                /*} else {
                    $foto_26 = false;
                }*/
            } else {
                $foto_26 = false;
            }
            $html_comentarios .= '
                <div class="col">
                    <div class="row">';
            $html_comentarios .= '
                        <div class="col-auto text-primary my-auto">
                            <small>
                                <strong>' . $row_comentarios['nome'] . '</strong>
                            </small>
                        </div>
                        <div class="col text-left my-auto">
                            <small>' . horas($row_comentarios['data']) . '</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col text-left my-auto">' . $row_comentarios['comentario'] . '</div>
                    </div>
                </div>
            </div>
            <div class="row text-muted">';
            if ($foto_26) {
                $html_comentarios .= '
                <div class="profile-img-col"></div>';
            }
            $html_comentarios .= '
                <div class="col-auto">
                    <div class="like-btn cursor-pointer ' . $avaliou_like . '" data-id="' . $row_comentarios['id'] . '" data-like="1" data-dislike="0">
                        <i class="fa fa-thumbs-up"></i> <span class="likes likes-dislikes">' . $likes . '</span>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="like-btn cursor-pointer ' . $avaliou_dislike . '" data-id="' . $row_comentarios['id'] . '" data-like="0" data-dislike="1">
                        <i class="fa fa-thumbs-down"></i> <span class="dislikes likes-dislikes">' . $dislikes . '</span>
                    </div>
                </div>';
            if ($resposta == 0) {
                // PERMITE RESPONDER APENAS OS COMENTÁRIOS PRINCIPAIS
                $html_comentarios .= '
                <div class="col my-auto">
                    <small class="cursor-pointer responder" data-id="' . $row_comentarios['id'] . '">Responder' . $total_respostas_txt . '</small>
                </div>';
                }
            $html_comentarios .= '
            </div>';
            if ($resposta == 0) {   
                $html_comentarios .= '
            <div class="row rounded shadow bg-white mx-2 my-2">';
                $html_comentarios .= '
                <div class="col text-center respostas p-2" style="display: none;"></div>
            </div>';
            }
            $html_comentarios .= '
        </div>
    </div>';
        }
    } else {
        if ($resposta != 0) {
            $nao_comentarios_respostas = 'respostas';
        } else {
            $nao_comentarios_respostas = 'comentários';
        }
        $html_comentarios .= '
        <div class="row nao-ha-respostas">
            <div class="col text-left">Ainda não há ' . $nao_comentarios_respostas . '.</div>
        </div>';
    }
} else {
    die(mysqli_error($link));
}

if (isset($_GET['id'])) {
    // BUSCANDO POR APENAS POR COMENTÁRIOS
    die($html_comentarios);
} else if (isset($_GET['ultimo_id'])) {
    // CARREGAR MAIS COMENTÁRIOS
    // VERIFICA SE HÁ MAIS COMENTÁRIOS
    $select_mais = "
    SELECT
        id
    FROM
        comentarios
    WHERE
        id < " . $ultimo_id . "
        " . $where_resposta . "
    LIMIT 1";
    if ($sql_mais = mysqli_query($link, $select_mais)) {
        if (mysqli_num_rows($sql_mais) == 0) {
            $acabou = 1;
        } else {
            $acabou = 0;
        }
    } else {
        die(mysqli_error($link));
    }
    $response = [
        'html' => $html_comentarios,
        'acabou' => $acabou
    ];
    die(json_encode($response));
}

// BUSCA O NÚMERO TOTAL DE COMENTÁRIOS
$select_no_comentarios = "SELECT COUNT(*) total FROM comentarios WHERE aprovado = 1 " . $where_resposta;
if ($sql_no_comentarios = mysqli_query($link, $select_no_comentarios)) {
    $row_no_comentarios = mysqli_fetch_assoc($sql_no_comentarios);
    $total_comentarios = $row_no_comentarios['total'];
    if ($total_comentarios > 1) {
        $s_no_comentarios = 's';
        if ($total_comentarios > $comentarios_por_pagina) {
            $div_mais_comentarios = '
            <div class="row mt-3">
                <div class="col">
                    <button type="button" class="btn btn-info w-100 carregar-mais" data-resposta="' . $resposta . '" style="height: 38px;">
                        <span class="spinner-border spinner-border-sm carregar-mais-loading" style="display: none;"></span>
                        <span class="carregar-mais-txt">Carregar mais comentários</span>
                    </button>
                </div>
            </div>';
        }
    } else {
        $s_no_comentarios = '';
    }
} else {
    die(mysqli_error($link));
}

if (isset($_SESSION['id']) && $_SESSION['id'] == 26) {
    // CRIS FRANKLIN
    $nome = $_SESSION['usuario'];
    $email = $_SESSION['email'];
    $readonly = 'readonly';
} else {
    // BUSCA O NOME DA ÚLTIMA PESSOA QUE COMENTOU NAQUELE IP
    $select_nome = "SELECT nome, email FROM comentarios WHERE cookie = '" . $cookie_id . "' ORDER BY id DESC LIMIT 1";
    if ($sql_nome = mysqli_query($link, $select_nome)) {
        if (mysqli_num_rows($sql_nome) > 0) {
            $row = mysqli_fetch_assoc($sql_nome);
            $nome = trim($row['nome']);
            $email = trim($row['email']);
        } else {
            $nome = $email = '';
        }
    } else {
        die(mysqli_error($sql_nome));
    }
}

if ($resposta == 0) {

    $total_comentarios_div = '
    <div class="row border-bottom">
            <div class="col-12">
            <strong>
                <span id="total-comentarios">' . $total_comentarios . '</span> Comentário' . $s_no_comentarios . '</strong>
        </div>
    </div>';

}

$html = '
<section id="container-comentarios" class="p-3">

    ' . $total_comentarios_div . '
    <div class="row mt-3">
        <div class="col">
            <form class="envia-comentario" action="' . $server . 'envia-comentario.php" data-resposta="' . $resposta . '" method="POST">
                <div class="form-row">
                    <div class="col">
                        <input type="text" name="nome" class="form-control nome" value="' . $nome .'" placeholder="Nome" ' . $readonly . ' maxlength="200" required>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col">
                        <input type="email" name="email" class="form-control email" value="' . $email .'" placeholder="Email" ' . $readonly . ' maxlength="255" required>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col">
                        <textarea class="form-control comentario" name="comentario" maxlength="200" placeholder="Participe da discussão..." required></textarea>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col text-right">
                        <input type="hidden" name="resposta" class="form-control" value="' . $resposta . '  ">
                        <button type="submit" class="btn btn-light border btn-envia-comentario">
                            <span class="btn-icon">Enviar</i>
                            <span class="spinner-border spinner-border-sm loading" style="display: none"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="comentarios" data-resposta="' . $resposta . '">
        ' . $html_comentarios . '
    </div>
    ' . $div_mais_comentarios . '
</section>';

if (isset($_GET['resposta'])) {

    die($html);

}

$modal = '
<div class="modal fade" id="comentario-enviado" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div class="h6 modal-title">Comentário enviado</div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">Comentário aguardando aprovação da moderação.</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>';

$html .= $modal;

if (isset($_GET['adm']) && $_GET['adm'] == 1) {
    die($html);
}

?>
<?=$html?>

<?php require 'footer.php'; ?>