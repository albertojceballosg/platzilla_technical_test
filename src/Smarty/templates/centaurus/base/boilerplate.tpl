{strip}
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<meta name="msapplication-TileColor" content="#ffffff" />
	<meta name="msapplication-TileImage" content="favicon/ms-icon-144x144.png" />
	<meta name="theme-color" content="#ffffff" />
	<title>{block name="title"}{$MODULE_NAME|@getTranslatedString:$MODULE_NAME} - {$USER} - {$APP.LBL_BROWSER_TITLE}{/block}</title>
	<link rel="manifest" href="favicon/manifest.json">
	<link type="text/css" href="themes/centaurus/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
	<link type="text/css" href="themes/centaurus/css/libs/font-awesome.css" rel="stylesheet" />
	<link type="text/css" href="themes/centaurus/css/compiled/theme_styles.css" rel="stylesheet" />
	<link type="text/css" href="themes/centaurus/css/compiled/theme_custom.css?v=1.1" rel="stylesheet" />
	<link type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400" rel="stylesheet">
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
	<!--[if lt IE 9]>
	<script src="themes/centaurus/js/html5shiv.js"></script>
	<script src="themes/centaurus/js/respond.min.js"></script>
	<![endif]-->
{block name="css"}{/block}
	<script src="themes/centaurus/js/jquery.js"></script>
	<script src="themes/centaurus/js/bootstrap.js"></script>
{block name="js"}{/block}
</head>
<body class="pace-done">
	<div class="page-wrap container">
{block name="body"}{/block}
{block name="scripts"}{/block}
	</div>
</body>
</html>
{/strip}