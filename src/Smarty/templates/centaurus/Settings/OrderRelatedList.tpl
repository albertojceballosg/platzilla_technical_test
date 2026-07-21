{strip}
<style type="text/css">
{literal}
	.md-content > .nav {
		border-bottom: 1px solid #E7EBEE;
		margin-left:   10px;
	}
	.md-content > .nav > .nav-item {
		display: inline-block;
	}
	.md-content > .nav > .nav-item.active > a,
	.md-content > .nav > .nav-item.active > a:hover,
	.md-content > .nav > .nav-item.active > a:focus {
		background-color: #3498DB;
	}
	.md-content > .tab-content {
		padding: 10px;
	}
	.md-content > .tab-content > .tab-pane .action-bar {
		border-top:  1px solid #E7EBEE;
		padding-top: 5px;
		text-align:  right;
	}
	.btn-icon {
		display: inline-block;
		height:           27px;
		line-height:      1em;
		margin:           0 0 0 5px;
		padding:          0;
		vertical-align:   top;
		width:            27px;
	}
{/literal}
</style>
<script type="text/javascript">
(function (jQuery) {
	var addRelatedList = function (link) {
		var thiz             = jQuery (link),
			templateContents = jQuery ('#related-list-template').html (),
			table            = thiz.closest ('table').find ('tbody'),
			row;
		if (!templateContents) {
			return;
		}
		row = jQuery (templateContents);
		table.append (row);
	};

	var closeModal = function () {
		jQuery ('#relatedlistdiv').removeClass ('md-show');
		jQuery ('.md-overlay').css ({
			opacity: 0.0,
			visibility: 'hidden'
		});
	};

	var isCreateListsFormValid = function (form) {
		var fields, row, field, value, i, n, labels, names, viewColumns, viewColumn;

		fields = form.find ('.related-name');
		if (fields.length === 0) {
			alert ('Introduce al menos una lista para relacionar');
			return false;
		}

		labels = [];
		names = [];
		n = fields.length;
		for (i = 0; i < n; i += 1) {
			row = jQuery (fields [ i ]).closest ('tr');

			field = row.find ('.related-label');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce la etiqueta del campo');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), labels) !== -1) {
				alert ('Introduce una etiqueta de campo única');
				field.focus ();
				return false;
			}
			labels.push (value.toLowerCase ());

			field = jQuery (fields [ i ]);
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Selecciona el módulo relacionado');
				field.focus ();
				return false;
			} else if (jQuery.inArray (value.toLowerCase (), names) !== -1) {
				alert ('Selecciona un módulo relacionado único');
				field.focus ();
				return false;
			}
			names.push (value.toLowerCase ());

			if ((!row.find ('.related-action-add').is (':checked')) && (!row.find ('.related-action-select').is (':checked')) && (!row.find ('.related-action-pattern').is (':checked'))) {
				alert ('Selecciona al menos una acción');
				row.find ('.related-action-add').focus ();
				return false;
			}
		}
		return true;
	};

	var changeListOrder = function (what_to_do, tabid, sequence, id, module) {
		jQuery.ajax ('index.php', {
			data: 'module=Settings&action=SettingsAjax&file=LayoutBlockList&sub_mode=changeRelatedInfoOrder&sequence=' + encodeURIComponent (sequence) + '&fld_module=' + encodeURIComponent (module) + '&parenttab=Settings&what_to_do=' + encodeURIComponent (what_to_do) + '&tabid=' + tabid + '&id=' + encodeURIComponent (id) + '&ajax=true',
			dataType: 'text',
			method: 'post'
		}).done (function (response) {
			var div = jQuery ('#relatedlistdiv'),
				tabs;
			div.html (response);
			tabs = div.find ('.nav-item');
			if (tabs.length === 0) {
				return;
			}
			jQuery (tabs [0]).removeClass ('active');
			jQuery (tabs [1]).addClass ('active');
			tabs = div.find ('.tab-pane');
			if (tabs.length === 0) {
				return;
			}
			jQuery (tabs [ 0 ]).removeClass ('active');
			jQuery (tabs [ 1 ]).addClass ('active');
		}).fail (function (jQueryResponse) {

		});
	};

	var createLists = function () {
		var form = jQuery ('form[name="create-related-lists"]');
		if (!isCreateListsFormValid (form)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'text',
			method:   form.attr ('method')
		}).done (function (response) {
			if (response === 'relatedlist_duplicate') {
				alert ('Ya existe una lista relacional hacia el mismo módulo o con la misma etiqueta');
			} else {
				jQuery ('#relatedlistdiv').removeClass ('md-show');
				alert ('La lista relacional fue añadida correctamente');
				window.location.reload ();
			}
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error. Intenta más tarde');
			console.log (jQueryResponse);
		});
	};

	var deleteList = function (button, moduleId, relatedModuleId, relatedModuleLabel) {
		var arguments;
		if ((!moduleId) || (!relatedModuleId)) {
			return;
		}

		arguments = [
			'action=ListasRelacionadas',
			'Ajax=true',
			'deleteRelatedlist=1',
			'module=Settings',
			'labelrel=' + encodeURIComponent (relatedModuleLabel),
			'related_tabid=' + encodeURIComponent (relatedModuleId),
			'tabid=' + encodeURIComponent (moduleId)
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'text',
			method:   'post'
		}).done (function (response) {
			if (response === 'relatedlist_recordsfound') {
				alert ('Imposible eliminar la lista: existen registros relacionados');
			} else {
				alert ('La lista relacional ha sido eliminada');
				jQuery (button).closest ('tr').remove ();
			}
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error. Intenta más tarde');
			console.log (jQueryResponse);
		});
	};

	window.RelatedListsUtils = {
		addRelatedList: addRelatedList,
		changeListOrder: changeListOrder,
		closeModal: closeModal,
		createLists: createLists,
		deleteList: deleteList
	};
} (jQuery));
</script>
<div class="md-content">
	<ul class="nav nav-pills">
	<li role="presentation" class="nav-item active">
		<a href="#new-lists" role="tab" data-toggle="tab">Agregar listas relacionada</a>
	</li>
{if (count ($RELATEDLIST) > 0)}
	<li role="presentation" class="nav-item">
		<a href="#existing-lists" role="tab" data-toggle="tab">Listas relacionadas existentes <span class="badge">{$RELATEDLIST|@count}</span></a>
	</li>
{/if}
	</ul>
	<div class="tab-content clearfix">
		<div role="tabpanel" class="tab-pane active" id="new-lists">
			<form method="post" action="index.php" onsubmit="RelatedListsUtils.createLists (); return false;" name="create-related-lists">
				<input type="hidden" name="module" value="Settings" />
				<input type="hidden" name="fld_module" value="{$_FLD_MODULE}" />
				<input type="hidden" name="action" id="action" value="ListasRelacionadas" />
				<input type="hidden" name="Ajax" value="true" />
				<div class="table-responsive col-xs-12">
					<table class="table">
						<thead>
						<tr>
							<th class="lvtCol" width="30%">{$MOD.LBL_LABEL}</th>
							<th class="lvtCol" width="30%">{$MOD.LBL_MODULE}</th>
							<th class="lvtCol" width="25%">{$MOD.LBL_ACTIONS}</th>
							<th class="lvtCol" width="15%">
								<button type="button" class="btn btn-primary pull-right" onclick="RelatedListsUtils.addRelatedList (this);">
									<i class="fa fa-plus-circle fa-lg"></i> {$MOD.LBL_ADD_LISTA}
								</button>
							</th>
						</tr>
						</thead>
						<tbody>
{include file='Settings/ModuleManager/WizardStep4RelatedLists.tpl' SELECTED_LABEL=null SELECTED_MODULE=null SELECTED_INSERT=null SELECTED_SELECT=null SELECTED_PATTERN=null}
						</tbody>
					</table>
				</div>
				<div class="action-bar col-xs-12">
					<button type="submit" class="btn btn-primary">{$APP.LBL_SAVE_BUTTON_LABEL}</button>
					&nbsp;
					<button type="button" class="btn btn-danger md-close" id="btnclose" onclick="RelatedListsUtils.closeModal ();">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
				</div>
			</form>
		</div>
{if (count ($RELATEDLIST) > 0)}
		<div role="tabpanel" class="tab-pane" id="existing-lists">
			<div class="table-responsive col-xs-12">
				<table class="table">
	{foreach $RELATEDLIST as $related}
					<tr>
						<td>{$related.label}</td>
						<td align="right" valign="middle" width="120">
		{if ($related@first)}
							<span class="btn-icon"></span>
		{else}
							<button type="button" class="btn btn-primary btn-icon" onclick="RelatedListsUtils.changeListOrder ('move_up','{$related.tabid}','{$related.sequence}','{$related.id}','{$MODULE}');" title="{$MOD.UP}">
								<i class="fa fa-arrow-up"></i>
							</button>
		{/if}
		{if ($related@last)}
							<span class="btn-icon"></span>
		{else}
							<button type="button" class="btn btn-primary btn-icon" onclick="RelatedListsUtils.changeListOrder ('move_down','{$related.tabid}','{$related.sequence}','{$related.id}','{$MODULE}');" title="{$MOD.DOWN}">
								<i class="fa fa-arrow-down"></i>
							</button>
		{/if}
							<button class="btn btn-danger btn-icon" type="button" onclick="RelatedListsUtils.deleteList (this, '{$related.tabid}', '{$related.related_tabid}', '{$related.label}')" title="{$MOD.DELETE}">
								<i class="fa fa-trash-o"></i>
							</button>
						</td>
					</tr>
	{/foreach}
				</table>
			</div>
			<div class="action-bar col-xs-12">
				<button type="button" class="btn btn-danger md-close" id="btnclose" onclick="RelatedListsUtils.closeModal ();">{$APP.LBL_CANCEL_BUTTON_LABEL}</button>
			</div>
		</div>
{/if}
	</div>
</div>
<script type="text/html" id="related-list-template">
{include file='Settings/ModuleManager/WizardStep4RelatedLists.tpl' SELECTED_LABEL=null SELECTED_MODULE=null SELECTED_INSERT=null SELECTED_SELECT=null SELECTED_PATTERN=null}
</script>
{/strip}