<?php
include_once 'acceso_restringido.php';
include_once 'clases/database.php';
$mysqli  = new Database();
if(!empty($_POST['id']) && is_numeric($_POST['id'])){
	$query = "update filmes_votos_usuarios set imdb_rated = '%u' where id = '%u'";
	$query = sprintf($query,1,$_POST['id']);
	$mysqli->query($query);
}
$mysqli->close();
?>