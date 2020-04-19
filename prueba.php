<?php
require_once('include/DB.php');
require_once('include/libs/Smarty.class.php');
$sel= $_POST['selec'];

session_start();

if (!isset($_SESSION['usuario'])){ 
    die("Error - debe <a href='login.php'>identificarse</a>.<br />");
}

$smarty = new Smarty;
$smarty->template_dir = 'smarty/templates/';
$smarty->compile_dir = 'smarty/templates_c/';
$smarty->config_dir = 'smarty/configs/';
$smarty->cache_dir = 'smarty/cache/';


$smarty->assign('usuario', $_SESSION['usuario']);



if($sel == 'Editar'){
$cod= $_POST['id'];



$smarty->assign('prueba', DB::obtienePrueba($cod));
$smarty->assign('pistaId', DB::obtienePistasId($cod));    
$smarty->display('prueba.tpl');   
}


elseif($sel == 'Duplicar'){
$cod= $_POST['id'];



$smarty->assign('prueba', DB::obtienePrueba($cod));
$smarty->assign('pistaId', DB::obtienePistasId($cod));
$smarty->display('prueba_duplicar.tpl');   
}



elseif($sel=='Eliminar' ){

$cod= $_POST['id'];



$smarty->assign('prueba', DB::obtienePrueba($cod));
$smarty->assign('pistaId', DB::obtienePistasId($cod));
$smarty->display('prueba_eliminar.tpl');
}


else{
//Crear prueba

    $pruebaNueva = new Prueba();
    $pruebaNueva->setNombrePrueba($_POST['nombre']);
    $pruebaNueva->setdescBrevePrueba($_POST['descripción']);
    $pruebaNueva->setdescExtendidaPrueba($_POST['DESCRIPCION']);
    $pruebaNueva->setTipoPrueba($_POST['tipo']);
    $pruebaNueva->setUrlPrueba($_POST['url']);


    $resultado = DB::crearPrueba($pruebaNueva);    
    
    
    if($resultado){
        setAlertMessage('Prueba creada con éxito','success');
        $juegoId = DB::getLastPruebaId();
        header('location:prueba.php?edit='.$pruebaId);
    } else {
        setAlertMessage('No se ha podido crear el juego', 'error');
        $smarty->assign('usuario', $_SESSION['usuario']);
        $smarty->assign('alertMessage', $_SESSION['alertMessage']);
        unsetAlertMessage();
        $smarty->assign('formType','default');
        $smarty->display('prueba_nueva.tpl');
    }
    

$smarty->display('prueba_nueva.tpl');   
}

?>