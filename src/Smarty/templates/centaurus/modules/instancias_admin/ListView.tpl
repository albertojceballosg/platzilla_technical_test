{strip}
<style type="text/css">
	.btn.btn-icon {
		font-size:   14px;
		height:      27px;
		line-height: 27px;
		margin:      0 5px 0 0;
		padding:     0;
		text-align:  center;
		width:       27px;
	}
	.required {
		color: #FF0000;
	}
	#change-users-modal {
		top: 0;
	}
	#change-users-modal .modal-dialog {
		margin:    0 auto;
		top:       50%;
		transform: translate(0, -50%);
	}
</style>
<div id="email-box" class="clearfix" style="padding-bottom: 20px;">
	<table class="table" width="100%" cellspacing="0" cellpadding="5" border="0">
		<tbody>
		<tr>
			<td rowspan="2" valign="top">
				<div class="infographic-box" style="width: 30px; padding: 0;"><i class="fa fa-cogs purple-bg"></i></div>
			</td>
			<td class="heading2" valign="bottom">
				<ol class="breadcrumb">
					<li><a href="index.php?module=Settings&amp;action=index&amp;parenttab=Settings">CONFIGURACIÓN</a></li>
					<li class="active">{$MOD.LBL_INSTANCIAS_ADMIN|upper}</li>
				</ol>
			</td>
		</tr>
		<tr>
			<td class="small" valign="top">{$MOD.LBL_INSTANCIAS_ADMIN_DESCRIPCION}</td>
		</tr>
		</tbody>
	</table>
{if (!empty ($MESSAGE))}
	<div class="alert alert-{if ($IS_ERROR)}danger{else}success{/if} fade in">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
		<i class="fa fa-times-circle fa-fw fa-lg"></i>
		<strong>{if ($IS_ERROR)}Error!{else}Listo!{/if}</strong> {$MESSAGE}
	</div>
{/if}
	<div class="tabs-wrapper">
		<ul class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#tab-assigned">Asignadas <span class="badge">{$INSTANCIAS['totalregistros']}</span></a></li>
		</ul>
		<div class="tab-content">
			<div id="tab-assigned" class="tab-pane fade in active" style="padding-top: 20px;">
{include file='modules/instancias_admin/ListViewContent.tpl'}
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="change-users-modal" tabindex="-1" role="dialog" aria-hidden="false">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<form method="post" action="index.php" class="form" onsubmit="return InstancesUtils.validateForm (this);">
				<input type="hidden" name="module" value="{$MODULE}" />
				<input type="hidden" name="action" value="SaveInstance" />
				<input type="hidden" name="instancecode" value="" />
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title">Instancia <span id="instance-code"></span></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-6">
							<div class="label-input">
								<label for="total-users">Total usuarios: <span class="required">*</span></label>
							</div>
						</div>
						<div class="form-group col-md-4 field-container">
							<div class="input-group" style="width: 100%;">
								<input type="number" id="total-users" name="totalusers" class="form-control" min="1" />
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Cambiar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script type="text/javascript">
	function getListViewInstancias (page) {
		var search_text = jQuery ('#search_text').val ();
		var param = 'paginadorInstancia=' + page + '&search_text=' + search_text;
		new Ajax.Request (
			'index.php',
			{
				queue: {
					position: 'end',
					scope: 'command'
				},
				method:       'post',
				postBody:     'action={$MODULE}Ajax&module={$MODULE}&file=index&ajax=true&' + param,
				onComplete:   function (response) {
					jQuery ('#instanciascontent').html (response.responseText);
				}
			}
		);
	}
(function (jQuery) {
	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('input[name="instancecode"]');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona la instancia');
			field.focus ();
			return false;
		}
		field = form.find ('#total-users');
		value = field.val ();
		if ((value === undefined) || (value === null) || (!value)) {
			alert ('Introduce el total de usuarios');
			field.focus ();
			return false;
		}
		return true;
	};

	var openModal = function (buttonElement) {
		var button = jQuery (buttonElement),
			instanceCode = button.attr ('data-instance-code'),
			totalUsers = button.attr ('data-instance-total-users'),
			modal;

		modal = jQuery ('#change-users-modal');
		modal.find ('input[name="instancecode"]').val (instanceCode);
		modal.find ('#instance-code').text (instanceCode);
		modal.find ('#total-users').val (totalUsers);
		modal.modal ({ backdrop: 'static' });
	};

	window.InstancesUtils = {
		openModal: openModal,
		validateForm: validateForm
	};
} (jQuery));
</script>
{/strip}