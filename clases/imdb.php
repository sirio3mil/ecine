<?php

class IMDB{
	protected $pagina;
	protected $url;
	protected $pagina_cast;
	protected $cast_director;
	protected $cast_writer;
	protected $titulo_estreno;
	public $imdbNr;
	public $temporada;
	public $capitulo;
	public $es_capitulo;
	public $es_serie;
	public $original;
	public $estado;

	public function __construct($url){
		$this->url = $url;
		$this->pagina = IMDB::clean($this->url);
		if(!$this->pagina){
			$this->estado = FALSE;
			throw new Exception("Error recuperando la página $this->url");
		}
		$this->estado = TRUE;
		$this->es_capitulo = $this->episode();
		if(!$this->es_capitulo){
			$this->es_serie = (strpos($this->pagina, "<h4 class=\"float-left\">Seasons</h4>") !== FALSE)?TRUE:FALSE;
		}
		else{
			$this->es_serie = FALSE;
		}
		$this->original = $this->calculaOriginal();
		if(!$this->original){
			$this->estado = FALSE;
			throw new Exception("Error recuperando el título original");
		}
		preg_match_all('|title/tt([^>]+)/|U', $this->url, $coincidencias);
		$this->imdbNr = (int) ($coincidencias[1][0]);
		/**
		 * ******** RELEASE INFO **************
		 */
		if(strpos($this->pagina, "Also Known As:") !== FALSE || strpos($this->pagina, "Release Date:") !== FALSE){
			$this->titulo_estreno = IMDB::clean($this->url . "releaseinfo");
		}
		/**
		 * ******** FULL CREDITS **************
		 */
		$this->pagina_cast = IMDB::clean($this->url . "fullcredits");
		$this->cast_director = '';
		$this->cast_writer = '';
		if(strpos($this->pagina_cast, "Directed by") !== false){
			$arrayTemp = explode("Directed by", $this->pagina_cast);
			$arrayTemp = explode("</table>", $arrayTemp[1]);
			if(!empty($arrayTemp[0])){
				$this->cast_director = $arrayTemp[0];
			}
		}
		if(strpos($this->pagina_cast, "Writing Credits") !== false){
			$arrayTemp = explode("Writing Credits", $this->pagina_cast);
			$arrayTemp = explode("</table>", $arrayTemp[1]);
			if(!empty($arrayTemp[0])){
				$this->cast_writer = $arrayTemp[0];
			}
		}
	}

	protected function episode(){
		$cadenas = [
				'Episode cast overview',
				'Episode credited cast',
				'Episode complete credited cast'
		];
		foreach($cadenas as $cadena){
			if(strpos($this->pagina, $cadena) !== FALSE){
				return true;
			}
		}
		return false;
	}

	public static function clean($url){
		$sustituye = array(
				"\r\n",
				"\n\r",
				"\n",
				"\r"
		);
		return str_replace("> <", "><", preg_replace('/\s+/', ' ', str_replace($sustituye, "", file_get_contents($url))));
	}

	private function calculaOriginal(){
		$coincidencias = array();
		preg_match_all('|<title>([^>]+) \(|U', $this->pagina, $coincidencias);
		if(!empty($coincidencias[1][0])){
			$titulo = html_entity_decode(trim($coincidencias[1][0]), ENT_QUOTES);
			if($this->es_capitulo){
				$partes = explode("\"", $titulo);
				$titulo = end($partes);
			}
			else{
				if(strpos($this->pagina, "(original title)") !== FALSE){
					preg_match_all('|<div class=\"originalTitle\">([^>]+)<span|U', $this->pagina, $coincidencias);
					$titulo = html_entity_decode(trim($coincidencias[1][0]), ENT_QUOTES);
				}
				else{
					$partes = explode("(", $titulo);
					$titulo = trim($partes[0]);
				}
			}
			if(!empty($titulo)){
				$titulo = str_replace('"', '', trim(strip_tags($titulo)));
				return $this->validaCampo($titulo);
			}
		}
		return NULL;
	}

	public function dameCapitulos($existentes = array()){
		$capitulos = array();
		if($this->es_serie){
			$html = file_get_contents($this->url . "episodes");
			if(!empty($html)){
				preg_match_all('|<a href=\"/title/([^>]+)\">|U', $html, $coincidencias);
				if(!empty($coincidencias[1])){
					foreach($coincidencias[1] as $url){
						$numero = str_replace('tt', '', $url);
						$trozos = explode("/", $numero);
						$numero = (int) ($trozos[0]);
						if(!in_array($numero, $existentes) && !in_array($numero, $capitulos))
							$capitulos[] = $numero;
					}
				}
			}
		}
		return $capitulos;
	}

	public function dameTitulo(){
		return $this->original;
	}

	public function dameSerie(){
		if($this->es_capitulo){
			preg_match_all('|<div class=\"titleParent\"><a href=\"/title/tt([0-9]{7})|U', $this->pagina, $coincidencias);
			return (int) ($coincidencias[1][0]);
		}
		return false;
	}

	public function dameAnno(){
		preg_match_all('|<title>([^>]+)([1-2][0-9][0-9][0-9])([^>]+)</title>|U', $this->pagina, $coincidencias);
		if(!empty($coincidencias[2][0])){
			$anno = $coincidencias[2][0];
			return (is_numeric($anno))?$anno:false;
		}
		return false;
	}

	public function dameDuracion(){
		preg_match_all('|datetime=\"PT([0-9]{1,3})M\"|U', $this->pagina, $coincidencias);
		return (!empty($coincidencias[1][0]))?trim($coincidencias[1][0]):false;
	}

	public function dameEstrenos(){
		$coincidencias = array();
		if(!empty($this->titulo_estreno)){
			preg_match_all('|<td><a href=\"([^>]+)\">([^>]+)</a></td><td class=\"release_date\">([^>]+)<a href=\"([^>]+)\">([^>]+)</a></td><td>([^>]*)</td>|U', $this->titulo_estreno, $coincidencias);
		}
		/*
		 * 2 nombre del pais USA
		 * 3 fecha 29 September
		 * 5 año 2014
		 * 6 detalle
		 */
		return $coincidencias;
	}

	public function dameEstrenoAnterior($timestamp){
		$minimo = $timestamp;
		$datos = $this->dameEstrenos();
		if(!empty($datos[0])){
			$elementos = count($datos[0]);
			for($i = 0; $i < $elementos; $i++){
				$dia_mes = trim($datos[3][$i]);
				$anno = trim($datos[5][$i]);
				if(!empty($dia_mes) && !empty($anno)){
					$actual = strtotime("{$dia_mes} {$anno}");
					if($actual && $minimo > $actual){
						$minimo = $actual;
					}
				}
			}
		}
		return $minimo;
	}

	public function damePuntuacionMedia(){
		$coincidencias = array();
		// <span itemprop="ratingValue">7,1</span>
		preg_match_all('|<span itemprop="ratingValue">([^>]+)</span>|U', $this->pagina, $coincidencias);
		if(empty($coincidencias[1][0])){
			return 0;
		}
		return intval(filter_var($coincidencias[1][0], FILTER_SANITIZE_NUMBER_INT)) / 20;
	}

	public function dameVotosTotales(){
		$coincidencias = array();
		// <span class="small" itemprop="ratingCount">58.111</span>
		preg_match_all('|<span class="small" itemprop="ratingCount">([^>]+)</span>|U', $this->pagina, $coincidencias);
		if(empty($coincidencias[1][0])){
			return 0;
		}
		return intval(filter_var($coincidencias[1][0], FILTER_SANITIZE_NUMBER_INT));
	}

	public function dameColor(){
		$coincidencias = array();
		preg_match_all('|<a href=\"/search/title\?colors=([^>]+)\"itemprop=\'url\'>([^>]+)</a>|U', $this->pagina, $coincidencias);
		return (!empty($coincidencias[2][0]))?$this->validaCampo(strip_tags($coincidencias[2][0])):false;
	}

	public function dameSonido(){
		preg_match_all('|<a href=\"/search/title\?sound_mixes=([^>]+)\"itemprop=\'url\'>([^>]+)</a>|U', $this->pagina, $coincidencias);
		if(!empty($coincidencias[2]) && is_array($coincidencias[2])){
			$sonido = "";
			foreach($coincidencias[2] as $sound)
				$sonido .= trim(strip_tags($sound)) . ", ";
			return $this->validaCampo(substr($sonido, 0, -2));
		}
		return false;
	}

	public function dameRecomendada(){
		if(strpos($this->pagina, "<h2>Recommendations</h2>") !== FALSE){
			$arrayTemp = explode("<h2>Recommendations</h2>", $this->pagina);
			if(!empty($arrayTemp[1])){
				preg_match_all('|/title/tt([^>]+)/\">|U', $arrayTemp[1], $coincidencias);
				if(!empty($coincidencias[1][0])){
					$imdb = trim(strip_tags($coincidencias[1][0]));
					settype($imdb, 'integer');
					return $imdb;
				}
			}
		}
		return false;
	}

	public function dameTituloAdicionales(){
		$coincidencias = array();
		if(!empty($this->titulo_estreno)){
			preg_match_all('|<tr class="([^>]+)"><td>([^>]+)</td><td>([^>]+)</td></tr>|U', $this->titulo_estreno, $coincidencias);
		}
		return $coincidencias;
	}

	public function dameDirector(){
		$coincidencias = array();
		if(!empty($this->cast_director)){
			// <a href="/name/nm0400850/?ref_=ttfc_fc_dr1"> Patrick Hughes</a>
			preg_match_all('|<a href=\"/name/nm([^>]+)/([^>]+)\"> ([^>]+)</a>|U', $this->cast_director, $coincidencias);
		}
		return $coincidencias;
	}

	public function dameEscritor(){
		$coincidencias = array();
		if(!empty($this->cast_writer)){
			// <a href="/name/nm4951717/?ref_=ttfc_fc_wr3"> Katrin Benedikt</a>
			preg_match_all('|<a href=\"/name/nm([^>]+)/([^>]+)\"> ([^>]+)</a>|U', $this->cast_writer, $coincidencias);
		}
		return $coincidencias;
	}

	public function dameLocalizacion(){
		$coincidencias = array();
		if(strpos($this->pagina, "Filming Locations:") !== FALSE){
			$html = IMDB::clean($this->url . "locations");
			if(!empty($html)){
				preg_match_all('|/search/title\?locations=([^>]+)\"itemprop=\'url\'>([^>]+)</a>|U', $html, $coincidencias);
				return $coincidencias;
			}
		}
		return $coincidencias;
	}

	public function dameActoresPersonajes(){
		$coincidencias = array();
		// <a href="/name/nm0005458/?ref_=ttfc_fc_cl_t2"itemprop='url'><span class="itemprop" itemprop="name">Jason Statham</span></a></td><td class="ellipsis"> ... </td><td class="character"><div><a href="/character/ch0133696/?ref_=ttfc_fc_cl_t2" >Lee Christmas</a></div></td></tr>
		preg_match_all('|<a href=\"/name/nm([^>]+)/([^>]+)\"itemprop=\'url\'><span class=\"itemprop\" itemprop=\"name\">([^>]+)</span></a></td><td class=\"ellipsis\">(.*)</td><td class=\"character\"><div>(.*)</div>|U', $this->pagina_cast, $coincidencias);
		return $coincidencias;
	}

	public static function damePaisReal($pais){
		switch($pais){
			case "PuertoRico":
				return "Puerto Rico";
			case "HongKong":
				return "Hong Kong";
			case "WestGermany":
				return "West Germany";
			case "NewZealand":
				return "New Zealand";
			case "SouthKorea":
				return "South Korea";
			case "CzechRepublic":
				return "Czech Republic";
			case "Bosnia and Herzegovina":
			case "Bosnia And Herzegovina":
				return "Bosnia-Herzegovina";
			case "Federal Republic of Yugoslavia":
				return "Yugoslavia";
		}
		return $pais;
	}

	public function damePaises(){
		$coincidencias = array();
		preg_match_all('|country_of_origin=([^>]+)>([^>]+)<|U', $this->pagina, $coincidencias);
		return $coincidencias;
	}

	public function dameIdiomas(){
		$coincidencias = array();
		preg_match_all('|primary_language=([^>]+)>([^>]+)<|U', $this->pagina, $coincidencias);
		return $coincidencias;
	}

	public function dameKeywords(){
		$coincidencias = array();
		if(strpos($this->pagina, "Plot Keywords:") !== FALSE){
			$html = file_get_contents($this->url . "keywords");
			if(!empty($html)){
				preg_match_all('|/keyword/([^>]+)\?|U', $html, $coincidencias);
				return $coincidencias;
			}
		}
		return $coincidencias;
	}

	public function dameGeneros(){
		$coincidencias = array();
		preg_match_all('|genre/([^>]+)>([^>]+)<|U', $this->pagina, $coincidencias);
		return $coincidencias;
	}

	public function dameCertificaciones(){
		$coincidencias = array();
		if(!$this->es_capitulo && (strpos($this->pagina, "See all certifications") !== FALSE)){
			$html = file_get_contents($this->url . "parentalguide");
			if(!empty($html)){
				preg_match_all('|<a href=\"/search/title\?certificates=([^>]+)\">([^>]+)</a>|U', $html, $coincidencias);
			}
		}
		return $coincidencias;
	}

	public function validaCampo($valor){
		if(empty($valor))
			return null;
		return trim(str_replace("%20", " ", str_replace("\"", "", $valor)));
	}

	public function actualizaTemporada(){
		if($this->es_capitulo){
			$sub_coincidencias = array();
			preg_match_all('|>Season ([0-9]{1,2}) <|U', $this->pagina, $sub_coincidencias);
			if(!empty($sub_coincidencias[1][0]) && is_numeric($sub_coincidencias[1][0]))
				$this->temporada = (int) ($sub_coincidencias[1][0]);
			$sub_coincidencias = array();
			preg_match_all('|> Episode ([0-9]{1,2})<|U', $this->pagina, $sub_coincidencias);
			if(!empty($sub_coincidencias[1][0]) && is_numeric($sub_coincidencias[1][0]))
				$this->capitulo = (int) ($sub_coincidencias[1][0]);
		}
	}
}
?>