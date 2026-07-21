{*<!--
/*********************************************************************************
** The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
*
 ********************************************************************************/
-->*}

<div class="col-lg-12">
	<div class="row">
		<div class="col-lg-12">
			<h1><a href="index.php?module=Settings&action=HelpSettingsListView">{$MOD.LBL_CONFIG_HELP}</a>
		</div>
	</div>
</div>


<!-- botones -->
<div class="row">
	<div class="col-lg-12">
	<div class="main-box no-header clearfix">
			<div class="main-box-body clearfix pull-right">
				<form action="index.php" method="post" name="index" id="form" onsubmit="VtigerJS_DialogBox.block();">
					<input type="hidden" name="module" value="Settings">
					<input type="hidden" name="action" value="index">
					<input type="hidden" name="record" value="{$ID}">
					<input type="submit" class="btn btn-primary btn-sm" value="Editar" onclick="this.form.action.value='HelpSettingsEditView'; this.form.record.value='{$ID}'">
					<a href="index.php?module=Settings&action=HelpSettingsListView&parenttab=Settings" class="btn btn-warning btn-sm">Volver</a>
				</form>

			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix"></header>
			<div class="main-box-body clearfix">
				<div class="row">
					<div class="col-md-4">
						<div class="form-group">
							<label>{$MOD.LBL_APP}</label>
							<span class="form-control" readonly="">{$AYUDAINFO.app_name}</span>
						</div>
					</div>
				</div>
				<div class="panel-group accordion" id="accordion">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">{$MOD.LBL_ACCORDION_ONE}</a>
							</h4>
						</div>
						<div id="collapseOne" class="panel-collapse collapse in">
							<div class="panel-body">
								{if $AYUDAINFO.plat_tips neq ''}
								<textarea class="form-control" readonly="" style="resize:none;">{$AYUDAINFO.plat_tips}</textarea>
								<br>
								{if $TIPS|@count > 0}
								<div>
									<h5><strong>{$MOD.LBL_SUGERENCIAS}</strong></h5>
									<table class="table table-bordered table-striped table-hover">
										<tr>
											<th>{$MOD.LBL_TITLE}</th>
											<th>{$MOD.LBL_DESCRIPTION}</th>
										</tr>
										{foreach item=tip from=$TIPS}
										<tr>
											<td>
												<input type="text" class="form-control" value="{$tip.titulo}" readonly="" />
											</td>
											<td>
												<textarea class="form-control" style="resize:none;" readonly="">{$tip.descripcion}</textarea>
											</td>
										</tr>
										{/foreach}
									</table>
								</div>
								{/if}
								{/if}
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse2">{$MOD.LBL_ACCORDION_TWO}</a>
							</h4>
						</div>
						<div id="collapse2" class="panel-collapse collapse in">
							<div class="panel-body">
								{if $AYUDAINFO.tutoriales neq ''}
								<textarea class="form-control" readonly="" style="resize:none;">{$AYUDAINFO.tutoriales}</textarea>
								<br>
								<div>
								{if $TUTORIAS_VIDEOS|@count > 0}
									<h5><strong>{$MOD.LBL_VIDEO}</strong></h5>
									<table class="table table-bordered table-striped table-hover">
										<tr>
											<th>{$MOD.LBL_NAME}</th>
											<th>{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}</th>
											<th>Vista Previa</th>
										</tr>
										{foreach item=video from=$TUTORIAS_VIDEOS}
										<tr>
											<td>
												<input type="text" class="form-control" readonly="" value="{$video.nombre}" />
											</td>
											<td>
												<a href="{$video.enlace}"><span>{$video.enlace}</span></a>
											</td>
											<td>
											<iframe src="{$video.urlIframe}" frameborder="0" width="235px"></iframe>
											</td>
										</tr>
										{/foreach}
									</table>
								{/if}
								{if $TUTORIAS_ARTS|@count > 0}
									<br>
									<h5><strong>{$MOD.LBL_ARTICULOS}</strong></h5>
									<table class="table table-bordered table-striped table-hover">
										<tr>
											<th>{$MOD.LBL_NAME}</th>
											<th>{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}</th>
										</tr>
										{foreach item=articulo from=$TUTORIAS_ARTS}
										<tr>
											<td>
												<input type="text" class="form-control" readonly="" value="{$articulo.nombre}" />
											</td>
											<td>
												<a href="{$articulo.enlace}"><span>{$articulo.enlace}</span></a>
											</td>
										</tr>
										{/foreach}
									</table>
								{/if}
								</div>
								{/if}
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse3">{$MOD.LBL_ACCORDION_THREE}</a>
							</h4>
						</div>
						<div id="collapse3" class="panel-collapse collapse in">
							<div class="panel-body">
								{if $AYUDAINFO.preguntas_frecuentes neq ''}
								<textarea class="form-control" readonly="" style="resize:none;">{$AYUDAINFO.preguntas_frecuentes}</textarea>
								<br>
								{if $QUESTIONS|@count > 0}
								<div>
									<h5><strong>{$MOD.LBL_PREGUNTAS}</strong></h5>
									<table class="table table-bordered table-striped table-hover" id="tableTips">
										<tr>
											<th>{$MOD.LBL_TITLE}</th>
											<th>{$MOD.LBL_DESCRIPTION}</th>
										</tr>
										{foreach item=tip from=$QUESTIONS}
										<tr>
											<td>
												<input type="text" class="form-control" value="{$tip.titulo}" readonly="" />
											</td>
											<td>
												<textarea class="form-control" style="resize:none;" readonly="">{$tip.descripcion}</textarea>
											</td>
										</tr>
										{/foreach}
									</table>
								</div>
								{/if}

								{/if}
							</div>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								<a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion" href="#collapse4">{$MOD.LBL_ACCORDION_FOUR}</a>
							</h4>
						</div>
						<div id="collapse4" class="panel-collapse collapse in">
							<div class="panel-body">
								{if $AYUDAINFO.mas_info neq ''}
								<textarea class="form-control" readonly="" style="resize:none;">{$AYUDAINFO.mas_info}</textarea>
								<br>
								<input type="text" class="form-control" value="{$AYUDAINFO.mas_info_enlace}" readonly="" />
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>