<?php
include_once 'clases/ImDB.php';
include_once 'includes/imdb.inc';
set_time_limit(0);
$imdbnr = str_pad($_GET['imdb'], 7, "0", STR_PAD_LEFT);
$pagina = "http://www.imdb.com/title/tt{$imdbnr}/";
$imdb = new ImDB($pagina);
if($imdb->estado){
	echo date("H:i:s"), " procesando ", $pagina, "<br />";
	echo date("H:i:s"), " titulo original ", $imdb->dameTitulo(), "<br />";
	echo date("H:i:s"), " capitulo ", ($imdb->es_capitulo)?"si":"no", "<br />";
	if($imdb->es_capitulo){
		echo date("H:i:s"), " serie ", $imdb->dameSerie(), "<br />";
		$imdb->actualizaTemporada();
		echo date("H:i:s"), " temporada ", $imdb->temporada, "<br />";
		echo date("H:i:s"), " capitulo ", $imdb->capitulo, "<br />";
	}
	else{
		echo date("H:i:s"), " serie ", ($imdb->es_serie)?"si":"no", "<br />";
	}
	echo date("H:i:s"), " año ", $imdb->dameAnno(), "<br />";
	echo date("H:i:s"), " duración ", $imdb->dameDuracion(), "<br />";
	echo date("H:i:s"), " recomendada ", $imdb->dameRecomendada(), "<br />";
	echo date("H:i:s"), " color ", $imdb->dameColor(), "<br />";
	echo date("H:i:s"), " sonido ", $imdb->dameSonido(), "<br />";
	echo date("H:i:s"), " imdb ", $imdb->imdbNr, "<br />";
	echo date("H:i:s"), " directores ", count($imdb->dameDirector()), "<br />";
	echo date("H:i:s"), " guionistas ", count($imdb->dameEscritor()), "<br />";
	$temporal = $imdb->dameActoresPersonajes();
	echo date("H:i:s"), " actores ", count($temporal), "<br />";
	$temporal = $imdb->damePaises();
	echo date("H:i:s"), " paises ", count($temporal), "<br />";
	$temporal = $imdb->dameIdiomas();
	echo date("H:i:s"), " idiomas ", count($temporal), "<br />";
	$temporal = $imdb->dameGeneros();
	echo date("H:i:s"), " géneros ", count($temporal), "<br />";
	$temporal = $imdb->dameTituloAdicionales();
	echo date("H:i:s"), " aka ", count($temporal), "<br />";
	$temporal = $imdb->dameCertificaciones();
	echo date("H:i:s"), " clasificaciones ", count($temporal), "<br />";
	$temporal = $imdb->dameKeywords();
	echo date("H:i:s"), " etiquetas ", (!empty($temporal[1]))?count($temporal[1]):0, "<br />";
	$temporal = $imdb->dameLocalizacion();
	echo date("H:i:s"), " localizaciones ", count($temporal), "<br />";
	$temporal = $imdb->dameEstrenos();
	echo date("H:i:s"), " estrenos ", count($temporal), "<br />";
	echo date("H:i:s"), " votos ", $imdb->dameVotosTotales(), "<br />";
	echo date("H:i:s"), " puntuación ", $imdb->damePuntuacionMedia(), "<br />";
}
?>