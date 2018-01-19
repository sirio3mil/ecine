<?php
include_once 'acceso_restringido.php';
include_once 'clases/filmesdb.php';
$mysqli = new filmesDB();
if(!empty($_GET['id']) && is_numeric($_GET['id']) && ($_GET['tabla'] == 'actores' || $_GET['tabla'] == 'filmes')){
	$query = "delete from %s where id = '%u'";
	$query = sprintf($query,
			$mysqli->real_escape_string($_GET['tabla']),
			$_GET['id']);
	$mysqli->query($query);
	header("Location: ./index.php");
}
?>
