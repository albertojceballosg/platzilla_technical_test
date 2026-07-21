<link rel="stylesheet" type="text/css" href="themes/centaurus/css/compiled/wizard.css" />
<script type="text/javascript" src="themes/centaurus/js/jquery.maskedinput.min.js"></script>
<script type="text/javascript" src="modules/Settings/wizard-utils.js"></script>
<div class="row">
	<div class="col-md-12">
		<h1>
			<a href="index.php?module=calculated_fields&action=index&parenttab=Settings">
			{$MOD.LBL_CONFIG_CALCULATED_FIELDS} </a>
			<small>{$MOD.LBL_CONFIG_CALCULATED_FIELDS_SUB}</small>
		</h1>
	</div>
	<div class="col-md-12">
		<div class="main-box clearfix">
          <br>
		<div class="wizard    main-box-body clearfix" id="myWizard">
			<div class="wizard-inner">
				<ul class="steps">
					<li><span class="badge">1</span>{$MOD.STEP1_TITLE}<span class="chevron"></span></li>
					<li class="active"><span class="badge badge-primary">2</span>{$MOD.STEP2_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">3</span>{$MOD.STEP3_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">4</span>{$MOD.STEP4_TITLE}<span class="chevron"></span></li>
					<li><span class="badge">5</span>{$MOD.STEP5_TITLE}<span class="chevron"></span></li>
				</ul>
				<div class="actions" style="background-color: #ffffff;border-bottom-color: #ffffff;margin-top: 8px">

				</div>
			</div>
		</div>

		</div>
	</div>
</div>
<div class="row" style="margin-top: 25px">
	<div class="col-md-12">
		<div class="main-box clearfix">
			<br>
			<div class="main-box-body clearfix" >
				<p class="text-left" style="padding: 12px  4px">
					{$MOD.STEP2_DES}
					<i class="fa fa-question-circle" style="margin-left: 8px; color: #337ab7; cursor: help;" 
					   title="Si vas a hacer un total que luego va a ser usado en un campo calculado de otro módulo, se debe crear una condición donde selecciones, del módulo donde están los datos, el campo que establece la relación entre ambos módulos, y luego, el operador 'igual a' y en Valor '__RECORD__'."></i>
				</p>

				<form id="calculate-field-form" class="form-horizontal" role="form" method="post"  action="?module=calculated_fields&action=addCalculatedFields">
					<div class="form-group condition-groups">
                        {if (isset ($CF_FILTER) && !empty ($CF_FILTER) && $CF_FILTER|@count gt 0)}
                            {assign var=filters value=$CF_FILTER}
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
                                {include file="modules/calculated_fields/filterEditElement.tpl"}
                                {$totalGroup = $totalGroup + 1}
                            {else}
                                {assign var="totalGroup" value=0}
                                {assign var="totalIndex" value=0}
                            {/if}

                        {else}
                            {assign var="totalGroup" value=0}
                            {assign var="totalIndex" value=0}
                        {/if}
						<div class="action-bar text-center">
							<button type="button" class="btn btn-success" data-group="0"   onclick="CFUtils.addFilterGroup (this);" title="Agregar grupo de condiciones">
								<i class="fa fa-plus"></i></button>
						</div>
					</div>

					<div class="form-group">
						<div class="col-lg-offset-2 col-lg-10">
							<button type="button" id="btn-back" data-step="2" class="btn btn-danger btn-sm" onclick="CFUtils.goPrevStep(this);">{$MOD.NAV_BUTTON_BACK}</button>
							<button type="button" id="btn-follow" data-step="2" class="btn btn-success btn-sm" onclick="CFUtils.validateRepeatData(this);">{$MOD.NAV_BUTTON_FOLLOW}</button>
							<a class="btn btn-default btn-sm" href="index.php?module=calculated_fields&action=index&parenttab=Settings">{$MOD.NAV_BUTTON_CANCEL}</a>
						</div>
					</div>
					<input type="hidden" id="numField" name="numField" value="{if isset($PF)}{$NF}{else}0{/if}">
					<input type="hidden" id="step" name="step" value=2>
					<input type="hidden" id="module" name="module" value="calculated_fields">
					<input type="hidden" id="method" name="method" value="stepTwo">
					<input type="hidden" id="action" name="action" value="addOperationCalculatedFields">
					<input type="hidden" id="inRecord" name="inRecord" value="{$IN_RECORD}">
                    {foreach from=$DFSO key=k item=v}
						<input type="hidden" id="{$k}" name="{$k}" value="{$v}">
                    {/foreach}
					<input type="hidden" id="operationfieldId" name="operationfieldId" value="{$OPERFID}">
					<input type="hidden" id="operation" name="operation" value="{$OPERID}">
                    {if isset($COD)}
						<input type="hidden" id="record" name="record" value="{$COD}">
                    {/if}
					<input type="hidden" id="period" name="period" value="{$PERIOD}">
					<input type="hidden" id="periodfieldId" name="periodfieldId" value="{$PERIOD_FIELDID}">
				</form>
			</div>
		</div>
	</div>

</div>
<div id="editdiv" style="display:none;position:absolute;width:400px;"></div>
<div class="md-overlay"></div>

<script type="text/html" id="condition-template">
    {include file="modules/calculated_fields/filterElement.tpl"}
</script>
<script type="text/html" id="condition-group-template">
    {include file="modules/calculated_fields/filterGroupElement.tpl"}
</script>
<script language="JavaScript" type="text/javascript" src="include/js/{php} echo $_SESSION['authenticated_user_language'];{/php}.lang.js?{php} echo $_SESSION['vtiger_version'];{/php}"></script>
<script type="text/javascript" src="modules/calculated_fields/calculatedfields.js"></script>
<script type="text/javascript">
    jQuery (document).ready (function () {
        totalFilterGroup = {($totalGroup + 1)};
        totalFilterRow   = {($totalIndex + 1)};
    });

</script>
