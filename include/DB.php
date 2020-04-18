<?php

require_once('juego.php');
require_once('prueba.php');

class DB {
    /**
     * Conecta con la base de datos y ejecuta las consultas
     */
    protected static function ejecutaConsulta($sql) {
        $opc = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
        $dsn = "mysql:host=localhost;dbname=crimebookluis";
        $usuario = 'dwes';
        $contrasena = 'abc123.';
        $dwes = new PDO($dsn, $usuario, $contrasena, $opc);
        $resultado = null;
        if (isset($dwes)) {$resultado = $dwes->query($sql);}
        return $resultado;
    }

    /**
     * Obtiene todos los juegos
     */
    public static function obtieneJuegos() {
        $sql = "SELECT id, nombre, descExtendida, descBreve, fechaCreacion, "
                . "username, COUNT(idPrueba) as numPruebas FROM juegos "
                . "LEFT JOIN pertenencias "
                . "ON pertenencias.idJuego=juegos.id "
                . "GROUP BY juegos.id;";
        $resultado = self::ejecutaConsulta ($sql);
        $juegos = array();

	if($resultado) {
            // Añadimos un elemento por cada registro obtenido
            while ($row = $resultado->fetch()) {
                $juegos[] = new Juego($row);
            }
	}     
        return $juegos;
    }

    /**
     * Obtener el id del último juego
     * 
     * @return Int $id
     */
    public static function getLastJuegoId(){
        $id = 0;
        $sql = "SELECT id FROM juegos ORDER BY id DESC LIMIT 1";
        $resultado = self::ejecutaConsulta($sql);
        if($resultado){
            $rawJuego = $resultado->fetch();
            $id = $rawJuego['id'];
        }
        return $id;
    }

    /**
     * obtener un juego por su id
     * 
     * @param int $id
     * @return Juego
     */
    public static function getJuegoById($id){
        $juego = new Juego();
        $sql = "SELECT * FROM juegos WHERE id = ".$id;
        $resultado = self::ejecutaConsulta($sql);
        if($resultado){
            $juego->populateJuego($resultado->fetch());
        }
        return $juego;
    }  
     
    /**
     * modifica un juego por su id
     * 
     * @param Int $id
     * @param String $nombre
     * @param String $desExten
     * @param String $descBreve
     * 
     * @return boolean $resultado
     */
    public static function modificarJuego($id, $nombre, $desExten, $descBreve) {
        $idJuego = $id;
        $sql = "UPDATE juegos SET "
                . " nombre = '". $nombre."', "
                . " descExtendida = '".$desExten."', "
                . " descBreve = '".$descBreve."' "
                . " WHERE id = '".$idJuego."';";
        $resultado = self::ejecutaConsulta ($sql); 
        return $resultado; 
    }
    
    /**
     * Insertar nuevo juego en el sistema
     * 
     * @param Object $juego
     * 
     * @return boolean $resultado true or false
     */
    public static function crearJuego($juego) {
        $sql = "INSERT INTO juegos (nombre, descExtendida, descBreve, fechaCreacion, username)"
            . " VALUES ("
            . "'". $juego->getNombre()."', "
            . "'". $juego->getdescExtendida()."', "
            . "'". $juego->getdescBreve()."', "
            . "'". $juego->getfechaCreacion()."', "
            . "'". $juego->getUsername()."');";
        return self::ejecutaConsulta ($sql);       
    }
    
    /**
     * Elimina un juego por su id
     * 
     * @param int $idJuego
     * 
     * @return boolean
     */
    public static function eliminarJuego($idJuego) {
        $sql = "DELETE  FROM juegos WHERE juegos.id = '".$idJuego."';";
        $resultado = self::ejecutaConsulta ($sql); 
        return $resultado; 
    }

    /**
     * Elimina un multiples juegos dado un array de identificadores
     * 
     * @param array $idJuegos
     * 
     * @return boolean
     */
    public static function eliminarJuegos($idJuegos) {
        $sql = "DELETE  FROM juegos WHERE juegos.id IN (".join(',',$idJuegos).");";
        $resultado = self::ejecutaConsulta ($sql); 
        return $resultado; 
    }

    /**
     * verifica las credenciales de un usuario
     * 
     * @param string $nombre
     * @param string $contrasena
     * 
     * @return boolean
     */
    public static function verificaCliente($nombre, $contrasena) {
        $sql = "SELECT username FROM usuarios ";
        $sql .= "WHERE username='$nombre' ";
        $sql .= "AND contrasenya='" .$contrasena . "';";
        $resultado = self::ejecutaConsulta ($sql);
        $verificado = false;

        if(isset($resultado)) {
            $fila = $resultado->fetch();
            if($fila !== false) $verificado=true;
        }
        return $verificado;
    }

    /**
     * obtiene las pruebas de un juego en base a su id
     * 
     * @param integer $id
     * 
     * @return array
     */
    public static function obtienePruebas($idJuego) {
        $sql = "SELECT id, nombre, descExtendida, descBreve, tipo, "
                . "dificultad, url, ayudaFinal, username FROM pruebas"
                . " INNER JOIN pertenencias "
                . " ON pertenencias.idPrueba = pruebas.id WHERE"
                . " pertenencias.idJuego = '".$idJuego."';";
        $resultado = self::ejecutaConsulta ($sql);
        $pruebas = array();

	if($resultado) {
            // Añadimos un elemento por cada registro obtenido
            $row = $resultado->fetch();
            while ($row != null) {
                $pruebas[] = new prueba($row);
                $row = $resultado->fetch();
            }
	}     
        return $pruebas;
    }

    /**
     * Obtener partidas
     * 
     * @return array $partidas
     */
    public static function obtienePartidas() {
        //Añadimos la familia
        $sql = "SELECT id, nombre, fechaCreacion, duracion, fechaInicio, idJuego, username, finalizada FROM partidas "
                . "ORDER BY nombre;";
        $resultado = self::ejecutaConsulta($sql);
        $partidas = array();

        if ($resultado) {
            // Añadimos un elemento por cada producto obtenido
            $row = $resultado->fetch();
            while ($row != null) {
                $partidas[] = new Partida($row);
                $row = $resultado->fetch();
            }
        }

        return $partidas;
    }

    /**
     * Obtener una partida
     * 
     * @param integer $idPartida
     * 
     * @return array $row
     */
    public static function obtienePartida($idPartida) {
        $sql = "SELECT id, nombre, fechaCreacion, duracion, fechaInicio, idJuego, username, finalizada FROM partidas ";
        $sql .= "WHERE id=$idPartida;";
        $resultado = self::ejecutaConsulta($sql);

        $row = null;
        if (isset($resultado)) {
            $row = $resultado->fetch();
        }

        return $row;
    }

    /**
     * obtiene los equipos
     * 
     * @return array $equipos
     */
    public static function obtieneEquipos() {
        //Añadimos la familia
        $sql = "SELECT id, codigo, nombre, tiempo, idPartida FROM equipos "
                . "ORDER BY nombre;";
        $resultado = self::ejecutaConsulta($sql);
        $equipos = array();

        if ($resultado) {
            // Añadimos un elemento por cada producto obtenido
            $row = $resultado->fetch();
            while ($row != null) {
                $equipos[] = new Equipo($row);
                $row = $resultado->fetch();
            }
        }

        return $equipos;
    }

    /**
     * da de alta una partida nueva
     * 
     * @param integer $idPartida
     * @param string $newDuracion
     * 
     * @return boolean
     */
    public static function grabarPartidaNueva($idPartida, $newDuracion) {
        $row = self::obtienePartida($idPartida);

        $newName = $row['nombre'];
        $pos = strpos($newName, ':');
        $newName = substr($newName, 0, $pos + 1);
        $newName = $newName . " " . $newDuracion;
        $newFechaCreacion = $row['fechaCreacion'];
        $newFechaInicio = $row['fechaInicio'];
        $newIdJuego = $row['idJuego'];
        $newUsername = $row['username'];

        $sql = "INSERT INTO partidas (nombre,fechaCreacion,duracion,fechaInicio,idJuego,username)"
                . " VALUES ('$newName','$newFechaCreacion','$newDuracion','$newFechaInicio','$newIdJuego','$newUsername')";

        $resultado = self::ejecutaConsulta($sql);
    }

    /**
     * da de alta una nueva partida
     * 
     * @param integer $idPartida
     * @param string $newEquipo
     */
    public static function grabarNuevoEquipo($idPartida, $newEquipo) {
        list($usec, $sec) = explode(" ", microtime());
        $sql = "INSERT INTO equipos (codigo,nombre,tiempo,idPartida)"
                . " VALUES ('$sec','$newEquipo','0','$idPartida')";

        $resultado = self::ejecutaConsulta($sql);
    }

    /**
     * borrar una partida
     * 
     * @param integer $idPartida
     * 
     * @param boolean $resultado
     */
    public static function borrarPartida($idPartida) {
        $sql = "DELETE FROM equipos WHERE idPartida='$idPartida';";
        $resultado = self::ejecutaConsulta($sql);
        
        $sql = "DELETE FROM partidas WHERE id='$idPartida';";
        $resultado = self::ejecutaConsulta($sql);
        return $resultado;
    }

    /**
     * obtener resoluciones
     * 
     * @return array $resoluciones
     */
     public static function obtieneResoluciones() {
        $sql = "SELECT idPrueba, idEquipo, resuelta, intentos, estrellas FROM resoluciones;";
        $resultado = self::ejecutaConsulta($sql);
        $resoluciones = array();

        if ($resultado) {
            // Añadimos un elemento por cada producto obtenido
            $row = $resultado->fetch();
            while ($row != null) {
                $resoluciones[] = new Resolucion($row);
                $row = $resultado->fetch();
            }
        }

        return $resoluciones;
    }
    
    /**
     * obtener todas las pruebas
     * 
     * @return array $pruebas
     */
     public static function obtieneTodasLasPruebas() {
        $sql = "SELECT id, nombre, descExtendida, descBreve, tipo, "
                . "dificultad, url, ayudaFinal, username FROM pruebas;";
        $resultado = self::ejecutaConsulta ($sql);
        $pruebas = array();

	if($resultado) {
            // Añadimos un elemento por cada registro obtenido
            $row = $resultado->fetch();
            while ($row != null) {
                $pruebas[] = new prueba($row);
                $row = $resultado->fetch();
            }
	}     
        return $pruebas;
    }

    
}
?>
