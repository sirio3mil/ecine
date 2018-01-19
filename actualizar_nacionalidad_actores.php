<?php
global $mysqli;
$modificados = 0;
if(!empty($_POST)){
?>
<div class="panel panel-default">
	<ul class="list-group">
	<?php
	$query = "SELECT id,nombre FROM paises ORDER BY LENGTH(nombre) DESC";
	$result = $mysqli->query($query);
	while($row = $result->fetch_object()){
		$total = 0;
		$pais = $mysqli->real_escape_string($row->nombre);
		$query = "UPDATE actores SET nacionalidad = '{$row->id}' WHERE nacionalidad IS NULL AND birthPlace LIKE '%, {$pais}'";
		if($mysqli->query($query)){
			if($mysqli->affected_rows){
				$total += $mysqli->affected_rows;
			}
		}
		else{
			break;
		}
		$query = "UPDATE actores SET nacionalidad = '{$row->id}' WHERE nacionalidad IS NULL AND birthPlace LIKE '{$pais}'";
		if($mysqli->query($query)){
			if($mysqli->affected_rows){
				$total += $mysqli->affected_rows;
			}
		}
		else{
			break;
		}
		$query = "UPDATE actores SET nacionalidad = '{$row->id}' WHERE nacionalidad IS NULL AND birthPlace LIKE '%{$pais}%' AND birthPlace REGEXP '^.*now.+{$pais}.?$'";
		if($mysqli->query($query)){
			if($mysqli->affected_rows){
				$total += $mysqli->affected_rows;
			}
		}
		else{
			break;
		}
		if($total){
			echo "<li class='list-group-item'>{$row->nombre} {$total} registros modificados</li>";
			$modificados += $total;
		}
	}
	$queries = array();
	$queries[] = "UPDATE actores SET nacionalidad = '14' WHERE nacionalidad IS NULL AND birthPlace LIKE '%German Democratic Republic%'";
	$queries[] = "UPDATE actores SET nacionalidad = '184' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Trinidad & Tobago%'";
	$queries[] = "UPDATE actores SET nacionalidad = '40' WHERE nacionalidad IS NULL AND birthPlace LIKE '%U.S.A%'";
	$queries[] = "UPDATE actores SET nacionalidad = '23' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Japan%'";
    $queries[] = "UPDATE actores SET nacionalidad = '7' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Edmonton%'";
	$queries[] = "UPDATE actores SET nacionalidad = '37' WHERE nacionalidad IS NULL AND birthPlace LIKE '%england%'";
	$queries[] = "UPDATE actores SET nacionalidad = '37' WHERE nacionalidad IS NULL AND birthPlace LIKE '%London%'";
	$queries[] = "UPDATE actores SET nacionalidad = '40' WHERE nacionalidad IS NULL AND birthPlace LIKE '%USA%'";
    $queries[] = "UPDATE actores SET nacionalidad = '40' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Hawaii%'";
	$queries[] = "UPDATE actores SET nacionalidad = '1' WHERE nacionalidad IS NULL AND birthPlace LIKE '%España%'";
    $queries[] = "UPDATE actores SET nacionalidad = '52' WHERE nacionalidad IS NULL AND birthPlace LIKE '%S.Korea%'";
    $queries[] = "UPDATE actores SET nacionalidad = '133' WHERE nacionalidad IS NULL AND birthPlace LIKE '%British Guiana%'";
    $queries[] = "UPDATE actores SET nacionalidad = '133' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Demerara%'";
	$queries[] = "UPDATE actores SET nacionalidad = '9' WHERE nacionalidad IS NULL AND birthPlace LIKE '%republic of china%'";
	$queries[] = "UPDATE actores SET nacionalidad = '67' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Moscow%'";
	$queries[] = "UPDATE actores SET nacionalidad = '67' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Leningrad%'";
	$queries[] = "UPDATE actores SET nacionalidad = '184' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Spain, Trinidad%'";
	$queries[] = "UPDATE actores SET nacionalidad = '184' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Trinidad, West Indies%'";
	$queries[] = "UPDATE actores SET nacionalidad = '85' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Slovak Republic%'";
    $queries[] = "UPDATE actores SET nacionalidad = '42' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Prague%'";
    $queries[] = "UPDATE actores SET nacionalidad = '102' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Zagreb%'";
    $queries[] = "UPDATE actores SET nacionalidad = '4' WHERE nacionalidad IS NULL AND birthPlace LIKE '%Vienna%'";
	foreach ($queries as $query){
		if($mysqli->query($query)){
			if($mysqli->affected_rows){
				$modificados += $mysqli->affected_rows;
			}
		}
		else{
			break;
		}
	}
	$result->close();
	?>
	</ul>
</div>
<?php
}
?>
<div class="btn-group margin-top-10" role="group">
	<form method='post'>
		<input type="hidden" value="1" name="update" />
		<button name="actualizar" class="btn btn-primary">Actualizar</button>
	</form>
</div>
<div class="panel panel-default margin-top-10">
	<ul class="list-group">
	<?php
	if(!empty($modificados)){
		echo "<li class='list-group-item'>{$modificados} nuevos actores con nacionalidad</li>";
	}
	$query = "select count(*) from actores where nacionalidad is not null";
	$cnacionalidad = $mysqli->fetch_value($query);
    $query = "select count(*) from actores where nacionalidad is null";
	$snacionalidad = $mysqli->fetch_value($query);
    $totala = $cnacionalidad + $snacionalidad;
	printf("<li class='list-group-item'>%u actores con nacionalidad (%.2f%s)</li>", $cnacionalidad, ($cnacionalidad*100/$totala), "%");
	printf("<li class='list-group-item'>%u actores sin nacionalidad (%.2f%s)</li>", $snacionalidad, ($snacionalidad*100/$totala), "%");
	?>
	</ul>
</div>
<div>
	<table class="datatable cell-border hover stripe">
        <caption>Actores sin nacionalidad con lugar de nacimiento</caption>
		<thead>
			<tr>
				<th>Actor</th>
				<th>Lugar de nacimiento</th>
                <th>Pais</th>
                <th>Area</th>
                <th>Detectado</th>
                <th></th>
			</tr>
		</thead>
        <?php
        $query = "SELECT id,
			nombre,
			birthPlace
			FROM actores
			WHERE birthPlace IS NOT NULL
			AND nacionalidad IS NULL
			ORDER BY birthPlace";
		$result = $mysqli->query($query);
        ?>
        <tfoot>
			<tr>
				<th><?=$result->num_rows?></th>
				<th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
			</tr>
		</tfoot>
		<tbody>
		<?php
        $ciudades = [];
        $paises = [];
        $search = "";
        $stmt = $mysqli->prepare("SELECT codigo_pais FROM localizaciones WHERE area LIKE ?");
        $stmt->bind_param("s", $search);
		while($row = $result->fetch_object()){
            $location = $row->birthPlace;
            $ciudad = $pais = $encontrado = "";
            if(stripos($location, ",") !== FALSE){
                $partes = explode(",", $location);
                $pais = trim(array_pop($partes));
                if(!empty($partes)){
                    $ciudad = trim(array_pop($partes));
                    if(!isset($ciudades[$ciudad])){
                        $ciudades[$ciudad] = 0;
                    }
                    $ciudades[$ciudad]++;
                    $search = $ciudad;
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    if($resultado->num_rows){
                        $fila = $resultado->fetch_array(MYSQLI_NUM);
                        $encontrado = $fila[0];
                        if($encontrado === "GB"){
                            $encontrado = "UK";
                        }
                    }
                    else{
                        $search = $pais;
                        $stmt->execute();
                        $resultado = $stmt->get_result();
                        if($resultado->num_rows){
                            $fila = $resultado->fetch_array(MYSQLI_NUM);
                            $encontrado = $fila[0];
                            if($encontrado === "GB"){
                                $encontrado = "UK";
                            }
                        }
                    }
                }
            }
            else{
                $pais = $location;
                $search = $pais;
                $stmt->execute();
                $resultado = $stmt->get_result();
                if($resultado->num_rows){
                    $fila = $resultado->fetch_array(MYSQLI_NUM);
                    $encontrado = $fila[0];
                    if($encontrado === "GB"){
                        $encontrado = "UK";
                    }
                }
            }
            if(!isset($paises[$pais])){
                $paises[$pais] = 0;
            }
            $paises[$pais]++;
             printf("<tr data-actor='%u' data-codigo='%s'><td><a href='index.php?page=actores&id=%u' target='actorwindow'>%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><i class='fa fa-pencil actualizar-nacionalidad-actor'></i></td></tr>",
                $row->id,
                $encontrado,
                $row->id,
                $row->nombre,
                $location,
                $pais,
                $ciudad,
                $encontrado
            );
		}
        $stmt->close();
		$result->close();
		?>
		</tbody>
	</table>
    <table class="datatable cell-border hover stripe">
        <caption>Provincias pendientes de identificar</caption>
		<thead>
			<tr>
				<th>Ciudad</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
        <?php
        foreach($ciudades as $ciudad => $total){
            printf("<tr><td>%s</td><td>%u</td></tr>", $ciudad, $total);
        }
        ?>
        </tbody>
    </table>
    <table class="datatable cell-border hover stripe">
        <caption>Paises pendientes de identificar</caption>
		<thead>
			<tr>
				<th>Pais</th>
				<th>Total</th>
			</tr>
		</thead>
		<tbody>
        <?php
        foreach($paises as $pais => $total){
            printf("<tr><td>%s</td><td>%u</td></tr>", $pais, $total);
        }
        ?>
        </tbody>
    </table>
    <table class="datatable cell-border hover stripe">
        <caption>Actores sin código imdb</caption>
		<thead>
			<tr>
				<th>Actor</th>
			</tr>
		</thead>
        <?php
        $query = "SELECT id,
			nombre
			FROM actores
			WHERE imdb is null
			ORDER BY nombre";
		$result = $mysqli->query($query);
        ?>
        <tfoot>
			<tr>
				<th><?=$result->num_rows?></th>
			</tr>
		</tfoot>
		<tbody>
		<?php
        while($row = $result->fetch_object()){
            ?>
			<tr>
				<td><a href="index.php?page=actores&id=<?=$row->id?>" target="actorwindow"><?=$row->nombre?></a></td>
			</tr>
			<?php
		}
		$result->close();
		?>
		</tbody>
	</table>
</div>