<script type="text/javascript" src="include/jquery/jquery.tablednd.js"></script>
<script type="text/javascript">
{literal}
	function vtlib_modulemanager_toggleTab (shownode, hidenode, highlighttab, dehighlighttab) {
		if ($ (shownode)) {
			$ (shownode).show ();
		}
		if ($ (hidenode)) {
			$ (hidenode).hide ();
		}
		if ($ (highlighttab)) {
			$ (highlighttab).addClassName ('dvtSelectedCell');
			$ (highlighttab).removeClassName ('dvtUnSelectedCell');
		}
		if ($ (dehighlighttab)) {
			$ (dehighlighttab).addClassName ('dvtUnSelectedCell');
			$ (dehighlighttab).removeClassName ('dvtSelectedCell');
		}
	}

	function hacerCombinable (estado, module) {
		new Ajax.Request ('index.php', {
			method:     'post',
			postBody:   'module=Settings&action=ActivityAjax&modulerel=' + module + '&estado=' + estado + '&funcion=hacerCombinable&Ajax=true',
			onComplete: function (response) {
			}
		});
	}

	function filterByApplication (select) {
		var thiz = jQuery (select),
			moduleNames, i, n,
			tab = jQuery ('#tab-custom'),
			applications = {/literal}{$APPLICATIONS_MODULE_NAMES|@json_encode}{literal};

		if (thiz.val ().trim () === '') {
			tab.find ('tr.module').show ();
		} else if (thiz.val () === '-1') {
			tab.find ('tr.module').show ();
			for (var applicationId in applications) {
				if (!applications.hasOwnProperty (applicationId)) {
					continue;
				}
				moduleNames = applications [ applicationId ][ 'modulenames' ];
				n = moduleNames.length;
				for (i = 0; i < n; i += 1) {
					tab.find ('#row-' + moduleNames [ i ]).hide ();
				}
			}
		} else if (!applications [ thiz.val () ][ 'modulenames' ]) {
			tab.find ('tr.module').hide ();
		} else {
			tab.find ('tr.module').hide ();
			moduleNames = applications [ thiz.val () ]['modulenames'];
			n = moduleNames.length;
			for (i = 0; i < n; i += 1) {
				tab.find ('#row-' + moduleNames [i]).show ();
			}
		}
	}
{/literal}
</script>
{if ($DIR_NOTWRITABLE_LIST) && (!empty ($DIR_NOTWRITABLE_LIST))}
<table class="small" width="100%" cellpadding=0 cellspacing=0 border=0>
	<tr>
		<td>
			<div style='background-color: #FFFABF; padding: 2px; margin: 0 0 2px 0; border: 1px solid yellow'>
				<b style='color: red'>{$MOD.VTLIB_LBL_WARNING}:</b> {$DIR_NOTWRITABLE_LIST|@implode:', '}
				<b>{$MOD.VTLIB_LBL_NOT_WRITEABLE}!</b>
			</div>
		</td>
	</tr>
</table>
{/if}
<div class="row">
	<div class="col-lg-12 col-md-12">
		<button class="md-trigger btn btn-primary mrg-b-lg" data-modal="{$ID_DLG_CREACION_MODULOS}">{$APP.LBL_CREATE_MODULE}</button>
		<a class="md-trigger btn btn-primary mrg-b-lg" href="index.php?module=Settings&action=ModuleDuplicator">{$APP.LBL_DUPLICATE_MODULE}</a>
	</div>
</div>
<div class="main-box-body clearfix" style="width:100%">
	<div class="tabs-wrapper">
		<ul class="nav nav-tabs">
			<li class="active"><a href="#tab-custom" data-toggle="tab">{$MOD.VTLIB_LBL_MODULE_MANAGER_CUSTOMMOD}</a></li>
			<li><a href="#tab-platzilla" data-toggle="tab">{$MOD.VTLIB_LBL_MODULE_MANAGER_PLATZILLA}</a></li>
		</ul>
		<div class="tab-content">
			<div id="leyenda" style='padding:15px'>
				<i class="fa fa-check-circle fa-fw fa-lg green"></i> Módulo Activo &nbsp; &nbsp; &nbsp;
				<i class="fa fa-times-circle fa-fw fa-lg red"></i> Módulo Inactivo &nbsp; &nbsp; &nbsp;
				<i class="fa fa-gears fa-fw fa-lg emerald"></i> Configuración &nbsp; &nbsp; &nbsp;
				<i class="fa fa-trash-o fa-fw fa-lg red"></i> Eliminar &nbsp; &nbsp; &nbsp;
			</div>
			<div class="tab-pane fade in active" id="tab-custom">
				<table class="table">
{include file="Settings/ModuleManager/ModuleManagerAjaxPersonalizados.tpl"}
				</table>
			</div>
			<div class="tab-pane fade" id="tab-platzilla">
				<table class="table">
{include file="Settings/ModuleManager/ModuleManagerAjaxPlatzilla.tpl"}
				</table>
			</div>
		</div>
	</div>
</div>
{$DLG_CREACION_MODULOS}
