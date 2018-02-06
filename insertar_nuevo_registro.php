<?php
include_once 'acceso_restringido.php';
include_once 'clases/FilmesDB.php';
include_once 'includes/funciones.inc';
include_once 'includes/filmes.inc';
if($_POST && $_GET){
	$mysqli = new FilmesDB();
	$soporte = "";
	$estreno = "";
	reset($_POST);
	if(isset($_GET["table"])){
		$open_table = trim($_GET["table"]);
	}
	$insert = "INSERT INTO $open_table (";
	$values = ") VALUES (";
	while(list($key,$value) = each($_POST)){
		if(!empty($value)){
			$value = trim($value);
			if(!empty($value)){
				$value = $mysqli->real_escape_string($value);
	        	$insert .= "$key,";
	            $values .= "'$value',";
	            if($key == 'soporte'){
	            	$soporte = $value;
	            }
	            elseif($key == 'estreno'){
	            	$estreno = $value;
	            }
	        }
		}
	}
    $insert = substr ($insert, 0, -1);
    $values = substr ($values, 0, -1).")";
    $query = $insert.$values;
    if($open_table == "filmes_fechas_estreno" && !empty($estreno)){
    	$query .= " ON DUPLICATE KEY UPDATE estreno = '{$estreno}'";
    }
    if($mysqli->query($query)){
    	$id = $mysqli->insert_id;
    	if(strstr($open_table,'filmes_')){
			$id = $_POST['id_filme'];
		  	$cabecera = "Location: ./index.php?page=filmes&id=$id";
		}
		else if($open_table == 'filmes'){
			$cabecera = "Location: ./index.php?page=filmes&id=$id";
		}
		else{
			$cabecera = "Location: ./index.php?page=$open_table&id=$id";
		}
    }
	else{
		echo "Error MySQL $mysqli->errno $mysqli->error en la consulta $query";
	}
    $mysqli->close();
	if(!empty($cabecera) && empty($_GET['ajax'])){
		header($cabecera);
	}
}
?>