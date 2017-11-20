<?php
global $mysqli;
$query = "SELECT count(*) as total
	FROM filmes_votos_usuarios
	INNER JOIN filmes ON filmes.id = filmes_votos_usuarios.id_filme
	WHERE id_usuario = '9'
	AND imdb IS NOT NULL
	AND imdb_rated = '1'";
$total = $mysqli->fetch_value($query);
?>
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=inserted><?=$total?></span> films rated.</p>
	</div>
</div>
<?php
$query = "SELECT count(*) as total
	FROM filmes_votos_usuarios
	INNER JOIN filmes ON filmes.id = filmes_votos_usuarios.id_filme
	WHERE id_usuario = '9'
	AND imdb IS NOT NULL
	AND serie IS NULL
	AND es_serie = '0'
	AND cine_adultos = '0'
	AND imdb_rated = '0'";
$total = $mysqli->fetch_value($query);
if(!empty($total)){
?>
<div class="ui-widget">
	<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=remain><?=$total?></span> films by rating missing.</p>
	</div>
</div>
<div style="margin-top: 20px; padding: 0 .7em;">
<table>
	<thead>
		<tr>
			<th>película</th>
			<th>Voto</th>
			<th>Data</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
	<?php
	$query = "SELECT filmes.id,
		filmes_votos_usuarios.id as filmes_votos_usuarios,
		filmes.original,
		filmes.predeterminado,
		filmes.permalink,
		filmes.anno,
		imdb,
		((voto * 2) - 1) AS voto
		FROM filmes_votos_usuarios
		INNER JOIN filmes ON filmes.id = filmes_votos_usuarios.id_filme
		WHERE id_usuario = '9'
		AND imdb IS NOT NULL
		AND serie IS NULL
		AND es_serie = '0'
		AND cine_adultos = '0'
		AND imdb_rated = '0'
		ORDER BY voto DESC
		LIMIT 100";
	$result = $mysqli->query($query);
	while($row = $result->fetch_object()){
		?>
		<tr>
			<td><?=$row->original?></td>
			<td><?=$row->voto?></td>
			<td><?=$row->predeterminado." ".$row->anno?></td>
			<td><button class=ratelist id=<?=$row->filmes_votos_usuarios?> data-rate=<?=$row->voto?>>tt<?=str_pad($row->imdb, 7, "0", STR_PAD_LEFT)?></button></td>
		</tr>
		<?php
		echo "";
	}
	$result->close();
	?>
	</tbody>
</table>
</div>
<?php
}
?>
<form method="post" target="ventana_detalle" action="" id="iformulariodetalle">
	<input type="hidden" value="" name="123" />
</form>