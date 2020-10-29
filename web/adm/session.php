<?php
session_start();

if (empty($_SESSION)) {
    $header = 'Location: ';
    if (isset($login_index) && $login_index == 1) {
        $header .= 'adm/';
    } else {
        $login_index = 0;
    }
    $header .= 'login.php?login_index=' . $login_index;
    
    header($header);
    exit;
}

?>