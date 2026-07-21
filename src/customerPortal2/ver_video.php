<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Zona Clientes, Time Management</title>
<link href="css/style.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="favicon.ico" />
<script language="javascript" type="text/javascript" src="js/prototype.js"></script>
<script language="javascript" type="text/javascript" src="js/general.js"></script>
<script>
function fnMySettings(){

		params = "last_login=support_start_date=2011-06-27&support_end_date=2012-06-27";

		window.open("MySettings.php?"+params,"MySetttings","menubar=no,location=no,resizable=no,scrollbars=no,status=no,width=500,height=350,left=550,top=200");

}
</script>
</head>

<body>

<input align="left" class="crmbutton small cancel" type="button" value="Volver" onclick="window.history.back();">
<table align="center">
<tr><td align="center">
<iframe id="iframeVideo" src="http://video.timemanagement.es/video.php?id=<?=$_GET['v']?>" width="420" height="340" frameborder="0" scrolling="no" align="middle"></iframe>
</td></tr></table>
</body>
</html>
