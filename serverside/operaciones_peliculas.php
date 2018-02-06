<?php
include_once '../acceso_restringido.php';
include_once '../clases/Database.php';
$json = [];
try{
	$mysqli = new Database();
	$action = (!empty($_REQUEST['action']))?$_REQUEST['action']:null;
	switch($action){
		case 'EliminarListadoUsuario':
			if(!isset($_SESSION['id_usuario']) || !filter_var($_SESSION['id_usuario'], FILTER_VALIDATE_INT)){
				throw new Exception("Datos incorrectos");
			}
			if(!isset($_POST['pelicula']) || !filter_var($_POST['pelicula'], FILTER_VALIDATE_INT)){
				throw new Exception("Datos incorrectos");
			}
			$query = sprintf("delete from usuarios_filmes_pendientes where id_filme = '%u' and id_usuario = '%u'", $_POST['pelicula'], $_SESSION['id_usuario']);
			if(!$mysqli->query($query)){
				throw new Exception($mysqli->error);
			}
			$query = sprintf("delete from usuarios_filmes_agregados where id_filme = '%u' and id_usuario = '%u'", $_POST['pelicula'], $_SESSION['id_usuario']);
			if(!$mysqli->query($query)){
				throw new Exception($mysqli->error);
			}
			if($mysqli->affected_rows){
				$query = sprintf("update usuarios set agregadas = agregadas - 1 where id = '%u'", $_SESSION['id_usuario']);
				if(!$mysqli->query($query)){
					throw new Exception($mysqli->error);
				}
			}
			break;
		case 'AgregarPeliculaUsuario':
			if(!isset($_SESSION['id_usuario']) || !filter_var($_SESSION['id_usuario'], FILTER_VALIDATE_INT)){
				throw new Exception("Datos incorrectos");
			}
			if(!isset($_POST['pelicula']) || !filter_var($_POST['pelicula'], FILTER_VALIDATE_INT)){
				throw new Exception("Datos incorrectos");
			}
			$query = sprintf("insert into usuarios_filmes_agregados (id_filme, id_usuario) values ('%u', '%u')", $_POST['pelicula'], $_SESSION['id_usuario']);
			if(!$mysqli->query($query)){
				throw new Exception($mysqli->error);
			}
			if(!$mysqli->affected_rows){
				throw new Exception("No se agregó ningún registro");
			}
			$query = sprintf("update usuarios set agregadas = agregadas + 1 where id = '%u'", $_SESSION['id_usuario']);
			if(!$mysqli->query($query)){
				throw new Exception($mysqli->error);
			}
			$query = sprintf("delete from usuarios_filmes_pendientes where id_filme = '%u' and id_usuario = '%u'", $_POST['pelicula'], $_SESSION['id_usuario']);
			if(!$mysqli->query($query)){
				throw new Exception($mysqli->error);
			}
			break;
		case 'AgregarPeliculaPendiente':
			if(!isset($_SESSION['id_usuario']) || !filter_var($_SESSION['id_usuario'], FILTER_VALIDATE_INT)){
				throw new Exception("Datos incorrectos");
			}
			if(!isset($_POST['pelicula']) || !filter_var($_POST['pelicula'], FILTER_VALIDATE_INT)){
				throw new Exception("Datos incorrectos");
			}
			$query = sprintf("INSERT INTO usuarios_filmes_pendientes (id_filme, id_usuario) VALUES ('%u', '%u')", $_POST['pelicula'], $_SESSION['id_usuario']);
			if(!$mysqli->query($query)){
				throw new Exception($mysqli->error);
			}
			if(!$mysqli->affected_rows){
				throw new Exception("No se agregó ningún registro");
			}
			break;
		case 'InsertarArchivosVideo':
			if(empty($_POST['pelicula']) || !filter_var($_POST['pelicula'], FILTER_VALIDATE_INT)){
				throw new Exception("No se ha recibido filme");
			}
			if(empty($_POST['archivos']) || !is_array($_POST['archivos'])){
				throw new Exception("No se han recibido archivos");
			}
			$query = "UPDATE file SET pelicula = ? WHERE file_id = ?";
			$stmt = $mysqli->prepare($query);
			if(!$stmt){
				throw new Exception($mysqli->error);
			}
			$archivo = 0;
			if(!$stmt->bind_param('ii', $_POST['pelicula'], $archivo)){
				throw new Exception($stmt->error);
			}
			foreach($_POST['archivos'] as $archivo){
				if(filter_var($archivo, FILTER_VALIDATE_INT)){
					if(!$stmt->execute()){
						throw new Exception($stmt->error);
					}
				}
			}
			$stmt->close();
			break;
		case 'ActualizarVotoPelicula':
			if(empty($_POST['pelicula']) || !filter_var($_POST['pelicula'], FILTER_VALIDATE_INT)){
				throw new Exception("No se ha recibido filme");
			}
			if(empty($_POST['voto']) || !filter_var($_POST['voto'], FILTER_VALIDATE_FLOAT)){
				throw new Exception("No se ha recibido un voto válido");
			}
			if($_POST['voto'] <= 0 || $_POST['voto'] > 5){
				throw new Exception("El voto recibido no puede ser admitido");
			}
			$query = "SELECT total_votes votos, total_value puntuacion FROM filmes WHERE id = {$_POST['pelicula']}";
			$pelicula = $mysqli->fetch_object($query);
			if(!$pelicula){
				throw new Exception("La película recibida no existe");
			}
			$query = "SELECT id, voto FROM filmes_votos_usuarios WHERE id_filme = {$_POST['pelicula']}";
			$puntuacion = $mysqli->fetch_object($query);
			if($puntuacion){
				if($puntuacion->voto != $_POST['voto']){
					$pelicula->puntuacion -= $puntuacion->voto;
					$pelicula->puntuacion += $_POST['voto'];
					$query = "UPDATE filmes_votos_usuarios SET voto = '%.1f' WHERE id = '%u'";
					if(!$mysqli->query(sprintf($query, $_POST['voto'], $puntuacion->id))){
						throw new Exception($mysqli->error);
					}
				}
				else{
					throw new Exception("Nada que modificar");
				}
			}
			else{
				$pelicula->puntuacion += $_POST['voto'];
				$pelicula->votos++;
				$query = "INSERT INTO filmes_votos_usuarios (voto, id_filme, id_usuario) VALUES ('%.1f', '%u', '9')";
				if(!$mysqli->query(sprintf($query, $_POST['voto'], $_POST['pelicula']))){
					throw new Exception($mysqli->error);
				}
			}
			$query = "UPDATE filmes SET total_votes = '%u', total_value = '%.1f' WHERE id = '%u'";
			if(!$mysqli->query(sprintf($query, $pelicula->votos, $pelicula->puntuacion, $_POST['pelicula']))){
				throw new Exception($mysqli->error);
			}
			$json['rating'] = round($pelicula->puntuacion / $pelicula->votos, 1);
			break;
	}
}
catch(Exception $e){
	$json['error'] = $e->getMessage();
}
finally {
	$mysqli->close();
	echo json_encode($json);
}