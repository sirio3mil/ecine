<?php 
global $mysqli;
if(empty($_REQUEST['listado'])){
	throw new Exception("No hay listado definido");
}
$query = "SELECT titulo, consulta FROM listados WHERE listado_id = %u";
$data = $mysqli->fetch_object(sprintf($query,
		$_REQUEST['listado']
		));
$result = $mysqli->query($data->consulta);
if($result->num_rows){
?>
<h3><?=$data->titulo?></h3>
<table class='datatable cell-border hover stripe'>
	<thead>
		<tr>
		<?php 
		$finfo = $result->fetch_field();
		$tabla = $finfo->table;
		do{
			printf("<th>%s</th>",
					ucfirst(strtolower(str_replace("_", " ", $finfo->name)))
					);
		}while ($finfo = $result->fetch_field());
		?>
		</tr>
	</thead>
	<tbody>
	<?php 
	while ($row = $result->fetch_array(MYSQLI_NUM)){
		print("<tr>");
		foreach ($row as $i => $value){
			if(!$i){
				printf("<td><a href='index.php?page=%s&id=%u' target='_listado'>%s</a></td>",
						$tabla,
						$value,
						$value
						);
			}
			else{
				printf("<td>%s</td>",
						$value
						);
			}
		}
		print("</tr>");
	}
	?>
	</tbody>
</table>
<?php 
}
$result->close();