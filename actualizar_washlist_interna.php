<?php
include_once 'acceso_restringido.php';
include_once 'clases/database.php';
$mysqli  = new Database();
if(!empty($_POST['id']) && is_numeric($_POST['id'])){
	$query = "update usuarios_filmes_agregados set imdb_ready = '%u' where id = '%u'";
	$query = sprintf($query,1,$_POST['id']);
	$mysqli->query($query);
}
$mysqli->close();
?>