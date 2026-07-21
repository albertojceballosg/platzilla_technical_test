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
	
	
	function obtenerPasswordLogin($user,$aplicativo){
		global $db_login;
		
		$conexion = mysql_connect($db_login['db_server'].$db_login['db_port'], 
									$db_login['db_username'], 
									$db_login['db_password'],true);
		if (!mysql_select_db($db_login['db_name'], $conexion))
		    die(mysql_error());
		      
		  
		$sql = "SELECT * FROM $aplicativo WHERE username = '$user'";
		$res = mysql_query($sql, $conexion)  or die(mysql_error());
		  
		$fila = mysql_fetch_assoc($res);
		$pass = $fila['password'];
		 
		//$pass = decrypt($pass,"estaeslaclave01EncryptadaDeEnacol");
		
		return $pass;
	}
	
	function encryptarPasswordLogin($user,$aplicativo){
		global $db_login;
		
		$conexion = mysql_connect($db_login['db_server'].$db_login['db_port'], 
									$db_login['db_username'], 
									$db_login['db_password'],true);
		if (!mysql_select_db($db_login['db_name'], $conexion))
		    die(mysql_error());
		      
		  
		$sql = "SELECT * FROM $aplicativo WHERE username = '$user'";
		$res = mysql_query($sql, $conexion)  or die(mysql_error());
		  
		$fila = mysql_fetch_assoc($res);
		$pass = $fila[password];
		  
		$pass = encrypt($pass,"estaeslaclave01EncryptadaDeEnacol");
		
		return $pass;
	}

	
?>