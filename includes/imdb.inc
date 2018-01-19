<?php
function decodificarTexto($nombre) {
	return htmlspecialchars_decode(html_entity_decode($nombre,ENT_QUOTES,"UTF-8"));
}

function insertarTituloAdicionales($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->dameTituloAdicionales();
	$total = 0;
	if(!empty($datos[0])){
	    $titulo = null;
	    $id_pais = null;
	    $nota = null;
	    $query = "insert ignore into filmes_titulos_adicionales (id_filme, otitulo, id_pais, notas) values (?, ?, ?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("isis", $id_filme, $titulo, $id_pais, $nota);
		$elementos = count($datos[0]);
		for ($i = 0; $i < $elementos; $i++){
			$titulo = decodificarTexto(trim($imdb->validaCampo($datos[3][$i])));
			$pais_nota = trim($datos[2][$i]);
			if(!empty($pais_nota) && !empty($titulo)){
				if(strpos($pais_nota,'(') !== FALSE){
					$partes = explode("(",str_replace(")","",$pais_nota));
					$pais = $partes[0];
					$nota = $partes[1];
				}
				else{
					$pais = $pais_nota;
					$nota = null;
				}
				$paises = array();
				if(stripos($pais, ",") !== FALSE){
					$paises = explode(",", $pais);
				}
				else{
					$paises[] = $pais;
				}
				foreach ($paises as $pais){
					$pais = trim(strip_tags($pais));
					$pais = (stripos($pais, '/') === false)?IMDB::damePaisReal($pais):null;
					$nota = (!empty($nota))?trim($imdb->validaCampo(strip_tags($nota))):null;
					$id_pais = null;
					if(!empty($pais)){
						$id_pais = $mysqli->dameIDPais($pais);
						if(empty($id_pais)){
							$id_pais = insertarPais($pais);
						}
					}
					if(!filter_var($id_pais, FILTER_VALIDATE_INT)){
					    $id_pais = null;
					}
					if(empty($nota)){
					    $nota = null;
					}
					$stmt->execute();
					$total += $stmt->affected_rows;
				}
			}
		}
		$stmt->close();
	}
	return $total;
}

function insertarPais($pais){
    global $mysqli;
    $query = "INSERT INTO paises (nombre) VALUES ('%s')";
    $query = sprintf($query,
        $mysqli->real_escape_string($pais)
        );
    $mysqli->query($query);
    return $mysqli->insert_id;
}

function insertarEstrenos($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->dameEstrenos();
	$paises = array();
	$total = 0;
	if(!empty($datos[0])){
	    $id_pais = null;
	    $fecha = null;
	    $query = "insert ignore into filmes_fechas_estreno (id_filme, id_pais, estreno) values (?, ?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("iis", $id_filme, $id_pais, $fecha);
		$elementos = count($datos[0]);
		for ($i = 0; $i < $elementos; $i++){
			$dia_mes = trim($datos[3][$i]);
			$anno = trim($datos[5][$i]);
			$pais = IMDB::damePaisReal(trim($imdb->validaCampo(strip_tags($datos[2][$i]))));
			if(!empty($pais) && !in_array($pais, $paises) && !empty($dia_mes) && !empty($anno)){
				$paises[] = $pais;
				$timestamp = strtotime("{$dia_mes} {$anno}");
				if($timestamp){
					$fecha = date("Y-m-d",$timestamp);
					$id_pais = $mysqli->dameIDPais($pais);
					if(!$id_pais){
						$id_pais = insertarPais($pais);
					}
					$stmt->execute();
				    $total += $stmt->affected_rows;
				}
			}
		}
		$stmt->close();
	}
	return $total;
}

function insertarLocalizacion($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->dameLocalizacion();
	$total = 0;
	if(!empty($datos[2])){
	    $id_location = null;
	    $query = "insert ignore into filmes_localizaciones (id_filme, id_localizacion) values (?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("ii", $id_filme, $id_location);
		foreach ($datos[2] as $place){
			$place = trim($imdb->validaCampo(strip_tags(nl2br($place))));
			if(!empty($place)){
				$query = "select id from localizaciones where localizacion like '%s'";
				$query = sprintf($query,
						$mysqli->real_escape_string($place)
					);
				$id_location = $mysqli->fetch_value($query);
				if(!$id_location){
					$query = "insert into localizaciones (localizacion) values ('%s')";
					$query = sprintf($query,
							$mysqli->real_escape_string($place)
						);
					$mysqli->query($query);
					$id_location = $mysqli->insert_id;
				}
				$stmt->execute();
				$total += $stmt->affected_rows;
			}
		}
		$stmt->close();
	}
	return $total;
}

function insertarKeywords($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->dameKeywords();
	$total = 0;
	if(!empty($datos[1])){
	    $keyword = null;
	    $query = "insert into etiquetas (keyword) values (?)";
	    $stkw = $mysqli->prepare($query);
	    if(!$stkw){
	        throw new Exception($stkw->error);
	    }
	    $stkw->bind_param("s", $keyword);
	    $id_keyword = null;
	    $query = "insert ignore into filmes_etiquetas (id_filme, id_keyword) values (?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("ii", $id_filme, $id_keyword);
		foreach ($datos[1] as $keyword){
			$keyword = trim($imdb->validaCampo(strip_tags($keyword)));
			if(!empty($keyword)){
				$query = "select id from etiquetas where keyword like '%s'";
				$query = sprintf($query,
						$mysqli->real_escape_string($keyword)
					);
				$id_keyword = $mysqli->fetch_value($query);
				if(!$id_keyword){
					$stkw->execute();
					$id_keyword = $stkw->insert_id;
				}
				$stmt->execute();
				$total += $stmt->affected_rows;
			}
		}
		$stkw->close();
		$stmt->close();
	}
	return $total;
}

function insertarPaises($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->damePaises();
	$total = 0;
	if(!empty($datos[2])){
	    $id_pais = null;
	    $query = "insert ignore into filmes_paises (id_filme, id_pais) values (?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("ii", $id_filme, $id_pais);
		foreach ($datos[2] as $nombre){
			$nombre = IMDB::damePaisReal(trim($imdb->validaCampo(strip_tags($nombre))));
			if(!empty($nombre)){
				$id_pais = $mysqli->dameIDPais($nombre);
				if(!$id_pais){
					$id_pais = insertarPais($nombre);
				}
				$stmt->execute();
				$total += $stmt->affected_rows;
			}
		}
		$stmt->close();
	}
	return $total;
}

function insertarIdiomas($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->dameIdiomas();
	$total = 0;
	if(!empty($datos[2])){
	    $id_idioma = null;
	    $query = "insert ignore into filmes_idiomas (id_filme, id_idioma) values (?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("ii", $id_filme, $id_idioma);
		foreach ($datos[2] as $nombre){
			$nombre = trim($imdb->validaCampo(strip_tags($nombre)));
			if(!empty($nombre)){
				$query = "select id from idiomas where idioma like '%s'";
				$query = sprintf($query,
						$mysqli->real_escape_string($nombre)
					);
				$id_idioma = $mysqli->fetch_value($query);
				if(!$id_idioma){
					$query = "insert into idiomas (idioma) values('%s')";
					$query = sprintf($query,
							$mysqli->real_escape_string($nombre)
						);
					$mysqli->query($query);
					$id_idioma = $mysqli->insert_id;
				}
				$stmt->execute();
				$total += $stmt->affected_rows;
			}
		}
		$stmt->close();
	}
	return $total;
}

function insertarGeneros($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->dameGeneros();
	$total = 0;
	if(!empty($datos[2])){
	    $id_tipo = null;
	    $query = "insert ignore into filmes_generos (id_filme, id_tipo) values (?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("ii", $id_filme, $id_tipo);
		foreach ($datos[2] as $key => $nombre){
			if(stripos($datos[1][$key], "tt_stry_gnr")){
				$nombre = trim($imdb->validaCampo(strip_tags($nombre)));
				if(!empty($nombre)){
					$query = "select id from tipos where tipo like '%s'";
					$query = sprintf($query,
							$mysqli->real_escape_string($nombre)
						);
					$id_tipo = $mysqli->fetch_value($query);
					if(!$id_tipo){
						$query = "insert into tipos (tipo) values('%s')";
						$query = sprintf($query,
								$mysqli->real_escape_string($nombre)
							);
						$mysqli->query($query);
						$id_tipo = $mysqli->insert_id;
					}
					$stmt->execute();
					$total += $stmt->affected_rows;
				}
			}
		}
		$stmt->close();
	}
	return $total;
}

function insertarCertificaciones($id_filme){
	global $imdb;
	global $mysqli;
	$datos = $imdb->dameCertificaciones();
	$total = 0;
	if(!empty($datos[2])){
	    $id_pais = null;
	    $clasificado = null;
	    $query = "insert ignore into filmes_clasificaciones (id_filme, id_pais, certificado) values (?, ?, ?)";
	    $stmt = $mysqli->prepare($query);
	    if(!$stmt){
	        throw new Exception($stmt->error);
	    }
	    $stmt->bind_param("iis", $id_filme, $id_pais, $clasificado);
		foreach ($datos[2] as $certificado){
			$partes = explode(":",$certificado);
			$pais = IMDB::damePaisReal(trim($imdb->validaCampo(strip_tags($partes[0]))));
			if(!empty($pais)){
				$clasificado = trim($imdb->validaCampo(strip_tags($partes[1])));
				$id_pais = $mysqli->dameIDPais($pais);
				if(!$id_pais){
					$id_pais = insertarPais($pais);
				}
				$stmt->execute();
				$total += $stmt->affected_rows;
			}
		}
		$stmt->close();
	}
	return $total;
}

function imprimeMensaje($total,$texto){
	if(!empty($total)){
		echo "Insertados {$total} {$texto}<br />";
	}
	else{
		echo "<strong>No se han insertado {$texto}</strong><br />";
	}
}

function insertarDatosVarios($id_filme){
	imprimeMensaje(insertarPaises($id_filme),"paises");
	imprimeMensaje(insertarIdiomas($id_filme),"idiomas");
	imprimeMensaje(insertarGeneros($id_filme),"generos");
	imprimeMensaje(insertarTituloAdicionales($id_filme),"titulos adicionales");
	imprimeMensaje(insertarCertificaciones($id_filme),"clasificaciones");
	imprimeMensaje(insertarKeywords($id_filme),"keywords");
	imprimeMensaje(insertarLocalizacion($id_filme),"localizaciones");
	imprimeMensaje(insertarEstrenos($id_filme),"fechas de estreno");
}

function buscaActor($nombre,$nmbr = null){
	global $mysqli;
	$imdb_valido = false;
	$id_actor = FALSE;
	if(filter_var($nmbr, FILTER_VALIDATE_INT)){
		$imdb_valido = true;
		$query = "select id from actores where imdb = '%u'";
		$query = sprintf($query,$nmbr);
		$id_actor = $mysqli->fetch_value($query);
		if(filter_var($id_actor, FILTER_VALIDATE_INT)){
		    return $id_actor;
		}
	}
	if(!empty($nombre)){
		$nombre = decodificarTexto($nombre);
		$query = "select id,imdb from actores where nombre = '%s'";
		$query = sprintf($query,
				$mysqli->real_escape_string($nombre));
		$temporal = $mysqli->fetch_assoc($query);
		$id_actor = $temporal['id'];
		if($imdb_valido){
			if(empty($temporal['imdb'])){
				$query = "update actores set imdb = '%u', fecha = CURRENT_TIMESTAMP where id = '%u'";
				$query = sprintf($query,$nmbr,$id_actor);
				$mysqli->query($query);
			}
			elseif ($temporal['imdb'] != $nmbr){
				return FALSE;
			}
		}
	}
	return $id_actor;
}

function dameMiembro($name,$nmbr){
	global $mysqli;
	$id_actor = buscaActor($name,$nmbr);
	if(empty($id_actor)){
		$query = "insert into actores (nombre,actorlink,imdb) values ('%s','%s','%u')";
		$query = sprintf($query,
				$mysqli->real_escape_string($name),
				FilmesDB::convierteURL($name),
				$nmbr
		);
		if($mysqli->query($query))
			$id_actor = $mysqli->insert_id;
	}
	return $id_actor;
}

function insertarElenco($id_filme,$name,$nmbr,$rol){
	global $mysqli;
	$id_actor = dameMiembro($name,$nmbr);
	if(!empty($id_actor)){
		$query = "insert ignore into filmes_miembros_reparto (filme,miembro,rol) values ('%u','%u','%u')";
		$query = sprintf($query,
				$id_filme,
				$id_actor,
				$rol
			);
		$mysqli->query($query);
		return $mysqli->affected_rows;
	}
    return 0;
}

function insertarDirectores($id_filme){
	global $imdb;
	$datos = $imdb->dameDirector();
	$total = 0;
	if(!empty($datos)){
		$elementos = count($datos[0]);
		for ($i=0; $i<$elementos ; $i++){
			$name = trim(strip_tags($datos[3][$i]));
			$nmbr = intval(trim(strip_tags($datos[1][$i])));
			if(!empty($name) && !empty($nmbr)){
				$total += insertarElenco($id_filme,$name,$nmbr,3);
			}
		}
	}
	return $total;
}

function insertarGuionistas($id_filme){
	global $imdb;
	$datos = $imdb->dameEscritor();
	$total = 0;
	if(!empty($datos)){
		$elementos = count($datos[0]);
		for ($i=0; $i<$elementos ; $i++){
			$name = trim(strip_tags($datos[3][$i]));
			$nmbr = intval(trim(strip_tags($datos[1][$i])));
			if(!empty($name) && !empty($nmbr)){
				$total += insertarElenco($id_filme,$name,$nmbr,2);
			}
		}
	}
	return $total;
}

function insertarActores($id_filme){
	global $imdb;
	global $mysqli;
	// obtengo los actores desde imdb
	$datos = $imdb->dameActoresPersonajes();
	// empiezo a insertar los datos
	$total = 0;
	if(!empty($datos)){
		// obtengo los actores actuales del filme
		$query = "SELECT miembro FROM filmes_miembros_reparto WHERE filme = '%u' AND rol = '1'";
		$query = sprintf($query,
				$id_filme
		);
		$actuales = $mysqli->fetch_array($query);
		// recorro los datos
		$elementos = count($datos[0]);
		for ($i=0; $i<$elementos ; $i++){
			$name = trim(strip_tags($datos[3][$i]));
			$nmbr = intval(trim(strip_tags($datos[1][$i])));
			if(!empty($name) && !empty($nmbr)){
				$id_actor = dameMiembro($name,$nmbr);
				if(!empty($id_actor)){
					$personaje = trim(strip_tags($datos[5][$i]));
					$query = "SELECT id, personaje FROM filmes_miembros_reparto WHERE filme = '%u' AND rol = '%u' AND miembro = '%u'";
					$query = sprintf($query,
							$id_filme,
							1,
							$id_actor
					);
					$data = $mysqli->fetch_object($query);
					$query = "";
					if(!empty($personaje)){
						if(empty($data->personaje)){
							$query = "INSERT INTO filmes_miembros_reparto (filme,miembro,personaje) VALUES ('%u','%u','%s') ON DUPLICATE KEY UPDATE personaje = '%s'";
							$query = sprintf($query,
									$id_filme,
									$id_actor,
									$mysqli->real_escape_string($personaje),
									$mysqli->real_escape_string($personaje)
								);
						}
					}
					elseif(empty($data->id)){
						$query = "INSERT IGNORE INTO filmes_miembros_reparto (filme,miembro) VALUES ('%u','%u')";
						$query = sprintf($query,
								$id_filme,
								$id_actor
							);
					}
					if(!empty($query)){
						$mysqli->query($query);
						$total += $mysqli->affected_rows;
					}
					if(in_array($id_actor, $actuales)){
						$key = array_search($id_actor, $actuales);
						unset($actuales[$key]);
					}
				}
			}
		}
		// borro todos los actores no encontrados
		if(!empty($actuales)){
			foreach ($actuales as $miembro){
				$query = "DELETE FROM filmes_miembros_reparto WHERE miembro = '%u' AND filme = '%u'";
				$query = sprintf($query,
						$miembro,
						$id_filme
				);
				$mysqli->query($query);
			}
		}
	}
	return $total;
}

function insertarReparto($id_filme){
	imprimeMensaje(insertarDirectores($id_filme),"directores");
	imprimeMensaje(insertarGuionistas($id_filme),"guionistas");
	imprimeMensaje(insertarActores($id_filme),"miembros del reparto");
}

function insertarDatosFilme($tabla_filmes){
	global $mysqli;
	$tabla_filmes['original'] = trim($tabla_filmes['original']);
	if(!empty($tabla_filmes['original'])){
		$insert = "insert into filmes (";
		$values = ") values (";
		while (list($key, $val) = each($tabla_filmes)){
			$val = trim($val);
	    	if(!empty($val)){
	    		$val = $mysqli->real_escape_string(htmlspecialchars_decode(html_entity_decode($val,ENT_QUOTES,"UTF-8")));
	        	$insert .= "$key,";
	            $values .= "'$val',";
	    	}
		}
		$insert = substr ($insert, 0, -1);
	    $values = substr ($values, 0, -1).");";
	    $sql = $insert.$values;
	    return (!$mysqli->query($sql))?0:$mysqli->insert_id;
	}
	echo "El título original está vacío.<br/>";
	return 0;
}

function modificarDatosFilme($id_actualizar,$tabla_filmes) {
	global $mysqli;
	$modificados = "";
	$update_query = "";
	$query = "SELECT anno,
            duracion,
			recomendada,
			color,
			sonido,
			imdb,
			temporada,
			capitulo,
			serie,
            total_votes,
            total_value
			FROM filmes
			WHERE id = '%u'";
	$query = sprintf($query,
			$id_actualizar
	);
	$filme_actualizar = $mysqli->fetch_assoc($query);
	foreach ($filme_actualizar as $campo=>$valor) {
		if(!empty($tabla_filmes[$campo]) && (empty($valor) || $valor != $tabla_filmes[$campo])){
			$update_query .= "$campo = '{$tabla_filmes[$campo]}',";
			$modificados .= "$campo => {$tabla_filmes[$campo]}, ";
		}
	}
	if(!empty($update_query)){
		$query = "UPDATE filmes SET %s WHERE id = '%u'";
		$query = sprintf($query,
				substr($update_query,0,-1),
				$id_actualizar
		);
		return (!$mysqli->query($query))?false:substr($modificados, 0, -2);
	}
	return false;
}


function asignaDatosRecogidos(&$tabla_filmes){
	global $imdb;
	global $mysqli;
	$tabla_filmes['anno'] = $imdb->dameAnno();
	$tabla_filmes['duracion'] = $imdb->dameDuracion();
	$recomendada_temporal = $imdb->dameRecomendada();
	$query = "select id from filmes where imdb = '$recomendada_temporal'";
	$recomendado = $mysqli->fetch_value($query);
	if(!empty($recomendado))
		$tabla_filmes['recomendada'] = $recomendado;
	$tabla_filmes['color'] = $imdb->dameColor();
	$tabla_filmes['sonido'] = $imdb->dameSonido();
	$tabla_filmes['imdb'] = $imdb->imdbNr;
	$votos = $imdb->dameVotosTotales();
	if($votos){
	    $puntuacion = $imdb->damePuntuacionMedia();
	    if($puntuacion){
	        $tabla_filmes['total_votes'] = $votos;
	        $tabla_filmes['total_value'] = $puntuacion*$votos;
	    }
	}
}

function asignaDatosSerie(&$tabla_filmes){
	global $imdb;
	global $mysqli;
	$serie = $imdb->dameSerie();
	$query = "select id from filmes where imdb = '$serie'";
	$data = $mysqli->fetch_value($query);
	if(!empty($data)){
		$imdb->actualizaTemporada();
		$tabla_filmes['temporada'] = $imdb->temporada;
		$tabla_filmes['capitulo'] = $imdb->capitulo;
		$tabla_filmes['serie'] = $data;
	}
}

function activaSerie($id_actualizar){
	global $imdb;
	global $mysqli;
	if($imdb->es_serie){
		$query = "update filmes set es_serie = '1' where id = '$id_actualizar'";
		$mysqli->query($query);
	}
}

function devuelveIMDB($contenido){
	$coincidencias = array();
	preg_match_all("|/tt([0-9]{7})/|U", $contenido, $coincidencias);
	return (!empty($coincidencias[1]))?$coincidencias[1]:array();
}

function devuelveNumeroIMDB($url){
	$numeros = devuelveIMDB($url);
	if(!empty($numeros[0])){
		return ($numeros[0]*1);
	}
	return false;
}

function creaURL($numero){
	return "http://www.imdb.com/title/tt".str_pad($numero, 7, "0", STR_PAD_LEFT)."/";
}

function dameDominio($url){
	$url = explode('.', str_replace('www.', '', str_replace('http://', '', str_replace('https://', '', $url))));
	return $url[0];
}
?>