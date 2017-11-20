<h3>Leyenda</h3>
<p>
	<span class='ui-icon ui-icon-video' style='display:inline-block'></span>Online
	<span class='ui-icon ui-icon-scissors' style='display:inline-block'></span>Trailer
	<span class='ui-icon ui-icon-pencil' style='display:inline-block'></span>Crítica
	<span class='ui-icon ui-icon-image' style='display:inline-block'></span>Cover
	<span class='ui-icon ui-icon-image' style='display:inline-block'></span>
	<span class='ui-icon ui-icon-image' style='display:inline-block'></span>Big Cover
</p>
<?php
include_once 'includes/funciones.inc';
global $mysqli;
if(!empty($_GET['cadena']))
	$palabra = trim(urldecode($_GET['cadena']));
if(!empty($palabra)){
	$identificadores = array();
	$buscar_palabra = $mysqli->real_escape_string($palabra);
	$longitud = strlen($buscar_palabra);
	$query = "select filmes.id,
		titulo,
		case
	       	when (CHAR_LENGTH(titulo) - $longitud) > 0 then 1
			else 0
		end as exacta,
		filmes.predeterminado,
		filmes.anno,
		filmes.online_lk,
		filmes.trailer_lk,
		filmes.critica_lk,
		filmes.cover,
		filmes.bigcover
		from filmes_busqueda
		inner join filmes on filmes.id = filmes_busqueda.id_filme
		where titulo like '%$buscar_palabra%'
		order by exacta";
	$result = $mysqli->query($query);
	if($result->num_rows){
		echo "<h3>Títulos exactos</h3><ul>";
		while($row = $result->fetch_assoc()){
			if(!in_array($row['id'], $identificadores)){
				$identificadores[] = $row['id'];
				echo "<li><a href='index.php?page=filmes&id={$row['id']}'>{$row['predeterminado']}";
				if($row['predeterminado'] != $row['titulo'])
					echo " [{$row['titulo']}]";
				if(!empty($row['anno']))
					echo " ({$row['anno']})";
				echo "</a>";
				if(!empty($row['online_lk']))
					echo "<span class='ui-icon ui-icon-video' style='display:inline-block'></span>";
				if(!empty($row['trailer_lk']))
					echo "<span class='ui-icon ui-icon-scissors' style='display:inline-block'></span>";
				if(!empty($row['critica_lk']))
					echo "<span class='ui-icon ui-icon-pencil' style='display:inline-block'></span>";
				if(!empty($row['cover']))
					echo "<span class='ui-icon ui-icon-image' style='display:inline-block'></span>";
				if(!empty($row['bigcover']))
					echo "<span class='ui-icon ui-icon-image' style='display:inline-block'></span>";
				echo "</li>";
			}
		}
		echo "</ul>";
	}
	$result->close();
	$query = "select distinct actores.id,
		actores.nombre,
		actores.cover
		from actores_busqueda
		inner join actores on actores.id = actores_busqueda.id_actor
		where actores_busqueda.nombre like '%$buscar_palabra%'";
	$result = $mysqli->query($query);
	if($result->num_rows){
		echo "<h3>Actores</h3><ul>";
		while($row = $result->fetch_assoc()){
			$tamanno = "foto";
			if($row['cover']){
				list($ancho, $altura, $tipo, $atr) = getimagesize("photos/actores/original/{$row['id']}.jpg");
				$tamanno .= " {$ancho}x{$altura}";
			}
			echo "<li><a href='index.php?page=actores&id={$row['id']}'>{$row['nombre']} ({$row['cover']} {$tamanno})</a></li>";
		}
		echo "</ul>";
	}
	$result->close();
	$buscando = cambiaWhere("titulo", $buscar_palabra);
	if(!empty($buscando)){
		$query = "select filmes.id,
			titulo,
			filmes.predeterminado,
			filmes.anno,
			filmes.online_lk,
			filmes.trailer_lk,
			filmes.critica_lk,
			filmes.cover,
			filmes.bigcover
			from filmes_busqueda
			inner join filmes on filmes.id = filmes_busqueda.id_filme
			where $buscando";
		$result = $mysqli->query($query);
		if($result->num_rows){
			echo "<h3>Títulos parciales</h3><ul>";
			$valores_ordenados = $ids_ordenados = array();
			while($row = $result->fetch_assoc()){
				$ids_ordenados[$row['id']] = cuantasTiene($palabra, $row['titulo']);
				$valores_ordenados[$row['id']] = $row;
			}
			arsort($ids_ordenados);
			reset($ids_ordenados);
			while(list($key, $val) = each($ids_ordenados)){
				if(!in_array($key, $identificadores)){
					$identificadores[] = $key;
					echo "<li><a href='index.php?page=filmes&id=$key'>{$valores_ordenados[$key]['predeterminado']}";
					if($valores_ordenados[$key]['predeterminado'] != $valores_ordenados[$key]['titulo'])
						echo " [{$valores_ordenados[$key]['titulo']}]";
					if(!empty($valores_ordenados[$key]['anno']))
						echo " ({$valores_ordenados[$key]['anno']})";
					echo "</a>";
					if(!empty($valores_ordenados[$key]['online_lk']))
						echo "<span class='ui-icon ui-icon-video' style='display:inline-block'></span>";
					if(!empty($valores_ordenados[$key]['trailer_lk']))
						echo "<span class='ui-icon ui-icon-scissors' style='display:inline-block'></span>";
					if(!empty($valores_ordenados[$key]['critica_lk']))
						echo "<span class='ui-icon ui-icon-pencil' style='display:inline-block'></span>";
					if(!empty($valores_ordenados[$key]['cover']))
						echo "<span class='ui-icon ui-icon-image' style='display:inline-block'></span>";
					if(!empty($valores_ordenados[$key]['bigcover']))
						echo "<span class='ui-icon ui-icon-image' style='display:inline-block'></span>";
					echo "</li>";
				}
			}
			echo "</ul>";
		}
		$result->close();
	}
}
?>