<?php

function existeTabla($conn)
{
	$result = $conn->query("SHOW TABLES LIKE 'pruebas';");
	return $result->num_rows === 1;
}

function crearTabla($conn)
{
	//Crear la base de datos
	$sql = "CREATE TABLE pruebas (
	id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
	nombre VARCHAR(30) NOT NULL
	);";

	if(!$conn->query($sql)){
		return false;
	}

	//Introducir datos de pruebas
	$sql = "INSERT INTO pruebas (id, nombre) VALUES (1, 'toda la clase')";
	return $conn->query($sql);

}

function obtenerNombre($conn)
{
	$sql = "SELECT id, nombre FROM pruebas where id = 1";
	$result = $conn->query($sql);
	//Obtenemos el resultado en las siguientes iteraciones
	while($row = $result->fetch_assoc()) {
		return $row['nombre'];
	}
}