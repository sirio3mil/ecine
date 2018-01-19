<?php
global $mysqli;
if(!empty($_POST)){
	if(!empty($_POST['idiomas_id'])){
		foreach($_POST['idiomas_id'] as $pos=>$id){
			$titulo = trim($_POST['idiomas_castellano'][$pos]);
			$id_real = $_POST['idiomas_duplicado'][$pos];
			if(!empty($id)){
				if(!empty($id_real)){
					$queries = array();
					$queries[] = "update ignore filmes_idiomas set id_idioma = '%d' where id_idioma = '%d'";
					$queries[] = "update ignore filmes_titulos_adicionales set idioma = '%d' where idioma = '%d'";
					foreach($queries as $query){
						$query = sprintf($query,
								$id_real,
								$id
							);
						$mysqli->query($query);
						if($mysqli->affected_rows){
							echo "{$mysqli->affected_rows} modificados<br />$query<br />";
						}
					}
					$sql = "delete from idiomas where id = '%d'";
					$query = sprintf($sql,$id);
					$mysqli->query($query);
					if($mysqli->affected_rows){
						echo "{$mysqli->affected_rows} <strong>borrado</strong><br />$query<br />";
					}
				}
				elseif(!empty($titulo)){
					$sql = "update idiomas set castellano = '%s' where id = '%d'";
					$query = sprintf($sql,$mysqli->real_escape_string($titulo),$id);
					$mysqli->query($query);
				}
			}
		}
	}
	if(!empty($_POST['generos_id'])){
		foreach($_POST['generos_id'] as $pos=>$id){
			$titulo = trim($_POST['generos_castellano'][$pos]);
			if(!empty($id) && !empty($titulo)){
				$query = "update tipos set castellano = '%s' where id = '%d'";
				$query = sprintf($query,
						$mysqli->real_escape_string($titulo),
						$id
					);
				$mysqli->query($query);
			}
		}
	}
	if(!empty($_POST['paises_id'])){
		foreach($_POST['paises_id'] as $pos=>$id){
			$titulo = trim($_POST['paises_castellano'][$pos]);
			$id_real = $_POST['paises_duplicado'][$pos];
			if(!empty($id)){
				if(!empty($id_real)){
					$queries = array();
					$queries[] = "update ignore filmes_paises set id_pais = '%u' where id_pais = '%u'";
					$queries[] = "update ignore filmes_titulos_adicionales set id_pais = '%u' where id_pais = '%u'";
					$queries[] = "update ignore filmes_fechas_estreno set id_pais = '%u' where id_pais = '%u'";
					$queries[] = "update ignore filmes_clasificaciones set id_pais = '%u' where id_pais = '%u'";
					$queries[] = "update ignore actores set nacionalidad = '%u' where nacionalidad = '%u'";
					foreach($queries as $query){
						$query = sprintf($query,
								$id_real,
								$id
							);
						$mysqli->query($query);
						if($mysqli->affected_rows){
							echo "{$mysqli->affected_rows} modificados<br />$query<br />";
						}
					}
					$sql = "delete from paises where id = '%d'";
					$query = sprintf($sql,$id);
					$mysqli->query($query);
					if($mysqli->affected_rows){
						echo "{$mysqli->affected_rows} <strong>borrado</strong><br />$query<br />";
					}
				}
				elseif(!empty($titulo)){
					$sql = "update paises set castellano = '%s' where id = '%d'";
					$query = sprintf($sql,$mysqli->real_escape_string($titulo),$id);
					$mysqli->query($query);
				}
			}
		}
	}
}
$sql = "select id,
	castellano
	from idiomas
	where castellano is not null
	order by castellano";
$result = $mysqli->query($sql);
$option_idiomas = "<option value='' selected>seleccione</option>";
while ($row = $result->fetch_object())
	$option_idiomas .= "<option value='$row->id'>$row->castellano</option>";
$result->close();
$sql = "select id,
	castellano
	from paises
	where castellano is not null
	order by castellano";
$result = $mysqli->query($sql);
$option_paises = "<option value='' selected>seleccione</option>";
while ($row = $result->fetch_object())
	$option_paises .= "<option value='$row->id'>$row->castellano</option>";
$result->close();
echo "<form method='post' action='#'><input type='submit' value='enviar' /><br/>";
$sql = "select id,
	idioma
	from idiomas
	where castellano is null";
$result = $mysqli->query($sql);
if($result->num_rows){
	echo "<h3>Idiomas</h3>";
	while($row = $result->fetch_object()){
		?>
		<label><?=$row->idioma?></label>
		<select name="idiomas_duplicado[]">
			<?=$option_idiomas?>
		</select>
		<input type="text" value="" name="idiomas_castellano[]" />
		<input type="hidden" value="<?=$row->id?>" name="idiomas_id[]" />
		<br/>
		<?php
	}
}
$result->close();
$sql = "select id,
	tipo
	from tipos
	where castellano is null";
$result = $mysqli->query($sql);
if($result->num_rows){
	echo "<h3>Géneros</h3>";
	while($row = $result->fetch_object()){
		?>
		<label><?=$row->tipo?></label>
		<input type="text" value="" name="generos_castellano[]" />
		<input type="hidden" value="<?=$row->id?>" name="generos_id[]" />
		<br/>
		<?php
	}
}
$result->close();
$sql = "select id,
	nombre
	from paises
	where castellano is null";
$result = $mysqli->query($sql);
if($result->num_rows){
	echo "<h3>Paises</h3>";
	while($row = $result->fetch_object()){
		?>
		<label><?=$row->nombre?></label>
		<select name="paises_duplicado[]">
			<?=$option_paises?>
		</select>
		<input type="text" value="" name="paises_castellano[]" />
		<input type="hidden" value="<?=$row->id?>" name="paises_id[]" />
		<br/>
		<?php
	}
}
$result->close();
echo "<input type='submit' value='enviar' /></form>";
?>