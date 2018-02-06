<?php

class Database extends mysqli
{

    public function __construct()
    {
        parent::__construct('35.185.40.249', 'root', '#LeNtilla1', 'db_peliculas');
        if ($this->connect_error)
            die($this->connect_error);
        parent::query("SET NAMES 'latin1'");
    }

    public function query($query, $resultmode = NULL)
    {
        if (! empty($query)) {
            $result = parent::query($query, $resultmode);
            if ($result) {
                return $result;
            }
            if ($this->errno != 1062) {
                $trace = debug_backtrace();
                echo "<strong>" . date("H:i") . "</strong> Error SQL {$this->errno} {$this->error}<br />";
                echo "<strong>" . date("H:i") . "</strong> $query<br />";
                echo "<strong>" . date("H:i") . "</strong> " . __FILE__ . " en la linea " . __LINE__ . "<br />";
                foreach ($trace as $indice => $datos) {
                    echo "<strong>" . date("H:i") . "</strong> #$indice {$datos['function']} en {$datos['file']} ({$datos['line']})<br/>";
                }
                if ($this->errno == 2006) {
                    $this->close();
                    die("Abortada conexion");
                }
            }
        }
        return false;
    }

    public function fetch_assoc($query)
    {
        $result = $this->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $result->close();
            return $row;
        }
        return false;
    }

    public function fetch_object($query)
    {
        $result = $this->query($query);
        if ($result) {
            $row = $result->fetch_object();
            $result->close();
            return $row;
        }
        return false;
    }

    public function fetch_array($query)
    {
        $retorno = array();
        $result = $this->query($query);
        if ($result) {
            while ($row = $result->fetch_row())
                $retorno[] = $row[0];
            $result->close();
        }
        return $retorno;
    }

    public function fetch_value($query)
    {
        $retorno = false;
        $result = $this->query($query);
        if ($result) {
            $row = $result->fetch_row();
            $retorno = (! empty($row[0])) ? $row[0] : false;
            $result->close();
        }
        return $retorno;
    }
}
?>
