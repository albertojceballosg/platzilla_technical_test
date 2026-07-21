{*
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/ *}
<style type="text/css">
	.table {
		margin-bottom: 0px;
	}
</style>
<div id="email-box" class="clearfix">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
			<tr>
				<td rowspan="2" valign="top">
					<div class="infographic-box" style="width: 30px; padding: 0;">
						<i class="fa fa-cube green-bg"></i>
		</div>
				</td>
				<td class="heading2" valign="bottom">
					<ol class="breadcrumb">
						<li>
							<a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a>
						</li>
						<li class="active">WIDGETS</li>
					</ol>
				</td>
			</tr>
			<tr>
				<td class="small" valign="top"></td>
			</tr>
		</tbody>
	</table>
	{if $MSG_ERROR neq ''}
	<div class="col-lg-12">
		<div class="alert alert-danger fade in">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
			<i class="fa fa-times-circle fa-fw fa-lg"></i>
			<strong>ERROR!</strong> {$MSG_ERROR}.
		</div>
	</div>
	{/if}
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box clearfix">
				<br/>
				{if $IS_ADMIN}
				<div class="pull-right" style="margin-right: 20px;">
					<a class="btn btn-primary" href="index.php?module={$MODULE}&action=crearwidget">
						Nuevo widget
					</a>
				</div>
				{/if}
				<br/>
				<div class="main-box-body clearfix">
				  	<br/>
				  	<div id="appscontents">
						{include file='modules/admin_widgets/widgetsContents.tpl'}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>