<?php
function dameTituloCastellano($id_filme){
	global $mysqli;
	$query ="select id_pais,
		otitulo 
		from filmes_titulos_adicionales  
		where (idioma = '12' 
		or idioma = '66' 
		or (idioma is null and id_pais = '1'))
		and id_filme = '$id_filme' 
		order by id_pais,idioma";
	$title = $mysqli->fetch_assoc($query);
	if(!empty($title)){
		if($title['id_pais'] != 1){
			$query = "select original,
				id_pais  
				from filmes 
				left join filmes_paises on filmes_paises.id_filme = filmes.id 
				where filmes.id = '$id_filme'";
			$result = $mysqli->query($query);
			while ($row = $result->fetch_assoc()){
				if($row['id_pais'] == 1){
					$title['otitulo'] = $row['original'];
					break;
				}
			}
			$result->close();
		}
		return $title['otitulo'];
	}
	$query = "select original from filmes where id = '$id_filme'";
	return $mysqli->fetch_value($query);
}

function formatBytes($size, $precision = 2){
	$base = log($size) / log(1024);
	$suffixes = array('B', 'kB', 'MB', 'GB', 'TB');
	return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}
?>