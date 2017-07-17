<?php
require 'database.php';

class User
{
	private $db;
	// Opciones de contraseña:
	const HASH = PASSWORD_DEFAULT;
	const COST = 14;

	/**
	* constructor
	*/
	public function __construct()
	{
		$this->db = Database::connect();
	}

	
	/**
	* Devuelve el usuario logueado en el sistma
	* @var $nick,$pass
	*/
	public function login($nick,$pass)
	{
		// preparar marcadores
		$datos = array("nick"=>$nick);

		// consulta que corresponda con el nick
		try{
			// recoger primero por el nick
			$query = $this->db->prepare("SELECT * FROM usuarios WHERE nick=:nick");
			$query->execute($datos);

			// guardar el resultado y desconexión
			$usuario = $query->fetch(PDO::FETCH_ASSOC);
			$this->db = Database::disconnect();

			// verificar la contraseña del usuario:
			if( password_verify($pass, $usuario['password']) ){
				return $usuario;
			} else {
				return null;
			}
		}catch(PDOException $e){
			echo $e->getMessage();
		}
	}
}
