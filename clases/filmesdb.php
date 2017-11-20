<?php
include_once 'database.php';

class FilmesDB extends Database{

	public static function convierteURL($nombre){
		$nombre = strtolower(str_replace(" ", "-", $nombre));
		return urlencode(preg_replace(array(
				'/[^a-z0-9\-<>]/',
				'/[\-]+/',
				'/<[^>]*>/'
		), array(
				"",
				'-',
				""
		), $nombre));
	}

	public function dameIDPais($nombre){
		$query = "select id from paises where nombre like '%s'";
		$query = sprintf($query, $this->real_escape_string($nombre));
		return $this->fetch_value($query);
	}
}
?>