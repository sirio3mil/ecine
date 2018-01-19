<?php
include_once '../acceso_restringido.php';
include_once '../clases/database.php';
$retorno = "";
if(!empty($_POST['id']) && !empty($_POST['table'])){
	$mysqli  = new Database();
	$partes = explode("_", $_POST['id']);
	$id_tabla = array_shift($partes);
	$campo = implode("_", $partes);
	if(empty($_POST['value']) && !is_numeric($_POST['value'])){
		$query = "update %s set %s = NULL where id = '%u'";
		$query = sprintf($query,
				$mysqli->real_escape_string($_POST['table']),
				$mysqli->real_escape_string($campo),
				$id_tabla);
	}
	else{
		$query = "update %s set %s = '%s' where id = '%u'";
		$query = sprintf($query,
				$mysqli->real_escape_string($_POST['table']),
				$mysqli->real_escape_string($campo),
				$mysqli->real_escape_string($_POST['value']),
				$id_tabla);
	}
	$mysqli->query($query);
	$retorno = $_POST['value'];
	$mysqli->close();
}
echo $retorno;
?>