<?php


class MySql {

	private $dbc;
	private $user;
	private $pass;
	private $dbname;
	private $host;

	function __construct($host = "localhost", $dbname = "your_databse_name_here", $user = "your_username", $pass = "your_password") {
		$this->user = $user;
		$this->pass = $pass;
		$this->dbname = $dbname;
		$this->host = $host;
		$opt = array(
			 PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
		);
		try {
			$this->dbc = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8', $user, $pass, $opt);
		} catch (PDOException $e) {
			echo $e->getMessage();
			echo "There was a problem with connection to db check credenctials";
		}
	}

/* end function */

	private function writeUTF8filename($filenamename, $content) { /* save as utf8 encoding */
		$f = fopen($filenamename, "w+");
		# Now UTF-8 - Add byte order mark
		fwrite($f, pack("CCC", 0xef, 0xbb, 0xbf));
		fwrite($f, $content);
		fclose($f);
		/* USE EXAMPLE this is only used by public function above...
		  $this->writeUTF8filename($filename,$data);
		 */
	}

/* end function */

	public function recoverDB($file_to_load) {
		echo "write some code to load and proccedd .sql file in here ...";
		/* USE EXAMPLE this is only used by public function above...
		  recoverDB("some_buck_up_file.sql");
		 */
	}

/* end function */

	public function closeConnection() {
		$this->dbc = null;
		//EXAMPLE OF USE
		/* $connection->closeConnection(); */
	}

/* end function */
}

/* END OF CLASS */
?>