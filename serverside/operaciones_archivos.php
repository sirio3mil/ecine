<?php
include_once '../acceso_restringido.php';
include_once '../clases/Database.php';
$mysqli  = new Database();
$query = "";
switch ($_GET['opp']){
	case "delete_file":
		$query = "UPDATE file SET existente = '0' WHERE file_id = '%u'";
		$query = sprintf($query,
				$_POST['file']
		);
		$mysqli->query($query);
		break;
}
$mysqli->close();