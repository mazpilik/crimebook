<!DOCTYPE html>
<html>
<head>
	<title>Crimebook</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta charset="UTF-8">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="css/estilos.css">
</head>
<body>


	<div class="topnav" id="myTopnav">
	 <a href="Index.html">Listado de juegos</a>
	 <a href="Pagina2.html">Listado de partidas</a>
	 <a href="listado-de-pruebas.php" class="active">Listado de pruebas</a>
	 <a href="Pagina4.html">Partida nueva</a>
	 <a href="Pagina5.html">Juego Nuevo/editar juego</a>
	 <a href="prueba.php">Prueba Nueva/ Editar prueba</a>
	 <a href="Pagina7.html">Consultar partida</a>
	 <a href="javascript:void(0);" class="icon" onclick="myFunction()">
		 <i class="fa fa-bars"></i>
	 </a>
	</div>

<h2 align="center">Pruebas</h2>
<table align="center">
	<tr>
                <th>Seleccionar </th>
		<th>Nombre</th>
		<th>Descripción</th>
		<th>Tipo</th>
		<th>Usuario que la creó</th>
               
	</tr>
        
       
	</tr>
        
       
        {foreach from=$pruebas item=prueba}
        <form id='{$prueba->getIdPrueba()}' action='prueba.php' method='post'>    
        <tr>
            
        
        <td>    
                
                <input type='hidden' name='id' value='{$prueba->getIdPrueba()}'/>
                
                <input type='submit' name='selec' value='Editar'/>
                <input type='submit' name='selec' value='Duplicar'/>
                <input type='submit' name='selec' value='Eliminar'/>
        </td>
                <td>{$prueba->getNombrePrueba()}</td>
                <td>{$prueba->getdescBrevePrueba()}</td>
                <td>{$prueba->getTipoPrueba()}</td>
                <td>{$prueba->getUsernamePrueba()}</td>  

        
                 
        </tr>
         </form>  
        {/foreach}
	 

</table>
<br>
<div align="center">
           
         <form id='{$prueba->getIdPrueba()}' action='prueba.php' method='post'>   
        <input type='submit' name='selec' value='Crear prueba'/>
	 </form>    

</div>

<script>
function myFunction() {
  var x = document.getElementById("myTopnav");
  if (x.className === "topnav") {
    x.className += " responsive";
  } else {
    x.className = "topnav";
  }
}
</script>

</body>
</html>