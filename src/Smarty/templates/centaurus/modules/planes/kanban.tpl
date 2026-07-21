<div class="row">
	<div class="col-lg-6">
		<h1>Kanban</h1>
	</div>
	<div class="col-lg-6 filter-block pull-right">

	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="main-box clearfix">
			<header class="main-box-header clearfix">
				<h2 class="pull-left"></h2>

				<div class="filter-block pull-right">

				</div>
			</header>

			<div class="main-box-body clearfix">
				<div class="table-responsive">
					<table class="table">
						<tr>
						{foreach item=arr from=$TIPOS_GENERICOS}
							<th style="width:{math equation="x/y" x=100 y=$TIPOS_GENERICOS|@count}%">
							{$arr|@getTranslatedString:$MODULE}
							</th>
						{/foreach}
						</tr>
						<tbody>
							<tr id="elsortable">
							{foreach item=arrX key=key from=$TIPOS_GENERICOS}
								{if $key eq 0}
									{assign var="class" value="alert-info"}
								{elseif $key eq 1}
									{assign var="class" value="alert-success"}
								{elseif $key eq 2 || $key eq 3 || $key eq 4}
									{assign var="class" value="alert-warning"}
								{elseif $key eq 5}
									{assign var="class" value="alert-success"}
								{/if}
								<td style="font-size: 0.875em;font-weight: normal;vertical-align: top;" id="elsortable_{$key}" data-tipo="{$arrX}" data-class="alert {$class}">
								{foreach item=arr2 key=key2 from=$REGISTROS_GENERICOS[$key]}
									<div class="alert {$class}" data-ticketid="{$arr2.ticketid}" id="ticketid_{$arr2.ticketid}">
										<a href="index.php?action=DetailView&module=HelpDesk&record={$arr2.ticketid}">{$arr2.title}</a><br/>
										<small><i class="fa fa-suitcase"></i> {$arr2.accountname}</small><br/>
										<small><i class="fa fa-user"></i> {$arr2.user_name}</small><br/><br/>
										<p class="text-right">
											<a href="javascript:confirmdelete('index.php?module=HelpDesk&action=Delete&record={$arr2.ticketid}&return_module={$MODULE}&return_action=index');">
											<i class="fa fa-trash-o"></i></a>

											<a href="index.php?action=EditView&module=HelpDesk&record={$arr2.ticketid}&return_module={$MODULE}&return_action=index"><i class="fa fa-edit"></i></a>
										</p>
									</div>

								{/foreach}
								</td>
							{/foreach}
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="themes/centaurus/js/jquery-ui.custom.min.js"></script>
<script src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
<script>
jQuery('#fecha_desde').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });
jQuery('#fecha_hasta').datepicker ({ format: 'dd-mm-yyyy', language: 'es', weekStart: 1 });
var evento;
{foreach item=arrX key=key from=$TIPOS_GENERICOS}
	var elsortable_{$key}=jQuery('#elsortable_{$key}').sortable({ldelim}
			connectWith: "td",
			cursor: "move",
			receive: function( event, ui ){ldelim}
				evento=event;
				jQuery(ui.item).removeClass();
				jQuery(ui.item).addClass(event.target.dataset.class);
				saveRegistro(event,ui);
			{rdelim},
		{rdelim});
	jQuery( "#selsortable_{$key}" ).disableSelection();
{/foreach}
{literal}
function saveRegistro(event,ui){
	var tipo=event.target.dataset.tipo;
	var ticketid=ui.item.attr("data-ticketid");
	jQuery('#status').show();
	jQuery.ajax({
		type: "POST",
		url: "index.php",
		data: { module: "mod_kanboard", action: "mod_kanboardAjax",file: "Save",save:'estadotarea',tipo:tipo,ticketid:ticketid }
	}).done(function( response ) {
		jQuery('#status').hide();
		console.log(response);
	});
	return false;
}
{/literal}

</script>
