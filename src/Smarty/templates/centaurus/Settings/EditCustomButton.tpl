{strip}
	<script type="text/javascript" src="include/js/menu.js"></script>
	<script type="text/javascript" src="modules/Settings/Settings.js"></script>
	<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
	<link rel="stylesheet" href="themes/centaurus/css/custombutton-fa.css" />
		
	<form action="index.php" method="post" id="SaveEditCustomButtons" name="index">
		<input type="hidden" name="module" value="Settings" />
		<input type="hidden" name="action" value="SaveEditCustomButtons" />
		<input type="hidden" id="actionCrud" name="actionCrud" value="Edit" />
		<input type="hidden" id="record" name="record" value="{$CUSTOMBUTTON.custombuttonid}" />
		<div class="row">
			<div class="col-lg-12">
				<div class="col-lg-9 pull-left">
					<h1><a href="index.php?module=Settings&action=CustomButtons&parenttab=Settings">{$MOD.LBL_CUSTOM_BUTTONS_EDIT}
						</a></h1>
				</div>
				<div class="col-lg-3 pull-right text-right">
					<button class="btn btn-primary" type="button" id="btnsave"
						onclick="CBUtils.validateRepeatData();">{$MOD.LBL_SAVE}</button>
					<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=CustomButtons"
						style="margin-left: 5px;">{$MOD.LBL_CANCEL_BUTTON}</a>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="main-box no-header">
					<div class="main-box-body clearfix" id="">
						<div id="cb-dv-title" class="form-group">
							<label for="title" id="label_title">{$MOD.LBL_CUSTOM_BUTTONS_TITLE}</label>&nbsp;<span
								style="color: red;">*</span>
							<input type="text" placeholder="Texto del botón" id="title" name="title" maxlength="30"
								class="form-control" title="Texto del botón" value="{$CUSTOMBUTTON.label}" />
							<span id="cb-title" class="help-block"></span>
						</div>
						<div id="cb-dv-description" class="form-group">
							<label for="description"
								id="label_description">{$MOD.LBL_CUSTOM_BUTTONS_DESCRIPCION}</label>&nbsp;<span
								style="color: red;">*</span>
							<input type="text" placeholder="Descripción" id="description" name="description"
								class="form-control" title="Descripción" value="{$CUSTOMBUTTON.description}" />
							<span id="cb-description" class="help-block"></span>
						</div>
						<div id="cb-dv-modulo" class="form-group">
							<label for="modulo">{$MOD.LBL_CUSTOM_BUTTONS_MODULE}</label>&nbsp;<span
								style="color: red;">*</span>
							<select id="modulo" name="modulo" class="form-control">
								{foreach item=module from=$MODULESFREE}
									<option value="{$module.name}" {if ($module.name == $CUSTOMBUTTON.module)}
										selected="selected" {/if}>{$module.tablabel}</option>
								{/foreach}
							</select>
							<span id="cb-modulo" class="help-block"></span>
						</div>
						<div id="cb-dv-vista" class="form-group">
							<label for="vista">{$MOD.LBL_CUSTOM_BUTTONS_VIEW}</label>&nbsp;<span
								style="color: red;">*</span>
							<select id="vista" name="vista" class="form-control" onclick="CBUtils.getViewsAvailable(this);">
								{foreach item=vista from=$VISTASDISPONIBLES}
									<option value="{$vista.name}" {if $vista.name eq $CUSTOMBUTTON.action}selected="selected"
										{/if}>{$vista.label}</option>
								{/foreach}
							</select>
							<span id="cb-vista" class="help-block"></span>
						</div>



						<div class="form-group condition-groups">
							<label for="filter">Filtros de visibilidad (se mostraran los resultados coincidentes tras
								aplicar el filtro)</label>


							{if isset ($CUSTOMBUTTON.arrayvisibility) && !empty ($CUSTOMBUTTON.arrayvisibility && !empty($FIELD_LIST))}
								{assign var=filters value=$CUSTOMBUTTON.arrayvisibility|json_decode:1}
								{assign var="totalGroup" value=$filters['filterGroupJoin']|@count}
								{assign var="filterField" value=$filters['filterField']}
								{assign var="filterOperator" value=$filters['filterOperator']}
								{assign var="filterValue" value=$filters['filterValue']}
								{assign var="filterJoin" value=$filters['filterJoin']}
								{assign var="filterGroupJoin" value=$filters['filterGroupJoin']}
								{assign var="indexGrupo" value=$filters['indexGrupo']}
								{assign var="totalIndex" value=$filters['indexGrupo']|@count}
								{assign var="star" value=1}
								{assign var="indexJoin" value=-1}
								{if ! empty($filters['filterField'])}
									{include file="Settings/CustomButton/filterEditVisibility.tpl"}
								{else}
									{assign var="totalGroup" value=0}
									{assign var="totalIndex" value=0}
								{/if}
							{else}
								{assign var="totalGroup" value=0}
								{assign var="totalIndex" value=0}
							{/if}


							<div class="action-bar text-center">
								<button type="button" class="btn btn-link" data-group="0"
									onclick="CBUtils.addFilterGroup (this);" title="Agregar grupo de condiciones"><i
										class="fa fa-plus"></i></button>
							</div>
						</div>


						<div class="form-group">
							<label for="type">{$MOD.LBL_CUSTOM_BUTTONS_TYPEBUTTON}</label>&nbsp;<span
								style="color: red;">*</span>
							<select id="type" name="type" class="form-control" onclick="CBUtils.blockTypeButton();">
								{foreach item=tipo from=$TIPOSBOTON}
									<option value="{$tipo.name}" {if $tipo.name eq $CUSTOMBUTTON.type}selected="selected" {/if}>
										{$tipo.label}</option>
								{/foreach}
							</select>
						</div>

						<div id="cb-dv-clickaction" class="form-group">
							<label for="clickaction"
								id="label_clickaction">{$MOD.LBL_CUSTOM_BUTTONS_CLICKACTION}</label>&nbsp;<span
								style="color: red;">*</span>
							<input type="text" placeholder="{$MOD.LBL_CUSTOM_BUTTONS_CLICKACTION}" id="clickaction"
								name="clickaction" class="form-control" title="{$MOD.LBL_CUSTOM_BUTTONS_CLICKACTION}"
								value="{$CUSTOMBUTTON.onclick}" />
							<span id="cb-clickaction" class="help-block"></span>
						</div>
						<div id="cb-dv-linkaction" class="form-group">
							<label for="linkaction" id="label_linkaction">{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}</label>&nbsp;
							<input type="text" id="linkaction" name="linkaction"
								value="{if (empty ($CUSTOMBUTTON.backgroundtaskname))}{$CUSTOMBUTTON.link}{/if}"
								class="form-control" title="{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}"
								placeholder="{$MOD.LBL_CUSTOM_BUTTONS_LINKACTION}" />
							<span id="cb-linkaction" class="help-block"></span>
						</div>


						<div id="cb-dv-backgroundtask" class="form-group">
							<label for="backgroundtaskaction"
								id="label_backgroundtaskaction">{$MOD.LBL_CUSTOM_BUTTONS_BACKGROUNDTASKACTION}</label>&nbsp;
							<select id="backgroundtaskaction" name="backgroundtaskaction" class="form-control"
								title="{$MOD.LBL_CUSTOM_BUTTONS_BACKGROUNDTASKACTION}">
								{if (!empty ($AVAILABLE_BACKGROUND_TASKS))}
									{foreach $AVAILABLE_BACKGROUND_TASKS as $scope => $tasks}
										{if (empty ($tasks))}
											{continue}
										{/if}
										<optgroup label="{$scope}">
											{foreach $tasks as $task}
												{assign var='taskName' value=$task->getName ()}
												<option value="{$taskName}"
													{if (isset ($CUSTOMBUTTON.backgroundtaskname)) && (sha1($taskName) == $CUSTOMBUTTON.backgroundtaskname)}
													selected="selected" {/if}>{$taskName}</option>
											{/foreach}
										</optgroup>
									{/foreach}
								{/if}
							</select>
							<span id="cb-backgroundtask" class="help-block"></span>
						</div>

						<div class="form-group">
							<label for="active">{$MOD.LBL_CUSTOM_BUTTONS_ACTIVE}</label>
							<select id="active" name="active" class="form-control">
								<option value="1" {if $CUSTOMBUTTON.active eq 1 }selected="selected" {/if}>Activa</option>
								<option value="0" {if $CUSTOMBUTTON.active eq 0 }selected="selected" {/if}>Inactiva</option>
							</select>
						</div>
						<div id="cb-dv- " class="form-group">
							<div class="checkbox-nice">
								<input type="checkbox" id="runinnewwindow" name="runinnewwindow" value="1"
									{if ($CUSTOMBUTTON.runinnewwindow == 1)} checked="checked" {/if} />
								<label for="runinnewwindow">{$MOD.LBL_CUSTOM_BUTTONS_RUNINNEWWINDOW}</label>
							</div>
						</div>

						<div class="form-group">

							<div class="row">
								<div class="col-md-2">
									<label for="styleButton">{$MOD.LBL_CUSTOM_BUTTONS_STYLEBUTTON}</label>
									<br />
									<div class="btn-group" data-toggle="buttons">

										<label class="btn btn-primary active"
											style="margin-left:.2em; margin-right:.5em; border-radius: 50rem;"
											title="Primary: botón de estilo Principal">
											<input name="styleButton" id="option1" type="radio" value="Primary"
												{if $CUSTOMBUTTON.style eq 'primary' }checked="checked" {/if} />P
										</label>
										<label class="btn btn-success"
											style="margin-left:.2em; margin-right:.5em; border-radius: 50rem;"
											title="Success: botón de estilo Éxito">
											<input name="styleButton" id="option2" type="radio" value="success"
												{if $CUSTOMBUTTON.style eq 'success' }checked="checked" {/if} />S
										</label>
										<label class="btn btn-info"
											style="margin-left:.2em; margin-right:.5em; border-radius: 50rem;"
											title="Info: botón de estilo Información">
											<input name="styleButton" id="option3" type="radio" value="info"
												{if $CUSTOMBUTTON.style eq 'info' }checked="checked" {/if} />I
										</label>
										<label class="btn btn-warning"
											style="margin-left:.2em; margin-right:.5em; border-radius: 50rem;"
											title="Warning: botón de estilo Advertencia">
											<input name="styleButton" id="option4" type="radio" value="warning"
												{if $CUSTOMBUTTON.style eq 'warning' }checked="checked" {/if} />W
										</label>
										<label class="btn btn-danger"
											style="margin-left:.2em; margin-right:.5em; border-radius: 50rem;"
											title="Danger: botón de estilo Cuidado">
											<input name="styleButton" id="option5" type="radio" value="danger"
												{if $CUSTOMBUTTON.style eq 'danger' }checked="checked" {/if} />D
										</label>


									</div>
									<br />
									<br />

									<div class="row">
										<label for="styleButton">Previsualización del Botón</label>
										<br />

										<a class="btn btn-{$CUSTOMBUTTON.style} btn-circle btn-xs" href="#"
											title="{$CUSTOMBUTTON.description}" id="button-preview"
											title="{$CUSTOMBUTTON.description|default:$CUSTOMBUTTON.label}"
											style="margin-left:.5em; margin-right:.5em; border-radius: 9999px;">
											<span class="fa fa-home"></span>
										</a>
									</div>

									<br />
									<div class="row">
										<label for="styleButton">Botón Actual</label>
										<br />
										<a class="btn btn-{$CUSTOMBUTTON.style} btn-circle btn-xs" href="#"
											title="{$CUSTOMBUTTON.description}"
											title="{$CUSTOMBUTTON.description|default:$CUSTOMBUTTON.label}"
											style="margin-left:.5em; margin-right:.5em; border-radius: 9999px;">
											<span class="fa {$CUSTOMBUTTON.faicon}"></span>
										</a>
									</div>

									<div class="row">
										<br />
										<input type="hidden" id="faIcon" name="faIcon" value="{$CUSTOMBUTTON.faicon}" />
									</div>

								</div>
								<div class="col-md-10">
									<label for="styleButton">Iconos Disponibles</label>
									<br />
									<div id="icon-grid" aria-label="Seleccion de iconos" role="list" tabindex="0">
									</div>
									<div id="icon-preview" aria-live="polite" style="display:none;">
									</div>
								</div>
							</div>
						</div>

					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-lg-12">
				<div class="col-lg-9 pull-left">
				</div>
				<div class="col-lg-3 pull-right text-right">
					<button class="btn btn-primary" type="button" id="btnsave"
						onclick="CBUtils.validateRepeatData();">{$MOD.LBL_SAVE}</button>
					<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=CustomButtons"
						style="margin-left: 5px;">{$MOD.LBL_CANCEL_BUTTON}</a>
				</div>
			</div>
		</div>
	</form>
	<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
	<div class="md-overlay"></div>

	<script type="text/html" id="condition-template">
		{include file="Settings/CustomButton/filterVisibility.tpl"}
	</script>
	<script type="text/html" id="condition-group-template">
		{include file="Settings/CustomButton/filterGroupVisibility.tpl"}
	</script>
	<script type="text/javascript" src="modules/Settings/custombutton.js"></script>
	<script>
		jQuery(document).ready(function() {
			totalFilterGroup = {($totalGroup + 1)};
			totalFilterRow   = {($totalIndex + 1)};
			CBUtils.blockTypeButton();
		});
	</script>
{/strip}