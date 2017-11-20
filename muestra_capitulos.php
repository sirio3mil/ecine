<?php
include_once 'includes/filmes.inc';
$serie = $_GET['serie'];
if(esSerie($serie)){
	$temporadas = cuantasTemporadas($serie);
	for ($j=1;$j<=$temporadas;$j++){
		$query = "select id,
			predeterminado,
			capitulo,
			plot_es
			from filmes
			where serie = '$serie'
			and temporada = '$j'
			order by capitulo asc";
		$result = $mysqli->query($query);
		echo "<h1>Temporada $j</h1>";
		echo "<ul>";
		while($row = $result->fetch_assoc()){
			$query = "select min(estreno) as estreno
				from filmes_fechas_estreno
				where filmes_fechas_estreno.id_filme = '{$row['id']}'";
			$estreno = $mysqli->fetch_value($query);
			$capitulo = str_pad($row['capitulo'], 2, 0, STR_PAD_LEFT);
			$temporada = str_pad($j, 2, 0, STR_PAD_LEFT);
			echo "<li>{$temporada}x{$capitulo} .- <a href='index.php?page=filmes&id={$row['id']}' target='_blank'>{$row['predeterminado']}</a><br />$estreno<br />{$row['plot_es']}</li>";
		}
		echo "</ul>";
		$result->close();
	}
}
?>