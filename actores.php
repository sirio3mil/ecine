<?php
global $mysqli;
$query = "SELECT id,
	nombre,
	fecha,
	sexo,
	imdb,
	actorlink,
	birthDate,
	deathDate,
	birthPlace,
	deathPlace,
	altura,
	cover,
	actualizado,
	nacionalidad,
	omitir
	from actores
	where id = '{$_GET['id']}'";
$actor = $mysqli->fetch_assoc($query);
?>
<div class="row">
	<div class="col-xs-6 col-md-3">
		<div align='center'>
			<?php
			if($actor['cover']){
			    $path = "photos/actores/280/{$_GET['id']}.jpg";
				list($width, $height, $tipo, $atr) = getimagesize($path);
				echo "<img width='{$width}' height='{$height}' src='{$path}' alt='{$actor['nombre']}' />";
				$readable_size = FileSizeUtilities::toReadable(filesize($path));
				echo "<p>{$width}x{$height} ({$readable_size} bytes)</p>";
				$path = "photos/actores/original/{$_GET['id']}.jpg";
				list($width, $height, $tipo, $atr) = getimagesize($path);
				$readable_size = FileSizeUtilities::toReadable(filesize($path));
				echo "<p>{$width}x{$height} ({$readable_size} bytes)</p>";
				$path = "photos/actores/40/{$_GET['id']}.jpg";
				list($width, $height, $tipo, $atr) = getimagesize($path);
				$readable_size = FileSizeUtilities::toReadable(filesize($path));
				echo "<p>{$width}x{$height} ({$readable_size} bytes)</p>";
			}
			?>
			<button id='agregar-foto-actor' class='btn btn-success'>Agregar</button>
			<form method="post" action="insertar_foto.php?id=<?=$_GET['id']?>&tabla=actores" enctype="multipart/form-data">
				<input type="file" name="userfile" id="userfile" class="autosubmit" size="2" accept="image/jpeg" style="display: none" />
			</form>
			<div class="alert alert-info margin-top-10" role="alert">Tamaño Foto 280 x 350 pixels</div>
		</div>
	</div>
	<div class="col-xs-6 col-md-9">
		<div class="btn-group">
			<?php
			// mel+gibson&s=nm
			if(!empty($actor['imdb'])){
				?>
			<a class="btn btn-default" href="http://www.imdb.com/name/nm<?=str_pad($actor['imdb'], 7, "0", STR_PAD_LEFT)?>/" title="Ver ficha en IMDb" target="_blank">IMDb</a>
			<?php
			}
			else{
				?>
			<a class="btn btn-default" href="http://www.imdb.com/find?q=<?=urlencode($actor['nombre'])?>&s=nm" title="Buscar en IMDb" target="_blank">IMDb</a>
			<?php
			}
			?>
			<a class="btn btn-default" href='https://www.google.es/search?q=<?=urlencode($actor['nombre'])?>&hl=es&prmd=imvnso&tbm=isch&tbo=u&source=univ&sa=X&ei=yYeOUOqLBITF0QWU8IDIDg&ved=0CDcQsAQ&biw=1680&bih=906#q=<?=urlencode($actor['nombre'])?>&hl=es&tbm=isch&prmd=imvnso&source=lnt&tbs=isz:m&sa=X&ei=yoeOUI_BMOLG0QWLtoHACg&ved=0CCIQpwUoAg&bav=on.2,or.r_gc.r_pw.r_cp.r_qf.&fp=e45c0b737b485a16&bpcl=35466521&biw=1680&bih=906' title='Buscar imagenes en Google' target='_blank'>Google</a> <a class="btn btn-default" href="eliminar.php?id=<?=$_GET['id']?>&tabla=actores" title="Eliminar <?=$actor['nombre']?> de la base de datos">Eliminar</a> <a class="btn btn-default" href="actualizar_elenco.php?idactor=<?=$_GET['id']?>" title="Reiniciar <?=$actor['nombre']?>">Reiniciar</a>
		</div>
		<?php
		if($actor['actualizado']){
			?>
		<div class="alert alert-info margin-top-10" role="alert">Sincronizado con IMDb</div>
		<?php
		}
		$query = "SELECT id,nombre FROM actores_busqueda WHERE id_actor = '%u' AND nombre NOT LIKE '%s'";
		$query = sprintf($query, $_GET['id'], $mysqli->real_escape_string($actor['nombre']));
		$result = $mysqli->query($query);
		if($result->num_rows){
			?>
		<div class="panel panel-default margin-top-10">
			<ul class="list-group">
			<?php
			while($row = $result->fetch_object()){
				echo "<li class='list-group-item'><span class='borrar-datos-actores pointer' id='$row->id' target='actores_busqueda'>$row->nombre</span></li>";
			}
			?>
			</ul>
		</div>
		<?php
		}
		$result->close();
		?>
		<div class="input-group margin-top-10">
			<input placeholder='nombre' id='nombre' name='nombre' type='text' class="form-control auto-actualizar-campo-actor" value="<?=$actor['nombre']?>" /> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<input placeholder='imdb' id='imdb' name='imdb' type='text' class="form-control auto-actualizar-campo-actor" value="<?=$actor['nombre']?>" /> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<input placeholder='fecha de nacimiento' id='birthDate' name='birthDate' type='text' class="datepicker form-control" value="<?=$actor['birthDate']?>" /> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<input placeholder='lugar de nacimiento' id='birthPlace' name='birthPlace' type='text' class="form-control auto-actualizar-campo-actor" value="<?=$actor['birthPlace']?>" /> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<select id="nacionalidad" name="nacionalidad" class="form-control auto-actualizar-actor">
				<option value="">No definido</option>
				<?php
				$query = "select id, nombre from paises order by nombre";
				$result = $mysqli->query($query);
				while($row = $result->fetch_object()){
					echo "<option value='{$row->id}'";
					if($row->id === $actor['nacionalidad'])
						echo " selected";
					echo ">{$row->nombre}</option>";
				}
				$result->close();
				?>
			</select> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<input placeholder='fecha de fallecimiento' id='deathDate' name='deathDate' type='text' class="datepicker form-control" value="<?=$actor['deathDate']?>" /> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<input placeholder='lugar de fallecimiento' id='deathPlace' name='deathPlace' type='text' class="form-control auto-actualizar-campo-actor" value="<?=$actor['deathPlace']?>" /> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<input placeholder='altura' id='altura' name='altura' type='text' class="form-control auto-actualizar-campo-actor" value="<?=$actor['altura']?>" /> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<select id="sexo" name="sexo" class="form-control auto-actualizar-actor">
				<option value="">No definido</option>
				<?php
				$sexos = [
						"M" => "masculino",
						"F" => "femenino"
				];
				foreach($sexos as $sexo => $texto){
					echo "<option value='$sexo'";
					if($sexo === $actor['sexo'])
						echo " selected";
					echo ">$texto</option>";
				}
				?>
			</select> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="input-group margin-top-10">
			<select id="omitir" name="omitir" class="form-control auto-actualizar-actor">
				<?php
				$omitidos = array(
						"No",
						"si"
				);
				foreach($omitidos as $omitir => $texto){
					echo "<option value='$omitir'";
					if($omitir === ($actor['omitir'] * 1))
						echo " selected";
					echo ">$texto</option>";
				}
				?>
			</select> <span class="input-group-addon"><a class="actualizar-actor fa fa-edit pointer"></a></span>
		</div>
		<div class="margin-top-10">
		<?php
		$query = "select filmes_miembros_reparto.filme,
			filmes.original,
			filmes.predeterminado,
			filmes.anno,
			filmes_miembros_reparto.id,
			filmes_miembros_reparto.rol,
			filmes_miembros_reparto.alias,
			filmes_miembros_reparto.personaje
			from filmes_miembros_reparto
			inner join filmes on filmes_miembros_reparto.filme = filmes.id
			where miembro = '{$_GET['id']}'
			order by rol, anno";
		$result = $mysqli->query($query);
		$row = $result->fetch_object();
		if($row && $row->rol == 1){
			echo "<table class='datatable cell-border hover stripe'><caption>Actuando</caption><thead><tr><th>año</th><th>Original</th><th>Castellano</th><th>Personaje</th><th>Alias</th><th style='width:90px'></th></tr></thead><tbody>";
			do{
				if($row->rol == 1){
					echo "<tr><td>$row->anno</td><td><a href='index.php?page=filmes&id=$row->filme'>$row->original</a></td><td>$row->predeterminado</td><td><div class=edit id={$row->id}_personaje>$row->personaje</div></td><td><div class=edit id={$row->id}_alias>$row->alias</div></td><td><a class='eliminar-datos-actores fa fa-trash' target='filmes_miembros_reparto' id='$row->id'></a></td></tr>";
				}
				else{
					break;
				}
			}
			while($row = $result->fetch_object());
			echo "</tbody></table>";
		}
		if($row && $row->rol == 2){
			echo "<table class='datatable cell-border hover stripe'><caption>Escribiendo</caption><thead><tr><th>año</th><th>Original</th><th>Castellano</th><th>Alias</th><th style='width:90px'></th></tr></thead><tbody>";
			do{
				if($row->rol == 2){
					echo "<tr><td>$row->anno</td><td><a href='index.php?page=filmes&id=$row->filme'>$row->original</a></td><td>$row->predeterminado</td><td><div class=edit id={$row->id}_alias>$row->alias</div></td><td><a class='eliminar-datos-actores fa fa-trash' target='filmes_miembros_reparto' id='$row->id'></a></td></tr>";
				}
				else{
					break;
				}
			}
			while($row = $result->fetch_object());
			echo "</tbody></table>";
		}
		if($row && $row->rol == 3){
			echo "<table class='datatable cell-border hover stripe'><caption>Dirigiendo</caption><thead><tr><th>año</th><th>Original</th><th>Castellano</th><th>Alias</th><th style='width:90px'></th></tr></thead><tbody>";
			do{
				echo "<tr><td>$row->anno</td><td><a href='index.php?page=filmes&id=$row->filme'>$row->original</a></td><td>$row->predeterminado</td><td><div class=edit id={$row->id}_alias>$row->alias</div></td><td><a class='eliminar-datos-actores fa fa-trash' target='filmes_miembros_reparto' id='$row->id'></a></td></tr>";
			}
			while($row = $result->fetch_object());
			echo "</tbody></table>";
		}
		$result->close();
		?>
		</div>
	</div>
</div>
<div id='datos-php' data-imdb='<?=$actor['imdb']?>'></div>