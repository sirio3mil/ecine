<?php
include_once 'acceso_restringido.php';
include_once 'clases/filmesdb.php';
include_once 'clases/reloj.php';
$mysqli = new filmesDB();
if(isset($_GET["page"])){
	$open_page = trim($_GET["page"]);
}
$open_page = (empty($open_page))?'series':$open_page;
?>
<!DOCTYPE>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?=ucfirst(strtolower($open_page))?> | Mantenimiento General</title>
<link rel="stylesheet" type="text/css" href="bower_components/jquery-ui/themes/base/jquery-ui.min.css" />
<link rel="stylesheet" type="text/css" href="bower_components/components-font-awesome/css/font-awesome.min.css" />
<link rel="stylesheet" type="text/css" href="bower_components/bootstrap/dist/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="bower_components/datatables.net-dt/css/jquery.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="bower_components/datatables.net-bs/css/dataTables.bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="bower_components/sumoselect/sumoselect.css" />
<link rel="stylesheet" type="text/css" href="bower_components/rateyo/src/jquery.rateyo.css" />
<link rel="stylesheet" type="text/css" href="css/custom.css" />
<link rel="shortcut icon" href="favicon.ico" />
</head>
<body>
	<div class="container-fluid margin-top-10">
		<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
			<form name="frmbusqueda" method="post" role="search" class="navbar-form navbar-left" action="redirect_busqueda.php">
				<div class="form-group">
					<input placeholder="buscar" class="form-control" name="condicion" type="text" value="<?=(!empty($_GET['cadena']))?urldecode($_GET['cadena']):""?>" />
				</div>
				<button type="submit" class="btn btn-default">Buscar</button>
			</form>
			<div class="navbar-form navbar-left">
				<div class="form-group">
					<input placeholder="id" class="form-control" type="text" id="id-avanzar-pelicula" />
				</div>
				<button type="button" class="btn btn-default" id="button-avanzar-pelicula">Ir</button>
			</div>
			<form name="formulario_antiguo_enviar" method="post" role="search" class="navbar-form navbar-left" action="index.php?page=extraer">
				<div class="form-group">
					<input name="pagina" placeholder="url a examinar" id="pagina_default" type="text" size="60" class="form-control" />
				</div>
				<button type="submit" class="btn btn-default">Importar</button>
			</form>
			<div class="navbar-form navbar-left">
				<div class="btn-group" role="group" aria-label="...">
					<button type="button" id="button-avanzar-anterior" class="btn btn-default">
						<i class="fa fa-step-backward" style="padding: 3px 0" aria-hidden="true"></i>
					</button>
					<button type="button" id="button-avanzar-siguiente" class="btn btn-default">
						<i class="fa fa-step-forward" style="padding: 3px 0" aria-hidden="true"></i>
					</button>
					<button type="button" id="button-avanzar-ultimo" class="btn btn-default">
						<i class="fa fa-fast-forward" style="padding: 3px 0" aria-hidden="true"></i>
					</button>
					<button type="button" id="button-avanzar-nuevo" class="btn btn-default">
						<i class="fa fa-asterisk" style="padding: 3px 0" aria-hidden="true"></i>
					</button>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						Insertar información <span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li><a href='#' class='contenido' data-page='nuevo_registro' data-table='filmes_titulos_adicionales'>Otítulo</a></li>
						<li><a href='#' class='contenido' data-page='nuevo_registro' data-table='filmes_fechas_estreno'>Estreno</a></li>
						<li><a href='#' class='contenido' data-page='nuevo_registro' data-table='filmes_idiomas'>Idioma</a></li>
						<li><a href='#' class='contenido' data-page='nuevo_registro' data-table='filmes_paises'>Pais</a></li>
						<li><a href='#' class='contenido' data-page='nuevo_registro' data-table='filmes_generos'>Genero</a></li>
						<li><a href="index.php?page=lotes" title="Agregar usuarios separados por comas a la película">Actores</a></li>
						<li><a href='#' class='contenido' data-page='ventana_cert'>Clasificación</a></li>
						<li><a href='#' class='contenido' data-page='ventana_estudios'>Distribuidor</a></li>
						<li><a href='#' class='contenido' data-page='ventana_filmes'>Recomendada</a></li>
						<li><a href="actualiza_votos.php">Actualizar Votos</a></li>
					</ul>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						Listados <span class="caret"></span>
					</button>
					<ul class="dropdown-menu" role="menu">
						<li><a href="index.php?page=series" title="series que sigo">Series que sigo</a></li>
						<li><a href="index.php?page=pendientes" title="películas pendientes de ver">Películas disponibles</a></li>
						<?php
						$query = "SELECT listado_id, codigo FROM listados WHERE anulado = 0";
						$result = $mysqli->query($query);
						while($row = $result->fetch_object()){
							printf("<li><a href='index.php?page=listados&listado=%u'>%s</a></li>", $row->listado_id, $row->codigo);
						}
						$result->close();
						?>
						<li><a href="index.php?page=traducciones" title="Muestra los paises, idiomas y géneros pendientes de traducir">Translations</a></li>
					</ul>
				</div>
			</div>
		</div>
	</div>
	<div class="container-fluid margin-top-10">
		<div class="row">
			<div class="col-xs-6 col-md-2">
				<ul class="list-group">
					<li class="list-group-item"><a href="index.php?page=imdb_busqueda" title="Buscar urls de películas en una página de imdb">Buscar en IMDB</a></li>
					<li class="list-group-item"><a href="index.php?page=imdb_cast" title="Actualizar datos de los actores desde imdb">Actualizar actores</a></li>
					<li class="list-group-item"><a href="index.php?page=imdb_filmes" title="actualizar peliculas antiguas con imdb">Actualizar películas</a></li>
					<li class="list-group-item"><a href="index.php?page=nacionalidades" title="Actualizar nacionalidades actores">Nacionalidades</a></li>
					<li class="list-group-item"><a href="index.php?page=clean_characters" title="eliminar los personajes en inglés">Corregir personajes</a></li>
					<li class="list-group-item"><a href="index.php?page=traducir">Completar localizaciones</a></li>
					<li class="list-group-item"><a href="index.php?page=washlist">Washlist ImDB</a></li>
					<li class="list-group-item"><a href="index.php?page=ratelist">Ratelist ImDB</a></li>
					<li class="list-group-item"><a href="index.php?page=actualizar_archivos" title="agregar archivos de video">Agregar archivos</a></li>
					<li class="list-group-item"><a href="index.php?page=test&imdb=4462690" title="comprobar importación">Comprobar IMDb</a></li>
				</ul>
			</div>
			<div class="col-xs-6 col-md-10">
				<?php
				try{
					switch($open_page){
						case 'series':
							include_once 'mostrar_series_pendientes.php';
							break;
						case 'extraer':
							include_once 'imdb_filmes_extraer.php';
							break;
						case 'filmes':
							if(isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)){
								include_once 'filmes.php';
							}
							break;
						case 'pendientes':
							include_once 'mostrar_pendientes.php';
							break;
						case 'actores':
							if(isset($_GET['id']) && filter_var($_GET['id'], FILTER_VALIDATE_INT)){
								include_once 'actores.php';
							}
							break;
						case 'imdb_busqueda':
							include_once 'imdb_extraer.php';
							break;
						case 'actualizar_archivos':
							include_once 'buscar_actualizar_archivos.php';
							break;
						case 'imdb_cast':
							include_once 'imdb_actualizar_actores.php';
							break;
						case 'imdb_filmes':
							include_once 'imdb_actualizar_movie.php';
							break;
						case 'clean_characters':
							include_once 'limpiar_personajes.php';
							break;
						case 'busq':
							include_once 'resultados.php';
							break;
						case 'traducir':
							include_once 'traducir_localizaciones.php';
							break;
						case 'washlist':
							include_once 'insertar_washlist_imdb.php';
							break;
						case 'ratelist':
							include_once 'insertar_ratelist_imdb.php';
							break;
						case 'nacionalidades':
							include_once 'actualizar_nacionalidad_actores.php';
							break;
						case 'traducciones':
							include_once 'traducciones.php';
							break;
						case 'test':
							include_once 'imdb_comprobar.php';
							break;
						case 'listados':
							include_once 'listados.php';
							break;
						case 'lotes':
							include_once 'ventana_actor_lotes.php';
							break;
						case 'insertar_lotes':
							include_once 'insertar_actor_lotes.php';
							break;
						case 'capitulos':
							include_once 'muestra_capitulos.php';
							break;
					}
				}
				catch(Exception $e){
					printf('<div class="alert alert-danger">%s</div>', $e->getMessage());
				}
				?>
			</div>
		</div>
	</div>
	<?php
	$mysqli->close();
	?>
	<script src="bower_components/jquery/dist/jquery.min.js"></script>
	<script src="bower_components/jquery-ui/jquery-ui.min.js"></script>
	<script src="bower_components/blockUI/jquery.blockUI.js"></script>
	<script src="javascript/jquery.alerts.js"></script>
	<script src="javascript/php.js"></script>
	<script src="bower_components/moment/min/moment-with-locales.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
	<script src="bower_components/datatables.net/js/jquery.dataTables.min.js"></script>
	<script src="bower_components/datatables.net-bs/js/dataTables.bootstrap.min.js"></script>
	<script src="bower_components/sumoselect/jquery.sumoselect.min.js"></script>
	<script src="bower_components/rateyo/src/jquery.rateyo.js"></script>
	<script src="javascript/jquery.admin.filmes.js"></script>
</body>
</html>