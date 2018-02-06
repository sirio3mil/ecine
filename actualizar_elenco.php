<?php
include_once 'includes/imdb.inc';
set_time_limit(0);
$mysqli  = new FilmesDB();
$query = "select id, 
	filme 
	FROM filmes_miembros_reparto 
	WHERE miembro = '%u'";
$query = sprintf($query,
		$_GET['idactor']);
$result = $mysqli->query($query);
$actualizados = 0;
$procesados = array();
if($result->num_rows){
	while($row = $result->fetch_object()){
		$query = "delete from filmes_miembros_reparto where id = '{$row->id}'";
		if($mysqli->query($query)){
			if(!in_array($row->filme, $procesados)){
				$procesados[] = $row->filme;
				$query = "select imdb from filmes where id = '$row->filme'";
				$imdb = $mysqli->fetch_value($query);
				if(!empty($imdb)){
					echo date("H:i")." procesando el filme $row->filme con imdb $imdb<br/>";
					$num_imdb = str_pad($imdb, 7, "0", STR_PAD_LEFT);
					$url_imdb = "http://www.imdb.com/title/tt$num_imdb/";
					$imdb = new ImDB($url_imdb);
					if(!empty($imdb->original)){
						$actualizados++;
						$tabla_filmes = array();
						asignaDatosRecogidos($tabla_filmes);
						modificarDatosFilme($row->filme,$tabla_filmes);
						insertarReparto($row->filme);
						insertarDatosVarios($row->filme);
					}
					else{
						echo date("H:i")." error obteniendo datos filme $row->filme con imdb $imdb<br/>";
					}
					$query = "update filmes set fecha = NOW() where id = '{$row->filme}'";
					$mysqli->query($query);
				}
				else{
					echo date("H:i")." filme $row->filme sin imdb<br/>";
				}
			}
		}
	}
}
$result->close();
if(!empty($actualizados))
	echo "Se han actualizado $actualizados peliculas<br/>";
$mysqli->close();