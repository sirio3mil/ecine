<?php  /* 02/01/2007 */
include_once '../acceso_restringido.php';
$data = [];
try{
	if(!$_POST){
		throw new Exception("No hay datos definidos");
	}
	if(!isset($_POST['id']) || !filter_var($_POST['id'], FILTER_VALIDATE_INT)){
		throw new Exception("No hay indice definido");
	}
	$mysqli  = new Database();
	$query = "delete from %s where id = '%d'";
	$mysqli->query(sprintf($query,
		$_POST["tabla"],
		$_POST["id"]
	));
	$data['modificados'] = $mysqli->affected_rows;
	$mysqli->close();

}
catch (Exception $e){
	$data['error'] = $e->getMessage();
}
echo json_encode($data);
?>
