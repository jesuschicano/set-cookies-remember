<?php
include('header.php');
?>

<h1 class="text-center">Inicio de sesión</h1>
<div class="box">
	<div class="line s-12 l-6 center">
		<form action="login.php" method="POST" class="customform">
			<label for="nick">Nick de usuario</label>
			<input type="text" name="nick" required value="<?php if( isset($_COOKIE['usuario_cookie']) ){ echo $_COOKIE['usuario_cookie']; } ?>">
			
			<label for="pass">Contraseña de usuario</label>
			<input type="password" name="pass" required value="<?php if( isset($_COOKIE['usuario_pass']) ){ echo $_COOKIE['usuario_pass']; } ?>">

			<button class="buttonn-submit-btn" type="submit" name="entrar">Entrar</button>
			
			<input type="checkbox" name="remember">
			<label for="remember">Recuérdame</label>
			
			<a href="forgotten.php" class="right">¿Has olvidado tu contraseña?</a>
		</form>
	</div>
</div>

<?php
if( isset($_POST['entrar']) ){
	// recoger el usuario en el sistema
	require_once('user.php');
	$usuario = new User();
	$data = $usuario->login($_POST['nick'], $_POST['pass']);

	if( !empty($data) ){
		// guardamos la sesión del usuario
		$_SESSION['perfil'] = $data['nick'];
		$_SESSION['rol'] = $data['id_tipo'];

		// guardar cookie cuando el login es ok
		if( !empty($_POST['remember']) ){
			setcookie('usuario_cookie',$_POST['nick'],time() + 3600);
			// el usuario expira en una hora
			setcookie('usuario_pass',$_POST['pass'],time() + 3600);
			// la pass expira en una hora
		} else {
			if( isset($_COOKIE["usuario_cookie"]) )
				setcookie("usuario_cookie","");
			if( isset($_COOKIE["usuario_pass"]) )
				setcookie("usuario_pass","");
		}

		// redirigir al index
		header("Location: index.php");
	} else {
		echo "<script>
						if(window.confirm('Usuario o contraseña no válidos.'))
							document.location = 'login.php';
					</script>";
	}
}
?>
<?php include('footer.php'); ?>