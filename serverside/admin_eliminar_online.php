<?php 
include_once '../acceso_restringido.php';
include_once '../clases/Database.php';
if(!empty($_POST['filme'])){
	$mysqli  = new Database();
	$query = "UPDATE filmes SET online_lk = NULL, broken_link_check = NULL WHERE id = '%u'";
	$query = sprintf($query,
			$_POST['filme']
	);
	$mysqli->query($query);
	$mysqli->close();
}
?>