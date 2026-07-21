<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ************************************************************************************/
/*[ TT11172 ] Ajustes Exportar PDF Factura - Platzilla
 * DM
 * 30/06/2016
 * Action para configurar las plantillas disponibles para reporte en los módulos.
 * Funciona como el MailManager (con botones que te lleven a un listado de reportes con 
 * su módulo asociado y otro botón que te muestre las plantillas disponibles)
 */

global $adb,$current_user, $mod_strings;
if (strstr(getcwd(),"reportmanager")) chdir('../../');
require_once('include/utils/utils.php');
require_once('modules/reportmanager/reportmanager.php');

define('DEFAULT_MODULE_FOLDER', 'modules/reportmanager/');

if (isset($_REQUEST['page']) and ($_REQUEST['page'])) $PAGEACTUAL = (int)$_REQUEST['page']; else $PAGEACTUAL = 1;

if ((isset($_GET['iddelete'])) and ($_GET['iddelete'])) {
	deleteTemplate($_GET['iddelete']);
}

$templates=array();
$templates = getTemplateList($PAGEACTUAL);

$serverPath=$_SERVER['SERVER_NAME'].str_replace("index.php","",$_SERVER['PHP_SELF']);
	
?>


<div class="row">
	<div class="col-lg-12">
		<h1><a href="index.php?module=reportmanager&action=index&parenttab=Settings">
				<?php echo getTranslatedString('ModuleName')." ".getTranslatedString('LBL_PLAT_REPORTMANAGER_TEMPLATE_TITLE'); ?>
		</a></h1>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
			
				<div class="filter-block pull-right">
					<div class="filter-block pull-right">
						<a href="index.php?module=reportmanager&action=EditViewTemplate&parenttab=Settings&page=<?php echo $PAGEACTUAL;?>" id="" class="btn btn-primary pull-right">
							<i class="fa fa-plus-circle fa-lg" title="<?php echo getTranslatedString('LBL_PLAT_REPORTLMANAGER_TEMPLATE_NEW');?>"></i>
							<?php echo getTranslatedString('LBL_PLAT_REPORTLMANAGER_TEMPLATE_NEW');?>
						</a>
					</div>
				</div>
			</header>
			
			<div class="main-box-body clearfix" id="ListViewContents">
				<div class="table-responsive">
					<table class="table table-striped table-hover">
						<thead>
							<tr>
								<th align="center" width="5%"><b>#</b></th>
								<th align="center" width="25%"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_CODE');?></b></th>
								<th align="center" width="30%"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_NAMEREPORT');?></b></th>
								<th align="center" width="10%"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_INVENTARY');?></b></th>
								<th align="center" width="20%"><b><?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS');?></b></th>								
							</tr>
						</thead>
						<tbody>
							<?php foreach ($templates as $template):?>
								<tr bgcolor="white" class="lvtColData">
										<td align="left" ><?php echo $template['templateid'];?></td>
										<td align="left"><?php echo $template['code'];?></td>										
										<td align="left"><?php echo $template['name'];?></td>
										<td align="center"><?php if($template['has_inventory'] == '1'){echo ' x ';}else{ echo '  ';}?>
										<td align="center">
											<a class="table-link" href="#" 
											onclick="location.href='index.php?module=reportmanager&action=reportmanagerAjax&file=View&idview=<?php echo $template['code'];?>&page=<?php echo $PAGEACTUAL;?>'" >
												<span class="fa-stack" title='<?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS_VIEW');?>'>
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-eye fa-stack-1x fa-inverse"></i>
												</span>
											</a>
											<!--a class="table-link" href='index?module=reportmanager&action=generateReport&parenttab=Settings'>
												<span class="fa-stack" title='<?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS_VIEW');?>'>
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-eye fa-stack-1x fa-inverse"></i>
												</span>
											</a-->											
											<a class="table-link" href='?module=reportmanager&action=EditViewTemplate&parenttab=Settings&idedit=<?php echo $template['templateid'];?>&page=<?php echo $PAGEACTUAL;?>'>
												<span class="fa-stack" title='<?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS_EDIT');?>'>
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-pencil fa-stack-1x fa-inverse"></i>
												</span>
											</a>
											<a class="table-link" href='?module=reportmanager&action=EditViewTemplate&parenttab=Settings&idduplicate=<?php echo $template['templateid'];?>&page=<?php echo $PAGEACTUAL;?>'>
												<span class="fa-stack" title='<?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS_DUPLICATE');?>'>
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-copy fa-stack-1x fa-inverse"></i>
												</span>
											</a>								
											<?php if (!$template['eventid'])  { ?><a class="table-link danger" href='?module=reportmanager&action=listTemplate&parenttab=Settings&iddelete=<?php echo $template['templateid'];?>&page=<?php echo $PAGEACTUAL;?>'  onclick='return confirm("<?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS_DELETE_WARNING');?>")'>
												<span class="fa-stack" title='<?php echo getTranslatedString('LBL_PLAT_REPORTMANAGER_ACTIONS_REMOVE');?>'>
													<i class="fa fa-square fa-stack-2x"></i>
													<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
												</span>
											</a><?php } else echo '&nbsp;'  ?>
										</td>
								</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
				<?php
				require('paginador.php');
				?>
			</div>
		</div>
	</div>
</div>

