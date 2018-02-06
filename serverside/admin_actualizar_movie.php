<?php
include_once '../acceso_restringido.php';
include_once '../clases/FilmesDB.php';
include_once '../clases/ImDB.php';
include_once '../clases/Reloj.php';
include_once '../includes/funciones.inc';
include_once '../includes/filmes.inc';
include_once '../includes/imdb.inc';
$retorno = [];
try{
    if(!isset($_POST['imdb']) || !filter_var($_POST['imdb'], FILTER_VALIDATE_INT)){
        throw new Exception("No hay filme definido");
    }
    $mysqli = new FilmesDB();
	$query = "SELECT id, original FROM filmes WHERE imdb = '%u'";
	$query = sprintf($query,
			$_POST['imdb']
	);
	$filme = $mysqli->fetch_object($query);
	$retorno['titulo'] = $filme->original;
	$retorno['id'] = $filme->id;
	$num_imdb = str_pad($_POST['imdb'], 7, "0", STR_PAD_LEFT);
	$url_imdb = "http://www.imdb.com/title/tt$num_imdb/";
	$imdb = new ImDB($url_imdb);
	if(!$imdb->original){
	    throw new Exception("Formato incorrecto");
	}
	$tabla_filmes = array();
	asignaDatosRecogidos($tabla_filmes);
	$retorno['mensaje'] = "";
	if($modificados = modificarDatosFilme($filme->id, $tabla_filmes)){
		$retorno['mensaje'] .= "{$modificados}<br />";
	}
	if($total = insertarDirectores($filme->id)){
		$retorno['mensaje'] .= "{$total} directores<br />";
	}
	if($total = insertarGuionistas($filme->id)){
		$retorno['mensaje'] .= "{$total} guionistas<br />";
	}
	if($total = insertarActores($filme->id)){
		$retorno['mensaje'] .= "{$total} miembros del reparto<br />";
	}
	if($total = insertarPaises($filme->id)){
		$retorno['mensaje'] .= "{$total} paises<br />";
	}
	if($total = insertarIdiomas($filme->id)){
		$retorno['mensaje'] .= "{$total} idiomas<br />";
	}
	if($total = insertarGeneros($filme->id)){
		$retorno['mensaje'] .= "{$total} generos<br />";
	}
	if($total = insertarTituloAdicionales($filme->id)){
		$retorno['mensaje'] .= "{$total} titulos adicionales<br />";
	}
	if($total = insertarCertificaciones($filme->id)){
		$retorno['mensaje'] .= "{$total} clasificaciones<br />";
	}
	if($total = insertarKeywords($filme->id)){
		$retorno['mensaje'] .= "{$total} keywords<br />";
	}
	if($total = insertarLocalizacion($filme->id)){
		$retorno['mensaje'] .= "{$total} localizaciones<br />";
	}
	if($total = insertarEstrenos($filme->id)){
		$retorno['mensaje'] .= "{$total} fechas de estreno<br />";
	}
}
catch (Exception $e){
    $retorno['error'] = $e->getMessage();
}
finally {
	$query = "update filmes set fecha = NOW() where id = '{$filme->id}'";
	$mysqli->query($query);
	$mysqli->close();
}
$retorno['segundos'] = Reloj::CalcularDuracionScript();
$retorno['tiempo'] = Reloj::DevolverDuracionFormateada($retorno['segundos']);
echo json_encode($retorno);
?>