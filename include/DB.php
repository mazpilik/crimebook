<?php

    require_once dirname(__FILE__) . '/Partida.php';
    require_once dirname(__FILE__) . '/Equipo.php';
    require_once dirname(__FILE__) . '/juego.php';
    require_once dirname(__FILE__) . '/Resolucion.php';
    require_once dirname(__FILE__) . '/Prueba.php';
    require_once dirname(__FILE__) . '/Respuesta.php';
    require_once dirname(__FILE__) . '/Pista.php';

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
                $pruebas[] = new Prueba($row);
                $row = $resultado->fetch();
            }
	}     
        return $pruebas;
    }

    /**
     * asociar prueba a juego
     * 
     * @param int $idPrueba
     * @param int $idJuego
     * 
     * @return boolean
     */
    public static function addPruebaToJuego(int $idJuego, int $idPrueba)
    {
        $sql = "INSERT INTO pertenencias (idJuego, idPrueba) VALUES ($idJuego, $idPrueba)";
        $resultado = self::ejecutaConsulta($sql);
        return $resultado;
    }

    /**
     * Borrar asociación de pruebas a juego
     * 
     * @param array $pruebas
     * @param int $idJuego
     * 
     * @return boolean
     */
    public static function deleteJuegoPruebas(array $pruebas, int $idJuego)   
    {
        $sql = "DELETE FROM pertenencias WHERE idJuego = ".$idJuego." AND idPrueba IN (".join(',',$pruebas).")";
        $result = self::ejecutaConsulta($sql);
        return $result;
    }

    /**
     * crear una partida nueva
     * 
     * @param array $partida
     * 
     * @return boolean
     */
    public static function crearPartida($partida){
        $sql = 'INSERT INTO partidas ';
        $sql .= '(nombre, fechaCreacion, duracion, fechaInicio, idJuego, username) ';
        $sql .= 'VALUES(';
        $sql .= '"'.$partida['nombre'].'", ';
        $sql .= '"'.$partida['fechaCreacion'].'", ';
        $sql .= $partida['duracion'].', ';
        $sql .= '"'.$partida['fechaInicio'].'", ';
        $sql .= $partida['idJuego'].', ';
        $sql .= '"'.$partida['username'].'"';
        $sql .= ');';
        $resultado = self::ejecutaConsulta($sql);
        
        return $resultado;
    }

    /**
     * Devuelve el id de la última partida insertada
     * 
     * @return integer $id
     */
    public static function getLastPartidaId(){
        $id=0;
        $sql = 'SELECT id FROM partidas ORDER BY id DESC LIMIT 1';
        $resultado = self::ejecutaConsulta($sql);
        if($resultado){
            $row = $resultado->fetch();
            $id = $row['id'];
        }
        return $id;
    }

    /**
     * Actualizar una partida
     * 
     * @param array $partida
     * 
     * @return boolean
     */
    public static function updatePartida($partida){
        $sql = 'UPDATE partidas SET '
                . 'duracion = '.$partida['duracion'].', '
                . 'fechaInicio = "'.$partida['fechaInicio'].'" '
                . 'WHERE id = '.$partida['id'].';';
        return self::ejecutaConsulta($sql);
    }

    /**
     * Obtener partidas
     * 
     * @return array $partidas
     */
    public static function obtienePartidas() {
        //Añadimos la familia
        $sql = "SELECT ptd.id, ptd.nombre, ptd.fechaCreacion, ptd.duracion, ptd.fechaInicio, ptd.idJuego, ptd.username, ptd.finalizada, COUNT(eqs.id) AS equipos FROM partidas ptd "
                . "LEFT JOIN equipos eqs ON (eqs.idPartida = ptd.id) "
                . "GROUP BY ptd.id "
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
        $sql = "SELECT ptd.id, ptd.nombre, ptd.fechaCreacion, ptd.duracion, ptd.fechaInicio, ptd.idJuego, ptd.username, ptd.finalizada, COUNT(eqs.id) AS equipos FROM partidas ptd "
                . "LEFT JOIN equipos eqs ON (eqs.idPartida = ptd.id) "
                . "WHERE ptd.id=$idPartida "
                . "GROUP BY ptd.id;";
        $resultado = self::ejecutaConsulta($sql);


        $row = null;
        if (isset($resultado)) {
            $row = $resultado->fetch();
        }
        $partida = new Partida($row);
        return $partida;
    }

    /**
     * obtiene los equipos de la partida
     * 
     * @param integer $idPartida
     * 
     * @return array $equipos
     */
    public static function getPartidaEquipos($idPartida){
        $sql = "SELECT * FROM equipos WHERE idPartida = $idPartida";
        $resultado = self::ejecutaConsulta($sql);
        $equipos = array();
        if($resultado){
            while($row = $resultado->fetch()){
                array_push($equipos, new Equipo($row));
            }
        }
        return $equipos;
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
     * da de alta un nuevo equipo
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
     * borrar equpos
     * 
     * @param array $equipos
     * 
     * @return boolean
     */
    public static function deleteEquipos(array $equipos){
        $sql = "DELETE FROM equipos WHERE id IN (".join(',', $equipos).")";
        $result = self::ejecutaConsulta($sql);
        return $result;
    }

    /**
     * borrar una partida
     * 
     * @param integer $idPartida
     * 
     * @param boolean $resultado
     */
    public static function borrarPartidas($partidas) {
        $sql = "DELETE FROM partidas WHERE partidas.id IN (".join(',',$partidas).");";
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

    /**
     * obtener todas las pruebas
     * 
     * @return array $pruebas
     */
    public static function getAllPruebas(){
        $sql = "SELECT * FROM pruebas ORDER BY nombre";
        $resultado = self::ejecutaConsulta($sql);
        $pruebas = array();
        if($resultado){
            while($row = $resultado->fetch()){
                array_push($pruebas, new Prueba($row));
            }
        }
        return $pruebas;
    }

    /**
     * dar de alta una prueba
     * 
     * @param array $prueba
     * 
     * @return boolean
     */
    public static function createPrueba($prueba){
        $keys = array();
        $insertFields = array();
        foreach ($prueba as $key => $value) {
            array_push($keys, $key);
            array_push($insertFields, "'".$value."'");
        }
        $sql = "INSERT INTO pruebas "
                . "(".join(',', $keys).") "
                . "VALUES ("
                . join(',',$insertFields)
                . ");";
        return self::ejecutaConsulta($sql);
    }

    /** 
     * obtener el id de la última prueba
     * 
     * @return integer $id
     */
    public static function getLastPruebaId(){
        $id = 0;
        $sql = "SELECT id FROM pruebas ORDER BY id DESC";
        $result = self::ejecutaConsulta($sql);
        if($result){
            $row = $result->fetch();
            $id = $row['id'];
        }
        return $id;
    }

    /**
     * obtener una prueba por su id
     * 
     * @param integer $id
     * 
     * @return Object $prueba
     */
    public static function getPruebaById($id){
        $sql = "SELECT * FROM pruebas WHERE id = ".$id;
        $result = self::ejecutaConsulta($sql);
        if($result){
            $row = $result->fetch();
            return new Prueba($row);
        }
        return false;
    }

    /** 
     * actualiza una prueba
     * 
     * @param integer $id
     * 
     * @return boolean
     */
    public static function updatePrueba($prueba){
        $updateFields = array();
        foreach ($prueba as $key => $value) {
            if($key != 'id'){
                array_push($updateFields, $key." = '".$value."'");
            }
        }
        $sql = "UPDATE pruebas SET "
                . join(',',$updateFields)." "
                . "WHERE id = ".$prueba['id'];
        $result = self::ejecutaConsulta($sql);
        return $result;
    }

    /**
     * Borrado de pruebas
     * 
     * @param array $pruebas ids de las pruebas
     * 
     * @return boolean
     */
    public static function deletePruebas(array $pruebas)
    {
        $sql = "DELETE FROM pruebas WHERE pruebas.id IN (".join(',',$pruebas).");";
        $resultado = self::ejecutaConsulta($sql);
        return $resultado;
    }

    /**
     * clonar una prueba
     * 
     * @param int $idPrueba
     * 
     * @return boolean
     */
    public static function clonePrueba($idPrueba){
        $result = true;
        //traemos la prueba
        $candidatePrueba = self::getPruebaById($idPrueba);
        $newPrueba = array(
            'nombre' => $candidatePrueba->getNombre().'-cloned',
            'descExtendida' => $candidatePrueba->getdescExtendida(),
            'descBreve' => $candidatePrueba->getdescBreve(),
            'url' => $candidatePrueba->getUrl(),
            'tipo' => $candidatePrueba->getTipo(),
            'dificultad' => $candidatePrueba->getDificultad(),
            'ayudaFinal' => $candidatePrueba->getAyudaFinal(),
            'username' => $candidatePrueba->getUsername()
        );

        //generamos la nueva prueba
        if(!self::createPrueba($newPrueba)){
            $result = false;
        }

        //traemos el id de la última prueba
        $lastPruebaId = self::getLastPruebaId();

        //traemos las respuestas
        $respuestas = self::getRespuestasOfPrueba($candidatePrueba->getId());

        foreach ($respuestas as $respuesta) {
            $newRespuesta = array(
                'idPrueba' => $lastPruebaId,
                'respuesta' => $respuesta->getRespuesta()
            );
            if(!self::addRespuesta($newRespuesta)){
                $result = false;
            }
        }

        //traemos las pistas de la pruebaCandidata
        $pistas = self::getPistasByIdPrueba($candidatePrueba->getId());
        foreach ($pistas as $pista ) {
            $newPista = array(
                'idPrueba' => $lastPruebaId,
                'texto' => $pista->getTexto(),
                'tiempo' => $pista->getTiempo(),
                'intentos' => $pista->getIntentos()
            );
            if(!self::addPista($newPista)){
                $result = false;
            }
        }
        return $result;

    }

    /**
     * obtener respuestas de una prueba
     * 
     * @param integer $pruebaId
     * 
     * @return array $repuestas
     */
    public static function getRespuestasOfPrueba(int $pruebaId)
    {
        $respuestas = array();
        $sql = "SELECT * FROM respuestas WHERE idPrueba = ".$pruebaId;
        $resultado = self::ejecutaConsulta($sql);
        if($resultado){
            while($row = $resultado->fetch()){
                array_push($respuestas, new Respuesta($row));
            }
        }
        return $respuestas;
    }

    /**
     * añadir una respuesta
     * 
     * @param array $respuesta
     * 
     * @return boolean
     */
    public static function addRespuesta(array $respuesta)
    {
        $sql = "INSERT INTO respuestas (idPrueba, respuesta)"
                . "VALUES( "
                . $respuesta['idPrueba'].", "
                . "'".$respuesta['respuesta']."'"
                .");";
        $result = self::ejecutaConsulta($sql);
        return $result;
    }

    /** 
     * borrar una respuesta
     * 
     * @param array $idRespuesta
     * 
     * @return boolean
     */
    public static function deleteRespuestas(array $respuestas) {
        $sql = "DELETE FROM respuestas WHERE respuestas.id IN (".join(',',$respuestas).")";
        $result = self::ejecutaConsulta($sql);
        return $result;
    }

    /**
     * crear pista
     * 
     * @param array $pista
     * 
     * @return boolean
     */
    public static function addPista(array $pista)
    {
        $keys = array();
        $values = array();
        foreach ($pista as $key => $value) {
            array_push($keys, $key);
            array_push($values, "'$value'");
        }
        $sql = "INSERT INTO pistas (".join(',',$keys).") "
                . "VALUES (".join(',',$values).")";
        $result = self::ejecutaConsulta($sql);
        return $result; 
    }
    /**
     * obtener pistas de una prueba
     * 
     * @param integer $idPrueba
     * 
     * @return array $pistas
     */
    public static function getPistasByIdPrueba(int $idPrueba)
    {
        $pistas = array();
        $sql = "SELECT * FROM pistas WHERE idPrueba = ".$idPrueba;
        $result = self::ejecutaConsulta($sql);
        if($result){
            while($row = $result->fetch()){
                array_push($pistas, new Pista($row));
            }
        }
        return $pistas;
    }
    /**
     * borrar pistas
     * 
     * @param array $pistas
     * 
     * @return boolean
     */
    public static function deletePistas(array $pistas)
    {
        $sql = "DELETE FROM pistas WHERE pistas.id IN (".join(',',$pistas).")";
        $result = self::ejecutaConsulta($sql);
        return $result;
    }

}
?>
