<?php
include_once 'acceso_restringido.php';
$mysqli = new Database();
$action = (!empty($_REQUEST['action']))?$_REQUEST['action']:null;
switch($action){
	case 'DevolverBusquedaPrincipal':
		$datos = [];
		try{
			if(!isset($_POST['cadena'])){
				throw new Exception("No se han recibido parámetros de búsqueda");
			}
			$palabra = trim($_POST['cadena']);
			if(empty($palabra)){
				throw new Exception("Los parámetros de búsqueda no pueden estar vacíos");
			}
			$palabra = $mysqli->real_escape_string($palabra);
			$query = "SELECT DISTINCT filmes.id
						,ROUND(filmes.total_value/filmes.total_votes, 2) ranking
						,filmes.poster
				    FROM filmes_busqueda
				    INNER JOIN filmes ON filmes.id = filmes_busqueda.id_filme
				    WHERE MATCH (titulo) AGAINST ('%s' IN NATURAL LANGUAGE MODE) > 0
				    ORDER BY MATCH (titulo) AGAINST ('%s' IN NATURAL LANGUAGE MODE) DESC";
			$result = $mysqli->query(sprintf($query, $palabra, $palabra));
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