<?php
include_once 'acceso_restringido.php';
include_once 'clases/filmesdb.php';
$mysqli = new FilmesDB();
$query = "update filmes 
	set duracion = '%d' 
	where serie = '%d' 
	and duracion is null";
$query = sprintf($query,
		$_GET['duracion'],
		$_GET['filme']);
$mysqli->query($query);
$mysqli->close();
?>