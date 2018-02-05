<?php
global $mysqli;
$fmt = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, "EEEE',' d 'de' MMMM 'de' y");
echo '<div class="listados-peliculas-usuario">';
$query = "SELECT filmes.id
			,filmes.predeterminado
			,filmes.permalink
			,filmes.pais_predeterminado
			,filmes.plot_es
			,filmes.anno
			,filmes.cover
			,filmes.duracion
			,filmes.fecha
			,filmes.terminada
			,MAX(filmes_fechas_estreno.estreno) AS estreno
			,MAX(capitulos.temporada) AS temporadas
		FROM filmes
		INNER JOIN usuarios_filmes_agregados ON usuarios_filmes_agregados.id_filme = filmes.id
		LEFT JOIN filmes AS capitulos ON capitulos.serie = filmes.id
		LEFT JOIN filmes_fechas_estreno ON filmes_fechas_estreno.id_filme = capitulos.id
		WHERE usuarios_filmes_agregados.id_usuario = '{$_SESSION['id_usuario']}'
			AND filmes.es_serie = '1'
			AND usuarios_filmes_agregados.listado = '1'
		GROUP BY filmes.id
		ORDER BY estreno DESC";
$result = $mysqli->query($query);
printf('<div class="alert alert-info">Encontradas %u series que estás viendo actualmente</div>', $result->num_rows);
$resultados = array();
while ($row = $result->fetch_object()) {
    $urlCover = "";
    for ($i = $row->temporadas; $i >= 1; $i --) {
        if (file_exists("photos/series/110/{$row->id}_{$i}.jpg")) {
            $urlCover = "photos/series/110/{$row->id}_{$i}.jpg";
            break;
        }
    }
    if (empty($urlCover) && $row->cover) {
        $urlCover = "photos/filmes/110/{$row->id}.jpg";
    }
    printf('<div class="row"><div class="col-md-2"><img class="rounded-circle mx-auto d-block" src="%s" /></div><div class="col-md-10"><p><a href="index.php?page=filmes&id=%u">%s</a> (%s %u minutos)</p>', $urlCover, $row->id, $row->predeterminado, $row->pais_predeterminado, $row->duracion);
    $query = "SELECT filmes.id
				,predeterminado
				,capitulo
				,temporada
				,permalink
			FROM filmes
			INNER JOIN usuarios_filmes_agregados ON usuarios_filmes_agregados.id_filme = filmes.id
			WHERE serie = '$row->id'
			ORDER BY temporada DESC
				,capitulo DESC
			LIMIT 1";
    $ult = $mysqli->fetch_object($query);
    $ultimo_visto = 0;
    if (! empty($ult)) {
        $ultimo_visto = $ult->id;
        printf('<p><span class="glyphicon glyphicon-eye-open" title="último capítulo visto"></span> %sx%s <a href="index.php?page=filmes&id=%u">%s</a></p>', str_pad($ult->temporada, 2, 0, STR_PAD_LEFT), str_pad($ult->capitulo, 2, 0, STR_PAD_LEFT), $ult->id, $ult->predeterminado);
        $query = "SELECT filmes.id
					,predeterminado
					,capitulo
					,temporada
					,permalink
				FROM filmes
				INNER JOIN file ON file.pelicula = filmes.id
				WHERE serie = '$row->id'
					AND ((temporada = '$ult->temporada' AND capitulo > '$ult->capitulo') OR (temporada > '$ult->temporada'))
				ORDER BY temporada DESC
					,capitulo DESC
				LIMIT 1";
    } else {
        echo "<p>No has visto ningún capítulo de la serie</p>";
        $query = "SELECT filmes.id
					,predeterminado
					,capitulo
					,temporada
					,permalink
				FROM filmes
				INNER JOIN file ON file.pelicula = filmes.id
				WHERE serie = '$row->id'
				ORDER BY temporada DESC
					,capitulo DESC
				LIMIT 1";
    }
    $dwn = $mysqli->fetch_object($query);
    $ultimo_disponible = 0;
    if (! empty($dwn) && $dwn->id != $ultimo_visto) {
        $ultimo_disponible = $dwn->id;
        printf('<p><span class="glyphicon glyphicon-save" title="último capítulo disponible"></span> %sx%s <a href="index.php?page=filmes&id=%u">%s</a></p>', str_pad($dwn->temporada, 2, 0, STR_PAD_LEFT), str_pad($dwn->capitulo, 2, 0, STR_PAD_LEFT), $dwn->id, $dwn->predeterminado);
    } else {
        echo "<p>No tienes episodios disponibles para ver</p>";
    }
    $query = "SELECT filmes.id
				,predeterminado
				,capitulo
				,temporada
				,permalink
				,MIN(estreno) estreno
			FROM filmes
			INNER JOIN filmes_fechas_estreno ON filmes_fechas_estreno.id_filme = filmes.id
			WHERE serie = '$row->id'
			GROUP BY filmes.id
				,predeterminado
				,capitulo
				,temporada
				,permalink
			HAVING MIN(estreno) <= CURDATE()
			ORDER BY temporada DESC
				,capitulo DESC
			LIMIT 1";
    $lst = $mysqli->fetch_object($query);
    if (! empty($lst) && $lst->id != $ultimo_visto && $lst->id != $ultimo_disponible) {
        $datetime = new DateTime($lst->estreno);
        printf('<p><span class="glyphicon glyphicon-bullhorn" title="último capítulo emitido"></span> %sx%s <a href="index.php?page=filmes&id=%u">%s</a> el %s</td></tr>', str_pad($lst->temporada, 2, 0, STR_PAD_LEFT), str_pad($lst->capitulo, 2, 0, STR_PAD_LEFT), $lst->id, $lst->predeterminado, $fmt->format($datetime));
    }
    $query = "SELECT filmes.id
				,predeterminado
				,capitulo
				,temporada
				,permalink
				,MIN(estreno) estreno
			FROM filmes
			INNER JOIN filmes_fechas_estreno ON filmes_fechas_estreno.id_filme = filmes.id
			WHERE serie = '$row->id'
			GROUP BY filmes.id
				,predeterminado
				,capitulo
				,temporada
				,permalink
			HAVING MIN(estreno) > CURDATE()
			ORDER BY temporada
				,capitulo
			LIMIT 1";
    $nxt = $mysqli->fetch_object($query);
    if (! empty($nxt)) {
        $datetime = new DateTime($nxt->estreno);
        printf('<p><span class="glyphicon glyphicon-time" title="próximo capítulo"></span> %sx%s <a href="index.php?page=filmes&id=%u">%s</a> el %s</td></tr>', str_pad($nxt->temporada, 2, 0, STR_PAD_LEFT), str_pad($nxt->capitulo, 2, 0, STR_PAD_LEFT), $nxt->id, $nxt->predeterminado, $fmt->format($datetime));
    } else {
        if (! empty($row->terminada)) {
            // mirar si está terminada la serie
            echo "<p>Esta serie se ha dejado de emitir</p>";
        } else {
            // estimar el inicio de la proxima temporada
            $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::FULL, IntlDateFormatter::FULL, 'Europe/Madrid', IntlDateFormatter::GREGORIAN, "MMMM 'de' y");
            $query = "SELECT MIN(estreno) estreno
					FROM filmes_fechas_estreno
					INNER JOIN filmes ON filmes.id = filmes_fechas_estreno.id_filme
					WHERE filmes.serie = '$row->id'
						AND filmes.capitulo = '1'
					GROUP BY filmes.id
						,filmes.temporada
					ORDER BY filmes.temporada DESC
					LIMIT 1";
            $month = $mysqli->fetch_object($query);
            if ($month) {
                $datetime = new DateTime($month->estreno);
                $datetime->add(new DateInterval('P1Y'));
                printf('<p>Próxima temporada en %s</p>', $formatter->format($datetime));
            }
        }
    }
    if ($ultimo_visto) {
        $query = "SELECT filmes.id
					,predeterminado
					,capitulo
					,temporada
					,permalink
				FROM filmes
				WHERE serie = '$row->id'
					AND ((temporada = '$ult->temporada' AND capitulo > '$ult->capitulo') OR (temporada > '$ult->temporada'))
				ORDER BY temporada ASC
					,capitulo ASC
				LIMIT 1";
        $ppv = $mysqli->fetch_object($query);
        if (! empty($ppv) && ((! empty($nxt) && $nxt->id != $ppv->id) || empty($nxt))) {
            printf('<p><span class="glyphicon glyphicon-eye-close" title="próximo capítulo para ver"></span> %sx%s <a href="index.php?page=filmes&id=%u">%s</a></p>', str_pad($ppv->temporada, 2, 0, STR_PAD_LEFT), str_pad($ppv->capitulo, 2, 0, STR_PAD_LEFT), $ppv->id, $ppv->predeterminado);
        }
    }
    printf('<p class="text-justify">%s</p>', $row->plot_es);
    echo '</div></div><hr />';
}
$result->close();
echo '</div>';