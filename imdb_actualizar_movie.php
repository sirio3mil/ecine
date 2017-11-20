<div id="progressbar">
	<div class="progress-label">Esperando...</div>
</div>
<div class="panel panel-default margin-top-10">
	<div class="input-group">
		<select class="form-control" id="listado-buscar">
			<option value="1">peliculas sin actualizar</option>
			<option value="2">peliculas pendientes de estreno</option>
			<option value="3">peliculas estrenadas en los últimos dos años</option>
			<option value="4">peliculas descargadas</option>
			<option value="5">capitulos de series que sigo y que no tengo</option>
			<option value="6">películas pendientes de ver</option>
			<option value="7">películas sin país predeterminado</option>
		</select> <span class="input-group-addon buscar-peliculas fa fa-search pointer"></span> <span class="input-group-addon accion-peliculas detener fa fa-stop pointer"></span>
	</div>
</div>
<div class="panel panel-default margin-top-10" id="contenedor-actualizaciones">
	<table class="table">
		<thead>
			<tr>
				<th id="th-current"></th>
				<th><span id="span_encontradas"></span><br /> <span id="span_total"></span></th>
				<th id="th-media"></th>
				<th id="th-hasta"></th>
			</tr>
		</thead>
		<tbody id="log"></tbody>
	</table>
</div>