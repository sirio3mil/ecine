<?php
global $mysqli;
$query = "SELECT count(*) as total
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE id_usuario = '9'
	AND imdb_ready = '1'
	AND serie IS NULL
	AND es_serie = '0'
	AND imdb IS NOT NULL";
$total = $mysqli->fetch_value($query);
?>
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=inserted><?=$total?></span> films inserted.</p>
	</div>
</div>
<?php
$query = "SELECT count(*) as total
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE id_usuario = '9'
	AND imdb_ready = '0'
	AND serie IS NULL
	AND es_serie = '0'
	AND imdb IS NOT NULL";
$total = $mysqli->fetch_value($query);
if(!empty($total)){
?>
<div class="ui-widget">
	<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=remain><?=$total?></span> films by inserting missing.</p>
	</div>
</div>
<div style="margin-top: 20px; padding: 0 .7em;">
<?php
$query = "SELECT filmes.imdb,
	usuarios_filmes_agregados.id
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE usuarios_filmes_agregados.id_usuario = '9'
	AND usuarios_filmes_agregados.imdb_ready = '0'
	AND filmes.serie IS NULL
	AND filmes.es_serie = '0'
	AND filmes.imdb IS NOT NULL
	ORDER BY usuarios_filmes_agregados.fecha
	LIMIT 100";
$result = $mysqli->query($query);
while($row = $result->fetch_object()){
	$numero = str_pad($row->imdb, 7, "0", STR_PAD_LEFT);
	echo "<button class=washlist id={$row->id}>tt{$numero}</button>";
}
$result->close();
?>
</div>
<?php
}
$query = "SELECT count(*) as total
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE id_usuario = '9'
	AND imdb_ready = '1'
	AND serie IS NULL
	AND es_serie = '1'
	AND imdb IS NOT NULL";
$total = $mysqli->fetch_value($query);
?>
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=sinserted><?=$total?></span> series inserted.</p>
	</div>
</div>
<?php
$query = "SELECT count(*) as total
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE id_usuario = '9'
	AND imdb_ready = '0'
	AND serie IS NULL
	AND es_serie = '1'
	AND imdb IS NOT NULL";
$total = $mysqli->fetch_value($query);
if(!empty($total)){
?>
<div class="ui-widget">
	<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=sremain><?=$total?></span> series by inserting missing.</p>
	</div>
</div>
<div style="margin-top: 20px; padding: 0 .7em;">
<?php
$query = "SELECT filmes.imdb,
	usuarios_filmes_agregados.id
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE usuarios_filmes_agregados.id_usuario = '9'
	AND usuarios_filmes_agregados.imdb_ready = '0'
	AND filmes.serie IS NULL
	AND filmes.es_serie = '1'
	AND filmes.imdb IS NOT NULL
	ORDER BY usuarios_filmes_agregados.fecha
	LIMIT 100";
$result = $mysqli->query($query);
while($row = $result->fetch_object()){
	$numero = str_pad($row->imdb, 7, "0", STR_PAD_LEFT);
	echo "<button class=swashlist id={$row->id}>tt{$numero}</button>";
}
$result->close();
?>
</div>
<?php
}
$query = "SELECT count(*) as total
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE id_usuario = '9'
	AND imdb_ready = '1'
	AND serie IS NOT NULL
	AND es_serie = '0'
	AND imdb IS NOT NULL";
$total = $mysqli->fetch_value($query);
?>
<div class="ui-widget">
	<div class="ui-state-highlight ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=einserted><?=$total?></span> episodes inserted.</p>
	</div>
</div>
<?php
$query = "SELECT count(*) as total
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE id_usuario = '9'
	AND imdb_ready = '0'
	AND serie IS NOT NULL
	AND es_serie = '0'
	AND imdb IS NOT NULL";
$total = $mysqli->fetch_value($query);
if(!empty($total)){
?>
<div class="ui-widget">
	<div class="ui-state-error ui-corner-all" style="margin-top: 20px; padding: 0 .7em;">
		<p><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span>
		<strong>Hey!</strong> <span id=eremain><?=$total?></span> episodes by inserting missing.</p>
	</div>
</div>
<div style="margin-top: 20px; padding: 0 .7em;">
<?php
$query = "SELECT filmes.imdb,
	usuarios_filmes_agregados.id
	FROM usuarios_filmes_agregados
	INNER JOIN filmes ON filmes.id = usuarios_filmes_agregados.id_filme
	WHERE usuarios_filmes_agregados.id_usuario = '9'
	AND usuarios_filmes_agregados.imdb_ready = '0'
	AND filmes.serie IS NOT NULL
	AND filmes.es_serie = '0'
	AND filmes.imdb IS NOT NULL
	ORDER BY usuarios_filmes_agregados.fecha
	LIMIT 98";
$result = $mysqli->query($query);
while($row = $result->fetch_object()){
	$numero = str_pad($row->imdb, 7, "0", STR_PAD_LEFT);
	echo "<button class=ewashlist id={$row->id}>tt{$numero}</button>";
}
$result->close();
?>
</div>
<?php
}
?>
<form method="post" target="ventana_detalle" action="" id="iformulariodetalle">
	<input type="hidden" value="" name="123" />
</form>
<form method="post" target="ventana" action="http://www.imdb.com/list/_ajax/edit" id="iformulario">
	<input type="hidden" value="" name="const" id="const" />
	<input type="hidden" value="" name="list_id" id="list_id" />
	<input type="hidden" value="title" name="ref_tag" id="ref_tag" />
	<input type="hidden" value="" name="49e6c" id="code_tag" />
</form>