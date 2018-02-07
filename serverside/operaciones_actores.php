<?php
include_once '../acceso_restringido.php';
include_once '../includes/filmes.inc';
$mysqli  = new Database();
$action = (!empty($_REQUEST['action']))?$_REQUEST['action']:null;
switch ($action){
	case 'ModificarCampo':
		$data = [];
		try{
			if(!isset($_POST['actor']) || !filter_var($_POST['actor'], FILTER_VALIDATE_INT)){
				throw new Exception("No hay actor definido");
			}
			$campo = strip_tags($_POST["campo"]);
			$valor = strip_tags(trim($_POST['valor']));
			if(empty($valor)){
				$query = "update actores set %s = null where id = '%u'";
				$query = sprintf($query,
						$mysqli->real_escape_string($campo),
						$_POST['actor']
						);
			}
			else{
				$query = "update actores set %s = '%s' where id = '%u'";
				$query = sprintf($query,
						$mysqli->real_escape_string($campo),
						$mysqli->real_escape_string($valor),
						$_POST['actor']
						);
			}
			if(!$mysqli->query($query)){
				if(strpos($mysqli->error, "actor_imdb") !== FALSE && $mysqli->errno == 1062){
					$query = "select nombre from actores where id = '{$_POST['actor']}'";
					$name = $mysqli->fetch_value($query);
					$query = "select id from actores where id <> '%u' and nombre like '%s'";
					$query = sprintf($query,
							$_POST['actor'],
							$mysqli->real_escape_string($name)
							);
					$newid = $mysqli->fetch_value($query);
					if(!empty($newid)){
						$query = "UPDATE IGNORE actores_premios SET id_actor = '$newid' WHERE id_actor = '{$_POST['actor']}'";
						$mysqli->query($query);
						$query = "UPDATE IGNORE filmes_miembros_reparto SET miembro = '$newid' WHERE miembro = '{$_POST['actor']}'";
						$mysqli->query($query);
						$query = "DELETE FROM actores where id = '{$_POST['actor']}'";
						$mysqli->query($query);
						$_POST['actor'] = $newid;
					}
				}
				else{
					echo "$mysqli->errno $mysqli->error";
				}
			}
			$query = "update actores set fecha = NOW() where id = '{$_POST['actor']}'";
			$mysqli->query($query);
			$mysqli->close();
		}
		catch (Exception $e){
			$data['error'] = $e->getMessage();
		}
		echo json_encode($data);
		break;
	case 'ActualizarNacionalidadActor':
		$datos = [];
		try{
			if(empty($_POST['actor']) || !filter_var($_POST['actor'], FILTER_VALIDATE_INT)){
                throw new Exception("Actor incorrecto");
            }
            if(empty($_POST['pais'])){
                throw new Exception("Pais incorrecto");
            }
            $query = "SELECT id FROM paises WHERE codigo = '%s'";
            $pais = $mysqli->fetch_value(sprintf($query,
                $mysqli->real_escape_string($_POST['pais'])
            ));
            if(empty($pais) || !filter_var($pais, FILTER_VALIDATE_INT)){
                throw new Exception("Pais {$_POST['pais']} no encontrado");
            }
            $query = "UPDATE actores SET nacionalidad = '%u' WHERE id = '%u'";
            $mysqli->query(sprintf($query,
                $pais,
                $_POST['actor']
            ));
            $datos['pais'] = $pais;
            $datos['actor'] = $_POST['actor'];
            $datos['modificados'] = $mysqli->affected_rows;
		}
		catch (Exception $e){
			$datos['error'] = $e->getMessage();
		}
		echo json_encode($datos);
		break;
}
$mysqli->close();
?>