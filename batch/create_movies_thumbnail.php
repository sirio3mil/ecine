<?php
/**
 * Created by PhpStorm.
 * User: sirio
 * Date: 09/02/2018
 * Time: 0:09
 */
include_once '../acceso_restringido.php';
$mysqli = new Database();
$query = "select id from filmes where cover = 1";
$result = $mysqli->query($query);
if ($result) {
	while ($row = $result->fetch_object()){
		$original_path = sprintf("%s%sphotos%sfilmes%soriginal%s%u.jpg", dirname(__FILE__, 2), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $row->id);
		if(file_exists($original_path)) {
			$target_path = sprintf("%s%sphotos%sfilmes%s40%s%u.jpg", dirname(__FILE__, 2), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $row->id);
			$image = imagecreatefromjpeg($original_path);
			PhotoThumbnail::create($target_path, $image, 40);
		}
		else{
			$query = "update filmes set cover = 0 where id = {$row->id}";
			$mysqli->query($query);
			echo $row->id . PHP_EOL;
		}
	}
	$result->close();
}
$mysqli->close();
echo Reloj::DevolverDuracionFormateada(Reloj::CalcularDuracionScript()) . PHP_EOL;