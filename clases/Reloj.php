<?php

class Reloj {

	public function getTime(){
		list($useg, $seg) = explode(" ", microtime());
		return ((float)$useg + (float)$seg);
	}
	
	public function difference($time1, $time2){
		return ($time2 > $time1)?($time2 - $time1):($time1 - $time2);
	}
	
	public static function DevolverDuracionFormateada($diferencia){
	    $milisegundos = floor(($diferencia - floor($diferencia)) * 1000);
	    $datetime = new DateTime();
	    $datetime->setTimestamp(floor($diferencia));
	    return $datetime->format("H:i:s") . "." . $milisegundos;
	}
	
	public static function CalcularDuracionScript(){
	    return microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
	}

}

?>
