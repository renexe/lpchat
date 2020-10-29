<?php

function horas ($data) {
	date_default_timezone_set('America/Sao_Paulo');

	$diaH  = date('d');
	$mesH  = date('m');
	$anoH  = date('Y');

	$horaH = date('H');
	$minH  = date('i');
	$segH  = date('s');

	$ano   = date('Y', strtotime($data));
	$mes   = date('m', strtotime($data));;
	$dia   = date('d', strtotime($data));;

	$hora  = date('H', strtotime($data));;
	$min   = date('i', strtotime($data));;
	$seg   = date('s', strtotime($data));;

	if ($anoH > $ano) {
		$anos = $anoH - $ano . ' ano';
        if ($anos > 1) {
            $anos .= 's';
        }
        $anos .= ' atr&aacute;s';
	} elseif ($mesH > $mes) {
		$meses = $mesH - $mes . ' m&ecirc;s';
        if ($meses > 1) {
            $meses .= 'es';
        }
        $meses .= ' atr&aacute;s';
	} elseif ($diaH > $dia) {
		$dias = $diaH - $dia . ' dia';
        if ($dias > 1) {
            $dias .= 's';
        }
        $dias .= ' atr&aacute;s';
	} elseif ($horaH > $hora) {
	    $horas = $horaH - $hora . ' hora';
        if ($horas > 1) {
            $horas .= 's';
        }
        $horas .= ' atr&aacute;s';
	} elseif ($minH > $min) {
		$mins = $minH - $min . ' minuto';
        if ($mins > 1) {
            $mins .= 's';
        }
        $mins .= ' atr&aacute;s';
	} elseif ($segH > $seg) {
		$segs = $segH - $seg . ' segundo';
        if ($segs > 1) {
            $segs .= 's';
        }
        $segs .= ' atr&aacute;s';
	} else {
		$agora = 'postado agora';
	}

	return @$anos . @$meses . @$dias . @$horas . @$mins . @$segs . @$agora;
}

function cookie() {
    $cookie_id = sha1($_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['HTTP_USER_AGENT']);
    return $cookie_id;
}

?>