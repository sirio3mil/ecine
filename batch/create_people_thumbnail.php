<?php
/**
 * Created by PhpStorm.
 * User: reynier.delarosa
 * Date: 07/02/2018
 * Time: 17:57
 */
include_once '../acceso_restringido.php';
$mysqli = new Database();
$query = "select id from actores where cover = 1 and id > 148054";
$result = $mysqli->query($query);
if ($result) {
    while ($row = $result->fetch_object()){
        $original_path = sprintf("%s%sphotos%sactores%soriginal%s%u.jpg", dirname(__FILE__, 2), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $row->id);
        if(file_exists($original_path)) {
	        $target_path = sprintf("%s%sphotos%sactores%s40%s%u.jpg", dirname(__FILE__, 2), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $row->id);
	        $image = imagecreatefromjpeg($original_path);
	        PhotoThumbnail::create($target_path, $image, 40);
        }
        else{
        	$query = "update actores set cover = 0 where id = {$row->id}";
        	$mysqli->query($query);
        	echo $row->id . PHP_EOL;
        }
    }
    $result->close();
}
$mysqli->close();
echo Reloj::DevolverDuracionFormateada(Reloj::CalcularDuracionScript()) . PHP_EOL;