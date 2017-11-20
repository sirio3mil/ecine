<?php
include_once 'acceso_restringido.php';
include_once 'clases/imdb.php';
include_once 'includes/imdb.inc';
global $mysqli;
try{
	$numero = devuelveNumeroIMDB($_REQUEST['pagina']);
	if(empty($numero)){
		throw new Exception("No se ha definido un numero correcto de IMDb");
	}
	$ruta_imdb = creaURL($numero);
	$query = "select id from filmes where imdb = '$numero'";
	$id_filme = $mysqli->fetch_value($query);
	$imdb = new IMDB($ruta_imdb);
	$tabla_filmes = [];
	$tabla_filmes['original'] = $imdb->dameTitulo();
	if(empty($tabla_filmes['original'])){
		throw new Exception("No hay título original definido");
	}
	if($id_filme){
		if($imdb->es_capitulo){
			asignaDatosSerie($tabla_filmes);
		}
		asignaDatosRecogidos($tabla_filmes);
		modificarDatosFilme($id_filme,$tabla_filmes);
		insertarReparto($id_filme);
		insertarDatosVarios($id_filme);
		if(!$imdb->es_capitulo){
			if(!$imdb->es_serie){
				activaSerie($id_filme);
				$capitulos = $imdb->dameCapitulos();
			}
			else{
				$query = "select imdb from filmes where serie = '$id_filme'";
				$actuales = $mysqli->fetch_array($query);
				$capitulos = $imdb->dameCapitulos($actuales);
			}
			if(!empty($capitulos)){
				$una_semana = strtotime("+7 day");
				foreach($capitulos as $capitulo){
					$url_episodio = creaURL($capitulo);
					$imdb = new IMDB($url_episodio);
					$tabla_filmes = array();
					$fecha_estreno = $imdb->dameEstrenoAnterior($una_semana);
					if($una_semana > $fecha_estreno){
						echo "<h3>Insertando capítulo nuevo</h3>";
						$capitulo = 0;
						$tabla_filmes['original'] = $imdb->dameTitulo();
						$tabla_filmes['fecha_alta'] = $tabla_filmes['fecha'] = date("Y-m-d H:i:s");
						asignaDatosSerie($tabla_filmes);
						asignaDatosRecogidos($tabla_filmes);
						$capitulo = insertarDatosFilme($tabla_filmes);
						if($capitulo){
							insertarReparto($capitulo);
							insertarDatosVarios($capitulo);
						}
					}
				}
			}
		}
		$query = "update filmes set fecha = NOW() where id = '$id_filme'";
		$mysqli->query($query);
	}
	else{
		$id_filme = 0;
		$tabla_filmes['fecha_alta'] = $tabla_filmes['fecha'] = date("Y-m-d H:i:s");
		if($imdb->es_capitulo){
			asignaDatosSerie($tabla_filmes);
		}
		asignaDatosRecogidos($tabla_filmes);
		$id_filme = insertarDatosFilme($tabla_filmes);
		if(!$id_filme){
			throw new Exception("No hay id filme definido");
		}
		insertarReparto($id_filme);
		insertarDatosVarios($id_filme);
		if(!$imdb->es_capitulo){
			if($imdb->es_serie){
				activaSerie($id_filme);
				$capitulos = $imdb->dameCapitulos();
				if(!empty($capitulos)){
					$una_semana = strtotime("+7 day");
					foreach($capitulos as $capitulo){
						$url_episodio = creaURL($capitulo);
						$imdb = new IMDB($url_episodio);
						$tabla_filmes = array();
						$fecha_estreno = $imdb->dameEstrenoAnterior($una_semana);
						if($una_semana > $fecha_estreno){
							echo "<h3>Insertando capítulo nuevo</h3>";
							$capitulo = 0;
							$tabla_filmes['original'] = $imdb->dameTitulo();
							$tabla_filmes['fecha_alta'] = $tabla_filmes['fecha'] = date("Y-m-d H:i:s");
							asignaDatosSerie($tabla_filmes);
							asignaDatosRecogidos($tabla_filmes);
							$capitulo = insertarDatosFilme($tabla_filmes);
							if($capitulo){
								insertarReparto($capitulo);
								insertarDatosVarios($capitulo);
							}
						}
					}
				}
			}
		}
	}
	printf('<div class="row margin-top-10"><a class="btn btn-primary" href="index.php?page=filmes&id=%u">Modificar</a></div>',
			$id_filme
	);
}
catch (Exception $e){
	printf('<div class="alert alert-danger margin-top-10" role="alert">%s</div>',
			$e->getMessage()
	);
}
?>