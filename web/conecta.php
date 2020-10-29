<?php

$link = mysqli_connect('localhost', 'bd850942326440', 'lVnoewHTDwVR6O0R', 'heroku_1821d2fa36478d4');
//$link = mysqli_connect('localhost', 'u484911653_admin', 'lVnoewHTDwVR6O0R', 'u484911653_o_proximo_gran');

$lc_time_names = "SET lc_time_names = 'pt_BR'";
if (!mysqli_query($link, $lc_time_names)) {
    die(mysqli_error($link));
}

if (!$link) {
    die();
}

?>