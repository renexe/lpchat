<?php
$server = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/';
?>

<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" crossorigin="anonymous">
<link rel="stylesheet" href="<?= $server ?>css/style.css" crossorigin="anonymous">

<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@rc/dist/js.cookie.min.js" crossorigin="anonymous"></script>
<script src="<?= $server ?>js/iframeResizer.contentWindow.min.js" crossorign="anonymous"></script>
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
            },
            success: function (response) {
                if (response.return == 1) {
                    /*var resposta = form.data('resposta');
                     if (resposta != 0) {
                     var total_respostas = $('.responder[data-id="' + resposta + '"]').find('.total-respostas');
                     total_respostas.html(' (' + parseInt(parseInt(total_respostas.html().replace('(', '').replace(')', '')) + 1) + ')');
                     }
                     total_comentarios.html(parseInt(total_comentarios.html()) + 1);*/
                    //$('#comentario-enviado').modal('show');
                    alert('Comentário enviado, aguardando aprovação da moderação.');
                    /*$.ajax({
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
                form
                        .find('.btn-envia-comentario')
                        .removeAttr('disabled');
            },
            error: function (error) {
                alert('Erro ao publicar comentário');
                console.log(error.responseText);
                /*form
                 .find('.loading')
                 .stop()
                 .fadeOut('fast', function() {
                 form
                 .find('.btn-icon')
                 .stop()
                 .fadeIn('fast', function() {
                 form
                 .find('.btn-envia-comentario')
                 .removeAttr('disabled');
                 });
                 });*/
                form
                        .find('.comentario')
                        .val('');
                form
                        .find('.btn-envia-comentario')
                        .removeAttr('disabled');
            }
        };

        /*tinymce.init({
         selector:'.comentario',
         menubar: false,
         });*/

        $('.envia-comentario').ajaxForm(options);
        /*$('#comentario-enviado').on('show.bs.modal', function (e) {
         var $this = $(this);
         var y = $('#comentario-enviado').data('y'); // gets the mouseY position
         $('#comentario-enviado').css('top', y);
         });*/

        $(document).on('keyup', '.nome', function () {
            var $this = $(this);
            $this.val($this.val().ucWords());
        });

        $(document).on('click', '.responder', function () {
            var $this = $(this);
            var respostas =
                    $this
                    .parents('.row')
                    .parents('.row')
                    .find('.respostas');
            var resposta = $this.data('id');
            if (respostas.html() == '') {
                $.ajax({
                    method: 'GET',
                    url: '<?= $server ?>' + 'index.php',
                    data: {
                        resposta: resposta
                    },
                    beforeSend: function () {
                        respostas
                                .stop()
                                .hide()
                                .html('<span class="spinner-border spinner-border-sm loading"></span>')
                                .slideDown('fast');
                    },
                    success: function (response) {
                        respostas.html(response);
                        respostas
                                .find('.envia-comentario')
                                .data('resposta', resposta)
                                .ajaxForm(options);
                    },
                    error: function (error) {
                        console.log(error.responseText);
                    }
                });
            } else {
                respostas
                        .stop()
                        .slideToggle('fast');
            }
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
                url: '<?= $server ?>' + 'like_dislike.php',
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
                url: '<?= $server ?>' + 'index.php',
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
                    carregar_mais_txt
                            //.stop()
                            .fadeOut('fast', function () {
                                carregar_mais_loading
                                        //.stop()
                                        .fadeIn('fast');
                            });
                },
                success: function (response) {
                    $('.comentarios[data-resposta=' + resposta + ']').append(response.html);
                    if (response.acabou == 0) {
                        carregar_mais_loading
                                .stop()
                                .fadeOut('fast', function () {
                                    carregar_mais_txt
                                            .stop()
                                            .fadeIn('fast', function () {
                                                $this.removeAttr('disabled');
                                            });
                                });
                    } else {
                        $this.remove();
                    }
                },
                error: function (error) {
                    console.log(error.responseText);
                    carregar_mais_loading
                            .stop()
                            .fadeOut('fast', function () {
                                carregar_mais_txt
                                        .stop()
                                        .fadeIn('fast', function () {
                                            $this.removeAttr('disabled');
                                        });
                            });
                }
            });
        });

        var nav_moderar_tab = $('#nav-moderar-tab');
        var nav_moderar = $('#nav-moderar');
        var nav_responder_tab = $('#nav-responder-tab');
        var nav_responder = $('#nav-responder');
        var loading = '<div class="d-flex justify-content-center">';
        loading += '<span class="spinner spinner-border mt-5"></span>';
        loading += '</div>';

        nav_moderar_tab.on('click', function () {
            $.ajax({
                method: 'GET',
                url: '<?= $server ?>' + 'adm/aba_moderar.php',
                beforeSend: function () {
                    nav_moderar.html(loading);
                },
                success: function (response) {
                    nav_moderar.html(response);
                },
                error: function (error) {
                    console.log(error.responseText);
                }
            });
        });


        nav_responder_tab.on('click', function () {
            $.ajax({
                method: 'GET',
                url: '<?= $server ?>' + 'index.php?adm=1',
                beforeSend: function () {
                    nav_responder.html(loading);
                },
                success: function (response) {
                    nav_responder.html(response);
                    $('.envia-comentario').ajaxForm(options);
                },
                error: function (error) {
                    console.log(error.responseText);
                }
            });
        });

        nav_moderar_tab.click();

        $(document).on('click', '.aprovar-reprovar', function () {
            var $this = $(this);
            var id = $this.data('id');
            var aprovado = $this.data('aprovado');
            var loading_aprovar_reprovar = $('.loading-aprovar-reprovar[data-id="' + id + '"]');
            $.ajax({
                method: 'POST',
                url: '<?= $server ?>' + 'adm/aprovar-reprovar.php',
                data: {
                    id: id,
                    aprovado: aprovado
                },
                dataType: 'json',
                beforeSend: function () {
                    loading_aprovar_reprovar.fadeIn('fast');
                },
                success: function (response) {
                    if (response.return == 1) {
                        // DECREMENTA O TOTAL
                        var total_aprovado = $('.nav-link.active').find('.total');
                        total_aprovado.html(parseInt(total_aprovado.text()) - 1);
                        // INCREMENTA O TOTAL
                        var total_aprovado_2 = $('.total[data-aprovado="' + aprovado + '"]');
                        total_aprovado_2.html(parseInt(total_aprovado_2.text()) + 1);
                        loading_aprovar_reprovar.hide();
                        row_comentario = $('.row-comentario[data-id="' + id + '"]');
                        // MOVE O COMENTÁRIO PRA OUTRA ABA
                        var row_comentario_clone = row_comentario.clone();
                        // EXIBE A OPÇÃO DE CANCELAR E ESCONDE A DE APROVAR E REPROVAR
                        if (aprovado == 0) {
                            // CANCELANDO
                            var aprovar_reprovar_data_aprovado = [1, 2];
                        } else {
                            // APROVANDO OU RECUSANDO
                            var aprovar_reprovar_data_aprovado = [0];
                        }
                        // ESCONDE TUDO E DEPOIS MOSTRA
                        row_comentario_clone
                                .find('.aprovar-reprovar')
                                .parent('div')
                                .hide();
                        $.each(aprovar_reprovar_data_aprovado, function (i, value) {
                            row_comentario_clone
                                    .find('.aprovar-reprovar[data-aprovado="' + value + '"]')
                                    .parent('div')
                                    .show();
                        });
                        var tab_pane = $('.tab-pane[data-aprovado="' + aprovado + '"]');
                        tab_pane
                                .find('.sem-comentarios')
                                .hide();
                        var tab_pane_html = tab_pane.find('.html');
                        console.log(tab_pane_html)
                        tab_pane_html.append(row_comentario_clone);
                        // REORDENA OS COMENTÁRIOS POR ID
                        var sort = tab_pane_html
                                .find('.row-comentario')
                                .sort(function (a, b) {
                                    var contentA = parseInt($(a).data('id'));
                                    var contentB = parseInt($(b).data('id'));
                                    return (contentA < contentB) ? 1 : (contentA > contentB) ? -1 : 0;
                                });
                        tab_pane_html.html(sort);
                        row_comentario
                                .stop()
                                .slideUp('fast', function () {
                                    row_comentario.remove();
                                    var tab_pane_active = $('.tab-pane.active');
                                    if (tab_pane_active.find('.row-comentario').length == 0) {
                                        // EXIBE A MENSAGEM DE QUE NÃO HÁ MAIS COMENTÁRIOS
                                        tab_pane_active
                                                .find('.sem-comentarios')
                                                .stop()
                                                .slideDown('fast');
                                    }
                                });
                    } else {
                        console.log(response.msg);
                    }
                },
                error: function (error) {
                    console.log(error.responseText);
                }
            })
        });

        $(document).on('click', '.responder', function () {
            id = $(this).attr('id').substr(9);
            $('.responder-form').each(function () {
                $(this).attr('hidden', 'hidden');
                $('.responder-btn').attr('hidden', 'hidden');
            })
            $('#responder-form' + id).removeAttr(('hidden'));
            $('#responder-btn' + id).removeAttr('hidden');
        });

        $(document).on('change', '.responder-form', function () {
            id = $(this).attr('id').substr(14);
            comentario = $(this).val();
            $.getJSON("/adm/responderAjax.php?resposta=" + id + "&comentario=" + comentario,
                    function (json) {
                        if (json.return == 1) {
                            alert('Sua resposta foi enviada com sucesso e já pode ser vista por todos.');
                        } else {
                            alert('Houve algum problema ao enviar a resposta, tente novamente em alguns minutos. Por favor informe a um administrador do sistema se o problema persistir');
                        }
                    });
            $('.responder-form').each(function () {
                $(this).attr('hidden', 'hidden');
                $('.responder-btn').attr('hidden', 'hidden');
            })
        });

    });

</script>