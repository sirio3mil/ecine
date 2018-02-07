<?php
include_once 'includes/filmes.inc';
include_once 'includes/imdb.inc';
include_once 'includes/admin.inc';
include_once 'includes/archivos.inc';
global $mysqli;
$fmt = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, "EEEE',' d 'de' MMMM 'de' y, H:mm");
$id_filme = $_GET['id'];
$_SESSION['id_page'] = $id_filme;
$query = "select id from filmes where predeterminado is null";
$result = $mysqli->query($query);
if ($result->num_rows) {
    $actualizados = 0;
    while ($row = $result->fetch_object()) {
        if (! empty($row->id)) {
            $h1 = dameTituloCastellano($row->id);
            $url_filme = FilmesDB::convierteURL($h1);
            if (! empty($h1) && ! empty($url_filme)) {
                $query = sprintf("update filmes set predeterminado = '%s', permalink = '%s' where id = '%d'", $mysqli->real_escape_string($h1), $mysqli->real_escape_string($url_filme), $row->id);
                $mysqli->query($query);
                if ($mysqli->affected_rows) {
                    $actualizados += $mysqli->affected_rows;
                }
            }
        }
    }
    if ($actualizados) {
        printf('<div class="alert alert-success margin-top-5 alert-update-films" role="alert">%u filmes sin título predeterminado actualizados</div>', $actualizados);
    }
}
$result->close();
$query = "select count(*) from filmes where actor_predeterminado is null";
if ($mysqli->fetch_value($query)) {
    $query = "update filmes
			set actor_predeterminado = (
				select actores.nombre
				from filmes_miembros_reparto
				inner join actores on filmes_miembros_reparto.miembro = actores.id
				where filmes_miembros_reparto.filme = filmes.id
				and filmes_miembros_reparto.rol = '1'
				order by actores.id
				limit 1
			)
			where actor_predeterminado is null";
    $mysqli->query($query);
    if ($mysqli->affected_rows) {
        printf('<div class="alert alert-success margin-top-5 alert-update-films" role="alert">%u filmes sin actor predeterminado actualizados</div>', $mysqli->affected_rows);
    }
}
$query = "select count(*) from filmes where director_predeterminado is null";
if ($mysqli->fetch_value($query)) {
    $query = "update filmes
			set director_predeterminado = (
				select actores.nombre
				from filmes_miembros_reparto
				inner join actores on filmes_miembros_reparto.miembro = actores.id
				where filmes_miembros_reparto.filme = filmes.id
				and filmes_miembros_reparto.rol = '3'
				order by actores.id
				limit 1
			)
			where director_predeterminado is null";
    $mysqli->query($query);
    if ($mysqli->affected_rows) {
        printf('<div class="alert alert-success margin-top-5 alert-update-films" role="alert">%u filmes sin director predeterminado actualizados</div>', $mysqli->affected_rows);
    }
}
$query = "SELECT COUNT(*) FROM filmes WHERE pais_predeterminado IS NULL AND serie IS NULL AND cine_adultos = 0";
if ($mysqli->fetch_value($query)) {
    $query = "update filmes
			set pais_predeterminado = (
				select case
                    when count(*) = 1 then (
                        select paises.castellano
    				    from paises
    				    inner join filmes_paises r on r.id_pais = paises.id
    				    where r.id_filme = filmes.id
                    )
                    when count(*) = 0 then null
                    else 'Coproducción' end pais
                from filmes_paises
                where filmes_paises.id_filme = filmes.id
			)
			where pais_predeterminado is null";
    $mysqli->query($query);
    if ($mysqli->affected_rows) {
        printf('<div class="alert alert-success margin-top-5 alert-update-films" role="alert">%u filmes sin pais predeterminado actualizados</div>', $mysqli->affected_rows);
    }
}
$query = "select id,
	original,
	fecha,
	fecha_alta,
	anno,
	duracion,
	plot_es,
	recomendada,
	color,
	sonido,
	imdb,
	trailer_lk,
	sitio_trailer,
	online_lk,
	critica_lk,
	serie,
	temporada,
	capitulo,
	cover,
	bigcover,
	predeterminado,
	permalink,
	descarga_lk,
	es_serie,
	terminada,
	actor_predeterminado,
	director_predeterminado,
	pais_predeterminado,
	total_votes,
	total_value,
	fa_checked,
	fa_updated,
	boletin,
	cine_adultos,
	broken_link_check,
	rss,
	bloqueada,
	presupuesto,
	moneda
	from filmes
	where id = '$id_filme'";
$filme = $mysqli->fetch_assoc($query);
if ($filme['total_votes']) {
    $rating = round($filme['total_value'] / $filme['total_votes'], 1);
} else {
    $rating = 0;
}
$query = "SELECT voto FROM filmes_votos_usuarios WHERE id_filme = {$id_filme}";
$voto = $mysqli->fetch_value($query);
$query = "select id_keyword
	from filmes_etiquetas
	where id_filme = '$id_filme'";
$etiquetas = $mysqli->fetch_array($query);
$imagenes = [];
if (! $filme['capitulo']) {
    $obj = new stdClass();
    $obj->relative = "photos/filmes/110/{$id_filme}.jpg";
    $obj->path = null;
    $obj->season = null;
    if ($filme['cover']) {
        $image = realpath($obj->relative);
        if ($image) {
            $obj->path = $image;
        }
    }
    $imagenes[] = $obj;
    $obj = new stdClass();
    $obj->relative = "photos/filmes/original/{$id_filme}.jpg";
    $obj->path = null;
    $obj->season = null;
    if ($filme['bigcover']) {
        $image = realpath($obj->relative);
        if ($image) {
            $obj->path = $image;
        }
    }
    $imagenes[] = $obj;
    if ($filme['es_serie']) {
        $temporadas = cuantasTemporadas($id_filme);
        for ($i = 1; $i <= $temporadas; $i ++) {
            $obj = new stdClass();
            $obj->relative = "photos/series/110/{$id_filme}_{$i}.jpg";
            $obj->path = null;
            $obj->season = $i;
            $image = realpath($obj->relative);
            if ($image) {
                $obj->path = $image;
            }
            $imagenes[] = $obj;
        }
    }
}
?>
<div class="row contenedor-global" data-pelicula="<?=$id_filme?>">
	<div class="col-xs-6 col-md-9">
		<div id="contenedor"></div>
		<div class="accordion">
			<h3>Datos básicos de <?=$filme['predeterminado']?></h3>
			<div>
				<div class="rating-container clearfix">
					<div id="rating-pelicula" data-value="<?=$rating?>" class="pull-left"></div>
					<div class="counter pull-left"><?=$rating?></div>
					<div class="pull-right margin-left-10">
						Tu voto <span id="voto-usuario"><?=intval($voto)?></span>
					</div>
					<div class="pull-right">
						Puntuación actual <span id="rating-total"><?=$rating?></span>
					</div>
				</div>
				<div class="input-group margin-top-10">
					<input placeholder='nombre original' id='original' name='original' type='text' class="form-control autoblur" value="<?=$filme['original']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<input placeholder='nombre predeterminado' id='predeterminado' name='predeterminado' type='text' class="form-control autoblur" value="<?=$filme['predeterminado']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="row margin-top-10">
					<div class="col-md-6">
						<div class="input-group">
							<span class="input-group-addon">S</span><input placeholder='temporada' id='temporada' name='temporada' type='text' class="form-control autoblur" value="<?=$filme['temporada']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
						</div>
					</div>
					<div class="col-md-6">
						<div class="input-group">
							<span class="input-group-addon">E</span><input placeholder='capitulo' id='capitulo' name='capitulo' type='text' class="form-control autoblur" value="<?=$filme['capitulo']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
						</div>
					</div>
				</div>
				<div class="input-group margin-top-10">
					<textarea id="plot_es" name="plot_es" class="form-control" rows="10"><?=$filme['plot_es']?></textarea>
					<span class="input-group-addon"><a class="fa fa-edit pointer actualizar"></a></span>
				</div>
				<hr />
				<?php
    $query = "select id, fecha from usuarios_filmes_pendientes where id_filme = '{$id_filme}'";
    $pendiente = $mysqli->fetch_object($query);
    $query = "select id, lugar, fecha from usuarios_filmes_agregados where id_filme = '{$id_filme}'";
    $agregada = $mysqli->fetch_object($query);
    $personalizado = null;
    $datetime = null;
    if ($agregada) {
        if ($filme['es_serie']) {
            $personalizado = "Sigues esta serie desde ";
        } else {
            $personalizado = ($filme['serie']) ? "Visto " : "Vista ";
            switch ($agregada->lugar) {
                case "v":
                case "d":
                    $personalizado .= "en casa ";
                    break;
                case "c":
                    $personalizado .= "en el cine ";
                    break;
                case "t":
                    $personalizado .= "en la tele ";
                    break;
                case "o":
                    $personalizado .= "online ";
                    break;
            }
        }
        $datetime = new DateTime($agregada->fecha);
    } elseif (! $filme['es_serie']) {
        $query = "SELECT MIN(file.agregado) FROM file WHERE file.pelicula = {$id_filme}";
        $disponible = $mysqli->fetch_value($query);
        if ($disponible) {
            $personalizado = "Esta disponible desde ";
            $datetime = new DateTime($disponible);
        } else if ($pendiente) {
            $personalizado = "Esta pendiente desde ";
            $datetime = new DateTime($pendiente->fecha);
        }
    }
    if ($personalizado) {
        printf('<div id="mensaje-estado-usuario" class="alert alert-info margin-top-10" role="alert">%s el %s</div>', $personalizado, $fmt->format($datetime));
    }
    ?>
				<div class="btn-toolbar margin-top-10" role="toolbar">
					<?php
    if (! $agregada) {
        print('<button role="button" class="btn btn-default" id="agregar-pelicula-vista">Agregar</button>');
        if (! $pendiente && ! $filme['es_serie']) {
            print('<button role="button" class="btn btn-default" id="agregar-pelicula-pendiente">Pendiente</button>');
        }
    }
    if ($filme['imdb']) {
        print('<a role="button" class="btn btn-default" title="actualizar desde ImDB" id="updateimdb">Actualizar</a>');
        if ($filme['serie']) {
            printf('<a role="button" class="btn btn-default" title="ver online" href="index.php?page=filmes&id=%u">Serie</a>', $filme['serie']);
            $siguiente = dameCapituloSiguiente($id_filme);
            if ($siguiente) {
                printf("<a role='button' class='btn btn-default' href='index.php?page=filmes&id=%u'>Siguiente capítulo</a>", $siguiente->id);
            }
        }
    }
    if ($filme['es_serie']) {
        printf("<a role='button' class='btn btn-default' href='index.php?page=capitulos&serie=%u'>Ver capítulos</a>", $id_filme);
        if ($filme['duracion']) {
            print("<a role='button' class='btn btn-default' id='actualizar-duracion-capitulos'>Actualizar duración capítulos</a>");
        }
        $temporadas = cuantasTemporadas($id_filme);
        if ($temporadas) {
            if (! $filme['terminada']) {
                $temporadas ++;
            }
        } else {
            $temporadas = 1;
        }
        print('<div class="btn-group dropup"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Temporadas <span class="caret"></span></button><ul class="dropdown-menu">');
        for ($i = 1; $i <= $temporadas; $i ++) {
            printf("<li><a href='index.php?page=imdb_busqueda&temporada=%u&imdb=%u' target='_blank'>S%s</a></li>", $i, $filme['imdb'], str_pad($i, 2, 0, STR_PAD_LEFT));
        }
        print('</ul></div>');
    }
    printf('<a role="button" class="btn btn-danger" title="eliminar película" href="eliminar.php?id=%u&tabla=filmes&id_filme=%u">Eliminar</a>', $id_filme, $id_filme);
    ?>
					<div class="btn-group dropup">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Enlaces <span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<?php
    $predeterminado_url_encoded = urlencode($filme['predeterminado']);
    $original_url_encoded = urlencode($filme['original']);
    if ($filme['serie']) {
        $query = "SELECT original FROM filmes WHERE id = %u";
        $serie_url_encoded = urlencode($mysqli->fetch_value(sprintf($query, $filme['serie'])));
    } else {
        $serie_url_encoded = $original_url_encoded;
    }
    printf('<li><a id="enlace-imdb" title="ver la página de ImDB" href="%s" target="_blank">IMDb</a></li>', creaURL($filme['imdb']));
    printf('<li><a title="buscar en film affinity" href="http://www.filmaffinity.com/es/search.php?stext=%s&stype=all" target="busqueda_affinity">FilmAffinity</a></li>', $original_url_encoded);
    printf('<li><a title="buscar poster en Google" href="https://www.google.es/search?q=%s+%s&safe=off&hl=es&source=lnms&tbm=isch&sa=X" target="busqueda_google">Google</a></li>', $original_url_encoded, $filme["anno"]);
    printf('<li><a target="_blank" href="http://www.youtube.com/results?search_query=%s+trailer" title="buscar trailers en youtube">Youtube</a></li>', $predeterminado_url_encoded);
    printf('<li><a target="_blank" href="http://www.elitetorrent.net/busqueda/%s" title="buscar en elite torrent">Elitetorrent</a></li>', $predeterminado_url_encoded);
    printf('<li><a target="_blank" href="http://www.mejorenvo.com/secciones.php?sec=buscador&q=%s" title="buscar en versión original">Mejorenvo</a></li>', $serie_url_encoded);
    printf('<li><a target="_blank" href="http://doblaje.wikia.com/wiki/index.php?search=%s&fulltext=Search" title="doblaje en latinoamerica">Wikia</a></li>', $original_url_encoded);
    printf('<li><a target="_blank" href="http://tepasmas.com/search/node/%s" title="curiosidades de cine">Tepasmas</a></li>', $predeterminado_url_encoded);
    if ($filme['serie']) {
        printf('<li><a target="_blank" href="http://www.subdivx.com/index.php?buscar=%s+s%se%s&accion=5&masdesc=&subtitulos=1&realiza_b=1" title="subtitulos en español">Subdivx</a></li>', $serie_url_encoded, str_pad($filme['temporada'], 2, 0, STR_PAD_LEFT), str_pad($filme['capitulo'], 2, 0, STR_PAD_LEFT));
    } else {
        printf('<li><a target="_blank" href="http://www.subdivx.com/index.php?buscar=%s+%u&accion=5&masdesc=&subtitulos=1&realiza_b=1" title="subtitulos en español">Subdivx</a></li>', $serie_url_encoded, $filme["anno"]);
    }
    printf('<li><a target="_blank" href="https://www.yify-torrent.org/search/%s/" title="descargar torrent con yify">Yify</a></li>', $original_url_encoded);
    if (! $filme['serie']) {
        printf("<li><a target='_blank' href='https://zooqle.com/search?q=%s+%u' title='torrents de series en HD'>Zooqle</a></li>", $original_url_encoded, $filme["anno"]);
    } else {
        printf("<li><a target='_blank' href='https://zooqle.com/search?q=%s+s%se%s' title='torrents de series en HD'>Zooqle</a></li>", $serie_url_encoded, str_pad($filme['temporada'], 2, 0, STR_PAD_LEFT), str_pad($filme['capitulo'], 2, 0, STR_PAD_LEFT));
    }
    ?>
						</ul>
					</div>
				</div>
			</div>
			<h3>Archivos</h3>
			<div>
				<?php
    $query = "SELECT file.file_id
							,file.file_name
						FROM file
						WHERE file.pelicula IS NULL
							AND mostrar = 1
							AND existente = 1
						ORDER BY file_name DESC";
    $result = $mysqli->query($query);
    if ($result->num_rows) {
        print('<div class="row margin-bottom-10"><div class="col-md-12"><div class="input-group"><select multiple id="archivos-descargados" class="form-control">');
        while ($row = $result->fetch_object()) {
            printf('<option value="%u">%s</option>', $row->file_id, $row->file_name);
        }
        print('</select><span class="input-group-addon"><a id="asignar-archivos-descargados" class="fa fa-edit pointer"></a></span></div></div></div>');
    }
    $result->close();
    ?>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    $query = "SELECT DISTINCT file.file_id
									,CASE
										WHEN file.complete_name IS NULL THEN CONCAT(file.file_name, '.', file.file_extension)
										ELSE file.complete_name
									END complete_name
									,file.existente
									,file.agregado
									,file_audio.format
									,file_video.codec
									,SEC_TO_TIME(CEIL(file.duration / 1000)) AS duration
									,file_audio.bit_rate
									,file_video.width
									,file_video.height
									,file_video.display_aspect_ratio
									,file_video.frame_rate
									,file.file_size AS size
                                    ,file.file_name
								FROM file
								LEFT JOIN file_audio ON file_audio.file = file.file_id
								LEFT JOIN file_video ON file_video.file = file.file_id
								WHERE file.pelicula = {$id_filme}
								ORDER BY file.existente DESC";
    $result = $mysqli->query($query);
    if ($result->num_rows) {
        while ($row = $result->fetch_object()) {
            printf("<tr class='%s'><td><a title='%s'>%s</a></td><td>%s</td><td>%s</td><td>%u / %u (%s)<br />%s<br />%s<br />%s</td><td>%s</td></tr>", $row->existente ? "" : "danger", $row->complete_name, str_replace(".", " ", $row->file_name), human_filesize($row->size), $row->duration, $row->width, $row->height, $row->display_aspect_ratio, $row->frame_rate, $row->codec, $row->format, (new DateTime($row->agregado))->format("d/m/Y"));
        }
    }
    $result->close();
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<h3>Enlaces y categorias</h3>
			<div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon">http://www.imdb.com/title/tt</span> <input id='imdb' name='imdb' type='text' class="form-control autoblur" value="<?=$filme['imdb']?>" /> <span class="input-group-addon">/</span> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="row margin-top-10">
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon"> <input class="actualizar-flag-filme" type="checkbox" name="es_serie" <?=$filme['es_serie'] ? "checked" : ""?>>
							</span> <span class="input-group-addon">serie</span>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon"> <input class="actualizar-flag-filme" type="checkbox" name="terminada" <?=$filme['terminada'] ? "checked" : ""?>>
							</span> <span class="input-group-addon">terminada</span>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon"> <input class="actualizar-flag-filme" type="checkbox" name="cine_adultos" <?=$filme['cine_adultos'] ? "checked" : ""?>>
							</span> <span class="input-group-addon">porno</span>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon"> <input class="actualizar-flag-filme" type="checkbox" name="erotica" <?=(in_array(2166, $etiquetas)) ? "checked" : ""?>>
							</span> <span class="input-group-addon">erótica</span>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon"> <input class="actualizar-flag-filme" type="checkbox" name="gay" <?=(in_array(60451, $etiquetas)) ? "checked" : ""?>>
							</span> <span class="input-group-addon">temática gay</span>
						</div>
					</div>
					<div class="col-md-2">
						<div class="input-group">
							<span class="input-group-addon"> <input class="actualizar-flag-filme" type="checkbox" name="hechos_reales" <?=(in_array(2174, $etiquetas)) ? "checked" : ""?>>
							</span> <span class="input-group-addon">hechos reales</span>
						</div>
					</div>
				</div>
				<div class="input-group margin-top-10">
					<input placeholder='enlace para ver online' id='online_lk' name='online_lk' type='text' class="form-control autoblur" value="<?=$filme['online_lk']?>" /> <span class="input-group-addon"><a class="fa fa-edit pointer actualizar"></a></span> <span class="input-group-addon"><a class="fa fa-trash pointer" id='elimina_online_lk'></a></span>
				</div>
				<div class="input-group margin-top-10">
					<input placeholder='enlace para descarga directa' id='descarga_lk' name='descarga_lk' type='text' class="form-control autoblur" value="<?=$filme['descarga_lk']?>" /> <span class="input-group-addon"><a class="fa fa-edit pointer actualizar"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<input placeholder='enlace para crítica especializada' id='critica_lk' name='critica_lk' type='text' class="form-control autoblur" value="<?=$filme['critica_lk']?>" /> <span class="input-group-addon"><a class="fa fa-edit pointer actualizar"></a></span>
				</div>
			</div>
			<?php
$sql = "select filmes_fechas_estreno.id as id,
				castellano,
				estreno,
				soporte
				from filmes_fechas_estreno
				inner join paises on id_pais=paises.id
				where id_filme = '$id_filme'
				order by castellano";
$result = $mysqli->query($sql);
if ($result->num_rows) {
    ?>
			<h3>Fechas de estreno</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
							<?php
    while ($row = $result->fetch_assoc())
        echo "<tr><td>{$row['estreno']}</td><td>{$row['soporte']}</td><td>{$row['castellano']}</td><td><a class='fa fa-trash eliminar' data-target='filmes_fechas_estreno' data-id='{$row['id']}'></a></td></tr>";
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$sql = "select filmes_titulos_adicionales.otitulo,
				filmes_titulos_adicionales.notas,
				filmes_titulos_adicionales.id,
				paises.castellano
				from filmes_titulos_adicionales
				left join paises on filmes_titulos_adicionales.id_pais = paises.id
				where id_filme = '$id_filme'
				order by castellano";
$result = $mysqli->query($sql);
if ($result->num_rows) {
    ?>
			<h3>También conocida como</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
							<?php
    while ($row = $result->fetch_assoc())
        echo "<tr><td>{$row['otitulo']}</td><td>{$row['castellano']}</td><td>{$row['notas']}</td><td><a class='fa fa-trash eliminar' data-target='filmes_titulos_adicionales' data-id='{$row['id']}'></a></td></tr>";
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$sql = "select filmes_busqueda.titulo,
				filmes_busqueda.id
				from filmes_busqueda
				where id_filme = '$id_filme'
				order by titulo";
$result = $mysqli->query($sql);
if ($result->num_rows) {
    ?>
			<h3>Títulos de búsqueda</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
							<?php
    while ($row = $result->fetch_assoc())
        echo "<tr><td>{$row['titulo']}</td><td><a class='fa fa-trash eliminar' data-target='filmes_busqueda' data-id='{$row['id']}'></a></td></tr>";
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
?>
			<h3>Reparto</h3>
			<div>
			<?php
$query = "SELECT filmes_miembros_reparto.id
						,filmes_miembros_reparto.miembro
						,actores.nombre
						,actores.imdb
						,actores.cover
						,filmes_miembros_reparto.personaje
					FROM filmes_miembros_reparto
					INNER JOIN actores ON filmes_miembros_reparto.miembro = actores.id
					WHERE filmes_miembros_reparto.filme = '$id_filme'
						AND filmes_miembros_reparto.rol = '1'
					ORDER BY filmes_miembros_reparto.id";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
				<div>
					<form name='modificar_actor'>
						<div class="panel panel-default">
							<table class="table">
								<thead>
									<tr>
										<th></th>
										<th></th>
										<th>Reparto</th>
										<th>Personaje</th>
									</tr>
								</thead>
								<tbody>
								<?php
    while ($row = $result->fetch_assoc()) {
        ?>
									<tr>
										<td>
										<?php
        if ($row['cover']) {
            printf('<img class="rounded-circle mx-auto d-block" src="photos/actores/40/%u.jpg" />', $row['miembro']);
        }
        ?>
										</td>
										<td>
										<?php
        if ($row['imdb']) {
            printf('<a class="fa fa-film" href="http://www.imdb.com/name/nm%s/" target="_blank"></a>', str_pad($row['imdb'], 7, "0", STR_PAD_LEFT));
        }
        ?>
										</td>
										<td><a href="index.php?page=actores&id=<?=$row['miembro']?>" target="_blank"><?=$row['nombre']?></a></td>
										<td><?=$row['personaje']?></td>
									</tr>
									<?php
    }
    ?>
								</tbody>
							</table>
						</div>
					</form>
				</div>
				<?php
}
$result->close();
?>
			</div>

		</div>
	</div>
	<div class="col-xs-6 col-md-3">
		<div style='text-align: center'>
		<?php
if (! empty($imagenes)) {
    foreach ($imagenes as $image) {
        if ($image->path) {
            $data = getimagesize($image->path);
            printf("<div style='display:inline-block;width:120px;height:250px;text-align:center'>" . "<img width='110' height='162' src='%s' class='pointer delete-movie-image' />" . "<br />%ux%u" . "<br />%s" . "<br />%s" . "</div>", $image->relative, $data[0], $data[1], formatBytes(filesize($image->path)), $image->season ? "S" . str_pad($image->season, 2, 0, STR_PAD_LEFT) : "&nbsp;");
        } elseif ($image->season) {
            printf("<div style='display:inline-block;width:120px;height:250px;text-align:center'>" . "<i class='fa fa-puzzle-piece fa-5x add-movie-image pointer' data-tabla='series' data-temporada='%u' data-id='%u' style='padding-top:73px'></i>" . "</div>", $image->season, $id_filme);
        } else {
            printf("<div style='display:inline-block;width:120px;height:250px;text-align:center'>" . "<i class='fa fa-puzzle-piece fa-5x add-movie-image pointer' data-tabla='filmes' data-id='%u' style='padding-top:73px'></i>" . "</div>", $id_filme);
        }
    }
}
?>
		</div>
		<div class="accordion">
			<h3>Otros Datos</h3>
			<div>
				<div class="input-group">
					<input placeholder='actor predeterminado' id='actor_predeterminado' name='actor_predeterminado' type='text' class="autoblur form-control" value="<?=$filme['actor_predeterminado']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<input placeholder='director predeterminado' id='director_predeterminado' name='director_predeterminado' type='text' class="autoblur form-control" value="<?=$filme['director_predeterminado']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<input placeholder='pais predeterminado' id='pais_predeterminado' name='pais_predeterminado' type='text' class="autoblur form-control" value="<?=$filme['pais_predeterminado']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-clock-o" aria-hidden="true"></i></span> <input placeholder='duracion' id='duracion' name='duracion' type='text' class="autoblur form-control" value="<?=$filme['duracion']?>" /> <span class="input-group-addon">minutos</span> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-calendar" aria-hidden="true"></i></span> <input placeholder='año' id='anno' name='anno' type='text' class="autoblur form-control" value="<?=$filme['anno']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-thumbs-up" aria-hidden="true"></i></span> <input placeholder='votos totales' id='total_votes' name='total_votes' type='text' class="autoblur form-control" value="<?=$filme['total_votes']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-calculator" aria-hidden="true"></i></span> <input placeholder='puntuación total' id='total_value' name='total_value' type='text' class="autoblur form-control" value="<?=$filme['total_value']?>" /> <span class="input-group-addon"><?=($filme['total_votes']) ? round($filme['total_value'] / $filme['total_votes'], 2) : 0?></span> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-paint-brush" aria-hidden="true"></i></span> <select name="color" id="color" class="form-control">
						<option value="">No definido</option>
						<?php
    $query = "select distinct(color) from filmes where color is not null";
    $result = $mysqli->query($query);
    while ($row = $result->fetch_assoc()) {
        $color = trim($row['color']);
        if (! empty($color)) {
            echo "<option value='$color' ";
            if ($filme['color'] == $color)
                echo "selected";
            echo ">$color</option>";
        }
    }
    $result->close();
    ?>
					</select>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-microphone" aria-hidden="true"></i></span> <input placeholder='sonido' id='sonido' name='sonido' type='text' class="autoblur form-control" value="<?=$filme['sonido']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-credit-card" aria-hidden="true"></i></span> <input placeholder='presupuesto' id='presupuesto' name='presupuesto' type='text' class="autoblur form-control" value="<?=$filme['presupuesto']?>" /> <span class="input-group-addon"><a class="actualizar fa fa-edit pointer"></a></span>
				</div>
				<div class="input-group margin-top-10">
					<span class="input-group-addon"><i class="fa fa-university" aria-hidden="true"></i></span> <select name="moneda" id="moneda" class="form-control">
						<option value="">No definida</option>
						<?php
    $divisas = array(
        1 => "€",
        2 => "$"
    );
    foreach ($divisas as $divisa => $simbolo) {
        echo "<option value='$divisa' ";
        if ($filme['moneda'] == $divisa)
            echo "selected";
        echo ">$simbolo</option>";
    }
    ?>
					</select>
				</div>
				<div class="alert alert-info margin-top-10" role="alert"><?=$fmt->format(new DateTime($filme['fecha']))?></div>
			</div>
			<?php
$query = "select filmes_miembros_reparto.id,
				filmes_miembros_reparto.miembro,
				actores.imdb,
				actores.cover,
				actores.nombre
				from filmes_miembros_reparto
				inner join actores on filmes_miembros_reparto.miembro = actores.id
				where filmes_miembros_reparto.filme = '$id_filme'
				and filmes_miembros_reparto.rol = '3'
				order by actores.nombre";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
			<h3>Directores</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    while ($row = $result->fetch_assoc()) {
        ?>
							<tr>
								<td>
								<?php
        if (! $row['cover']) {
            ?>
									<i class="fa fa-user-times" aria-hidden="true"></i>
									<?php
        } else {
            ?>
									<i class="fa fa-user" aria-hidden="true"></i>
									<?php
        }
        ?>
								</td>
								<td>
								<?php
        if ($row['imdb']) {
            ?>
									<a class="fa fa-film" href="http://www.imdb.com/name/nm<?=str_pad($row['imdb'], 7, "0", STR_PAD_LEFT)?>/" target="_blank"></a>
									<?php
        }
        ?>
								</td>
								<td><a href="index.php?page=actores&id=<?=$row['miembro']?>" target="_blank"><?=$row['nombre']?></a></td>
								<td><a class='fa fa-trash eliminar' data-target='filmes_miembros_reparto' data-id='<?=$row['id']?>'></a></td>
							</tr>
							<?php
    }
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$query = "select filmes_miembros_reparto.id as id,
				filmes_miembros_reparto.miembro,
				actores.imdb,
				actores.cover,
				actores.nombre
				from filmes_miembros_reparto
				inner join actores on filmes_miembros_reparto.miembro = actores.id
				where filmes_miembros_reparto.filme = '$id_filme'
				and filmes_miembros_reparto.rol = '2'
				order by actores.nombre";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
			<h3>Escritores</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    while ($row = $result->fetch_assoc()) {
        ?>
							<tr>
								<td>
								<?php
        if (! $row['cover']) {
            ?>
									<i class="fa fa-user-times" aria-hidden="true"></i>
									<?php
        } else {
            ?>
									<i class="fa fa-user" aria-hidden="true"></i>
									<?php
        }
        ?>
								</td>
								<td>
								<?php
        if ($row['imdb']) {
            ?>
									<a class="fa fa-film" href="http://www.imdb.com/name/nm<?=str_pad($row['imdb'], 7, "0", STR_PAD_LEFT)?>/" target="_blank"></a>
									<?php
        }
        ?>
								</td>
								<td><a href="index.php?page=actores&id=<?=$row['miembro']?>" target="_blank"><?=$row['nombre']?></a></td>
								<td><a class='fa fa-trash eliminar' data-target='filmes_miembros_reparto' data-id='<?=$row['id']?>'></a></td>
							</tr>
							<?php
    }
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$query = "select estudios.nombre
				from filmes_estudios
				inner join estudios on id_estudio = estudios.id
				where id_filme = '$id_filme'";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
			<h3>Distribuidora</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    while ($row = $result->fetch_assoc())
        echo "<tr><td>{$row['nombre']}</td><tr>";
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$query = "select filmes_idiomas.id,
				idiomas.castellano
				from filmes_idiomas
				inner join idiomas on id_idioma = idiomas.id
				where id_filme = '$id_filme'";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
			<h3>Idiomas</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    while ($row = $result->fetch_assoc())
        echo "<tr><td>{$row['castellano']}</td><td><a class='fa fa-trash eliminar' data-target='filmes_idiomas' data-id='{$row['id']}'></a></td></tr>";
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$query = "select filmes_paises.id as id,
				paises.castellano
				from filmes_paises
				inner join paises on id_pais=paises.id
				where id_filme = '$id_filme'
				and castellano is not null";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
			<h3>Paises</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>{$row['castellano']}</td><td><a class='fa fa-trash eliminar' data-target='filmes_paises' data-id='{$row['id']}'></a></td></tr>";
    }
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$query = "select filmes_generos.id as id,
				tipos.castellano
				from filmes_generos
				inner join tipos on id_tipo = tipos.id
				where id_filme = '$id_filme'
				and castellano is not null";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
			<h3>Géneros</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    while ($row = $result->fetch_assoc())
        echo "<tr><td>{$row['castellano']}</td><td><a class='fa fa-trash eliminar' data-target='filmes_generos' data-id='{$row['id']}'></a></td></tr>";
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
$query = "select filmes_clasificaciones.id,
				nombre,
				castellano,
				certificado
				from filmes_clasificaciones
				inner join paises on id_pais = paises.id
				where id_filme = '$id_filme'";
$result = $mysqli->query($query);
if ($result->num_rows) {
    ?>
			<h3>Clasificación</h3>
			<div>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
    while ($row = $result->fetch_assoc())
        echo "<tr><td>{$row['certificado']}</td><td>{$row['castellano']}</td><td><a class='fa fa-trash eliminar' data-target='filmes_clasificaciones' data-id='{$row['id']}'></a></td></tr>";
    ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php
}
$result->close();
?>
			<h3>Etiquetas</h3>
			<div>
				<?php
    $query = "select keyword,
					mostrar,
					filmes_etiquetas.id as id
					from etiquetas
					inner join filmes_etiquetas on etiquetas.id = id_keyword
					where id_filme = '$id_filme'
					order by keyword";
    $result = $mysqli->query($query);
    if ($result->num_rows) {
        ?>
				<div class="panel panel-default">
					<table class="table">
						<tbody>
						<?php
        while ($row = $result->fetch_object())
            echo "<tr><td><a class='eliminar' data-target='filmes_etiquetas' data-id='$row->id'>$row->keyword</a></td><td>$row->mostrar</td></tr>";
        ?>
						</tbody>
					</table>
				</div>
				<?php
    }
    $result->close();
    ?>
			</div>
		</div>
	</div>
</div>
<form id="add-movie-image-form" method="post" action="insertar_foto.php" enctype="multipart/form-data">
	<input type="file" name="userfile" class="autosubmit" accept="image/gif, image/jpeg, image/png" style="display: none" /> <input type="hidden" name="id" /> <input type="hidden" name="tabla" /> <input type="hidden" name="temporada" />
</form>
<form id="formimdbnew" name="formimdbnew" method="post" action="index.php?page=extraer">
	<input id="pagina-imdb" name="pagina" type="hidden" value="" />
</form>
<div id='datos-php' data-imdb='<?=$filme['imdb']?>'></div>