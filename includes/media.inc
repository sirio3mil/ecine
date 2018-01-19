<?php
define("TAG_GENERAL", 1);
define("TAG_VIDEO", 2);
define("TAG_AUDIO", 3);

function PreparaQuery($datos, $validos){
	global $mysqli;
	$query = array(
			"datos" 	=> "",
			"campos"	=> ""
	);
	foreach ($datos as $campo => $valor){
		if(in_array($campo, $validos)){
			if(stripos($campo, "date") !== FALSE){
				$valor = date("Y-m-d H:i:s", strtotime($valor));
			}
			elseif ($campo == "bit_rate"){
				$temp = array_map("trim", explode("/", $valor));
				$valor = $temp[0];
				if(!filter_var($valor, FILTER_VALIDATE_INT)){
					$valor = NULL;
				}
			}
			$query["campos"] .= "`$campo`, ";
			if(empty($valor) && !is_numeric($valor)){
				$query["datos"] .= "NULL, ";
			}
			else{
				$query["datos"] .= sprintf("'%s', ",
						$mysqli->real_escape_string($valor)
				);
			}
		}
	}
	if(!empty($query["campos"])){
		$query["campos"] = substr($query["campos"], 0, -2);
		$query["datos"] = substr($query["datos"], 0, -2);
	}
	return $query;
}

function ExisteMedia($filepath){
	global $mysqli;
	$query = sprintf("SELECT COUNT(*) FROM file WHERE complete_name = '%s'",
			$mysqli->real_escape_string($filepath)
	);
	return ($mysqli->fetch_value($query))?TRUE:FALSE;
}

function ExisteNombreMedia($filename, $extension){
    global $mysqli;
    $query = sprintf("SELECT COUNT(*) FROM file WHERE file_name = '%s' AND file_extension = '%s'",
        $mysqli->real_escape_string($filename),
        $mysqli->real_escape_string($extension)
        );
    return ($mysqli->fetch_value($query))?TRUE:FALSE;
}

function DevolverCamposPermitidos($tipo){
	global $mysqli;
	$tabla = "";
	switch ($tipo){
		case TAG_GENERAL:
			$tabla = "file";
			break;
		case TAG_VIDEO:
			$tabla = "file_video";
			break;
		case TAG_AUDIO:
			$tabla = "file_audio";
			break;
	}
	if(!empty($tabla)){
		$query = "SELECT COLUMN_NAME
				FROM information_schema.COLUMNS
				WHERE TABLE_SCHEMA  LIKE 'db_peliculas'
				AND TABLE_NAME = '%s'
				AND COLUMN_KEY NOT LIKE 'PRI'";
		$query = sprintf($query,
				$tabla
		);
		return $mysqli->fetch_array($query);
	}
	return array();
}

function ProcesarMediaInfo($comando, $filepath){
	$media_data = shell_exec($comando);
	$arrData = explode("\n", $media_data);
	$categoria = 0;
	$contenido = array();
	$datos = array();
	foreach ($arrData as $data){
		$data = trim($data);
		if(!empty($data)){
			if(strpos($data, " : ") !== FALSE){
				list($campo, $valor) = explode(" : ", $data);
				$conservar = 'a-z';
				$regex = sprintf('~[^%s]++~i', $conservar);
				$campo = str_replace(" ", "_", trim(preg_replace($regex, ' ', strtolower($campo))));
				if(!empty($campo)){
					if(!isset($datos[$campo])){
						$datos[$campo] = trim($valor);
					}
				}
			}
			else{
				if(!empty($datos)){
					$contenido[$categoria][] = $datos;
				}
				if(stripos($data, "General") === 0){
					$categoria = TAG_GENERAL;
					$datos = array();
				}
				elseif(stripos($data, "Video") === 0){
					$categoria = TAG_VIDEO;
					$datos = array();
				}
				elseif(stripos($data, "Audio") === 0){
					$categoria = TAG_AUDIO;
					$datos = array();
				}
			}
		}
	}
	if(!empty($datos)){
		$contenido[$categoria][] = $datos;
	}
	return $contenido;
}
?>