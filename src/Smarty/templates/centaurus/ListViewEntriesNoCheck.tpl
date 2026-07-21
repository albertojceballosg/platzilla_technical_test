{extends file="base/BaseList.tpl"}

{block name ="js"}
{if $smarty.request.ajax neq ''}
&#&#&#{$ERROR}&#&#&#
{/if}
<script language="JavaScript" type="text/javascript" src="include/js/ListView.js"></script>
<script language="JavaScript" type="text/javascript" src="include/js/Mail.js"></script>
{/block}

{block name="scripts"}
<script>

function viewSearch(){ldelim}

if(!jQuery("#divsearch").is(':visible')){ldelim}
	jQuery("#imgsearch").removeClass("fa-search-plus");
jQuery("#imgsearch").addClass("fa-search-minus");
jQuery("#divsearch").show();

{rdelim}else{ldelim}

jQuery("#imgsearch").removeClass("fa-search-minus");
jQuery("#imgsearch").addClass("fa-search-plus");
jQuery("#divsearch").hide();

{rdelim}

var module = '{$MODULE}';
var parent = '{$CATEGORY}';

//jQuery('#viewname option:contains("Todos")').prop('selected', true);
//jQuery('#viewname').trigger('change');
//showDefaultCustomView1(jQuery('#viewname').val(),module,parent);
{rdelim}

function messageConstruction(){ldelim}
alert("Funcionalidad En construcci\u00f3n");
{rdelim}

</script>
{/block}

{block name="header-buttons-row-one" prepend}			
<div class="pull-left">
	<div class="pull-right" style="float: right">
		<a href="index.php?module=Calendar&amp;action=index" class="btn btn-default" style="margin-top: .1em;padding: .5em 1em;margin-right: .29em;">
			<i class="fa fa-default"></i>Regresar a {$APP.Calendar}
		</a>
	</div>
</div>
{/block}

{block name="header-buttons-row-two" prepend}	
{/block}

