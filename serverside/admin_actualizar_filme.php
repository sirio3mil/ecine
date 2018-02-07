<?php  /* 02/01/2007 */
include_once '../acceso_restringido.php';
include_once '../includes/filmes.inc';
if(!empty($_POST)){
	$mysqli = new FilmesDB();
	$nuevo_valor = strip_tags(trim($_POST['valor']));
	$valor_vacio = FALSE;
	$etiquetas = array(
			2166=>"erotica",
			60451=>"gay",
			2174=>"hechos_reales"
			);
	$id_etiqueta = array_search($_POST["campo"], $etiquetas);
	if(!empty($id_etiqueta)){
		if(empty($nuevo_valor)){
			$query = "delete from filmes_etiquetas where id_keyword = '%u' and id_filme = '%u'";
			$query = sprintf($query,
					$id_etiqueta,
					$_POST["id_filme"]);
		}
		else{
			$query = "insert into filmes_etiquetas (id_keyword,id_filme) values ('%u','%u')";
			$query = sprintf($query,
					$id_etiqueta,
					$_POST["id_filme"]);
		}
		$valor_vacio = TRUE;
	}
	elseif($_POST["campo"] == "clasico"){
		if(empty($nuevo_valor)){
			$query = "delete from filmes_clasicos where id_filme = '%u'";
			$query = sprintf($query,
					$_POST["id_filme"]);
		}
		else{
			$query = "insert into filmes_clasicos (id_filme) values ('%u')";
			$query = sprintf($query,
					$_POST["id_filme"]);
		}
		$valor_vacio = TRUE;
	}
	elseif($_POST["campo"] == "destacada"){
		if(empty($nuevo_valor)){
			$query = "delete from filmes_destacados where id_filme = '%u'";
			$query = sprintf($query,
					$_POST["id_filme"]);
		}
		else{
			$query = "insert into filmes_destacados (id_filme) values ('%u')";
			$query = sprintf($query,
					$_POST["id_filme"]);
		}
		$valor_vacio = TRUE;
	}
	else{
		if($_POST["campo"] == "sitio_trailer"){
			$query = "update filmes set trailer_lk = '%s' where id = '%u'";
			$query = sprintf($query,
					$mysqli->real_escape_string($_POST["contenido"]),
					$_POST["id_filme"]
			);
			$mysqli->query($query);
		}
		if($_POST["campo"] == "broken_link_check"){
			$nuevo_valor = (empty($nuevo_valor))?"":date("Y-m-d H:i:s");
		}
		if(!is_numeric($nuevo_valor) && (empty($nuevo_valor) || $nuevo_valor == "&nbsp;")){
			$query = "update filmes set %s = null where id = '%u'";
			$query = sprintf($query,
				$_POST["campo"],
				$_POST["id_filme"]
			);
			$valor_vacio = TRUE;
		}
		else{
			if($_POST["campo"] == "online_lk"){
				$query = "update filmes set broken_link_check = CURRENT_TIMESTAMP where id = '%u'";
				$query = sprintf($query,
						$_POST["id_filme"]
				);
				$mysqli->query($query);
			}
			$query = "update filmes set %s = '%s' where id = '%u'";
			$query = sprintf($query,
				$_POST["campo"],
				$mysqli->real_escape_string($nuevo_valor),
				$_POST["id_filme"]
			);
		}
	}
	if(!$mysqli->query($query)){
		echo $query;
	}
	elseif($_POST["campo"] == "predeterminado" && !empty($nuevo_valor)){
		// actualizo el enlace permanente
		$permalink = $mysqli::convierteURL($nuevo_valor);
		$query = "UPDATE filmes SET permalink = '%s' WHERE id = '%u'";
		$query = sprintf($query,
				$mysqli->real_escape_string($permalink),
				$_POST["id_filme"]
		);
		$mysqli->query($query);
		// inserto el titulo como español
		$query = "INSERT IGNORE INTO filmes_titulos_adicionales	(id_filme, otitulo, id_pais, idioma, notas) VALUES ('%u', '%s', '%u', '%u', 'manual update')";
		$query = sprintf($query,
				$_POST["id_filme"],
				$mysqli->real_escape_string($nuevo_valor),
				1,
				12
		);
		$mysqli->query($query);
	}
	$mysqli->close();
}
?>