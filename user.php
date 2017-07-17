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
	* Devuelve todos los usuarios
	* @var return array $data
	*/
	public function readAll()
	{
		$query = $this->db->prepare("SELECT * FROM usuarios");
		$query->execute();

		$data = array();
		while( $row = $query->fetch(PDO::FETCH_ASSOC) )
		{
			$data[] = $row;
		}
		$this->db = Database::disconnect();
		return $data;
	}

	/**
	* Devuelve los datos de un único usuario
	* @var $nick
	*/
	public function readUser($nick)
	{
		$data = array("nick"=>$nick);

		try{
			$query = $this->db->prepare("SELECT * FROM usuarios WHERE nick=:nick");
			$query->execute($data);

			if( $query->rowCount() > 0 ){
				return $query->fetch(PDO::FETCH_ASSOC);
				$this->db = Database::disconnect();
			}
		}catch(PDOException $e){
			echo $e->getMessage();
		}
	}

	/**
	* Inserta un nuevo usuario en la base de datos
	* @var $nombre, $apellidos, $password, $localidad, $mail, $telefono, $tipo
	*/
	public function save($nombre, $apellidos, $password, $localidad, $mail, $telefono, $tipo)
	{
		// generar el nick
		$nick = "";
		// ****-*****

		// preparación de marcadores
		$datos = array(
			"nick" => $nick,
			"nombre" => $nombre,
			"apellidos" => $apellidos,
			"password" => password_hash( $password, self::HASH, ['cost' => self::COST] ),
			"localidad" => $localidad,
			"mail" => $mail,
			"telefono" => $telefono,
			"id_tipo" => $tipo,
			"id_regimen" => 3,
		);

		try{
			$launch = $this->db->prepare("INSERT INTO usuarios (nick,nombre,apellidos,password,localidad,mail,telefono,id_tipo,id_regimen) values (:nick,:nombre,:apellidos,:password,:localidad,:mail,:telefono,:id_tipo,:id_regimen)");
			$launch->execute($datos);

			if( $launch->rowCount() > 0 ){
				echo "<script>
						if(window.confirm('Usuario creado satisfactoriamente.'))
							document.location = 'index.php';
					</script>";
			}
			else{
				echo "<div class='box text-center'>Error en el registro.</div>";
			}
		}catch(PDOException $e){
			echo $e->getMessage();
		}

		$this->db = Database::disconnect();
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

	/**
	* Envía la contraseña al usuario por mail
	*/
	public function forgotten($nick)
	{
		// marcadores
		$datos = array("nick" => $nick);

		// consulta
		try{
			$query = $this->db->prepare("SELECT * FROM usuarios WHERE nick=:nick");
			$query->execute($datos);
		}catch(PDOException $e){
			echo $e->getMessage();
		}

		// comprobar que existe el usuario
		if( $query->rowCount() > 0 ){
			// guardamos los resultados
			$r = $query->fetch(PDO::FETCH_ASSOC);

			// preparar la contraseña que se va a enviar por mail
			// y los datos para preparar el mail
			//$password = password_get_info($r['password']); // devuelve un array con info del hash
			echo crypt($r['password']);
			$to = $r['mail'];
			$subject = "[BICVEY] Tu contraseña recuperada";
			$message = "Esta es tu contraseña del sistema, no la olvides: " . $password;

			// envío del correo sin fallos
			if( mail($to, $subject, $message) ){
				echo "<div class='box text-center error'>Se ha enviado la contraseña a tu bandeja de correo.</div>";
			} else {
				echo "<div class='box text-center error'>Ha fallado el envío del correo</div>";
			}
		} else {
			// no se encuentra el usuario en la base de datos
			echo "<div class='box text-center error'>No se encuentra el usuario en el sistema</div>";
		}

		$this->db = Database::disconnect();
	}

	public function editUser($nick,$nombre, $apellidos, $password, $localidad, $mail, $telefono)
	{
		// marcadores:
		$data = array(	
			"nick" => $nick,
			"nombre" => $nombre,
			"apellidos" => $apellidos,
			"password" => password_hash( $password, self::HASH, ['cost' => self::COST] ),
			"localidad" => $localidad,
			"mail" => $mail,
			"telefono" => $telefono,
		);

		try{
			$query = $this->db->prepare("UPDATE usuarios SET 
			nombre=:nombre,
			apellidos=:apellidos,
			password=:password,
			localidad=:localidad,
			mail=:mail,
			telefono=:telefono WHERE nick=:nick");
			$query->execute($data);

			if( $query->rowCount() > 0 ){
				echo "<script>
						if(window.confirm('Usuario modificado satisfactoriamente.'))
							document.location = 'index.php';
					</script>";
			}
			else{
				echo "<div class='box text-center'>Error en el registro.</div>";
			}
		}catch(PDOException $e){
			echo $e->getMessage();
		}
		$this->db = Database::disconect();
	}
}