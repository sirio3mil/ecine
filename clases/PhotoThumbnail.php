<?php
/**
 * Created by PhpStorm.
 * User: sirio
 * Date: 06/02/2018
 * Time: 23:05
 */

class PhotoThumbnail
{
	public static function store(string $file_path, resource $image)
	{
		if(file_exists($file_path)){
			if(!unlink($file_path)){
				throw new Exception("Error deleting the original image $file_path");
			}
		}
		imagejpeg($image, $file_path, 100);
		if(!file_exists($file_path)){
			throw new Exception("Error creating {$file_path}");
		}
	}

	public static function create(string $file_path, resource $image, int $desired_width)
	{
		$width = imagesx($image);
		$height = imagesy($image);
		$desired_height = ceil($height * $desired_width / $width);
		$virtual_image = imagecreatetruecolor($desired_width, $desired_height);
		imagecopyresampled($virtual_image, $image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
		PhotoThumbnail::store($file_path, $virtual_image);
	}
}