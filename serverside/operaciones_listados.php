<?php
include_once '../acceso_restringido.php';
$mysqli = new Database();
$action = (!empty($_REQUEST['action']))?$_REQUEST['action']:null;
switch($action){
	case 'DevolverPeliculasFiltradas':
		$datos = [];
		try{
			$peliculas = [];
			$ignoradas = [];
			if(!empty($_POST['peliculas']) && is_array($_POST['peliculas'])){
				$peliculas = $_POST['peliculas'];
			}
			if(isset($_POST['descargadas'])){
				$query = "SELECT descargados.pelicula FROM file descargados WHERE descargados.existente = 1 AND descargados.pelicula IS NOT NULL";
				if($peliculas){
					$peliculas = array_intersect($peliculas, $mysqli->fetch_array($query));
				}
				else{
					$peliculas = $mysqli->fetch_array($query);
				}
			}
			if(isset($_POST['agregadas'])){
				$query = "SELECT id_filme FROM usuarios_filmes_agregados";
				if($peliculas){
					$peliculas = array_intersect($peliculas, $mysqli->fetch_array($query));
				}
				else{
					$peliculas = $mysqli->fetch_array($query);
				}
			}
			elseif(isset($_POST['pendientes'])){
				$query = "SELECT id_filme FROM usuarios_filmes_pendientes";
				if($peliculas){
					$peliculas = array_intersect($peliculas, $mysqli->fetch_array($query));
				}
				else{
					$peliculas = $mysqli->fetch_array($query);
				}
			}
			if(isset($_POST['invisibles'])){
				$query = "SELECT id_filme FROM usuarios_filmes_agregados";
				$ignoradas = array_unique(array_merge($ignoradas, $mysqli->fetch_array($query)));
			}
			$filtros = [];
			if(isset($_POST['series'])){
				$filtros[] = "filmes.es_serie = 1";
			}
			if(filter_var($_POST['desde'], FILTER_VALIDATE_INT) && filter_var($_POST['hasta'], FILTER_VALIDATE_INT)){
				$filtros[] = "filmes.anno between '{$_POST['desde']}' AND '{$_POST['hasta']}'";
			}
			elseif(filter_var($_POST['hasta'], FILTER_VALIDATE_INT)){
				$filtros[] = "filmes.anno >= '{$_POST['hasta']}'";
			}
			elseif(filter_var($_POST['desde'], FILTER_VALIDATE_INT)){
				$filtros[] = "filmes.anno <= '{$_POST['desde']}'";
			}
			if(filter_var($_POST['puntuacion'], FILTER_VALIDATE_INT)){
				$filtros[] = "(filmes.total_value/filmes.total_votes) >= '{$_POST['puntuacion']}'";
			}
			if(!isset($_POST['capitulos'])){
				$filtros[] = "filmes.serie IS NULL";
			}
			if($filtros){
				$query = sprintf("SELECT id FROM filmes WHERE %s", implode(" AND ", $filtros));
				if($peliculas){
					$peliculas = array_intersect($peliculas, $mysqli->fetch_array($query));
				}
				else{
					$peliculas = $mysqli->fetch_array($query);
				}
			}
			if(!$peliculas){
				throw new Exception("No hay películas para los filtros seleccionados");
			}
			if($ignoradas){
				$peliculas = array_diff($peliculas, $ignoradas);
			}
			$query = "SELECT filmes.id
						,ROUND(filmes.total_value/filmes.total_votes, 2) ranking
						,filmes.poster
					FROM filmes
					WHERE filmes.id IN (%s)
					ORDER BY filmes.anno DESC
						,ranking DESC";
			$result = $mysqli->query(sprintf($query, implode(",", $peliculas)));
			$datos['total'] = $result->num_rows;
			$datos['listado'] = [];
			while($row = $result->fetch_array(MYSQLI_ASSOC)){
				$datos['listado'][] = $row;
			}
			$result->close();
			$datos['duracion'] = Reloj::CalcularDuracionScript();
			$datos['formateada'] = Reloj::DevolverDuracionFormateada($datos['duracion']);
		}
		catch(Exception $e){
			$datos['error'] = $e->getMessage();
		}
		echo json_encode($datos);
		break;
}
$mysqli->close();