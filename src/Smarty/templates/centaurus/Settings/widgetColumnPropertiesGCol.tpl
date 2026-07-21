<div class="main-box-body clearfix col-lg-6" id="graph-box-col-{$COL_INDEX}">
	<table class="table">
		<tr>
			<td align="center"><input type="checkbox" name="colenable[{$COL_INDEX}]" checked="checked" /> Habilitar Columna</td>
			<td align="center">
				<a href="javascript:if(confirm('Esta seguro que desea eliminar la columna?')){ldelim}jQuery('#graph-box-col-{$COL_INDEX}').remove(){rdelim}" class="table-link danger">
					<span class="fa-stack">
						<i class="fa fa-square fa-stack-2x"></i>
						<i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>
					</span>
				</a>
			</td>
		</tr>
		<tr>
			<td>Modulos</td>
			<td>{$LISTAMODULOS}</td>
		</tr>
		<tr>
			<td>{$MOD.LBL_FIELD_COLUMN}</td>
			<td>
				<select class="form-control" id="fieldop{$COL_INDEX}" name="fieldop[{$COL_INDEX}]">
					<option value="">{'LBL_NONE'|@getTranslatedString:$MODULE}</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>Tipo de C&aacute;lculo</td>
			<td>
				<select name="opcolumn[{$COL_INDEX}]" id="opcolumn{$COL_INDEX}" class="form-control">
				{$OPERATIONS}
				</select>
				<select name="opcolumngroup[{$COL_INDEX}]" id="opcolumngroup{$COL_INDEX}" class="form-control">
					<option value=""> - </option>
					<option value="group">Agrupar</option>
				</select>
			</td>
		</tr>
		<tr>
			<td>
				Etiqueta
			</td>
			<td>
				<input name="label[{$COL_INDEX}]" id="label{$COL_INDEX}" class="form-control" type="text" value="{$LABEL}" placeholder="Etiqueta columna">
			</td>
		</tr>
		<tr>
			<td colspan="2">Condiciones</td>
		</tr>
		<tr>
			<td colspan="2">
				<div id="mnuTab2" >
					{include file='Settings/AdvanceFilterWidget.tpl' SOURCE='customview'}
				</div>
			</td>
		</tr>
	</table>

</div>