<?php
function ValidarUrl($url) {	
	str_replace("http://","",$url);	
	$validar = @fsockopen($url, 80, $errno, $errstr, 15);	
	if ($validar) { 		
		fclose($validar);		
		return true;	
	}	
	return false;
}

function cambiaMysql($fecha){     
	$mifecha = array();	
	preg_match( "/([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})/", $fecha, $mifecha);     
	return "{$mifecha[3]}-{$mifecha[2]}-{$mifecha[1]}"; 
}

function esCriterioParaOmitir($palabra){
	if(is_numeric($palabra))
		return false;
	$a_omitir = array('The','the','los','las','del');
	return (strlen($palabra)<=2 or in_array($palabra,$a_omitir))?true:false;
}

function validaMail ($address){
	if (function_exists('filter_var'))
		return (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE)?false:true;
	return preg_match('/^(?:[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+\.)*[\w\!\#\$\%\&\'\*\+\-\/\=\?\^\`\{\|\}\~]+@(?:(?:(?:[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!\.)){0,61}[a-zA-Z0-9_-]?\.)+[a-zA-Z0-9_](?:[a-zA-Z0-9_\-](?!$)){0,61}[a-zA-Z0-9_]?)|(?:\[(?:(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\.){3}(?:[01]?\d{1,2}|2[0-4]\d|25[0-5])\]))$/', $address);
}

function cambiaWhere($campo,$palabra){	
	$where = "";
	if(strpos($palabra," ") !== false){
		$palabras = explode (" ", $palabra);	
		foreach ($palabras as $param) {
			if(!esCriterioParaOmitir($param))
				$where .= "$campo like '%$param%' or ";
		}
		$where = substr($where,0,-4);
	}
	return $where;
}

function cuantasTiene($palabra,$titulo){
	if(empty($titulo) || empty($palabra))
		return 0;
	$palabras = explode (" ", $palabra);
	$contador_total = 0;
	foreach($palabras as $word){
		if(!empty($word) && stristr($titulo,$word))				
			$contador_total++;
	}
	return $contador_total;
}
?>