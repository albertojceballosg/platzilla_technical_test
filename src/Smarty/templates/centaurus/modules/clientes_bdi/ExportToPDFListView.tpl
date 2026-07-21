{strip}
<link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css" />
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<style type="text/css">
{literal}
	.col-checkbox {
		width: 10px;
	}
	.col-checkbox input[type="checkbox"] {
		height:     auto;
		margin-top: 0;
	}
	.col-ordering {
		width: 80px;
	}
	.col-media {
		width: 20%;
	}
	.col-creation-date {
		width: 100px;
	}
	.col-repercussion-date {
		width: 100px;
	}
	.col-area {
		width: 100px;
	}
	select.ordering {
		text-align: right;
	}
	.options input[type="checkbox"] {
		display:        inline-block;
		margin:         0 1em 0 0;
		vertical-align: middle;
		width:          auto;
	}
	.required {
		color: #FF0000;
	}
{/literal}
</style>
<div class="row module-buttons">
	<div class="col-lg-12">
		<div class="col-lg-12 pull-left" style="float:left;">
			<h1><a href="index.php?action=ListView&amp;module={$CURRENT_MODULE}">{$MOD[$CURRENT_MODULE]} - Informe de repercusiones</a></h1>
		</div>
	</div>
	<div class="col-lg-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2>Buscar repercusiones para el cliente {$CUSTOMER.nombre_de_la_entidad}</h2>
			</header>
			<div class="main-box-body clearfix">
				<form method="get" action="index.php" name="search">
					<input type="hidden" name="module" value="{$CURRENT_MODULE}" />
					<input type="hidden" name="action" value="ExportToPDFListView" />
					<input type="hidden" name="record" value="{$RECORD_ID}" />
					<div class="row">
						<div class="col-md-4">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="date-field">Informe por: <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<select id="date-field" name="datefield" class="form-control">
											<option value="1"{if ($DATE_FIELD == '1')} selected="selected"{/if}>Fecha repercusión</option>
											<option value="2"{if ($DATE_FIELD == '2')} selected="selected"{/if}>Fecha creación</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="from">Desde: <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<div class="input-group-addon" style="border: 1px solid #ddd !important"><i class="fa fa-calendar"></i></div>
										<input type="text" id="from" name="from" class="form-control pull-right input-readonly b-left date-field" maxlength="18" value="{$FROM}" readonly="readonly" />
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="to">Hasta: <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<div class="input-group-addon" style="border: 1px solid #ddd !important"><i class="fa fa-calendar"></i></div>
										<input type="text" id="to" name="to" class="form-control pull-right input-readonly b-left date-field" maxlength="18" value="{$TO}" readonly="readonly" />
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
						<div class="col-md-4">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="support-type">Tipo de soporte: <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<select id="support-type" name="supporttype" class="form-control">
											<option value=""{if ($SUPPORT_TYPE == '')} selected="selected"{/if}>Todos</option>
{foreach $SUPPORT_TYPES as $supportType}
											<option value="{$supportType}"{if ($SUPPORT_TYPE == $supportType)} selected="selected"{/if}>{$supportType}</option>
{/foreach}
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4">
							<div class="row">
								<div class="col-md-4">
									<div class="label-input">
										<label for="order-by">Orden: <span class="required">*</span></label>
									</div>
								</div>
								<div class="form-group col-md-8 field-container">
									<div class="input-group" style="width: 100%;">
										<select id="order-by" name="orderby" class="form-control">
											<option value=""{if ($ORDER_BY == '')} selected="selected"{/if}>Sin Orden</option>
											<option value="1"{if ($ORDER_BY == '1')} selected="selected"{/if}>T.M.</option>
											<option value="2"{if ($ORDER_BY == '2')} selected="selected"{/if}>T.M., T.S.</option>
											<option value="3"{if ($ORDER_BY == '3')} selected="selected"{/if}>T.M., T.S., T.R.</option>
											<option value="4"{if ($ORDER_BY == '4')} selected="selected"{/if}>Por fecha descendente</option>
											<option value="5"{if ($ORDER_BY == '5')} selected="selected"{/if}>Por fecha ascendente</option>
										</select>
									</div>
								</div>
							</div>
						</div>
						<div class="col-md-4 text-right">
							<button type="submit" class="btn btn-info">Buscar</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
{if (!empty ($FROM)) && (!empty ($TO))}
	<div class="col-lg-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2>Resultados</h2>
			</header>
			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table table-striped table-hover results">
						<thead>
						<tr>
							<th class="col-checkbox">
	{if (!empty ($REPERCUSSIONS))}
								<input type="checkbox" checked="checked" class="form-control print-all" placeholder="" />
	{/if}
							</th>
							<th class="col-ordering">Orden</th>
							<th class="col-heading">Titular</th>
							<th class="col-media">Medio</th>
							<th class="col-creation-date">Fecha creación</th>
							<th class="col-repercussion-date">Fecha publicación</th>
							<th class="col-area">Superficie</th>
						</tr>
						</thead>
						<tbody>
	{if (!empty ($REPERCUSSIONS))}
		{foreach $REPERCUSSIONS as $repercussion}
						<tr>
							<td class="col-checkbox">
								<input type="hidden" class="id" value="{$repercussion.repercusiones_prensaid}" />
								<input type="checkbox" checked="checked" class="form-control print" placeholder="" />
							</td>
							<td class="col-ordering">
								<select class="form-control ordering" title="">
			{for $i = 1; $i <= count ($REPERCUSSIONS); $i++}
									<option value="{$i}"{if ($i == $repercussion@iteration)} selected="selected"{/if}>{$i}</option>
			{/for}
								</select>
							</td>
							<td class="col-heading"><a href="index.php?module=repercusiones_prensa&action=DetailView&record={$repercussion.repercusiones_prensaid}" target="_blank">{$repercussion.titular}</a></td>
							<td class="col-media">{$repercussion.medio}</td>
							<td class="col-creation-date">{$repercussion.createdtime|date_format: 'd/m/Y'}</td>
							<td class="col-repercussion-date">{$repercussion.fecha|date_format: 'd/m/Y'}</td>
							<td class="col-area">{$repercussion.superficie}</td>
						</tr>
		{/foreach}
	{else}
						<tr>
							<td colspan="7" class="text-center">No se encuentran repercusiones que cumplan con los parámetros suministrados</td>
						</tr>
	{/if}
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
	{if (!empty ($REPERCUSSIONS))}
	<div class="col-lg-12">
		<div class="main-box">
			<header class="main-box-header clearfix">
				<h2>Opciones</h2>
			</header>
			<div class="main-box-body clearfix options">
				<form method="get" action="index.php" name="print">
					<input type="hidden" name="module" value="{$CURRENT_MODULE}" />
					<input type="hidden" name="action" value="CreatePDF" />
					<input type="hidden" name="from" value="{$FROM}" />
					<input type="hidden" name="record" value="{$RECORD_ID}" />
					<input type="hidden" name="to" value="{$TO}" />
					<input type="hidden" name="ids" value="" />
					<div class="row">
						<div class="col-md-3">
							<input type="checkbox" id="add-cover" name="addcover" class="form-control" value="1" checked="checked" />
							<label for="add-cover">Imprimir portada</label>
						</div>
						<div class="col-md-3">
							<input type="checkbox" id="add-index" name="addindex" class="form-control" value="1" checked="checked" />
							<label for="add-index">Imprimir índice</label>
						</div>
						<div class="col-md-3">
							<input type="checkbox" id="only-index" name="onlyindex" value="1" class="form-control" />
							<label for="only-index">Sólo índice</label>
						</div>
						<div class="col-md-3 text-right">
							<button type="submit" class="btn btn-info">Imprimir</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	{/if}
{/if}
</div>
<script type="text/javascript">
{literal}
(function (jQuery) {
	var onPrintAllCheckboxClickHandler = function (evt) {
		var isChecked       = jQuery (evt.currentTarget).is (':checked'),
			printCheckboxes = jQuery ('input.print[type="checkbox"]'),
			i, n;
		n = printCheckboxes.length;
		for (i = 0; i < n; i += 1) {
			jQuery (printCheckboxes [ i ]).prop ('checked', isChecked);
		}
	};

	var onPrintCheckboxClickHandler = function () {
		var printAllCheckbox = jQuery ('input.print-all[type="checkbox"]'),
			printCheckboxes  = jQuery ('input.print[type="checkbox"]'),
			i, n;

		n = printCheckboxes.length;
		for (i = 0; i < n; i += 1) {
			if (!jQuery (printCheckboxes [ i ]).is (':checked')) {
				printAllCheckbox.prop ('checked', false);
				return;
			}
		}
		printAllCheckbox.prop ('checked', true);
	};

	var onSearchFormSubmitHandler = function (evt) {
		var form = jQuery (evt.currentTarget),
			value;

		value = form.find ('input#from').val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona la fecha de inicio');
			evt.preventDefault ();
			return;
		}

		value = form.find ('input#to').val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona la fecha de fin');
			evt.preventDefault ();
		}
	};

	var onPrintFormSubmitHandler = function (evt) {
		var form    = jQuery (evt.currentTarget),
			results = jQuery ('.results'),
			idFields, orderingFields, printFields, ids, dummy, i, n;

		printFields = results.find ('input.print:checked');
		if (printFields.length === 0) {
			alert ('Selecciona al menos una repercusión para imprimir');
			evt.preventDefault ();
			return;
		}

		printFields = results.find ('input.print');
		idFields = results.find ('input.id');
		orderingFields = results.find ('select.ordering');

		dummy = [];
		n = printFields.length;
		for (i = 0; i < n; i += 1) {
			if (!jQuery (printFields [ i ]).is (':checked')) {
				continue;
			}
			dummy.push ({
				id: jQuery (idFields [ i ]).val (),
				position: jQuery (orderingFields [ i ]).val ()
			});
		}
		dummy = dummy.sort (function (a, b) {
			if (a.position === b.position) {
				return (a.id - b.id);
			} else {
				return (a.position - b.position);
			}
		});

		ids = dummy.map (function (a) {
			return a.id;
		});

		form.find ('input[name="ids"]').val (ids.join (','));
	};

	var onDocumentReadyHandler = function () {
		jQuery ('input.date-field').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		jQuery ('input.print-all[type="checkbox"]').on ('click', onPrintAllCheckboxClickHandler);
		jQuery ('input.print[type="checkbox"]').on ('click', onPrintCheckboxClickHandler);
		jQuery ('form[name="search"]').on ('submit', onSearchFormSubmitHandler);
		jQuery ('form[name="print"]').on ('submit', onPrintFormSubmitHandler);
	};

	jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
{/literal}
</script>
{/strip}