<?php
include_once 'clases/ImDB.php';
include_once 'includes/imdb.inc';
set_time_limit(0);
if(!empty($_POST['url']) && is_array($_POST['url'])){
	foreach ($_POST['url'] as $url){
		$imdb = new ImDB($url);
		$id_filme = 0;
		$tabla_filmes = array();
		$tabla_filmes['original'] = $imdb->dameTitulo();
		$tabla_filmes['fecha_alta'] = $tabla_filmes['fecha'] = date("Y-m-d H:i:s");
		if(!empty($tabla_filmes['original'])){
			if($imdb->es_capitulo)
				asignaDatosSerie($tabla_filmes);
			asignaDatosRecogidos($tabla_filmes);
			$id_filme = insertarDatosFilme($tabla_filmes);
			if($id_filme){
				insertarReparto($id_filme);
				insertarDatosVarios($id_filme);
			}
			else
				echo "<br>ERROR: No hay id filme definido<br>";
		}
	}
}
elseif(!empty($_POST['pagina']) || !empty($_GET['imdb'])){
	set_time_limit(0);
	if(!empty($_GET['imdb'])){
		$imdbnr = str_pad($_GET['imdb'], 7, "0", STR_PAD_LEFT);
		$_POST['pagina'] = "http://www.imdb.com/title/tt{$imdbnr}/episodes?season={$_GET['temporada']}";
	}
	echo "<p>Procesando <a href='{$_POST['pagina']}' target='_blank'>{$_POST['pagina']}</a></p>";
	$pagina = file_get_contents($_POST['pagina']);
	$hay_registros = false;
	if(!empty($pagina)){
		$pagina = preg_replace('[\n|\r|\n\r]','',$pagina);
		$datos = array();
		$rutas = array();
		$detalles = array();
		preg_match_all('|<div class=\"filmo(.*)<div|U', $pagina, $detalles);
		if(!empty($detalles[1])){
			foreach ($detalles[1] as $detalle){
				$detalle = "<div class=\"filmo{$detalle}</div>";
				$numero = devuelveImDB($detalle);
				if(!empty($numero[0])){
					$rutas[] = $numero[0];
					$datos[] = strip_tags(trim(str_replace("<", " <", $detalle)));
				}
			}
		}
		else{
			$detalles = array();
			preg_match_all('|<div class=\"list_item(.*)<div class=\"clear\">|U', $pagina, $detalles);
			if(!empty($detalles[1])){
				foreach ($detalles[1] as $detalle){
					$detalle = "<div class=\"list_item{$detalle}</div>";
					$numero = devuelveImDB($detalle);
					if(!empty($numero[0])){
						$rutas[] = $numero[0];
						$datos[] = trim(str_replace("                      Watch now                             Watch now                     Buy it at Amazon.co.uk", "", strip_tags(trim(str_replace("<", " <", $detalle)))));
					}
				}
			}
			else{
				$rutas = array_unique(devuelveImDB($pagina));
			}
		}
		echo "Encontradas ".count($rutas)." coincidencias<br/>";
		echo "A insertar <span id=insertar>0</span><br/>";
		if(!empty($rutas)){
			$ya_verificados = array();
			$hidden = "";
			foreach ($rutas as $i=>$cadena_numerica){
				$numero = $cadena_numerica * 1;
				if (!in_array ($numero, $ya_verificados)){
					$ya_verificados[] = $numero;
					$query = "select id from filmes where imdb = '$numero'";
					$result = $mysqli->query($query);
					if($result->num_rows < 1){
				  		$hay_registros = true;
	    				$hidden .= "<input type='hidden' class='{$cadena_numerica} pendientes' name='url[]' value='http://www.imdb.com/title/tt{$cadena_numerica}/' />";
						?>	 
						<div id="form_container" class='<?=$cadena_numerica?>'>
	                    <form id="form<?=$cadena_numerica?>" method="post" action="index.php?page=extraer" target="new">
							<fieldset>
	                        <legend align="left">Importar</legend>
	                        <label for="pagina">URL:</label>
	                        <input name="pagina" type="text" size="40" value="http://www.imdb.com/title/tt<?=$cadena_numerica?>/">
							<input type="button" data-form="<?=$cadena_numerica?>" value="Ir" class="botonIr">
							<input type="button" data-form="<?=$cadena_numerica?>" value="Eliminar" class="removeURL">
							<?php 
							if(!empty($datos[$i])){
								echo "<span>{$datos[$i]}</span>";
							}
							?>
							</fieldset>
						</form>
	                    </div>
						<?php
				  	}
				  	$result->close();
				}
			}
			?>
			<div id="form_container">
				<form method="post" action="#">
					<button name="importar_todos">Importar Todos</button>
					<?=$hidden?>
				</form>
			</div>
			<?php
		}
	}
	if(!$hay_registros)
		echo "<p>No se encontraron películas para importar</p>";
}
else{
	?>	 
	<div id="form_url">
    <form name="form1" method="post" action="index.php?page=imdb_busqueda">
		<fieldset>
        <legend align="left">Examinar</legend>
        <label for="pagina">URL:</label>
        <input name="pagina" type="text" size="40">
		<input type="submit" value="Ir">
        </fieldset>
	</form>
    </div>
	<?php
}
?>