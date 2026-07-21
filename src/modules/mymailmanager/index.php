<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
global $currentModule;

// $plat = '';
// if (isset($_SESSION['plat']))
	// $plat = $_SESSION['plat'].'/';

// checkFileAccessForInclusion($plat."modules/$currentModule/ListView.php");
// include_once($plat."modules/$currentModule/ListView.php");


$vista='<div class="row">
	<div class="col-lg-12">
		<h1>
			<a class="hdrLink" href="index.php?action=index&amp;module=mymailmanager&amp;parenttab=">Administrador Correo</a>
		</h1><h1>
	</h1></div>
</div><br>';

$vista.='<div class="row">
	<div class="col-lg-12">	
		<div class="main-box">
			
			<header class="main-box-header clearfix">
				<h2 id="settings_mail_fldrname">Configuración</h2>

			</header>
			<div class="main-box-body clearfix" id="">

				<div id="_mainfolderdiv_"><br>
<div id="mm_selected_folder"></div>
<div id="_folderdiv_"></div>
<input type="hidden" name="refresh_timeout" id="refresh_timeout" value=""></div>
				
				<span id="_messagediv_" style="display: none;">
									</span>
				<div id="_contentdiv_" style="display: none;"><span class="moduleName" id="mail_fldrname">INBOX</span>
<div class="mailClientBg mm_outerborder" id="email_con" name="email_con">
<table width="100%" cellpadding="3" cellspacing="0" border="0" class="small">
	

</table><table cellpadding="0" cellspacing="0" border="0" width="100%" class="cmall mm_mailwrapper">
	</table>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tbody><tr>
	<td><a href="#INBOX" onclick="MailManager.folder_open(\'INBOX\');"><b>INBOX</b></a></td>
</tr>
<tr>
	<td>No se han encontrado correos.</td>
</tr>
</tbody></table>
</div></div>
				<div id="_contentdiv2_" style="display: none;"></div>
				<div id="_settingsdiv_" style="display: block;"><form action="javascript:void(0);" method="POST" style="display:inline;">
<!--span class="dvHeaderText" id="settings_mail_fldrname">Configuración</span-->
<div class="mm_outerborder" id="settings_email_con" name="settings_email_con">
    <input type="hidden" id="action" name="action" value="loginGoogle1">
    <input type="hidden" id="module" value="mymailmanager">
    


    <table width="100%" cellpadding="5" cellspacing="0" border="0" class="table table-striped table-hover" style="clear: both;">
        <tbody><tr>
            <td width="15%">Selecciona Tipo Cuenta</td>
            <td>
                <select id="_mbox_helper" class="form-control" onchange="MailManager.handle_settings_confighelper(this);">
                    <option value="">Elige tipo servidor</option>
                    <option value="gmail">Gmail</option>
                    
                </select>
            </td>
        </tr>
    </tbody></table>
    
    <div id="settings_details" >
        <table width="100%" cellpadding="5" cellspacing="0" border="0" class="table table-striped table-hover" style="clear: both;">
            

            <tr>
                <td width="15%" nowrap="nowrap"><font color="red">*</font>Nombre Usuario</td>
                <td>
                    <input name="_mbox_user" id="_mbox_user" value="" type="text" style="width: 60%" class="form-control" placeholder="tu cuenta de correo">
                </td>
            </tr>

            <tr>
                <td width="15%" nowrap="nowrap"><font color="red">*</font>Contraseña</td>
                <td>
                    <input name="_mbox_pwd" id="_mbox_pwd" value="" type="password" style="width: 60%" class="form-control" placeholder="contraseña cuenta">
                </td>
            </tr>
        </tbody></table>
        

        <table width="100%" cellpadding="5" cellspacing="0" border="0" class="table table-striped table-hover" style="clear: both;">
            <tbody><
            <tr>
                <td width="15%">&nbsp;</td>
                <td colspan="2" class="text-right">
                    <input type="submit" class="btn btn-primary btn-md" value="Guardar" onclick="MailManager.save_settings(this.form);">
                                    </td>
            </tr>
        </tbody></table>
    </div>

</div></form></div>
				
				
				
			</div>
		</div>
	</div>
</div>';

echo $vista;

//echo "si toma los cambios";
//include_once("modules/$currentModule/loginGoogle.php");

?>
