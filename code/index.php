<?php
require 'funciones.php';

$name = 'todos';
$conn = mysqli_connect("mysql", "user", "password", "db");
if(!$conn->connect_error) {
	if(!existeTabla($conn)){
		echo "La base de datos no existe. Voy a crearla!<br>";
		crearTabla($conn);
	}
	$name = obtenerNombre($conn);
	$conn->close();
}else{
	die('La base de datos no está preparada.');
}

echo "Hola a $name";
