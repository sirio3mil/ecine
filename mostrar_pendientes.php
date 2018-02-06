<?php
include_once 'includes/archivos.inc';
global $mysqli;
$disponibles = (! $_POST || isset($_POST['disponibles'])) ? 1 : 0;
$pendientes = (! $_POST || isset($_POST['pendientes'])) ? 1 : 0;
$fmt = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, "EEEE d 'de' MMMM 'de' y 'a las' H:mm");
$splfmt = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, "EEEE d 'de' MMMM 'de' y");
?>
<div>
	<div class="panel panel-primary" id="filtros-peliculas-pendientes">
		<div class="panel-heading">Filtros</div>
		<div class="panel-body">
			<form method="post" action="#">
				<div class="row">
					<div class="col-md-3">
						<div class="checkbox">
							<label> <input type="checkbox" name="disponibles" <?=($disponibles) ? "checked" : ""?> /> Mostrar disponibles
							</label>
						</div>
					</div>
					<div class="col-md-3">
						<div class="checkbox">
							<label> <input type="checkbox" name="pendientes" <?=($pendientes) ? "checked" : ""?> /> Mostrar pendientes
							</label>
						</div>
					</div>
				</div>
				<hr />
				<input type="submit" class="btn btn-default" name="boton" />
			</form>
		</div>
	</div>
	<div class="listados-peliculas-usuario">
	<?php
if ($pendientes) {
    if (! $disponibles) {
        $query = "SELECT filmes.id
					,filmes.original
					,filmes.imdb
					,filmes.predeterminado
					,filmes.permalink
					,filmes.pais_predeterminado
					,filmes.plot_es
					,filmes.anno
					,filmes.duracion
					,filmes.cover
					,filmes.total_value value
					,filmes.total_votes votes
					,usuarios_filmes_pendientes.fecha
				FROM filmes
				INNER JOIN usuarios_filmes_pendientes ON usuarios_filmes_pendientes.id_filme = filmes.id
				LEFT JOIN file ON file.pelicula = filmes.id
					AND file.existente = 1
				WHERE filmes.serie IS NULL
					AND filmes.es_serie = 0
					AND file.file_id IS NULL
				ORDER BY fecha DESC";
        $result = $mysqli->query($query);
        printf('<div class="alert alert-info">Encontradas %u películas pendientes para ver no disponibles</div>', $result->num_rows);
    } else {
        $query = "SELECT filmes.id
					,filmes.original
					,filmes.imdb
					,filmes.predeterminado
					,filmes.permalink
					,filmes.pais_predeterminado
					,filmes.plot_es
					,filmes.anno
					,filmes.duracion
					,filmes.cover
					,filmes.total_value value
					,filmes.total_votes votes
					,file.agregado fecha
					,file.complete_name
					,file_audio.format
					,file_video.codec
					,SEC_TO_TIME(CEIL(file.duration / 1000)) AS duration
					,file_audio.bit_rate
					,file_video.width
					,file_video.height
					,file_video.display_aspect_ratio
					,file_video.frame_rate
					,file.file_size size
				FROM filmes
				INNER JOIN usuarios_filmes_pendientes ON usuarios_filmes_pendientes.id_filme = filmes.id
				INNER JOIN file ON file.pelicula = filmes.id
					AND file.existente = 1
				LEFT JOIN file_audio ON file_audio.file = file.file_id
				LEFT JOIN file_video ON file_video.file = file.file_id
				WHERE filmes.serie IS NULL
					AND filmes.es_serie = 0
				ORDER BY file.agregado DESC
					,usuarios_filmes_pendientes.fecha DESC";
        $result = $mysqli->query($query);
        printf('<div class="alert alert-info">Encontradas %u películas disponibles para ver</div>', $result->num_rows);
    }
} else {
    if (! $disponibles) {
        $query = "SELECT filmes.id
					,filmes.original
					,filmes.imdb
					,filmes.predeterminado
					,filmes.permalink
					,filmes.pais_predeterminado
					,filmes.plot_es
					,filmes.anno
					,filmes.duracion
					,filmes.cover
					,filmes.total_value value
					,filmes.total_votes votes
					,usuarios_filmes_agregados.fecha
				FROM filmes
				INNER JOIN usuarios_filmes_agregados ON usuarios_filmes_agregados.id_filme = filmes.id
				LEFT JOIN file ON file.pelicula = filmes.id
					AND file.existente = 1
				WHERE filmes.serie IS NULL
					AND filmes.es_serie = 0
					AND file.file_id IS NULL
				ORDER BY fecha DESC";
        $result = $mysqli->query($query);
        printf('<div class="alert alert-info">Encontradas %u películas ya vistas pero que no están disponibles</div>', $result->num_rows);
    } else {
        $query = "SELECT filmes.id
					,filmes.original
					,filmes.imdb
					,filmes.predeterminado
					,filmes.permalink
					,filmes.pais_predeterminado
					,filmes.plot_es
					,filmes.anno
					,filmes.duracion
					,filmes.cover
					,filmes.total_value value
					,filmes.total_votes votes
					,file.agregado fecha
					,file.complete_name
					,file_audio.format
					,file_video.codec
					,SEC_TO_TIME(CEIL(file.duration / 1000)) AS duration
					,file_audio.bit_rate
					,file_video.width
					,file_video.height
					,file_video.display_aspect_ratio
					,file_video.frame_rate
					,file.file_size size
				FROM filmes
				INNER JOIN usuarios_filmes_agregados ON usuarios_filmes_agregados.id_filme = filmes.id
				INNER JOIN file ON file.pelicula = filmes.id
					AND file.existente = 1
				LEFT JOIN file_audio ON file_audio.file = file.file_id
				LEFT JOIN file_video ON file_video.file = file.file_id
				WHERE filmes.serie IS NULL
					AND filmes.es_serie = 0
				ORDER BY file.agregado DESC
					,usuarios_filmes_agregados.fecha DESC";
        $result = $mysqli->query($query);
        printf('<div class="alert alert-info">Encontradas %u películas ya vistas que están disponibles para ver</div>', $result->num_rows);
    }
}
$agregadas = [];
$query = "SELECT DISTINCT actores.id
				,actores.nombre
                ,filmes_miembros_reparto.id orden
			FROM actores
			INNER JOIN filmes_miembros_reparto ON filmes_miembros_reparto.miembro = actores.id
			WHERE filmes_miembros_reparto.filme = ?
				AND actores.cover = 1
			ORDER BY orden
			LIMIT 10";
$stmt = $mysqli->prepare($query);
if (! $stmt) {
    throw new Exception($mysqli->error);
}
$pelicula = 0;
if (! $stmt->bind_param("i", $pelicula)) {
    throw new Exception($stmt->error);
}
if (! $disponibles) {
    $query = "SELECT MIN(estreno) fecha
				FROM filmes_fechas_estreno
				WHERE filmes_fechas_estreno.id_filme = ?
				LIMIT 1";
    $stmt2 = $mysqli->prepare($query);
    if (! $stmt2) {
        throw new Exception($mysqli->error);
    }
    if (! $stmt2->bind_param("i", $pelicula)) {
        throw new Exception($stmt->error);
    }
}
while ($row = $result->fetch_object()) {
    if (! in_array($row->id, $agregadas)) {
        $agregadas[] = $row->id;
        $predeterminado_url_encoded = urlencode($row->predeterminado);
        $original_url_encoded = urlencode($row->original);
        $urlCover = "";
        if ($row->cover) {
            $urlCover = "photos/filmes/110/{$row->id}.jpg";
        }
        printf('<div class="row datos-pelicula-listado"><div class="col-md-2"><img class="rounded-circle mx-auto d-block" src="%s" /></div><div class="col-md-10"><div class="row"><div class="col-md-10"><p><a href="index.php?page=filmes&id=%u">%s</a></p>', $urlCover, $row->id, ($row->predeterminado != $row->original) ? "$row->predeterminado ($row->original - $row->anno)" : "$row->predeterminado ($row->anno)");
        if ($row->votes) {
            $rating = round($row->value / $row->votes, 1);
        } else {
            $rating = 0;
        }
        printf('<div class="rating-pelicula-pendiente margin-bottom-10" data-value="%.1f" title="%.1f / 5"></div>', $rating, $rating);
        printf('<p>%s', $row->pais_predeterminado);
        if ($row->duracion) {
            printf(' %u minutos', $row->duracion);
        }
        $pelicula = $row->id;
        if (! $disponibles) {
            $stmt2->execute();
            $resultado = $stmt2->get_result();
            if ($resultado->num_rows) {
                $estreno = $resultado->fetch_object();
                $datetime = new DateTime($estreno->fecha);
                $ahora = new DateTime();
                printf(', %s el %s', $datetime > $ahora ? 'estreno' : 'estrenada', $splfmt->format($datetime));
            }
            $resultado->close();
        }
        echo '</p></div>';
        echo '<div class="col-md-2"><div class="btn-toolbar" role="toolbar">';
        echo '<div class="btn-group dropdown"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-link"></span></button>';
        echo '<ul class="dropdown-menu">';
        printf('<li><a id="enlace-imdb" title="ver la página de ImDB" href="http://www.imdb.com/title/tt%s/" target="_blank">IMDb</a></li>', str_pad($row->imdb, 7, "0", STR_PAD_LEFT));
        printf('<li><a title="buscar en film affinity" href="http://www.filmaffinity.com/es/search.php?stext=%s&stype=all" target="busqueda_affinity">FilmAffinity</a></li>', $original_url_encoded);
        printf('<li><a title="buscar poster en Google" href="https://www.google.es/search?q=%s+%s&safe=off&hl=es&source=lnms&tbm=isch&sa=X" target="busqueda_google">Google</a></li>', $original_url_encoded, $row->anno);
        printf('<li><a target="_blank" href="http://www.youtube.com/results?search_query=%s+trailer" title="buscar trailers en youtube">Youtube</a></li>', $predeterminado_url_encoded);
        printf('<li><a target="_blank" href="http://www.elitetorrent.net/busqueda/%s" title="buscar en elite torrent">Elitetorrent</a></li>', $predeterminado_url_encoded);
        printf('<li><a target="_blank" href="http://www.mejorenvo.com/secciones.php?sec=buscador&q=%s" title="buscar en versión original">Mejorenvo</a></li>', $original_url_encoded);
        printf('<li><a target="_blank" href="http://doblaje.wikia.com/wiki/index.php?search=%s&fulltext=Search" title="doblaje en latinoamerica">Wikia</a></li>', $original_url_encoded);
        printf('<li><a target="_blank" href="http://tepasmas.com/search/node/%s" title="curiosidades de cine">Tepasmas</a></li>', $predeterminado_url_encoded);
        printf('<li><a target="_blank" href="http://www.subdivx.com/index.php?buscar=%s+%u&accion=5&masdesc=&subtitulos=1&realiza_b=1" title="subtitulos en español">Subdivx</a></li>', $original_url_encoded, $row->anno);
        printf("<li><a target='_blank' href='https://zooqle.com/search?q=%s+%u' title='torrents de series en HD'>Zooqle</a></li>", $original_url_encoded, $row->anno);
        printf('<li><a target="_blank" href="https://www.yify-torrent.org/search/%s/" title="descargar torrent con yify">Yify</a></li>', $original_url_encoded);
        echo '</ul></div>';
        printf('<div class="btn-group" role="group"><button type="button" class="btn btn-danger eliminar-pelicula-listado" data-pelicula="%u"><span class="glyphicon glyphicon-minus"></span></button></div>', $row->id);
        echo '</div>';
        echo '</div>';
        echo '</div>';
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows) {
            echo '<div class="clearfix margin-bottom-10">';
            while ($actor = $resultado->fetch_object()) {
                printf('<div class="pull-left margin-right-10"><a href="index.php?page=actores&id=%u" title="%s" class="rounded-circle mx-auto d-block" style="background-image: url(photos/actores/original/%u.jpg)"></a></div>', $actor->id, $actor->nombre, $actor->id);
            }
            echo '</div>';
        }
        $resultado->close();
        printf('<p>Desde el %s</p>', $fmt->format(new DateTime($row->fecha)));
        printf('<p class="text-justify">%s</p>', $row->plot_es);
        if ($disponibles) {
            printf('<p>%s</p>', dirname($row->complete_name));
            printf('<p>%s %s %u / %u (%s) %s %s %s</p>', human_filesize($row->size), $row->duration, $row->width, $row->height, $row->display_aspect_ratio, $row->frame_rate, $row->codec, $row->format);
        }
        echo '</div></div><hr />';
    }
}
$result->close();
$stmt->close();
if (isset($stmt2)) {
    $stmt2->close();
}
?>
	</div>
</div>