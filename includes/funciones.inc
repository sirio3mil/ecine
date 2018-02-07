<?php
function esCriterioParaOmitir($palabra){
	if(is_numeric($palabra))
		return false;
	$a_omitir = array('The','the','los','las','del');
	return (strlen($palabra)<=2 or in_array($palabra,$a_omitir))?true:false;
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