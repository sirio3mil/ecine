<?php
global $mysqli;
if(trim($_POST['actores']) != "" && !empty($_POST['actores'])){
	$actores = explode (",", $_POST['actores']);
	?>
	<a href=index.php?page=filmes&id=<?=$_POST['id_filme']?>>Modificar los datos en la BD</a>
	<br />
	<?php
	foreach ($actores as $actor){
		$actor = trim($actor);
		$query = "select id from actores where nombre like '%s'";
		$query = sprintf($query,
				$mysqli->real_escape_string($actor));
		$idactor = $mysqli->fetch_value($query);
		if(empty($idactor)){
			echo "el actor $actor no existe<br />";
			$query = "insert into actores (nombre,actorlink) values ('%s','%s')";
			$query = sprintf($query,
					$mysqli->real_escape_string($actor),
					FilmesDB::convierteURL($actor));
			if($mysqli->query($query)){
				$idactor = $mysqli->insert_id;
				echo "nuevo id $idactor<br />";
			}
		}
		else{
			echo "el actor $actor existe y tiene el id $idactor<br />";
		}
		if(!empty($idactor)){
			$query = "insert into filmes_miembros_reparto (filme,miembro) values ('%u','%u')";
			$query = sprintf($query,
					$_POST['id_filme'],
					$idactor);
			if($mysqli->query($query))
				echo "OK<br />";
			else
				echo "NOK<br />";
		}
		else
			echo "NOK<br />";
	}
}
?>