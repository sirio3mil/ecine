<?php

class UploadFileSizeCheck{

	public static function getMaxAllowedSize(){
		return min(self::getBytes(ini_get('upload_max_filesize')), self::getBytes(ini_get('post_max_size')));
	}

	public static function getBytes($val){
		$val = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		switch($last){
			// The 'G' modifier is available since PHP 5.1.0
			case 'g':
				$val *= 1024;
			case 'm':
				$val *= 1024;
			case 'k':
				$val *= 1024;
		}
		return $val;
	}

	public static function getReadeableFileSize($bytes, $precision = 2){
		$units = array(
				'B',
				'KB',
				'MB',
				'GB',
				'TB'
		);
		$bytes = max($bytes, 0);
		$pow = floor(($bytes?log($bytes):0) / log(1024));
		$pow = min($pow, count($units) - 1);
		// Uncomment one of the following alternatives
		$bytes /= pow(1024, $pow);
		// $bytes /= (1 << (10 * $pow));
		return round($bytes, $precision) . ' ' . $units[$pow];
	}
}

