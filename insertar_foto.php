<?php
include_once 'acceso_restringido.php';
include_once 'clases/filmesdb.php';
include_once 'clases/UploadFileSizeCheck.php';
try{
	$form_name = 'userfile';
	if($_SERVER['REQUEST_METHOD'] !== 'POST'){
		throw new Exception("Invalid request method");
	}
	if(!$_FILES){
		throw new Exception("There are no files submitted");
	}
	if(!array_key_exists($form_name, $_FILES)){
		throw new Exception("Invalid upload files form name");
	}
	if(!class_exists("finfo")){
		throw new Exception("File info library missing");
	}
	if(!function_exists('imagecreatetruecolor')){
		throw new Exception("Gd library missing");
	}
	if(!function_exists('uuid_create')){
		throw new Exception("Uuid library missing");
	}
	$name = $_FILES[$form_name]['name'];
	$tmp_name = $_FILES[$form_name]['tmp_name'];
	$error = $_FILES[$form_name]['error'];
	switch($error){
		case UPLOAD_ERR_OK:
			break;
		case UPLOAD_ERR_INI_SIZE:
			throw new Exception('The uploaded file exceeds the upload_max_filesize directive in php.ini.');
		case UPLOAD_ERR_FORM_SIZE:
			throw new Exception('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.');
		case UPLOAD_ERR_PARTIAL:
			throw new Exception('The uploaded file was only partially uploaded.');
		case UPLOAD_ERR_NO_FILE:
			throw new Exception('No file was uploaded.');
		case UPLOAD_ERR_NO_TMP_DIR:
			throw new Exception('Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.');
		case UPLOAD_ERR_CANT_WRITE:
			throw new Exception('Failed to write file to disk. Introduced in PHP 5.1.0.');
		case UPLOAD_ERR_EXTENSION:
			throw new Exception('File upload stopped by extension. Introduced in PHP 5.2.0.');
		default:
			throw new Exception('Unknown upload error');
	}
	$max_allowed_size = UploadFileSizeCheck::getMaxAllowedSize();
	$size = filesize($tmp_name);
	if(!$size){
		$size = $_FILES[$form_name]['size'];
	}
	if($size > $max_allowed_size){
		throw new Exception(sprintf("File size %s exceeded %s filesize limit", UploadFileSizeCheck::getReadeableFileSize($size), UploadFileSizeCheck::getReadeableFileSize($max_allowed_size)));
	}
	// DO NOT TRUST $file['mime'] VALUE !! Check MIME Type by yourself.
	$finfo = new finfo(FILEINFO_MIME_TYPE);
	$mime = $finfo->file($tmp_name);
	if($mime == "application/zip"){
		$mime = $_FILES[$form_name]['type'];
	}
	if(false === $ext = array_search($mime, array(
			'jpg' => 'image/jpeg',
			'png' => 'image/png',
			'gif' => 'image/gif'
	), true)){
		throw new Exception("Invalid file format {$mime}");
	}
	$mysqli = new filmesDB();
	$path = dirname(__FILE__);
	$id = filter_var($_REQUEST["id"], FILTER_VALIDATE_INT);
	if(!$id){
		throw new Exception("Destino incorrecto");
	}
	$temp = sprintf("%s%stemporal%s%s.jpg", $path, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, uuid_create());
	if(!move_uploaded_file($tmp_name, $temp)){
		throw new Exception("Imposible mover archivo a {$temp}");
	}
	switch($_REQUEST["tabla"]){
		case 'actores':
			$path .= sprintf("%sphotos%sactores%s", DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
			$filepath = sprintf("%soriginal%s%u.jpg", $path, DIRECTORY_SEPARATOR, $id);
			if(!rename($temp, $filepath)){
				throw new Exception("Error moviendo {$temp} a {$filepath}");
			}
			$query = "update actores set fecha=NOW(),cover='1' where id='$id'";
			$mysqli->query($query);
			break;
		case 'filmes':
			$path .= sprintf("%sphotos%sfilmes%s", DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
			switch($mime){
				case 'image/jpeg':
					$image = imagecreatefromjpeg($temp);
					break;
				case 'image/gif':
					$image = imagecreatefromgif($temp);
					break;
				case 'image/png':
					$image = imagecreatefrompng($temp);
					break;
				default:
					$image = imagecreatefromstring(file_get_contents($temp));
			}
			if(!$image){
				throw new Exception("Error creando imagen {$mime} {$temp}");
			}
			$width = imagesx($image);
			$height = imagesy($image);
			/* find the "desired height" of this thumbnail, relative to the desired width */
			$desired_width = 110;
			$desired_height = ceil($height * $desired_width / $width);
			/* create a new, "virtual" image */
			$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
			/* copy source image at a resized size */
			imagecopyresampled($virtual_image, $image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
			$filepath = sprintf("%s110%s%u.jpg", $path, DIRECTORY_SEPARATOR, $id);
			/* create the physical thumbnail image to its destination */
			if(file_exists($filepath)){
				if(!unlink($filepath)){
					throw new Exception("Error al borrar la imagen original $filepath");
				}
			}
			imagejpeg($virtual_image, $filepath, 100);
			if(!file_exists($filepath)){
				throw new Exception("Error al crear {$filepath} {$desired_width}x{$desired_height}");
			}
			$filepath = sprintf("%soriginal%s%u.jpg", $path, DIRECTORY_SEPARATOR, $id);
			if(file_exists($filepath)){
				if(!unlink($filepath)){
					throw new Exception("Error al borrar la imagen original $filepath");
				}
			}
			if(!rename($temp, $filepath)){
				throw new Exception("Error moviendo {$temp} a {$filepath}");
			}
			$query = "update filmes set fecha=NOW(),cover='1',bigcover='1' where id='$id'";
			$mysqli->query($query);
			break;
		case 'series':
			$path .= sprintf("%sphotos%sseries%s", DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR);
			$temporada = filter_var($_REQUEST['temporada'], FILTER_VALIDATE_INT);
			if(!$temporada){
				throw new Exception("Temporada incorrecta");
			}
			switch($mime){
				case 'image/jpeg':
					$image = imagecreatefromjpeg($temp);
					break;
				case 'image/gif':
					$image = imagecreatefromgif($temp);
					break;
				case 'image/png':
					$image = imagecreatefrompng($temp);
					break;
				default:
					$image = imagecreatefromstring(file_get_contents($temp));
			}
			if(!$image){
				throw new Exception("Error creando imagen {$mime} {$temp}");
			}
			$width = imagesx($image);
			$height = imagesy($image);
			/* find the "desired height" of this thumbnail, relative to the desired width */
			$desired_width = 110;
			$desired_height = ceil($height * $desired_width / $width);
			/* create a new, "virtual" image */
			$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
			/* copy source image at a resized size */
			imagecopyresampled($virtual_image, $image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
			$filepath = sprintf("%s110%s%u_%u.jpg", $path, DIRECTORY_SEPARATOR, $id, $temporada);
			if(file_exists($filepath)){
				if(!unlink($filepath)){
					throw new Exception("Error al borrar la imagen original $filepath");
				}
			}
			imagejpeg($virtual_image, $filepath, 100);
			if(!file_exists($filepath)){
				throw new Exception("Error al crear la imagen de temporada {$filepath} {$desired_width}x{$desired_height}");
			}
			$filepath = sprintf("%soriginal%s%u_%u.jpg", $path, DIRECTORY_SEPARATOR, $id, $temporada);
			if(file_exists($filepath)){
				if(!unlink($filepath)){
					throw new Exception("Error al borrar la imagen original $filepath");
				}
			}
			if(!rename($temp, $filepath)){
				throw new Exception("Error moviendo {$temp} a {$filepath}");
			}
			break;
		default:
			throw new Exception("No se reciben las opciones correctas");
	}
	$mysqli->close();
	header("Location: {$_SERVER['HTTP_REFERER']}");
	exit();
}
catch(Exception $e){
	if(file_exists($temp)){
		if(!unlink($temp)){
			echo "El archivo {$temp} no ha podido ser borrado<br />";
		}
	}
	echo $e->getMessage();
}