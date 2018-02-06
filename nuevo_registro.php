<?php
include_once 'acceso_restringido.php';
$mysqli = new FilmesDB();
if(!empty($_GET["table"])){
	$open_table = $mysqli->real_escape_string(trim($_GET["table"]));
	$hay_fechas = false;
	$campo_predeterminado = "";
	if(!empty($_GET["campo"])){
		// si recibo elparametro campo significa que es un valor predeterminado para la columna del mismo nombre en la tupla a insertar
	    $campo_predeterminado = $_GET["campo"];
	    // obtengo tambien el valor
		$valor_predeterminado = (!empty($_SESSION['id_page']))?$_SESSION['id_page']:0;
	}
	$mysqli->select_db("information_schema");
	$query = "SELECT COLUMN_NAME as `columna`,
		COLUMN_DEFAULT as `default`,
		COLUMN_TYPE as `type`,
		IS_NULLABLE as `anulable`
		FROM COLUMNS
		where TABLE_SCHEMA = 'ecine'
		and TABLE_NAME = '$open_table'
		and COLUMN_KEY <> 'PRI'";
	$result = $mysqli->query($query);
	if($result){
		$foreign_keys = array();
		$query = "SELECT COLUMN_NAME as columna,
			REFERENCED_TABLE_NAME as tabla,
			REFERENCED_COLUMN_NAME as original
			FROM KEY_COLUMN_USAGE 
			where TABLE_SCHEMA = 'ecine'
			and TABLE_NAME = '$open_table'
			and REFERENCED_COLUMN_NAME is not null";
		$data = $mysqli->query($query);
		while ($row = $data->fetch_object())
			$foreign_keys[$row->columna] = array("tabla"=>$row->tabla,"campo"=>$row->original);
		$data->close();
		$mysqli->select_db("ecine");
		?>
		<form id="fr_new_record" name="form1" method="post" action="insertar_nuevo_registro.php?table=<?=$open_table?>" class="niceform">
			<fieldset>
			    <legend align="left">Nuevo <?=$open_table?></legend>
		    	<?php
		    	while ($row = $result->fetch_object()) {
		    		$nombre_campo = ucwords(trim(str_replace("id ", "",str_replace("_", " ", $row->columna))));
		    		$obligatorio = ($row->anulable == "NO")?"<sup>*</sup>":"";
		    		echo "<dl><dt><label for='$row->columna'>{$nombre_campo}{$obligatorio}</label></dt><dd>";
		        	//  compruebo que no exista ningun campo determinado pasado via WEB
					if($campo_predeterminado == $row->columna){
		        	    echo "<input type='text' name='$row->columna' size='4' value='$valor_predeterminado' />";
		            }
		            //  compruebo que el campo haga referencia a otra tabla extranjera
		            else if(array_key_exists($row->columna,$foreign_keys)){
		            	$predeterminado = "";
		            	switch($foreign_keys[$row->columna]["tabla"]){
		            		case "filmes":
		            			$campo_a_mostrar = "original";
		            			break;
		            		case "sitios":
		            			$campo_a_mostrar = "codigo";
		            			break;
		            		case "idiomas":
		            			$campo_a_mostrar = "idioma";
		            			$predeterminado = 12;
		            			break;
		            		case "tipos":
		            			$campo_a_mostrar = "tipo";
		            			break;
		            		case "usuarios":
		            			$campo_a_mostrar = "nick";
		            			break;
		            		default:
		            			$campo_a_mostrar = "nombre";
		            			$predeterminado = 1;
		            	}
		            	$campo_indice = $foreign_keys[$row->columna]["campo"];
	                    $query = "select %s,
	                    	%s 
	                    	from %s 
	                    	order by %s";
	                    $query = sprintf($query,
	                    		$campo_a_mostrar,
	                    		$campo_indice,
	                    		$foreign_keys[$row->columna]["tabla"],
	                    		$campo_a_mostrar);
		                $data = $mysqli->query($query);
		                ?>
		                <select name='<?=$row->columna?>'>
		                	<?php 
		                	if($row->anulable == "YES"){
		                	?>
		                	<option value='' selected>Seleccionar uno...</option>
		                	<?php 
		                	}
		                	while ($row = $data->fetch_object()){
		                		echo "<option value='{$row->$campo_indice}'";
		                		if($row->$campo_indice == $predeterminado)
		                			echo " selected";
		                		echo ">{$row->$campo_a_mostrar}</option>";
		                	}
		                	?>
		                </select>
		                <?php 
		                $data->close();
					}
		            else{
		            	$key = substr($row->type,0,3);
						switch($key){
		                	// trato los campos tipo enum
		                    case "enu":
		                    	// separo todas las posibles opciones del campo enum en el array
						        $enumeradores = explode("'",$row->type);
						        $total = count($enumeradores);
						        for($h=0;$h<$total;++$h){
		                        	if($h%2!==0){
		                            	echo "<input type='radio' name='$row->columna' value='{$enumeradores[$h]}'";
									   	// determino como predeterminado el valor Movie de darse el caso
									   	if($enumeradores[$h] == "Movie")
									   		echo " checked";
									   	echo ">{$enumeradores[$h]}    ";
		                            }
								}
								break;
							case "tex":
		                    	echo "<textarea name='$row->columna' id='$row->columna' cols='30' rows='5'></textarea>";
								break;
							case "dat":
								$hay_fechas = true;
								echo "<input type='text' name='$row->columna' size='10' class='datepicker' value='".date('Y-m-d')."'>";
		                        break;
							case "var":
								echo "<input type='text' name='$row->columna' size='30' value='$row->default'>";
		                        break;
							default:
						        echo "<input type='text' name='$row->columna' size='4' value='$row->default'>";
		                        break;
						}
					}
		            echo "</dd>";
		            echo "</dl>";
				}
				$result->close();
		    	?>
		    </fieldset>
		    <fieldset class="action">
		    	<input type='submit' value='insertar datos'>
		    	<input type='button' value='mandar ajax' id='enviar_ajax'>
		    	<input type='button' value='cancelar' id='cancelar_ajax'>
		    </fieldset>
		</form>
		<?php 
	}
}
$mysqli->close();
?>