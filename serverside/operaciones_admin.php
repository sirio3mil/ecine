<?php
include_once '../acceso_restringido.php';
include_once '../clases/FilmesDB.php';
include_once '../includes/funciones.inc';
include_once '../includes/filmes.inc';
$mysqli = new FilmesDB();
switch($_REQUEST['action']){
	case 'EliminarImagen':
		$data = [];
		try{
			if(!file_exists($_POST['src'])){
				$_POST['src'] = '..' . DIRECTORY_SEPARATOR . $_POST['src'];
			}
			if(!file_exists($_POST['src'])){
				throw new Exception("El archivo {$_POST['src']} no existe");
			}
			$realpath = realpath($_POST['src']);
			if(!unlink($realpath)){
				throw new Exception("Imposible borrar $realpath");
			}
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case 'DevolverTotalFilmesActualizar':
		$data = [];
		try{
			if(!isset($_POST['listado']) || !filter_var($_POST['listado'], FILTER_VALIDATE_INT)){
				throw new Exception("No se ha especificado el listado");
			}
			switch($_POST['listado']){
				case 1:
					$query = "SELECT COUNT(*)
		            		FROM filmes
		            		WHERE fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 MONTH)
			            		AND imdb IS NOT NULL
			            		AND serie IS NULL
			            		AND es_serie = 0";
					break;
				case 2:
					$query = "SELECT COUNT(DISTINCT filmes.imdb)
		        			FROM filmes
		        			INNER JOIN filmes_fechas_estreno ON filmes_fechas_estreno.id_filme = filmes.id
		        			WHERE filmes_fechas_estreno.estreno > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 MONTH)
			        			AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH)
			        			AND filmes.imdb IS NOT NULL
			        			AND filmes.serie IS NULL
			        			AND filmes.es_serie = 0";
					break;
				case 3:
					$query = "SELECT COUNT(DISTINCT filmes.imdb)
				        	 FROM filmes
				        	 INNER JOIN filmes_fechas_estreno ON filmes_fechas_estreno.id_filme = filmes.id
				        	 WHERE filmes_fechas_estreno.estreno BETWEEN DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 MONTH) AND CURRENT_TIMESTAMP
					        	 AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH)
					        	 AND filmes.imdb IS NOT NULL
					        	 AND filmes.serie IS NULL
					        	 AND filmes.es_serie = 0";
					break;
				case 4:
					$query = "SELECT COUNT(DISTINCT filmes.imdb)
				        	 FROM filmes
				        	 INNER JOIN file ON file.pelicula = filmes.id
				        	 WHERE filmes.imdb IS NOT NULL
					        	 AND filmes.es_serie = 0
					        	 AND filmes.serie IS NULL
					        	 AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MONTH)";
					break;
				case 5:
					$query = "SELECT COUNT(DISTINCT filmes.imdb)
		        			FROM filmes
		        			WHERE filmes.imdb IS NOT NULL
		        				AND filmes.serie IN (
				        			SELECT DISTINCT filmes.id
				        			FROM filmes
				        			INNER JOIN usuarios_filmes_agregados ON usuarios_filmes_agregados.id_filme = filmes.id
				        			WHERE filmes.es_serie = 1
				        				AND usuarios_filmes_agregados.id_usuario = 9
			        			)
		        				AND filmes.id NOT IN (
		        					SELECT id_filme
        							FROM usuarios_filmes_agregados
        							WHERE id_usuario = 9
		        				)
		        				AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK)";
					break;
				case 6:
					$query = "SELECT COUNT(*)
		        			FROM filmes
							INNER JOIN usuarios_filmes_pendientes p ON p.id_filme = filmes.id
							WHERE filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK)";
					break;
				case 7:
					$query = "SELECT COUNT(*)
		        			FROM filmes
							WHERE filmes.pais_predeterminado IS NULL
								AND filmes.serie IS NULL
								AND filmes.cine_adultos = 0
								AND filmes.imdb IS NOT NULL
								AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK)";
					break;
			}
			if(!isset($query)){
				throw new Exception("No hay listado definido");
			}
			$data['total'] = $mysqli->fetch_value($query);
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case 'DevolverTotalActoresActualizar':
		$data = [];
		try{
			$query = "SELECT COUNT(*)
					FROM actores
					WHERE actores.imdb IS NOT NULL
					AND actores.actualizado = 0";
			$data['total'] = $mysqli->fetch_value($query);
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case 'DevolverActoresActualizar':
		$data = [];
		try{
			$query = "SELECT actores.imdb
					FROM actores
					WHERE actores.imdb IS NOT NULL
					AND actores.actualizado = 0
    				LIMIT 100";
			$data = $mysqli->fetch_array($query);
			if(!$data){
				throw new Exception("No hay actores para actualizar");
			}
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case 'DevolverFilmesActualizar':
		$data = [];
		try{
			if(!isset($_POST['listado']) || !filter_var($_POST['listado'], FILTER_VALIDATE_INT)){
				throw new Exception("No se ha especificado el listado");
			}
			switch($_POST['listado']){
				case 1:
					$query = "SELECT filmes.imdb
		            		FROM filmes
		            		WHERE fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 12 MONTH)
			            		AND imdb IS NOT NULL
			            		AND serie IS NULL
			            		AND es_serie = 0
		            		ORDER BY total_votes DESC
		            		LIMIT 100";
					break;
				case 2:
					$query = "SELECT DISTINCT filmes.imdb
		        			    ,filmes.fecha
		        			FROM filmes
		        			INNER JOIN filmes_fechas_estreno ON filmes_fechas_estreno.id_filme = filmes.id
		        			WHERE filmes_fechas_estreno.estreno > DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 MONTH)
			        			AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH)
			        			AND filmes.imdb IS NOT NULL
			        			AND filmes.serie IS NULL
			        			AND filmes.es_serie = 0
		        			ORDER BY filmes.fecha
		        			LIMIT 100";
					break;
				case 3:
					$query = "SELECT DISTINCT filmes.imdb
				        	    ,filmes.fecha
        						,filmes.total_votes
				        	FROM filmes
				        	INNER JOIN filmes_fechas_estreno ON filmes_fechas_estreno.id_filme = filmes.id
				        	WHERE filmes_fechas_estreno.estreno BETWEEN DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 24 MONTH) AND CURRENT_TIMESTAMP
								AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 3 MONTH)
					        	AND filmes.imdb IS NOT NULL
					        	AND filmes.serie IS NULL
					        	AND filmes.es_serie = 0
				        	ORDER BY filmes.fecha
        						,filmes.total_votes DESC
				        	LIMIT 100";
					break;
				case 4:
					$query = "SELECT DISTINCT filmes.imdb
				        	    ,filmes.total_votes
				        	FROM filmes
				        	INNER JOIN file ON file.pelicula = filmes.id
				        	WHERE filmes.imdb IS NOT NULL
					        	AND filmes.es_serie = 0
					        	AND filmes.serie IS NULL
					        	AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 MONTH)
				        	ORDER BY filmes.total_votes DESC
							LIMIT 100";
					break;
				case 5:
					$query = "SELECT DISTINCT filmes.imdb
		        			    ,filmes.fecha_alta
		        			FROM filmes
		        			WHERE filmes.imdb IS NOT NULL
		        				AND filmes.serie IN (
				        			SELECT DISTINCT filmes.id
				        			FROM filmes
				        			INNER JOIN usuarios_filmes_agregados ON usuarios_filmes_agregados.id_filme = filmes.id
				        			WHERE filmes.es_serie = 1
				        				AND usuarios_filmes_agregados.id_usuario = 9
			        			)
		        				AND filmes.id NOT IN (
		        					SELECT id_filme
        							FROM usuarios_filmes_agregados
        							WHERE id_usuario = 9
		        				)
		        				AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK)
		        			ORDER BY fecha_alta DESC
        					LIMIT 100";
					break;
				case 6:
					$query = "SELECT filmes.imdb
		        			FROM filmes
							INNER JOIN usuarios_filmes_pendientes p ON p.id_filme = filmes.id
							WHERE filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK)
		        			ORDER BY p.fecha DESC
        					LIMIT 100";
					break;
				case 7:
					$query = "SELECT filmes.imdb
		        			FROM filmes
							WHERE filmes.pais_predeterminado IS NULL
								AND filmes.serie IS NULL
								AND filmes.cine_adultos = 0
								AND filmes.imdb IS NOT NULL
								AND filmes.fecha < DATE_SUB(CURRENT_TIMESTAMP, INTERVAL 1 WEEK)
		        			LIMIT 100";
					break;
			}
			if(!isset($query)){
				throw new Exception("No hay listado definido");
			}
			$data = $mysqli->fetch_array($query);
			if(!$data){
				throw new Exception("No hay filmes para actualizar");
			}
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case 'DevolverUltimoRegistro':
		$data = [];
		try{
			if(empty($_REQUEST['pagina'])){
				throw new Exception("La tabla no es correcta");
			}
			$query = "SELECT MAX(id) FROM %s";
			$data['ultimo'] = $mysqli->fetch_value(sprintf($query, $mysqli->real_escape_string($_REQUEST['pagina'])));
			if(!filter_var($data['ultimo'], FILTER_VALIDATE_INT)){
				throw new Exception("Registro incorrecto");
			}
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
}
$mysqli->close();