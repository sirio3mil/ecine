<?php
/**
 * Created by PhpStorm.
 * User: sirio
 * Date: 07/02/2018
 * Time: 22:52
 */

class FileSizeUtilities
{
	public static function toReadable(int $bytes, int $decimals = 2)
	{
		$size = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
	}
}