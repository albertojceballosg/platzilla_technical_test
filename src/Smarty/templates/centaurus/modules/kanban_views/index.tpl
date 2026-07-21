{strip}
<style type="text/css">
	.main-box {
		margin-top: 15px;
	}
	.row-graphic {
		display:       -webkit-box;
		display:       -ms-flexbox;
		display:       flex;
		-ms-flex-wrap: wrap;
		flex-wrap:     wrap;
		margin-right:  -15px;
		margin-left:   -15px;
		margin-top:    -2px;
	}
	.justify-content-center {
		-webkit-box-pack: center !important;
		-ms-flex-pack:    center !important;
		justify-content:  center !important
	}
</style>
<script>
{literal}
	function selectApp () {
		if (jQuery ('#codeApp').length > 0 && jQuery ('#codeApp').val () != '') {
			var appSelect = jQuery ('#codeApp').val ();
		} else {
			jQuery ('#codeElement').html ('');
			jQuery ('#codeElement').html ('<option value="">Seleccione ...</option>');
			jQuery ('#viewKanban').html ('');
			jQuery ('#viewKanban').html ('<option value="">Seleccione ...</option>');
			alert ('Seleccione una aplicación');
			return false;
		}

		jQuery ('#codeElement').html ('');
		jQuery ('#codeElement').html ('<option value="">Seleccione ...</option>');
		jQuery ('#viewKanban').html ('');
		jQuery ('#viewKanban').html ('<option value="">Seleccione ...</option>');
		jQuery ('#fieldname').val ('');
		jQuery ('#modulename').val ('');

		new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=paramFieldElementsView&appSelect=' + appSelect,
					onComplete: function (response) {
						var data = JSON.parse (response.responseText);
						jQuery ('#codeElement').html ('');
						var htmlElement = '<option value="">Seleccione ...</option>';
						var selected = '';
						for (var j = 0; j < data.length; j++) {
							htmlElement += '<option value="' + data[ j ].tabid + '" tabname="' + data[ j ].name + '" tablabel="' + data[ j ].tablabel + '"';
							htmlElement += '>' + data[ j ].tablabel + '</option>';
						}
						jQuery ('#codeElement').html (htmlElement);
					}
				}
		);
	}

	function selectModule (element) {
		if (jQuery ('#codeElement').length > 0 && jQuery ('#codeElement').val () != '') {
			var moduleSelect = jQuery ('#codeElement').val ();
			var tab = jQuery ('option:selected', element).attr ('tabname');
			var app = jQuery ('#codeApp').val ();
		} else {
			jQuery ('#viewKanban').html ('');
			jQuery ('#viewKanban').html ('<option value="">Seleccione ...</option>');
			alert ('Seleccione un módulo');
			return false;
		}

		jQuery ('#viewKanban').html ('');
		jQuery ('#viewKanban').html ('<option value="">Seleccione ...</option>');
		jQuery ('#fieldname').val ('');
		jQuery ('#modulename').val ('');

		new Ajax.Request (
				'index.php',
				{
					queue:      { position: 'end', scope: 'command' },
					method:     'post',
					postBody:   'module=Settings&action=SettingsAjax&file=LoadElementsKanban&function=codeElementFieldView&tabid=' + moduleSelect + '&app=' + app,
					onComplete: function (response) {
						var data = JSON.parse (response.responseText);
						jQuery ('#viewKanban').html ('');
						var htmlElement = '<option value="">Seleccione ...</option>';
						if (data != null && data.length > 0) {
							for (var j = 0; j < data.length; j++) {
								htmlElement += '<option value="' + data[ j ].kanbanviewid + '" tabid="' + data[ j ].moduletabid + '" ' + ' " fieldname="' + data[ j ].fieldname + '" ';
								htmlElement += '>' + data[ j ].label + '</option>';
							}
							jQuery ('#viewKanban').html (htmlElement);
							jQuery ('#modulename').val (tab);
						}
					}
				}
		);
	}

	function selectViewKanban (element) {
		if (jQuery ('#viewKanban').length > 0 && jQuery ('#viewKanban').val () != '') {
			var viewSelect = jQuery ('#viewKanban').val ();
			var fieldname = jQuery ('option:selected', element).attr ('fieldname');

			jQuery ('#fieldname').val (fieldname);

			//Submmit search
			callSearch ();

		} else {
			jQuery ('#codeApp').html ('');
			jQuery ('#codeApp').html ('<option value="">Seleccione ...</option>');
			jQuery ('#codeElement').html ('');
			jQuery ('#codeElement').html ('<option value="">Seleccione ...</option>');
			jQuery ('#viewKanban').html ('');
			jQuery ('#viewKanban').html ('<option value="">Seleccione ...</option>');
			jQuery ('#fieldname').val ('');
			jQuery ('#modulename').val ('');
			return false;
		}

	}

	function callSearch () {
		if (validateSearch ()) {
			jQuery ('#parametersDetail').submit ();
		} else {
			return false;
		}

	}

	function validateSearch () {
		if (jQuery ('#codeApp').val () == '') {
			alert ('Seleccione una aplicación de la lista');
			return false;
		}
		if (jQuery ('#codeElement').val () == '') {
			alert ('Seleccione un módulo de la lista');
			return false;
		}
		if (jQuery ('#viewKanban').val () == '') {
			alert ('Seleccione una vista de la lista');
			return false;
		}
		return true;
	}

{/literal}
</script>
<div class="row" style="background-color: white; padding: 11px 7px; margin: 0px 0px 18px">
	<div class="col-lg-12">
		<h1 class="pull-left" style="padding: 0px !important; margin-top: 11px !important">Kanban</h1>
		<form class="row-graphic justify-content-center" name="parametersDetail" id="parametersDetail" method="POST" action="index.php">
			<input type="hidden" name="module" id="module" value="{$MODULE}">
			<input type="hidden" name="action" id="action" value="index">
			<input type="hidden" name="view" id="view" value="{$VIEWID}">
			<div class="col-md-3">
				<div class="form-group">
					<label>Aplicación</label>
					<div class="input-group">
						<div class="input-group-addon">
							<i class="fa fa-th-large"></i>
						</div>
						<select id="codeApp" name="codeApp" class="form-control" onchange="selectApp();">
							<option value="">Seleccione ...</option>
							{foreach $APPLICATIONS as $keyApp => $itemApp}
								{if $keyApp == $CODE_APP}
									{assign var='selected' value='selected="selected"'}
								{else}
									{assign var='selected' value=''}
								{/if}
								<option value="{$keyApp}" {$selected}>{$itemApp.app_name}</option>
							{/foreach}
						</select>
					</div>
				</div>
			</div>
			<div class="col-md-3" style="padding-right:0px;">
				<div class="form-group">
					<label>Módulo</label>
					<div class="input-group">
						<div class="input-group-addon" style="border: 1px solid #ddd !important">
							<i class="fa fa-list-alt"></i>
						</div>
						<select class="form-control" id="codeElement" name="codeElement" title="Modules" onchange="selectModule(this)">
							<option value="">Seleccione ...</option>
                            {foreach $AVAIABLE_MODULES as  $row}
								<option value="{$row.tabid}" {if $row.tabid eq $CODE_ELEMENT}selected="selected" {/if} tabname="{$row.name}" tablabel="{$row.tablabel}">{$row.tablabel}</option>
                            {/foreach}
						</select>
						<input type="hidden" id="modulename" name="modulename" value="{if !empty($MODULENAME)}{$MODULENAME}{else}''{/if}">
						<input type="hidden" id="fieldname" name="fieldname" value="{if !empty($FIELDNAME)}{$FIELDNAME}{else}''{/if}">
					</div>
				</div>
			</div>
			<div class="col-md-3" style="padding-right:0px;">
				<div class="form-group">
					<label>Vista</label>
					<div class="input-group">
						<div class="input-group-addon" style="border: 1px solid #ddd !important">
							<i class="fa fa-th"></i>
						</div>
						<select class="form-control" id="viewKanban" name="viewKanban" title="Vista" onchange="selectViewKanban(this)">
							<option value="">Seleccione ...</option>
                            {foreach $AVAIABLE_KANBAN as  $row}
								<option value="{$row.kanbanviewid}" {if $row.kanbanviewid eq $VIEWID}selected="selected" {/if} fieldname="{$row.fieldname}" tabid="{$row.moduletabid}">{$row.label}</option>
                            {/foreach}
						</select>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="col-lg-12">
		</div>
	</div>
</div>
	{if !empty($VIEWID) }
		{include file="modules/kanban_views/DetailViewKanban.tpl"}
	{/if}
{/strip}