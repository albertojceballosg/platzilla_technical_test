<div class="col-md-12">
	<label class="control-label">{$MOD.LBL_LIST_CONDICIONAR}</label>
	<select class="form-control" id="listValues" onchange="getPickList(this.value)">
		{foreach item=opcion from=$LIST_VALUES name=list}			
            {assign var=count value=$smarty.foreach.list.iteration}
			<option value="{$opcion.value}" {if $count eq '1'}selected{/if}>{$opcion.text}</option>
		{/foreach}
	</select>
</div>