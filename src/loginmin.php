<html>
	<body>
	<div style="position:absolute;top:0px;left:0px;width:100%;height:200%;background-color:white;z-index:1000000;" id="divLoading">
	<div align="center" style="position:absolute;top: 50%;left: 50%;height: 30%;width: 50%;margin: -15% 0 0 -25%;">
	<h2>Loading...</h2>
	<img src="themes/images/loading.gif" />
	</div>
	</div>
	<div style="display:none">
	<form action="index.php" method="post" name="DetailView" id="form">
					<input type="hidden" name="module" value="Users" />
					<input type="hidden" name="action" value="Authenticate" />
					<input type="hidden" name="return_module" value="Users" />
					<input type="hidden" name="return_action" value="Login" />
					<div class="inputs">
						<div class="label">Usuario</div>
						<div class="input"><input type="text" name="user_name" value="<?php echo $_REQUEST['user_name'];?>"/></div>
						<br />
						<br/>
						<div class="label">Contrase&ntilde;a</div>
						<div class="input"><input type="text" name="user_password" id="user_password" value="<?php echo $_REQUEST['user_password'];?>"/></div>
												<div class="errorMessage">
							You must specify a valid username and password.
						</div>
												<br />
						<br/>
						<div class="button">
							<input type="submit" id="submitButton" value="Conexi&oacute;n" style="width:120; height:25;"/>
						</div>
					</div>
	</form>
	</div>
	<script>
		function onCargar() {
			document.DetailView.submit();
		}
		
		if (window.addEventListener)
			window.addEventListener('load',onCargar,false);
		else
			window.attachEvent('onload',onCargar,false);
	</script>
	</body>
</html>