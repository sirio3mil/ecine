<?php
session_start();
if(empty($_SESSION['id_usuario'])){
	$_SESSION['id_usuario'] = 9;
	$_SESSION['usuario_admin'] = TRUE;
	$_SESSION['id_pais'] = 1;
}
?>