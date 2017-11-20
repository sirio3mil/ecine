<?php
function esSerie($id_filme){
	global $mysqli;
	$query = "select id from filmes where serie = '$id_filme' limit 1";
	$filme = $mysqli->fetch_value($query);
	return (!empty($filme))?TRUE:FALSE;
}

function cuantasTemporadas($serie){
	global $mysqli;
	$query = "select MAX(temporada) from filmes where serie = '$serie'";
	$seassons = $mysqli->fetch_value($query);
	return (empty($seassons))?0:$seassons;
}

function dameCapituloSiguiente($capitulo){
	global $mysqli;
	$query = "select temporada,
		serie,
		capitulo
		from filmes
		where id = '$capitulo'";
	$datos = $mysqli->fetch_assoc($query);
	if(!empty($datos)){
		$temporadas = cuantasTemporadas($datos['serie']);
		$query = "select count(*) as total
			from filmes
			where serie = '{$datos['serie']}'
			and temporada = '{$datos['temporada']}'
			and capitulo is not null
			order by capitulo asc";
		$capitulos = $mysqli->fetch_value($query);
		if(empty($datos['capitulo']))
			$datos['capitulo'] = 0;
		if($datos['capitulo'] < $capitulos){
			$temporada_buscar = $datos['temporada'];
			$capitulo_buscar = $datos['capitulo'] + 1;
		}
		elseif($datos['temporada'] < $temporadas){
			$temporada_buscar = $datos['temporada'] + 1;
			$capitulo_buscar = 1;
		}
		else{
			$temporada_buscar = 1;
			$capitulo_buscar = 1;
		}
		$query = "select id,
			predeterminado,
			permalink
			from filmes
			where serie = '{$datos['serie']}'
			and capitulo = '$capitulo_buscar'
			and temporada = '$temporada_buscar'";
		return $mysqli->fetch_object($query);
	}
	return FALSE;
}

function dameRecomendada($id_filme, $recomendada = NULL){
	if(empty($recomendada) || $recomendada == $id_filme){
		global $mysqli;
		// busco por los generos de la pelicula
		$query = "select id_tipo from filmes_generos where id_filme = '$id_filme'";
		$generos = $mysqli->fetch_array($query);
		$subquery_adultos = (in_array(34, $generos))?" AND filmes.cine_adultos = '1'":" AND filmes.cine_adultos = '0'";
		// busco por las etiquetas de la pelicula
		$query = "select id_keyword from filmes_etiquetas where id_filme = '$id_filme'";
		$keywords = $mysqli->fetch_array($query);
		if(!empty($keywords)){
			$string_keywords = implode(",", $keywords);
			$query = "SELECT filmes_etiquetas.id_filme
				FROM filmes
				INNER JOIN filmes_etiquetas ON filmes.id = filmes_etiquetas.id_filme
				WHERE filmes_etiquetas.id_keyword IN ($string_keywords)
				AND filmes_etiquetas.id_filme <> '$id_filme'
				AND filmes.serie IS NULL
				AND filmes.es_serie = '0'
				$subquery_adultos
				GROUP BY filmes_etiquetas.id_filme
				ORDER BY COUNT(*) DESC, (filmes.total_value/filmes.total_votes) DESC
				LIMIT 1";
			$encontrada = $mysqli->fetch_value($query);
			if(!empty($encontrada)){
				return $encontrada;
			}
		}
		if(!empty($generos)){
			$string_generos = implode(",", $generos);
			$query = "select filmes_generos.id_filme
				from filmes
				inner join filmes_generos on filmes.id = filmes_generos.id_filme
				WHERE filmes_generos.id_tipo IN ($string_generos)
				AND filmes_generos.id_filme <> '$id_filme'
				AND filmes.serie IS NULL
				AND filmes.es_serie = '0'
				$subquery_adultos
				GROUP BY filmes_generos.id_filme
				ORDER BY COUNT(*) DESC, (filmes.total_value/filmes.total_votes) DESC
				LIMIT 1";
			$encontrada = $mysqli->fetch_value($query);
			if(!empty($encontrada)){
				return $encontrada;
			}
		}
		return null;
	}
	return $recomendada;
}

function vote($vote, $film){
	global $mysqli;
	if(!empty($_SESSION['id_usuario'])){
		$query = "INSERT INTO filmes_votos_usuarios (voto, id_usuario, id_filme) VALUES ('%u', '%u', '%u') ON DUPLICATE KEY UPDATE voto = '%u'";
		$query = sprintf($query, $vote, $_SESSION['id_usuario'], $film, $vote);
		return $mysqli->query($query);
	}
	$query = "INSERT INTO filmes_votos_ips (voto, ip, id_filme) VALUES ('%u', '%u', '%u') ON DUPLICATE KEY UPDATE voto = '%u'";
	$query = sprintf($query, $vote, ip2long($_SERVER['REMOTE_ADDR']), $film, $vote);
	return $mysqli->query($query);
}
?>