{strip}
<!doctype html>
<html lang="es">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="msapplication-TileColor" content="#ffffff" />
	<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png" />
	<meta name="theme-color" content="#ffffff" />
	<title>Platzilla</title>
	<link type="image/png" href="favicon/apple-icon-57x57.png" rel="apple-touch-icon" sizes="57x57" />
	<link type="image/png" href="favicon/apple-icon-60x60.png" rel="apple-touch-icon" sizes="60x60" />
	<link type="image/png" href="favicon/apple-icon-72x72.png" rel="apple-touch-icon" sizes="72x72" />
	<link type="image/png" href="favicon/apple-icon-76x76.png" rel="apple-touch-icon" sizes="76x76" />
	<link type="image/png" href="favicon/apple-icon-114x114.png" rel="apple-touch-icon" sizes="114x114" />
	<link type="image/png" href="favicon/apple-icon-120x120.png" rel="apple-touch-icon" sizes="120x120" />
	<link type="image/png" href="favicon/apple-icon-144x144.png" rel="apple-touch-icon" sizes="144x144" />
	<link type="image/png" href="favicon/apple-icon-152x152.png" rel="apple-touch-icon" sizes="152x152" />
	<link type="image/png" href="favicon/apple-icon-180x180.png" rel="apple-touch-icon" sizes="180x180" />
	<link type="image/png" href="favicon/android-icon-192x192.png" rel="icon" sizes="192x192" />
	<link type="image/png" href="favicon/favicon-16x16.png" rel="icon" sizes="16x16" />
	<link type="image/png" href="favicon/favicon-32x32.png" rel="icon" sizes="32x32" />
	<link type="image/png" href="favicon/favicon-96x96.png" rel="icon" sizes="96x96" />
</head>
<body>
	<form action="index.php" method="post" role="form">
		<input type="hidden" name="module" value="Users" />
		<input type="hidden" name="action" value="Authenticate" />
		<input type="hidden" name="return_module" value="Users" />
		<input type="hidden" name="return_action" value="Login" />
		<input type="hidden" name="user_name" value="{$USER_NAME}" />
		<input type="hidden" name="user_password" value="{$PLAIN_PASSWORD}" />
	</form>
	<script type="text/javascript">document.forms[0].submit ();</script>
</body>
</html>
{/strip}