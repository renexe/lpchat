<?php

if (isset($_POST['usuario']) && isset($_POST['senha'])) {

    require '../conecta.php';

    $return = 0;
    $msg = '';
    
    $select_login = "
    SELECT
        id,
        usuario,
        email,
        foto
    FROM
        adm
    WHERE
        usuario = '" . trim($_POST['usuario']) . "'
        AND senha = '" . trim($_POST['senha']) . "'
    LIMIT 1";
    if ($sql_login = mysqli_query($link, $select_login)) {
        if (mysqli_num_rows($sql_login) > 0) {
            // LOGIN BEM SUCEDIDO
            $row_login = mysqli_fetch_assoc($sql_login);
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION['id'] = $row_login['id'];
            $_SESSION['usuario'] = $row_login['usuario'];
            $_SESSION['email'] = $row_login['email'];
            $_SESSION['foto'] = $row_login['foto'];
            $return = 1;
        } else {
            $msg = 'Usuário não encontrado';
        }
    } else {
        die(mysqli_error($link));
    }
    $return = [
        'return' => $return,
        'msg' => $msg
    ];

    die(json_encode($return));

}
?>

<html lang="pt-br">

    <head>

        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <title>Login</title>

    </head>

    <body class="container-fluid justify-content-center d-flex align-items-center letter-spacing">

        <div class="d-inline-block bg-light rounded shadow br-20 p-5">

            <div class="row">
                <div class="col-12 h4 text-uppercase">Login</div>
            </div>
            <div class="row mt-4">
                <div class="col-12">
                    <form id="login-form" action="login.php" method="post">
                        <div class="form-group text-left">
                            <label for="usuario">Usuário</label>
                            <input type="text" class="form-control br-20 border-blue bg-grey" id="usuario" name="usuario" placeholder="Nome de Usuário" required>
                        </div>
                        <div class="form-group text-left">
                            <label for="senha">Senha</label>
                            <input type="password" class="form-control br-20 border-blue bg-grey" id="senha" name="senha" placeholder="Senha" required>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" id="btn-sigin" class="btn btn-secondary letter-spacing text-uppercase">
                                <span class="spinner spinner-border spinner-border-sm mr-2" id="loading" style="display: none;"></span>Entrar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <footer>

            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" crossorigin="anonymous">

            <script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.form/4.3.0/jquery.form.min.js" crossorigin="anonymous"></script>

            <script>

            $(function() {

                var btn_sign = $('#btn-sigin');
                var loading = $('#loading');
                var login_index = '<?=isset($_GET['login_index']) ? $_GET['login_index'] : ''?>';
                
                $('#login-form').ajaxForm({
                    dataType: 'json',
                    beforeSubmit: function() {
                        btn_sign.prop('disabled', true);
                        loading.fadeIn('fast');
                    },
                    success: function(response) {
                        if (response.return == '1') {
                            var href = 'index.php';
                            if (login_index == 1) {
                                href = '../' + href;
                            }
                            window.location.href = href;
                        } else {
                            alert(response.msg);
                            btn_sign.prop('disabled', false);
                        }
                        loading.fadeOut('fast');
                    },
                    error: function(error) {
                        alert(error.responseText);
                        btn_sign.prop('disabled', false);
                        loading.fadeOut('fast');
                    }
                });

            });

            </script>

        </footer>

    </body>

</html>