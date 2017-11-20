<?php
if(!empty($_POST['condicion'])){
	$location = "Location: index.php?page=busq&cadena=%s";
	$location = sprintf($location,urlencode($_POST['condicion']));
}
else{
	$location = "Location: index.php?page=extraer";
}
header($location);
?>