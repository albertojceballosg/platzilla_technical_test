<?php
	include_once('../include/utils/utils.php');
	global $default_language;
	setPortalCurrentLanguage();
	$default_language = getPortalCurrentLanguage();
	require_once("../language/".$default_language.".lang.php");
	
?>
<script language="javascript"> 
function loadTwo(iframe1URL ) 
{ 
top.frames['left'].location.href = 'menu.php'; 
} 
</script>
<?php 
$customerid = $_REQUEST['user'];
 
 
 $pepe=0;
if(isset($_REQUEST['pepe'])){
if	($_REQUEST['pepe']=='1'){$pepe=1;}else{$pepe=0;}
	
	}
 $accion="top.frames['left'].location.href = 'menu.php?pepe=1&user=";
 $accion.=$customerid;
 $accion.="&cuenta=";
 $accion.=$_REQUEST['cuenta'];
 $accion.="'";
  $accion2="top.frames['left'].location.href = 'menu.php?pepe=0&user=";
 $accion2.=$customerid;
 $accion2.="&cuenta=";
 $accion2.=$_REQUEST['cuenta'];
 $accion2.="'";
$output='<table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="FFFFFF" align="left">
<tbody>
 <tr>
<td class="toggle_table" height="5"></td>
</tr>
<tr>
<td background="bg_folder_title.gif" align="center" height="30" style="font-family: Arial,Helvetica,sans-serif; font-size: 15px; "><b>
'.getTranslatedString('Carpetas').'</a></b></b></td></tr><tr>
<td bgcolor="808080" height="1" align="left">
</td></tr><tr>
<td align="left">
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<tbody> <!--<tr>

<td background="bg_folder_title.gif"  height="19" align="left">
&nbsp;&nbsp;Carpetas&nbsp;</td></tr>--></tbody></table><br><table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="FFFFFF" align="left">
<tbody><tr>
<td align="left" style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; ">';
if($pepe==1) {
$output.='

<span style="white-space: nowrap;"><tt>&nbsp;&nbsp;</tt> <a style="text-decoration:none" onClick="'.$accion2.';" target="right" href="mails.php?user='.$customerid.'&cuenta='.$_REQUEST['cuenta'].'"><font color="#000000"><img width="16" vspace="1" border="0" hspace="1" height="15" align="absbottom" src="inbox.gif"> Entrada</font></a> &nbsp;<font color="#0000FF"></font></span><br>
<span style="white-space: nowrap;"><tt>&nbsp;&nbsp;</tt><b><a style="text-decoration:none" onClick="'.$accion.';" target="right" href="enviados.php?user='.$customerid.'&cuenta='.$_REQUEST['cuenta'].'>"><font color="#000000"><img width="16" vspace="1" border="0" hspace="1" height="15" align="absbottom" src="sent.gif"> Enviados</font></a></b></span>
';
}else {
$output.='

<span style="white-space: nowrap;"><tt>&nbsp;&nbsp;</tt><b><a style="text-decoration:none" onClick="'.$accion2.';" target="right" href="mails.php?user='.$customerid.'&cuenta='.$_REQUEST['cuenta'].'"><font color="#000000"><img width="16" vspace="1" border="0" hspace="1" height="15" align="absbottom" src="inbox.gif"> Entrada</font></a></b>&nbsp;<font color="#0000FF"></font></span><br>
<span style="white-space: nowrap;"><tt>&nbsp;&nbsp;</tt> <a style="text-decoration:none" onClick="'.$accion.';" target="right" href="enviados.php?user='.$customerid.'&cuenta='.$_REQUEST['cuenta'].'>"><font color="#000000"><img width="16" vspace="1" border="0" hspace="1" height="15" align="absbottom" src="sent.gif"> Enviados</font></a> </span>
';	
	}

$output.='
</span><br>

<br><!-- Calendario -->

<!-- Calendario -->


<!-- Fim SmallCall -->
<!-- Contatos -->

<!-- Contatos -->
<!-- Anotacoes -->

<!-- Anotacoes -->
</td></tr></tbody></table></td></tr></tbody></table>';

echo($output);
?>