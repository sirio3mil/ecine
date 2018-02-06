<?php
session_start();
if(empty($_SESSION['id_usuario'])){
	$_SESSION['id_usuario'] = 9;
	$_SESSION['usuario_admin'] = TRUE;
	$_SESSION['id_pais'] = 1;
}
spl_autoload_register(function ($class_name) {
	include dirname(__FILE__) . DIRECTORY_SEPARATOR . 'clases' . DIRECTORY_SEPARATOR . $class_name . '.php';
});