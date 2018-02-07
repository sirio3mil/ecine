<?php
global $mysqli;
if(!empty($_GET['cadena']))
	$palabra = trim(urldecode($_GET['cadena']));
if(!empty($palabra)){
	$identificadores = array();
	$buscar_palabra = $mysqli->real_escape_string($palabra);
	$query = "select filmes.id,
		filmes_busqueda.titulo,
		filmes.predeterminado,
		filmes.anno,
		filmes.cover
		from filmes
		inner join filmes_busqueda on filmes.id = filmes_busqueda.id_filme
		where MATCH (titulo) AGAINST ('{$buscar_palabra}' IN NATURAL LANGUAGE MODE)";
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
				echo "</li>";
			}
		}
		echo "</ul>";
	}
	$result->close();
	$identificadores = [];
	$query = "select actores.id,
		actores_busqueda.nombre,
		actores.nombre,
		actores.cover
		from actores_busqueda
		inner join actores on actores.id = actores_busqueda.id_actor
		where MATCH (actores_busqueda.nombre) AGAINST ('{$buscar_palabra}' IN NATURAL LANGUAGE MODE)";
	$result = $mysqli->query($query);
	if($result->num_rows){
		echo "<h3>Actores</h3><ul>";
		while($row = $result->fetch_assoc()){
			if(!in_array($row['id'], $identificadores)) {
				$identificadores[] = $row['id'];
				echo "<li><a href='index.php?page=actores&id={$row['id']}'>{$row['nombre']}</a></li>";
			}
		}
		echo "</ul>";
	}
	$result->close();
}