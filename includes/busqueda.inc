<?php
function encuentraCoincidencias($titulo,$anno = null){
	global $mysqli;
	$id_filme = 0;
	$anno = (empty($anno))?(date("Y")-1):$anno;
	$sql = "select id from filmes where original like '%s' and (anno between '".($anno-1)."' and '".($anno+1)."')";
	$query = sprintf($sql,$mysqli->real_escape_string($titulo));
	$result = $mysqli->query($query);
	if(!$result->num_rows){
		$sql = "select distinct id_filme as id 
			from filmes_titulos_adicionales 
			inner join filmes on id_filme = filmes.id 
			where otitulo like '%s' 
			and (anno between '".($anno-1)."' and '".($anno+1)."')";
		$query = sprintf($sql,$mysqli->real_escape_string($titulo));
		$result = $mysqli->query($query);
	}
	if($result->num_rows != 1){
		$delimitador = "-";
		if(strpos($titulo,$delimitador)){
			$trozos = explode($delimitador,$titulo);
			$where = "";
			$where_o = "";
			foreach($trozos as $parcial){
				if(strlen($parcial) > 5){
					$parcial = $mysqli->real_escape_string($parcial);
					$where .= "original like '%$parcial%' and ";
					$where_o .= "otitulo like '%$parcial%' and ";
				}
			}
			if(!empty($where)){
				$where = substr($where,0,-5);
				$where_o = substr($where_o,0,-5);
				$query = "select id from filmes where $where and (anno between '".($anno-1)."' and '".($anno+1)."')";
				$result = $mysqli->query($query);
				if(!$result->num_rows){
					$query = "select distinct id_filme as id 
						from filmes_titulos_adicionales 
						inner join filmes on id_filme = filmes.id 
						where $where_o 
						and (anno between '".($anno-1)."' and '".($anno+1)."')";
					$result = $mysqli->query($query);
				}
			}
		}
	}
	if($result->num_rows != 1){
		$delimitador = ".";
		if(strpos($titulo,$delimitador)){
			$trozos = explode($delimitador,$titulo);
			$where = "";
			$where_o = "";
			foreach($trozos as $parcial){
				if(strlen($parcial) > 5){
					$parcial = $mysqli->real_escape_string($parcial);
					$where .= "original like '%$parcial%' and ";
					$where_o .= "otitulo like '%$parcial%' and ";
				}
			}
			if(!empty($where)){
				$where = substr($where,0,-5);
				$where_o = substr($where_o,0,-5);
				$query = "select id from filmes where $where and (anno between '".($anno-1)."' and '".($anno+1)."')";
				$result = $mysqli->query($query);
				if(!$result->num_rows){
					$query = "select distinct id_filme as id 
						from filmes_titulos_adicionales 
						inner join filmes on id_filme = filmes.id 
						where $where_o 
						and (anno between '".($anno-1)."' and '".($anno+1)."')";
					$result = $mysqli->query($query);
				}
			}
		}
	}
	if($result->num_rows != 1){
		$delimitador = ":";
		if(strpos($titulo,$delimitador)){
			$trozos = explode($delimitador,$titulo);
			$where = "";
			$where_o = "";
			foreach($trozos as $parcial){
				if(strlen($parcial) > 5){
					$parcial = $mysqli->real_escape_string($parcial);
					$where .= "original like '%$parcial%' and ";
					$where_o .= "otitulo like '%$parcial%' and ";
				}
			}
			if(!empty($where)){
				$where = substr($where,0,-5);
				$where_o = substr($where_o,0,-5);
				$query = "select id from filmes where $where and (anno between '".($anno-1)."' and '".($anno+1)."')";
				$result = $mysqli->query($query);
				if(!$result->num_rows){
					$query = "select distinct id_filme as id 
						from filmes_titulos_adicionales 
						inner join filmes on id_filme = filmes.id 
						where $where_o 
						and (anno between '".($anno-1)."' and '".($anno+1)."')";
					$result = $mysqli->query($query);
				}
			}
		}
	}
	if($result->num_rows != 1){
		$sql = "select id from filmes where original like '%s' and (anno between '".($anno-3)."' and '".($anno+3)."')";
		$query = sprintf($sql,$mysqli->real_escape_string($titulo));
		$result = $mysqli->query($query);
	}
	if($result->num_rows != 1){
		$sql = "select distinct id_filme as id 
			from filmes_titulos_adicionales 
			inner join filmes on id_filme = filmes.id 
			where otitulo like '%s' 
			and (anno between '".($anno-3)."' and '".($anno+3)."')";
		$query = sprintf($sql,$mysqli->real_escape_string($titulo));
		$result = $mysqli->query($query);
	}
	$titulo = $mysqli->real_escape_string($titulo);
	if($result->num_rows != 1){
		$query = "select id from filmes where original like '%$titulo%' and (anno between '".($anno-1)."' and '".($anno+1)."')";
		$result = $mysqli->query($query);
	}
	if($result->num_rows != 1){
		$query = "select distinct id_filme as id 
			from filmes_titulos_adicionales 
			inner join filmes on id_filme = filmes.id 
			where otitulo like '%$titulo%' 
			and (anno between '".($anno-1)."' and '".($anno+1)."')";
		$result = $mysqli->query($query);
	}
	if($result->num_rows != 1){
		$query = "select id from filmes where original like '$titulo'";
		$result = $mysqli->query($query);
	}
	if($result->num_rows != 1){
		$query = "select filmes.id from filmes_titulos_adicionales inner join filmes on filmes.id = filmes_titulos_adicionales.id_filme where otitulo like '$titulo'";
		$result = $mysqli->query($query);
	}
	if($result->num_rows == 1)
		$row = $result->fetch_assoc();
	$result->close();
	return (!empty($row['id']))?$row['id']:false;
}

function encuentraCoincidenciasExactas($titulo){
	global $mysqli;
	$id_filme = 0;
	$sql = "select id from filmes where original like '%s'";
	$query = sprintf($sql,$mysqli->real_escape_string($titulo));
	$result = $mysqli->query($query);
	if(!$result->num_rows){
		$sql = "select distinct id_filme as id from filmes_titulos_adicionales where otitulo like '%s'";
		$query = sprintf($sql,$mysqli->real_escape_string($titulo));
		$result = $mysqli->query($query);
	}
	if($result->num_rows == 1)
		$row = $result->fetch_assoc();
	$result->close();
	return (!empty($row['id']))?$row['id']:false;
}
?>