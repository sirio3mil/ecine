<?php
include_once '../acceso_restringido.php';
$mysqli  = new Database();
switch ($_REQUEST['action']){
	case "ActualizarLocalizacionesPendientes":
		$data = [
			'direcciones' 	=> [],
			'correctos' 	=> 0,
			'fallidos'		=> 0,
			'peticiones'	=> 0
		];
		try{
			$query = "SELECT id
		            	,direccion
		            FROM localizaciones
		            WHERE direccion IS NOT NULL
		            	AND google = 0 
		            LIMIT 200";
			$result = $mysqli->query($query);
			// Iterate through the rows, geocoding each address
			$stmt = $mysqli->prepare("UPDATE localizaciones SET latitud = ?,  longitud = ?,  codigo_pais = ?, area = ?, sub_area = ?, localidad = ?, via = ?, codigo_postal = ?, google = 1 WHERE id = ?");
			if(!$stmt){
				throw new Exception($mysqli->error);
			}
			$postal = $via = $local = $sub = $area = $code = $latitud = $longitud = $localizacion = NULL;
			$stmt->bind_param("ddssssssi", $latitud, $longitud, $code, $area, $sub, $local, $via, $postal, $localizacion);
			while($row = $result->fetch_object()){
				$geocode_pending = true;
				while ($geocode_pending) {
					$via = $postal = $local = $sub = $area = $code = $latitud = $longitud = $localizacion = NULL;
					$url = "http://maps.googleapis.com/maps/api/geocode/xml?address=%s&sensor=false";
					$url = sprintf($url,
							urlencode($row->direccion)
							);
					$xml = simplexml_load_string(file_get_contents($url));
					$data['peticiones']++;
					if(!$xml){
						throw new Exception("XML incorrecto para {$row->direccion}");
					}
					$status = strval($xml->status);
					switch ($status){
						case 'OK':
							// OK indica que no se ha producido ningun error; la dirección se ha analizado correctamente y se ha devuelto al menos un código geografico
							$geocode_pending = false;
							foreach ($xml->result->address_component as $address){
								$tipos = [];
								if(!is_array($address->type)){
									$tipos[] = strval($address->type);
								}
								else{
									foreach($address->type as $tipo){
										$tipos[] = strval($tipo);
									}
								}
								if(in_array("route", $tipos)){
									$via = strval($address->long_name);
									if(!$via){
										$via = null;
									}
								}
								elseif(in_array("postal_code", $tipos)){
									$postal = strval($address->long_name);
									if(!$postal){
										$postal = null;
									}
								}
								elseif(in_array("locality", $tipos)){
									$local = strval($address->long_name);
									if(!$local){
										$local = null;
									}
								}
								elseif(in_array("administrative_area_level_1", $tipos)){
									$area = strval($address->long_name);
									if(!$area){
										$area = null;
									}
								}
								elseif(in_array("administrative_area_level_2", $tipos)){
									$sub = strval($address->long_name);
									if(!$sub){
										$sub = null;
									}
								}
								elseif(in_array("country", $tipos)){
									$code = strval($address->short_name);
									if(!$code){
										$code = null;
									}
								}
							}
							$latitud = strval($xml->result->geometry->location->lat);
							$longitud = strval($xml->result->geometry->location->lng);
							$localizacion = $row->id;
							if(!$stmt->execute()){
								throw new Exception($stmt->error);
							}
							if($stmt->affected_rows){
								$data['correctos']++;
								$data['direcciones'][] = [
										$row->direccion,
										$status,
										$code,
										$area,
										$sub,
										$local,
										$via,
										$postal,
										$latitud,
										$longitud
								];
							}
							break;
						case 'INVALID_REQUEST':
							$geocode_pending = false;
							$data['fallidos']++;
							$query = "UPDATE localizaciones	SET google = '1' WHERE id = '%u'";
							$query = sprintf($query,
									$row->id
									);
							$mysqli->query($query);
							break;
						case 'ZERO_RESULTS':
							// ZERO_RESULTS indica que la codificación geografica se ha realizado correctamente pero no ha devuelto ningun resultado
							// Esto puede ocurrir si en la codificación geografica se incluye una dirección (address) inexistente o un valor latlng en una ubicación remota
							if (strpos($row->direccion," - ")){
								list($lugar, $direccion) = explode(" - ",$row->direccion);
								$row->direccion = trim($direccion);
							}
							else {
								// failure to geocode
								$geocode_pending = false;
								$data['fallidos']++;
								$query = "UPDATE localizaciones	SET google = '1' WHERE id = '%u'";
								$query = sprintf($query,
										$row->id
										);
								$mysqli->query($query);
							}
							break;
						case 'OVER_QUERY_LIMIT':
							// OVER_QUERY_LIMIT indica que se ha excedido el cupo de solicitudes
							throw new Exception(ucfirst(strtolower(str_replace("_", " ", $status))));
					}
				}
				
			}
			$stmt->close();
			$result->close();
		}
		catch(Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case "DevolverTotalLocalizacionesPendientes":
		$data = [];
		try{
			$query = "SELECT COUNT(*) FROM localizaciones WHERE direccion IS NOT NULL AND google = 0";
			$data['total'] = $mysqli->fetch_value($query);
		}
		catch (Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case "ActualizarLocalizacionesProcesadas":
		$data = [];
		try{
			$query = "UPDATE localizaciones SET direccion = localizacion WHERE direccion IS NULL";
			$mysqli->query($query);
			$data['nuevos'] = $mysqli->affected_rows;
			$query = "SELECT codigo, nombre FROM paises WHERE codigo IS NOT NULL ORDER BY LENGTH(nombre) DESC";
			$result = $mysqli->query($query);
			$modificados = 0;
			$direccion = null;
			$codigo_pais = null;
			$query = "UPDATE localizaciones SET codigo_pais = ? WHERE codigo_pais IS NULL AND direccion LIKE ?";
			$stmt = $mysqli->prepare($query);
			if(!$stmt){
				throw new Exception($stmt->error);
			}
			$stmt->bind_param("ss", $codigo_pais, $direccion);
			while($row = $result->fetch_object()){
				$data['paises'][$row->nombre] = 0;
				$direccion = "%, {$row->nombre}";
				$codigo_pais = $row->codigo;
				$stmt->execute();
				$data['paises'][$row->nombre] += $stmt->affected_rows;
				$direccion = $row->nombre;
				$stmt->execute();
				$data['paises'][$row->nombre] += $stmt->affected_rows;
				$modificados += $data['paises'][$row->nombre];
			}
			$stmt->close();
			$data['modificados'] = $modificados;
		}
		catch (Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
}
$mysqli->close();