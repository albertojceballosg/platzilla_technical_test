<link rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/daterangepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/bootstrap-timepicker.css" type="text/css" />
<link rel="stylesheet" href="themes/centaurus/css/libs/font-awesome.min.css" type="text/css" />
<style type="text/css">
	.col-actions {
		text-align: center;
		width: 80px;
	}

	.btn.btn-icon {
		font-size: 12px;
		line-height: 1.5;
		padding: 3px 7px;
	}
</style>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script type="text/javascript" src="themes/centaurus/js/moment.min.js"></script>
<script type="text/javascript" src="themes/centaurus/js/daterangepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-timepicker.min.js"></script>
{assign var="MODULELABEL" value=$MODULE|@getTranslatedString:$MODULE}
{if $APP.$SINGLE_MOD}
	{assign var="SINGLE_MOD_LABEL" value=$APP.SINGLE_MOD}
{else}
	{assign var="SINGLE_MOD_LABEL" value=$SINGLE_MOD}
{/if}
<div class="row">
	<div class="col-xs-12">
		<h1 id="title-view">
			<a
				href="index.php?action=ListView&module={$MODULE}&parenttab={$CATEGORY}">{$SINGLE_MOD|@getTranslatedString:$MODULE}</a>
		</h1>
	</div>
</div>
<form name="EditView" method="post" action="index.php" enctype="multipart/form-data"
	onsubmit="VtigerJS_DialogBox.block();" role="form">
	<input type="hidden" name="pagenumber" value="{$smarty.request.start|@vtlib_purify}">
	<input type="hidden" name="module" value="{$MODULE}">
	<input type="hidden" name="record" value="{$ID}">
	<input type="hidden" name="mode" value="{$MODE}">
	<input type="hidden" name="action">
	<input type="hidden" name="parenttab" value="{$CATEGORY}">
	<input type="hidden" name="return_module" value="{$RETURN_MODULE}">
	<input type="hidden" name="return_id" value="{$RETURN_ID}">
	<input type="hidden" name="return_action" value="{$RETURN_ACTION}">
	<input type="hidden" name="return_viewname" value="{$RETURN_VIEWNAME}">
	<input type="hidden" name="createmode" value="{$CREATEMODE}" />
	{if $smarty.request.frontendsid}
		<input type="hidden" name="frontendsid" value="{$smarty.request.frontendsid}" />
	{/if}
	{if $PLATDB neq ''}
		<input type="hidden" name="platdb" value="{$PLATDB}" />
	{/if}
	{foreach key=header item=data from=$BLOCKS name=block}
		<div class="row block-container" id="block_{$smarty.foreach.block.iteration}">
			<div class="col-xs-12">
				<div class="main-box">
					<header class="title-section main-box-header clearfix">
						<h2>{$header}</h2>
					</header>
					<div class="main-box-body clearfix" id="tbl{$header|replace:' ':''}">
						{include file="DisplayFields.tpl"}
					</div>
				</div>
			</div>
		</div>
	{/foreach}
	<div class="row block-container" id="block_2">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="title-section main-box-header">
					<h2 class="col-md-10">Usuarios</h2>
					<button type="button" class="btn btn-default col-md-2 select-users"
						onclick="ReservationUtils.openAvailableUsersDialog ();">Seleccionar usuarios</button>
				</header>
				<div class="main-box-body" id="tblUsuarios">
					<div class="table-responsive">
						<table class="table users">
							<thead>
								<tr>
									<th class="col-code">Código</th>
									<th class="col-name">Nombres y apellidos</th>
									<th class="col-phone">Teléfono</th>
									<th class="col-email">Email</th>
									<th class="col-actions">Acciones</th>
								</tr>
							</thead>
							<tbody>
								{if (isset ($RELATED_USERS))}
									{foreach $RELATED_USERS as $user}
										<tr>
											<td class="col-code">
												<input type="hidden" name="relateduserids[]"
													value="{$user.usuarios_colladitoid}" />
												<span class="code">{$user.cod_usuarios_col}</span>
											</td>
											<td class="col-name">{$user.nombres} {$user.apellidos}</td>
											<td class="col-phone">{$user.telf}</td>
											<td class="col-email">{$user.correo_electronico}</td>
											<td class="col-actions">
												<button type="button" class="btn btn-danger btn-icon" title="Eliminar"
													onclick="ReservationUtils.deleteRow (this);">
													<i class="fa fa-trash-o fa-inverse"></i>
												</button>
											</td>
										</tr>
									{/foreach}
								{/if}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row block-container" id="block_3">
		<div class="col-xs-12">
			<div class="main-box">
				<header class="title-section main-box-header">
					<h2 class="col-md-10">Espacios</h2>
					<button type="button" class="btn btn-default col-md-2"
						onclick="ReservationUtils.openAvailableLocationsDialog ();">Seleccionar espacios</button>
				</header>
				<div class="main-box-body" id="tblEspacios">
					<div class="table-responsive">
						<table class="table locations">
							<thead>
								<tr>
									<th class="col-code">Código</th>
									<th class="col-number">Número</th>
									<th class="col-type">Tipo</th>
									<th class="col-location">Centro</th>
									<th class="col-actions">Acciones</th>
								</tr>
							</thead>
							<tbody>
								{if (isset ($RELATED_LOCATIONS))}
									{foreach $RELATED_LOCATIONS as $location}
										<tr>
											<td class="col-code">
												<input type="hidden" name="relatedlocationids[]"
													value="{$location.espaciosid}" />
												<span class="code">{$location.cod_espacios}</span>
											</td>
											<td class="col-number">{$location.numero_}</td>
											<td class="col-type">{$location.tipo_area}</td>
											<td class="col-location">{$location.centro}</td>
											<td class="col-actions">
												<button type="button" class="btn btn-danger btn-icon" title="Eliminar"
													onclick="ReservationUtils.deleteRow (this);">
													<i class="fa fa-trash-o fa-inverse"></i>
												</button>
											</td>
										</tr>
									{/foreach}
								{/if}
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="clearfix" style="height:25px; margin-bottom: 16px;"></div>
	<div class="row">
		<div id="fixed-btns-bar" style="display:block">
			<div class="container">
				<div class="row">
					<div class="col-xs-12" style="padding:25px;height: 75px;">
						{if $MODE eq 'edit'}
							<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
								class="btn btn-success"
								onclick="this.form.action.value='Save'; if(formValidate()) {ldelim}  validationCheckFields('{$MODULE}',this.form); {rdelim} "
								type="button" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  ">
						{else}
							<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accessKey="{$APP.LBL_SAVE_BUTTON_KEY}"
								class="btn btn-success"
								onclick="this.form.action.value='Save';  if(formValidate()){ldelim} validationCheckFields('{$MODULE}',this.form);{rdelim}"
								type="button" name="button" value="  {$APP.LBL_SAVE_BUTTON_LABEL}  ">
						{/if}
						{if $POPUPCREATE neq 'create'}
							<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
								class="btn btn-default" onclick="window.history.back()" type="button" name="button"
								value="{$APP.LBL_CANCEL_BUTTON_LABEL}  ">
						{else}
							<input title="{$APP.LBL_CANCEL_BUTTON_TITLE}" accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
								class="btn btn-default" onclick="window.close();" type="button" name="button"
								value="  {$APP.LBL_CANCEL_BUTTON_LABEL}  ">
						{/if}
					</div>
				</div>
			</div>
		</div>
	</div>
	<input name='search_url' id="search_url" type='hidden' value='{$SEARCH}'>
</form>
<div id="available-users-dialog" class="modal fade" role="dialog" style="z-index: 10000;">
	<div class="modal-dialog" style="width: 80%;">
		<div class="modal-content">
			<div class="modal-header">
				<button class="close" aria-hidden="true" data-dismiss="modal" type="button">×</button>
				<h4 class="modal-title">Usuarios</h4>
			</div>
			<div class="modal-body">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th class="col-checkbox"><input type="checkbox" class="form-control select-all"
										placeholder="" onclick="ReservationUtils.toggleRecordsSelected (this);" /></th>
								<th class="col-code">Código</th>
								<th class="col-name">Nombres y apellidos</th>
								<th class="col-phone">Teléfono</th>
								<th class="col-email">Email</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary"
					onclick="ReservationUtils.addSelectedUsers ();">Aceptar</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
			</div>
		</div>
	</div>
</div>
<div id="available-locations-dialog" class="modal fade" role="dialog" style="z-index: 10000;">
	<div class="modal-dialog" style="width: 80%;">
		<div class="modal-content">
			<div class="modal-header">
				<button class="close" aria-hidden="true" data-dismiss="modal" type="button">×</button>
				<h4 class="modal-title">Espacios</h4>
			</div>
			<div class="modal-body">
				<div class="table-responsive">
					<table class="table">
						<thead>
							<tr>
								<th class="col-checkbox">
									<input type="checkbox" class="form-control select-all" placeholder=""
										onclick="ReservationUtils.toggleRecordsSelected (this);" />
								</th>
								<th class="col-code">Código</th>
								<th class="col-number">Número</th>
								<th class="col-type">Tipo</th>
								<th class="col-location">Centro</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary"
					onclick="ReservationUtils.addSelectedLocations ();">Aceptar</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
			</div>
		</div>
	</div>
</div>
<script type="text/html" id="available-user-template">
	<tr>
		<td class="col-checkbox">
			<input type="checkbox" value="" class="form-control select" placeholder=""
				onclick="ReservationUtils.toggleAllRecordsSelected (this);" />
		</td>
		<td class="col-code"></td>
		<td class="col-name"></td>
		<td class="col-phone"></td>
		<td class="col-email"></td>
	</tr>
</script>
<script type="text/html" id="available-location-template">
	<tr>
		<td class="col-checkbox">
			<input type="checkbox" value="" class="form-control select" placeholder=""
				onclick="ReservationUtils.toggleAllRecordsSelected (this);" />
		</td>
		<td class="col-code"></td>
		<td class="col-number"></td>
		<td class="col-type"></td>
		<td class="col-location"></td>
	</tr>
</script>
<script type="text/html" id="user-template">
	<tr>
		<td class="col-code">
			<input type="hidden" name="relateduserids[]" value="" />
			<span class="code"></span>
		</td>
		<td class="col-name"></td>
		<td class="col-phone"></td>
		<td class="col-email"></td>
		<td class="col-actions">
			<button type="button" class="btn btn-danger btn-icon" title="Eliminar"
				onclick="ReservationUtils.deleteRow (this);">
				<i class="fa fa-trash-o fa-inverse"></i>
			</button>
		</td>
	</tr>
</script>
<script type="text/html" id="location-template">
	<tr>
		<td class="col-code">
			<input type="hidden" name="relatedlocationids[]" value="" />
			<span class="code"></span>
		</td>
		<td class="col-number"></td>
		<td class="col-type"></td>
		<td class="col-location"></td>
		<td class="col-actions">
			<button type="button" class="btn btn-danger btn-icon" title="Eliminar"
				onclick="ReservationUtils.deleteRow (this);">
				<i class="fa fa-trash-o fa-inverse"></i>
			</button>
		</td>
	</tr>
</script>
<script type="text/javascript" src="modules/Settings/fieldValidationsAjax.js"></script>
<script type="text/javascript" src="modules/reservas/reservas.js"></script>
{if $PICKIST_DEPENDENCY_DATASOURCE != ''}
	<script type="text/javascript" src="include/js/FieldDependencies.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			(new FieldDependencies({$PICKIST_DEPENDENCY_DATASOURCE})).init();
		});
	</script>
{/if}
<script type="text/javascript">
	{literal}
		var fieldname = [{/literal}{$VALIDATION_DATA_FIELDNAME}{literal}];
		var fieldlabel = [{/literal}{$VALIDATION_DATA_FIELDLABEL}{literal}];
		var fielddatatype = [{/literal}{$VALIDATION_DATA_FIELDDATATYPE}{literal}];
		var ProductImages = [];
		var count = 0;

		function delRowEmt(imagename) {
			ProductImages[count++] = imagename;
		}

		function displaydeleted() {
			var imagelists = '';
			for (var x = 0; x < ProductImages.length; x++) {
				imagelists += ProductImages[x] + '###';
			}

			if (imagelists != '') {
				document.EditView.imagelist.value = imagelists;
			}
		}

		function openPopup() {
			window.open("index.php?module=Users&action=UsersAjax&file=RolePopup&parenttab=Settings", "roles_popup_window",
				"height=425,width=640,toolbar=no,menubar=no,dependent=yes,resizable =no");
		}
	{/literal}
</script>
<script type="text/javascript">
	{literal}
		jQuery(document).ready(function() {
			jQuery('.modal').on('shown.bs.modal', function(e) {
				jQuery('#page-wrapper').css('opacity', 1);
			});
		});
	{/literal}
</script>