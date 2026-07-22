<?php

	function encrypt($string, $key) {
	   $result = '';
	   for($i=0; $i<strlen($string); $i++) {
	      $char = substr($string, $i, 1);
	      $keychar = substr($key, ($i % strlen($key))-1, 1);
	      $char = chr(ord($char)+ord($keychar));
	      $result.=$char;
	   }
	   return base64_encode($result);
	}

	function decrypt($string, $key) {
	   $result = '';
	   $string = base64_decode($string);
	   for($i=0; $i<strlen($string); $i++) {
	      $char = substr($string, $i, 1);
	      $keychar = substr($key, ($i % strlen($key))-1, 1);
	      $char = chr(ord($char)-ord($keychar));
	      $result.=$char;
	   }
	   return $result;
	}

	// Conexión a la BD de login (config global $db_login). Migrado de la extensión
	// nativa mysql_* (eliminada en PHP 7) a mysqli. mysqli_connect recibe host y
	// puerto por separado (mysql_connect aceptaba "host:puerto" en un solo argumento).
	function _loginDbConnect() {
		global $db_login;
		$port     = (int) ltrim($db_login['db_port'], ':');
		$conexion = mysqli_connect($db_login['db_server'], $db_login['db_username'],
									$db_login['db_password'], '', $port);
		if (!$conexion) {
			die(mysqli_connect_error());
		}
		if (!mysqli_select_db($conexion, $db_login['db_name'])) {
			die(mysqli_error($conexion));
		}
		return $conexion;
	}

	function obtenerPasswordLogin($user,$aplicativo){
		$conexion = _loginDbConnect();

		$sql = "SELECT * FROM $aplicativo WHERE username = '$user'";
		$res = mysqli_query($conexion, $sql)  or die(mysqli_error($conexion));

		$fila = mysqli_fetch_assoc($res);
		$pass = $fila['password'];

		//$pass = decrypt($pass,"estaeslaclave01EncryptadaDeEnacol");

		return $pass;
	}

	function encryptarPasswordLogin($user,$aplicativo){
		$conexion = _loginDbConnect();

		$sql = "SELECT * FROM $aplicativo WHERE username = '$user'";
		$res = mysqli_query($conexion, $sql)  or die(mysqli_error($conexion));

		$fila = mysqli_fetch_assoc($res);
		$pass = $fila['password'];

		$pass = encrypt($pass,"estaeslaclave01EncryptadaDeEnacol");

		return $pass;
	}


?>
