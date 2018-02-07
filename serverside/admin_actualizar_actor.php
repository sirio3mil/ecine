<?php
include_once '../acceso_restringido.php';
include_once '../includes/filmes.inc';
$retorno = [];
try{
    if(!isset($_POST['imdb']) || !filter_var($_POST['imdb'], FILTER_VALIDATE_INT)){
        throw new Exception("No hay actor definido");
    }
    $mysqli = new FilmesDB();
	$query = "SELECT id,
			nombre,
			sexo,
			birthDate,
			deathDate,
			birthPlace,
			deathPlace,
			altura
			FROM actores
			WHERE imdb = '%u'";
	$query = sprintf($query,
			$_POST['imdb']
	);
	$actor = $mysqli->fetch_object($query);
	$retorno['nombre'] = $actor->nombre;
	$retorno['id'] = $actor->id;
	$_options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => false,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_USERAGENT      => "Mozilla/5.0 (Windows NT 5.1) AppleWebKit/535.6 (KHTML, like Gecko) Chrome/16.0.897.0 Safari/535.6",
			CURLOPT_AUTOREFERER    => true,
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT        => 120,
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_POST           => 0,
			CURLOPT_SSL_VERIFYHOST => 0,
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_VERBOSE        => 1
	);
	$urlimdb = "http://www.imdb.com/name/nm".str_pad($_POST['imdb'], 7, "0", STR_PAD_LEFT)."/";
	$ch	= curl_init($urlimdb);
	curl_setopt_array($ch,$_options);
	$html = curl_exec($ch);
	$err = curl_errno($ch);
	if(!empty($err)){
		$errmsg  = curl_error($ch);
		throw new Exception("cURL err $err $errmsg");
	}
	$header  = curl_getinfo($ch);
	curl_close($ch);
	$http_code = trim($header['http_code']) * 1;
	if($http_code == 404){
		throw new Exception("el <a href='index.php?page=actores&id={$actor->id}' target='_blank'>actor</a> no existe con este <a href='$urlimdb' target='_blank'>imdb</a>");
	}
	$campos = array(
			"sexo"			=> "",
			"birthDate"		=> "",
			"deathDate"		=> "",
			"birthPlace"	=> "",
			"deathPlace"	=> "",
			"altura"		=> ""
	);
	if(empty($html)){
		throw new Exception("el archivo está vacío");
	}
	$sustituye = array("\r\n", "\n\r", "\n", "\r");
	$html = str_replace("> <", "><", preg_replace('/\s+/', ' ', str_replace($sustituye, "", $html)));
	if(stripos($html, "#Actress") !== FALSE){
		$campos["sexo"] = "F";
	}
	elseif(stripos($html, "#Actor") !== FALSE){
		$campos["sexo"] = "M";
	}
	$sexo_no_encontrado = FALSE;
	if(!empty($campos["sexo"])){
		if($campos["sexo"] == $actor->sexo){
			unset($campos["sexo"]);
		}
	}
	else{
		$sexo_no_encontrado = TRUE;
	}
	preg_match_all('|<time datetime=\"([^>]+)\" itemprop=\"birthDate\">|U', $html, $coincidencias);
	if(!empty($coincidencias[1][0])){
		$timestamp = strtotime(trim($coincidencias[1][0]));
		if($timestamp > 0){
			$date = date("Y-m-d", $timestamp);
			if($actor->birthDate != $date){
				$campos["birthDate"] = $date;
			}
		}
	}
	preg_match_all('|href=\"/search/name\?birth_place=([^>]+)\">([^>]+)</a>|U', $html, $coincidencias);
	if(!empty($coincidencias[2][0]) && $coincidencias[2][0] != $actor->birthPlace){
		$campos["birthPlace"] = trim($coincidencias[2][0]);
	}
	preg_match_all('|<time datetime=\"([^>]+)\" itemprop=\"deathDate\">|U', $html, $coincidencias);
	if(!empty($coincidencias[1][0])){
		$timestamp = strtotime(trim($coincidencias[1][0]));
		if($timestamp > 0){
			$date = date("Y-m-d", $timestamp);
			if($actor->deathDate != $date){
				$campos["deathDate"] = $date;
			}
		}
	}
	preg_match_all('|href=\"/search/name\?death_place=([^>]+)\">([^>]+)</a>|U', $html, $coincidencias);
	if(!empty($coincidencias[2][0]) && $coincidencias[2][0] != $actor->deathPlace){
		$campos["deathPlace"] = trim($coincidencias[2][0]);
	}
	preg_match_all('|<h4 class=\"inline\">Height:</h4>([^>]+)\(([^>]+)m\)|U', $html, $coincidencias);
	if(!empty($coincidencias[2][0]) && $coincidencias[2][0] != $actor->altura){
		$campos["altura"] = trim($coincidencias[2][0]) * 100;
	}
	$query = "update actores set ";
	$modificados = "";
	foreach ($campos as $field => $value){
		if(!empty($value)){
			$modificados .= "$field => $value, ";
			$value = $mysqli->real_escape_string($value);
			$query .= " $field = '$value', ";
		}
	}
	$query .= " actualizado = '1', fecha = NOW() where id = '{$actor->id}'";
	if(!empty($modificados)){
		if($mysqli->query($query)){
			$retorno['mensaje'] = substr($modificados, 0, -2). "<br />";
			if($sexo_no_encontrado && $actor->sexo != "M"){
				$query = "update actores set sexo = NULL where id = '{$actor->id}'";
				if($mysqli->query($query)){
					$retorno['mensaje'] .= "eliminado el sexo<br />";
				}
				else{
					$retorno['mensaje'] .= "error $mysqli->errno $mysqli->error<br />";
				}
			}
		}
		else{
			$retorno['mensaje'] = "error $mysqli->errno $mysqli->error<br />";
		}
	}
	elseif($mysqli->query($query)){
		$retorno['mensaje'] = "nada que modificar<br />";
	}
}
catch (Exception $e){
    $retorno['error'] = $e->getMessage();
}
finally {
	$mysqli->close();
}
$retorno['segundos'] = Reloj::CalcularDuracionScript();
$retorno['tiempo'] = Reloj::DevolverDuracionFormateada($retorno['segundos']);
echo json_encode($retorno);
?>