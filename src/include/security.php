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

	// Busca un usuario con SENTENCIA PREPARADA (evita inyección SQL vía $user).
	// El nombre de tabla no puede parametrizarse en un prepared statement, así que se
	// valida como identificador seguro (whitelist) antes de interpolarlo entre backticks.
	function _loginFetchUser($conexion, $aplicativo, $user) {
		if (!preg_match('/^[A-Za-z0-9_]+$/', $aplicativo)) {
			die('Nombre de tabla no válido');
		}
		$sql  = "SELECT * FROM `$aplicativo` WHERE username = ?";
		$stmt = mysqli_prepare($conexion, $sql) or die(mysqli_error($conexion));
		mysqli_stmt_bind_param($stmt, 's', $user);
		mysqli_stmt_execute($stmt);
		$res  = mysqli_stmt_get_result($stmt);
		$fila = $res ? mysqli_fetch_assoc($res) : null;
		mysqli_stmt_close($stmt);
		return $fila;
	}

	function obtenerPasswordLogin($user,$aplicativo){
		$conexion = _loginDbConnect();
		$fila = _loginFetchUser($conexion, $aplicativo, $user);
		$pass = $fila ? $fila['password'] : null;

		//$pass = decrypt($pass,"estaeslaclave01EncryptadaDeEnacol");

		return $pass;
	}

	function encryptarPasswordLogin($user,$aplicativo){
		$conexion = _loginDbConnect();
		$fila = _loginFetchUser($conexion, $aplicativo, $user);
		$pass = $fila ? $fila['password'] : null;

		$pass = encrypt($pass,"estaeslaclave01EncryptadaDeEnacol");

		return $pass;
	}


?>
