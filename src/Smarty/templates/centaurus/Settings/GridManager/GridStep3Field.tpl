{strip}
<tr>
	<td>
		<div style="width: auto; display: block;">
			<header class="main-box-header clearfix" style="margin-top: 0; padding-bottom: 0; padding-top: 0;">
				<h4 class="pull-left block-name">{$BLOCK_NAME}</h4>
				<div class="filter-block pull-right">
					<a id="add-field" href="#" class="btn btn-primary pull-right" data-block-number="{$BLOCK_NUMBER}" onclick="AddGridFieldsUtils.addFieldToGrid (this); return false;">
						<i class="fa fa-plus-circle fa-lg"></i>{$MOD.LBL_ADD_CAMPO}
					</a>
				</div>
			</header>
			<table id="lvt-table"   width="100%" cellspacing="0" cellpadding="0" border="0" class="lvt small block-fields">
				<thead>
				<tr>
					<th class="lvtCol" width="{if !isset($NOMBRE_WIDTH)}30% {else}$NOMBRE_WIDTH) {/if}">{$MOD.LBL_NOMBRE_CAMPO}</th>
					<th class="lvtCol" width="10%">{$MOD.POS_CAMPO}</th>
					<th class="lvtCol" width="20%">{$MOD.LBL_ETIQUETA_CAMPO}</th>
					<th class="lvtCol" width="20%">{$MOD.LBL_TIPO_CAMPO}</th>
					<th class="lvtCol" width="20%">{$MOD.LBL_OPCIONES}</th>
				</tr>
				</thead>
				<tbody>
{if (isset ($IS_FIRST_BLOCK)) && ($IS_FIRST_BLOCK == true)}
	{include file='Settings/GridManager/GridStep3FieldDetail.tpl'
		FIELD_LABEL='Código'
		FIELD_LENGTH=''
		FIELD_MODULE=''
		FIELD_NAME='cod_'|cat:substr($MODULE_NAME, 0, 12)
		FIELD_PRECISION=''
		FIELD_PREFIX=strtoupper(substr($MODULE_NAME, 0, 3))|cat:'-'
		FIELD_SEQUENCE='001'
		FIELD_TYPE=4
		FIELD_VALUE=''
		VISIBLE=false
	}
	{assign var='row' value=1}
{else}
	{assign var='row' value=0}
{/if}
{assign var='n' value=(count($FIELD_NAMES) - 1)}
{for $i=$row to $n}
	{if ($FIELD_BLOCK_NUMBERS[$i] == $BLOCK_NUMBER)}
		{include file='Settings/GridManager/GridStep3FieldDetail.tpl'
			FIELD_LABEL=$FIELD_LABELS[$i]
			FIELD_LENGTH=$FIELD_LENGTHS[$i]
			FIELD_MODULE=$FIELD_MODULES[$i]
			FIELD_NAME=$FIELD_NAMES[$i]
			FIELD_PRECISION=$FIELD_PRECISIONS[$i]
			FIELD_PREFIX=$FIELD_PREFIXES[$i]
			FIELD_SEQUENCE=$FIELD_SEQUENCES[$i]
			FIELD_TYPE=$FIELD_TYPES[$i]
			FIELD_VALUE=$FIELD_VALUES[$i]
			VISIBLE=true
		}
	{/if}
{/for}
{if (count ($FIELD_NAMES) <= 1)}
	{include file='Settings/GridManager/GridStep3FieldDetail.tpl'
		FIELD_LABEL=''
		FIELD_LENGTH=''
		FIELD_MODULE=''
		FIELD_NAME=''
		FIELD_PRECISION=''
		FIELD_PREFIX=''
		FIELD_SEQUENCE=''
		FIELD_TYPE=1
		FIELD_VALUE=''
		VISIBLE=true
	}
{/if}
				</tbody>
			</table>
		</div>
	</td>
</tr>
{/strip}