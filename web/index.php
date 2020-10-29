<?php
ini_set('display_errors', 'ON');
error_reporting(E_ALL | E_STRICT);
session_start();

require 'conecta.php';
require 'functions.php';

if (isset($_GET['login']) && $_GET['login'] == 1) {
    $login_index = true;
    require 'adm/session.php';
}

if (isset($_SESSION['id']) && $_SESSION['id'] == 26) {
    $titulo_menu = '';
    $dir_menu = 'adm/';
    require 'adm/menu.php';
} else {
    $menu = '';
}

// NÃO FUNCIONA COM CORS
if (!isset($_COOKIE['id'])) {
    // SETA UM COOKIE
    $cookie_id = sha1($_SERVER['REMOTE_ADDR'] . ' ' . date('d-m-Y H:i:s') . ' ' . mt_rand());
    setcookie('id', $cookie_id, time() + (10 * 365 * 24 * 60 * 60));
    $_COOKIE['id'] = $cookie_id;
}

$cookie_id = cookie();

$html_comentarios = $div_mais_comentarios = $where = $total_comentarios_div = $where_resposta = '';
$comentarios_por_pagina = 20;
if (isset($_REQUEST['mais'])) {
    $comentarios_por_pagina += $_REQUEST['mais'];
}

// BUSCA OS COMENTÁRIOS
$select_comentarios = "
SELECT
    c.id,
    c.nome,
    c.usuario,
    c.resposta,
    a.foto,
    c.comentario,
    DATE_SUB(c.data, INTERVAL 3 HOUR) data
FROM
    comentarios c
    LEFT JOIN
        adm a ON c.usuario = a.id
WHERE
    aprovado = 1 and resposta = 0
ORDER BY
    id DESC
LIMIT " . $comentarios_por_pagina;

if ($sql_comentarios = mysqli_query($link, $select_comentarios)) {
    if (mysqli_num_rows($sql_comentarios) > 0) {
        while ($result_c = mysqli_fetch_array($sql_comentarios)) {
            $comentarios[$result_c['id']]['nome'] = $result_c['nome'];
            $comentarios[$result_c['id']]['usuario'] = $result_c['usuario'];
            $comentarios[$result_c['id']]['resposta'] = $result_c['resposta'];
            $comentarios[$result_c['id']]['comentario'] = $result_c['comentario'];
            $comentarios[$result_c['id']]['data'] = $result_c['data'];
            $comentarios[$result_c['id']]['foto'] = $result_c['foto'];

            //VERIFICA SE É A CRIS
            if ($result_c['usuario'] == 26) {
                $foto = 'img/' . $result_c['foto'];
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

            //BUSCA LIKES E DISLIKES
            $select_likes_dislikes = "
            SELECT
                like_dislike,
                cookie
            FROM
                like_dislike
            WHERE
                comentario = '" . $result_c['id'] . "'";
            $avaliou_like = $avaliou_dislike = '';
            if ($sql_likes_dislikes = mysqli_query($link, $select_likes_dislikes)) {
                $likes[$result_c['id']] = 0;
                $dislikes[$result_c['id']] = 0;
                if (mysqli_num_rows($sql_likes_dislikes) > 0) {
                    while ($row_likes_dislikes = mysqli_fetch_assoc($sql_likes_dislikes)) {
                        if ($row_likes_dislikes['like_dislike'] == 1) {
                            $likes[$result_c['id']] ++;
                            if ($row_likes_dislikes['cookie'] == $cookie_id) {
                                $avaliou_like = 'text-primary avaliou';
                            }
                        } else if ($row_likes_dislikes['like_dislike'] == 2) {
                            $dislikes[$result_c['id']] ++;
                            if ($row_likes_dislikes['cookie'] == $cookie_id) {
                                $avaliou_dislike = 'text-primary avaliou';
                            }
                        }
                    }
                }
            } else {
                die(mysqli_error($link));
            }

            // BUSCA AS RESPOSTAS DO COMENTÁRIO
            $select_respostas = "
                SELECT
                    c.id,
                    c.nome,
                    c.usuario,
                    c.resposta,
                    a.foto,
                    c.comentario,
                    DATE_SUB(c.data, INTERVAL 3 HOUR) data
                FROM
                    comentarios c
                    LEFT JOIN
                        adm a ON c.usuario = a.id
                WHERE
                    aprovado = 1 and
                    resposta = " . $result_c['id'] . "
                ORDER BY
                    id DESC
                LIMIT " . $comentarios_por_pagina;
            if ($sql_respostas = mysqli_query($link, $select_respostas)) {
                if (mysqli_num_rows($sql_respostas) > 0) {
                    $total_respostas = mysqli_num_rows($sql_respostas);
                    while ($result_r = mysqli_fetch_array($sql_respostas)) {
                        $respostas[$result_c['id']][$result_r['id']]['nome'] = $result_r['nome'];
                        $respostas[$result_c['id']][$result_r['id']]['usuario'] = $result_r['usuario'];
                        $respostas[$result_c['id']][$result_r['id']]['resposta'] = $result_r['resposta'];
                        $respostas[$result_c['id']][$result_r['id']]['comentario'] = $result_r['comentario'];
                        $respostas[$result_c['id']][$result_r['id']]['data'] = $result_r['data'];
                        $respostas[$result_c['id']][$result_r['id']]['foto'] = $result_r['foto'];
                    }
                    $resposta = $result_r['resposta'];
                    if ($result_r['usuario'] == 26) {
                        $foto = 'img/' . $result_r['foto'];
                    }
                } else {
                    $resposta = NULL;
                    $total_respostas = 0;
                }
                if ($total_respostas > 0) {
                    $total_respostas_display = '';
                } else {
                    $total_respostas_display = 'display: none';
                }
                $total_respostas_txt[$result_c['id']] = '<span class="total-respostas" style="' . $total_respostas_display . '"> (' . $total_respostas . ')</span>';
            } else {
                die(mysqli_error($link));
            }
        }
    }
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
                    <button type="button" class="btn btn-info w-80 carregar-mais" data-resposta="' . $resposta . '" style="height: 38px;">
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
?>
<div class="">
    <div class="row border-bottom pl-3 mb-1">
        <div class="col pb-2">
            <strong>
                <span id="total-comentarios"><?= $total_comentarios ?></span> Comentário<?= $s_no_comentarios ?></strong>
        </div>
    </div>
    <div class="row p-3">
        <div class="col-md-12">
            <form class="envia-comentario" action="envia-comentario.php" data-resposta="<?= $resposta ?>" method="POST">
                <div class="form-row">
                    <div class="col-10 offset-1 offset-md-2 col-md-8">
                        <input type="text" name="nome" class="form-control form-control-sm nome" value="<?= $nome ?>" placeholder="Nome" maxlength="200" required>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-10 offset-1 offset-md-2 col-md-8">
                        <input type="email" name="email" class="form-control form-control-sm email" value="<?= $email ?>" placeholder="Email" maxlength="255" required>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-xs-12 col-md-10 offset-md-1">
                        <textarea class="form-control form-control-sm comentario" name="comentario" maxlength="200" placeholder="Deixe seu comentário aqui" required></textarea>
                    </div>
                </div>
                <div class="form-row mt-2">
                    <div class="col-10 offset-1 offset-md-2 col-md-8 text-center">
                        <input type="hidden" name="resposta" class="form-control" value="">
                        <button type="submit" class="btn btn-light border btn-envia-comentario">
                            <span class="btn-icon">Enviar</span>
                            <span class="spinner-border spinner-border-sm loading" style="display: none"></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
    if ($comentarios) {
        foreach ($comentarios as $id => $comentario) {
            if ($comentario['resposta'] == 0) {
                ?>
                <div class="row py-2 mx-0 row-comentario" data-id="<?= $id ?>">
                    <?php
                    if ($comentario['usuario'] == 26) {
                        ?>
                        <div class="profile-img-col mb-5">
                            <img class="mw-100 rounded-circle" src="img/<?= $comentario['foto'] ?>" title="<?= $comentario['nome'] ?>" alt="<?= $comentario['nome'] ?>">
                        </div>
                        <?php
                    }
                    ?>
                    <div class="col">
                        <div class="row">
                            <div class="col-auto text-primary my-auto">
                                <small>
                                    <strong><?= $comentario['nome'] ?></strong>
                                </small>
                            </div>
                            <div class="col text-left my-auto">
                                <small><?= horas($comentario['data']) ?></small>
                            </div>
                        </div>
                        <div class="row">
                            <small class="d-block d-md-none">
                                <div class="col text-left my-auto"><?= $comentario['comentario'] ?></div>
                            </small>
                            <div class="d-none d-md-block col text-left my-auto"><?= $comentario['comentario'] ?></div>
                        </div>
                        <div class="row text-muted">
<!--                            <div class="col-auto">
                                <div class="like-btn cursor-pointer <?= $avaliou_like ?>" data-id="<?= $id ?>" data-like="1" data-dislike="0">
                                    <i class="fa fa-thumbs-up"></i> <span class="likes likes-dislikes"><?= $likes[$id] ?></span>
                                </div>
                            </div>-->
                            <!--                                    <div class="col-auto">
                                                                    <div class="like-btn cursor-pointer <?= $avaliou_dislike ?>" data-id="<?= $id ?>" data-like="0" data-dislike="1">
                                                                        <i class="fa fa-thumbs-down"></i> <span class="dislikes likes-dislikes"><?= $dislikes[$id] ?></span>
                                                                    </div>
                                                                </div>-->
                            <div class="col my-auto">
                                <small class="cursor-pointer responder" id="resp<?= $id ?>">Responder<?= $total_respostas_txt[$id] ?></small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row ml-5 py-2 mx-0 form-respostas" id="form<?= $id ?>" hidden="hidden">
                    <div class="col">
                        <form class="envia-comentario" action="envia-comentario.php" data-resposta="<?= $resposta ?>" method="POST">
                            <div class="form-row">
                                <div class="col-10 offset-1 offset-md-2 col-md-8">
                                    <input type="text" name="nome" class="form-control form-control-sm nome" value="<?= $nome ?>" placeholder="Nome" maxlength="200" required>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-10 offset-1 offset-md-2 col-md-8 offset-xs-1">
                                    <input type="email" name="email" class="form-control form-control-sm email" value="<?= $email ?>" placeholder="Email" maxlength="255" required>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-xs-12 col-md-10 offset-md-1">
                                    <textarea class="form-control form-control-sm comentario" name="comentario" maxlength="200" placeholder="Deixe seu comentário aqui" required></textarea>
                                </div>
                            </div>
                            <div class="form-row mt-2">
                                <div class="col-10 offset-1 offset-md-2 col-md-8 text-center">
                                    <input type="hidden" name="resposta" class="form-control" value="">
                                    <button type="submit" class="btn btn-light border btn-envia-comentario">
                                        <span class="btn-icon">Enviar</span>
                                        <span class="spinner-border spinner-border-sm loading" style="display: none"></span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
                if (isset($respostas[$id])) {
                    foreach ($respostas[$id] as $r_id => $resposta) {
                        ?>
                        <div class="row ml-5 py-2 mx-0 mb-2 mt-3 row-comentario border-left" data-id="<?= $r_id ?>">
                            <?php
                            if ($resposta['usuario'] == 26) {
                                ?>
                                <div class="profile-img-col mb-5">
                                    <img class="mw-100 rounded-circle" src="img/<?= $resposta['foto'] ?>" title="<?= $resposta['nome'] ?>" alt="<?= $resposta['nome'] ?>">
                                </div>
                                <?php
                            }
                            ?>
                            <div class="col">
                                <div class="row">
                                    <div class="col-auto text-primary my-auto">
                                        <small>
                                            <strong><?= $resposta['nome'] ?></strong>
                                        </small>
                                    </div>
                                    <div class="col text-left my-auto">
                                        <small><?= horas($resposta['data']) ?></small>
                                    </div>
                                </div>
                                <div class="row">
                                    <small class="d-block d-md-none">
                                        <div class="col text-left my-auto"><?= $resposta['comentario'] ?></div>
                                    </small>
                                    <div class="d-none d-md-block col text-left my-auto"><?= $resposta['comentario'] ?></div>
                                </div>
<!--                                <div class="row text-muted">
                                    <div class="col-auto">
                                        <div class="like-btn cursor-pointer <?= $avaliou_like ?>" data-id="<?= $r_id ?>" data-like="1" data-dislike="0">
                                            <i class="fa fa-thumbs-up"></i> <span class="likes likes-dislikes">0</span>
                                        </div>
                                    </div>
                                    <div class="col my-auto">
                                        <small class="cursor-pointer responder" id="treplica<?= $id ?>">Responder (0)</small>
                                    </div>
                                </div>-->
                            </div>
                        </div>
<!--                        <div class="row ml-5 py-2 mx-0 form-treplicas" id="form<?= $id ?>" hidden="hidden">
                            <div class="col">
                                <form class="envia-comentario" action="envia-comentario.php" data-resposta="<?= $resposta ?>" method="POST">
                                    <div class="form-row">
                                        <div class="col-10 offset-1 offset-md-2 col-md-8">
                                            <input type="text" name="nome" class="form-control form-control-sm nome" value="<?= $nome ?>" placeholder="Nome" maxlength="200" required>
                                        </div>
                                    </div>
                                    <div class="form-row mt-2">
                                        <div class="col-10 offset-1 offset-md-2 col-md-8 offset-xs-1">
                                            <input type="email" name="email" class="form-control form-control-sm email" value="<?= $email ?>" placeholder="Email" maxlength="255" required>
                                        </div>
                                    </div>
                                    <div class="form-row mt-2">
                                        <div class="col-xs-12 col-md-10 offset-md-1">
                                            <textarea class="form-control form-control-sm comentario" name="comentario" maxlength="200" placeholder="Deixe seu comentário aqui" required></textarea>
                                        </div>
                                    </div>
                                    <div class="form-row mt-2">
                                        <div class="col-10 offset-1 offset-md-2 col-md-8 text-center">
                                            <input type="hidden" name="resposta" class="form-control" value="">
                                            <button type="submit" class="btn btn-light border btn-envia-comentario">
                                                <span class="btn-icon">Enviar</span>
                                                <span class="spinner-border spinner-border-sm loading" style="display: none"></span>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>-->
                        <?php
                    }
                }
                ?>

                <div class='row pl-3 pb-1 border-bottom'></div>
                <?php
            }
        }
        ?>

        <?php
    } else {
        ?>
        <div class='row p-5'>
            <div class='col text-center'>Seja o primeiro(a) a comentar.</div>
        </div>
        <?php
    }
    ?>
    <div class="row mt-3">
        <div class="col-10 offset-1">
            <a href="index.php?mais=<?= $comentarios_por_pagina ?>">
                <button type="button" class="btn btn-info w-100 carregar-mais" style="height: 38px;">
                    <span class="spinner-border spinner-border-sm carregar-mais-loading" style="display: none;"></span>
                    <span class="carregar-mais-txt"><small>Carregar mais comentários</small></span>
                </button>
            </a>
        </div>
    </div>
    <hr class="pb-3 d-block md-none">
    <hr class="pb-5 d-none md-block">
</div>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="css/style.css" crossorigin="anonymous">

<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js" crossorigin="anonymous"></script>
<script src="js/
iframeResizer.contentWindow.min.js" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!--<script src="https://cdn.tinymce.com/4/tinymce.min.js"></script>-->

<script type="text/javascript">
    $(function () {

        String.prototype.ucWords = function () {
            let str = this.toLowerCase()
            let re = /(^([a-zA-Z\p{M}]))|([ -][a-zA-Z\p{M}])/g
            return str.replace(re, s => s.toUpperCase())
        }

        var total_comentarios = $('#total-comentarios');
        var comentarios_por_pagina = parseInt('<?= $comentarios_por_pagina ?>');
        var form;
        var comentario_enviado = $('#comentario-enviado');

        options = {
            dataType: 'json',
            beforeSubmit: function (formData, jqForm) {
                form = jqForm;
                if (form.find('.nome').val().trim().split(' ').length <= 1) {
                    alert('Por favor, informe seu nome e sobrenome');
                    return false;
                }
                form
                        .find('.btn-envia-comentario')
                        .attr('disabled', true);
                //form
                //.find('.loading')
                //.stop()
                //.fadeIn('fast');
                //.show();
                //form
                //.find('.btn-icon')
                //.stop()
                //.hide();
                //.fadeOut('fast', function() {
                //});
            },
            success: function (response) {
                if (response.return == 1) {
                    // COMENTÁRIO ENVIADO COM SUCESSO
                    alert('Seu comentário foi enviado com sucesso e aguarda aprovação. Agradecemos sua participação.');
//                    comentario_enviado.modal('show');
//                    comentario_enviado.on('show.bs.modal', function (e) {
//                        var y = comentario_enviado.data('y'); // gets the mouseY position
//                        comentario_enviado.css('top', y);
//                    });
                    /*var resposta = form.data('resposta');
                     if (resposta != 0) {
                     var total_respostas = $('.responder[data-id="' + resposta + '"]').find('.total-respostas');
                     total_respostas.html(' (' + parseInt(parseInt(total_respostas.html().replace('(', '').replace(')', '')) + 1) + ')');
                     }
                     total_comentarios.html(parseInt(total_comentarios.html()) + 1);
                     $.ajax({
                     method: 'GET',
                     data: {
                     id: response.msg
                     },
                     success: function(response) {
                     $('.comentarios[data-resposta="' + resposta + '"]')
                     .prepend(response)
                     .find('.nao-ha-respostas')
                     .remove();
                     },
                     error: function(error) {
                     console.log(error.responseText);
                     }
                     });*/
                } else {
                    alert('Erro ao publicar comentário');
                    console.log(response.msg);
                }
                form
                        .find('.comentario')
                        .val('');
                //form
                //.find('.btn-icon')
                //.stop()
                //.show()
                //form
                //.find('.loading')
                //.stop()
                //.hide();
                //.fadeOut('fast', function() {
                //.fadeIn('fast', function() {
                form
                        .find('.btn-envia-comentario')
                        .removeAttr('disabled');
                //});
                //});
            },
            error: function (error) {
                alert('Erro ao publicar comentário');
                console.log(error.responseText);
                form
                        .find('.loading')
                        //.stop()
                        .fadeOut('fast', function () {
                            form
                                    .find('.btn-icon')
                                    //.stop()
                                    .fadeIn('fast', function () {
                                        form
                                                .find('.btn-envia-comentario')
                                                .removeAttr('disabled');
                                    });
                        });
            }
        };

        /*tinymce.init({
         selector:'.comentario',
         menubar: false,
         });*/

        $('.envia-comentario').ajaxForm(options);

        $(document).on('keyup', '.nome', function () {
            var $this = $(this);
            $this.val($this.val().ucWords());
        });

        $(document).on('click', '.responder', function () {
            id = $(this).attr('id');
            id = id.substr(4);
            $('.form-respostas').each(function () {
                $(this).attr('hidden', 'hidden');
            });
            if ($('#form' + id).attr('hidden') == 'hidden') {
                $('#form' + id).removeAttr('hidden');
            } else {
                $('#form' + id).attr('hidden', 'hidden');
            }
//            var $this = $(this);
//            var respostas =
//                    $this
//                    .parents('.row')
//                    .parents('.row')
//                    .find('.respostas');
//            var resposta = $this.data('id');
//            if (respostas.html() == '') {
//                $.ajax({
//                    method: 'GET',
//                    url: 'index.php',
//                    data: {
//                        resposta: resposta
//                    },
//                    beforeSend: function () {
//                        respostas
//                                //.stop()
//                                .hide()
//                                .html('<span class="spinner-border spinner-border-sm loading"></span>')
//                                .slideDown('fast');
//                    },
//                    success: function (response) {
//                        respostas
//                                .html(response)
//                                .find('.nome')
//                                .val($('.nome').first().val());
//                        respostas
//                                .find('.envia-comentario')
//                                .data('resposta', resposta)
//                                .ajaxForm(options);
//                    },
//                    error: function (error) {
//                        console.log(error.responseText);
//                    }
//                });
//            } else {
//                respostas
//                        .stop()
//                        .slideToggle('fast');
//            }
        });

        $(document).on('click', '.like-btn', function () {
            var $this = $(this);
            var avaliou = $this.hasClass('avaliou');
            var avaliou_outro = false;
            if (avaliou) {
                // RETIRA A AVALIAÇÃO
                if ($this.data('like') == 1) {
                    var like = -1;
                    var dislike = 0;
                } else if ($this.data('dislike') == 1) {
                    var like = 0;
                    var dislike = -1;
                }
            } else {
                if ($this.data('like') == 1) {
                    var like = 1;
                    var outro = $this.parents('.row').find('.like-btn[data-dislike="1"]');
                    if (outro.hasClass('avaliou')) {
                        // JÁ TINHA DADO DISLIKE, REMOVE ELE
                        var avaliou_outro = true;
                        var dislike = -1;
                    } else {
                        var dislike = 0;
                    }
                } else if ($this.data('dislike') == 1) {
                    var dislike = 1;
                    var outro = $this.parents('.row').find('.like-btn[data-like="1"]');
                    if (outro.hasClass('avaliou')) {
                        // JÁ TINHA DADO LIKE, REMOVE ELE
                        var avaliou_outro = true;
                        var like = -1;
                    } else {
                        var like = 0;
                    }
                } else {
                    like = 0;
                    dislike = 0;
                }
            }
            $.ajax({
                method: 'POST',
                data: {
                    like: like,
                    dislike: dislike,
                    id: $this.data('id')
                },
                url: 'like_dislike.php',
                beforeSend: function () {
                    var likes = $this.find('.likes');
                    likes.html(parseInt(likes.html()) + like);
                    var dislikes = $this.find('.dislikes');
                    dislikes.html(parseInt(dislikes.html()) + dislike);
                    if (!avaliou) {
                        $this.addClass('text-primary avaliou');
                    } else {
                        $this.removeClass('text-primary avaliou');
                    }
                    if (avaliou_outro) {
                        // REMOVE A AVALIAÇÃO DO OUTRO
                        var likes_dislikes = outro
                                .removeClass('text-primary avaliou')
                                .find('.likes-dislikes');
                        likes_dislikes.html(parseInt(likes_dislikes.html()) - 1);
                    }
                },
                success: function (response) {
                    console.log(response);
                },
                error: function (erorr) {
                    console.log(erorr.responseText);
                }
            });
        });

        $(document).on('click', '.carregar-mais', function () {
            var $this = $(this);
            var carregar_mais_loading = $this.find('.carregar-mais-loading');
            var carregar_mais_txt = $this.find('.carregar-mais-txt');
            var resposta = $this.data('resposta');

            $.ajax({
                method: 'GET',
                url: 'index.php',
                dataType: 'json',
                data: {
                    ultimo_id: $('.comentarios[data-resposta="' + resposta + '"]')
                            .find('.row-comentario')
                            .last()
                            .data('id'),
                    resposta: resposta
                },
                beforeSend: function () {
                    $this.attr('disabled', true)
                    carregar_mais_txt.show();
                    carregar_mais_loading.hide();
                },
                success: function (response) {
                    $('.comentarios[data-resposta=' + resposta + ']').append(response.html);
                    if (response.acabou == 0) {
                        carregar_mais_loading.hide();
                        carregar_mais_txt.show();
                        $this.removeAttr('disabled');
                    } else {
                        $this.remove();
                    }
                },
                error: function (error) {
                    console.log(error.responseText);
                    carregar_mais_loading
                            //.stop()
                            .fadeOut('fast', function () {
                                carregar_mais_txt
                                        //.stop()
                                        .fadeIn('fast', function () {
                                            $this.removeAttr('disabled');
                                        });
                            });
                }
            });
        });

    });

</script>