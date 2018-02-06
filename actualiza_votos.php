<?php
include_once 'acceso_restringido.php';
$mysqli = new FilmesDB();
$query = "select imdb from filmes where id = '{$_SESSION['id_page']}'";
$filme = $mysqli->fetch_value($query);
if(!empty($filme)){
	$numero = str_pad($filme, 7, "0", STR_PAD_LEFT);
	$imdb = new ImDB("http://www.imdb.com/title/tt$numero/");
	$votos = $imdb->dameVotosTotales();
	$puntaje = (int)(($imdb->damePuntuacionMedia()*$votos)/2);
	$query = "update filmes 
		set total_votes = '$votos',
		total_value = '$puntaje' 
		where id = '{$_SESSION['id_page']}'";
	$mysqli->query($query);
	header("Location: ./index.php?page=filmes&id={$_SESSION['id_page']}");
}
$mysqli->close();