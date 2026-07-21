{*<!--
/*+********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 ********************************************************************************/
-->*}
{if $smarty.request.ajax neq ''}
&#&#&#{$ERROR}&#&#&#
{/if}

{foreach item=curso from=$CURSOS}
	{if !$curso.titulo}
			<div id="folders{$curso[0].formacion_cur_caid}" class="folder shadow-this">
				<div class="box-header">
					<h3 class="box-title">
						{$curso[0].categoria}
					</h3>
					<div class="box-tools pull-right">
					</div>
				</div>
				<a href="javascript:void(0)" class="close-editor" onclick="OpenCloseFolder({$curso[0].formacion_cur_caid})"></a>
				{foreach item=cur key=k from=$curso}
				
				<div class="col-lg-3" style="width: 33%;">
					<!-- Success box -->
					<a href="index.php?module=encuesta_online&parenttab=Analytics&action=DetailView&record={$cur.encuesta_onlineid}" alt="{$cur.titulo}" title="{$cur.titulo}">
					<div class="box box-solid box-{$cur.color}" style="height: 300px;">
						<div class="box-header">
							<h3 class="box-title">
								{$cur.titulo|truncate:30:"...":true}
							</h3>
							<div class="box-tools pull-right">
							</div>
						</div>
						<div class="image-curso" style="background-image:url('{$cur.image}');"></div>
						<div class="box-body" style="font-size: 15px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
							<p>{$cur.descripcion|truncate:80:"...":true}</p>
							<div class="curso-bottom">
								Puntuaci&oacute;n: 
								<div class="starRating">
								  <div>
									<div>
									  <div>
										<div>
										  <input id="rating{$cur.encuesta_onlineid}" type="radio" name="rating[{$cur.encuesta_onlineid}]" value="1" disabled {if $cur.puntuacion >= 1}checked{/if}>
										  <label for="rating{$cur.encuesta_onlineid}"><span>1</span></label>
										</div>
										<input id="rating{$cur.encuesta_onlineid}" type="radio" name="rating[{$cur.encuesta_onlineid}]" value="2" disabled {if $cur.puntuacion >= 2}checked{/if}>
										<label for="rating{$cur.encuesta_onlineid}"><span>2</span></label>
									  </div>
									  <input id="rating{$cur.encuesta_onlineid}" type="radio" name="rating[{$cur.encuesta_onlineid}]" value="3" disabled {if $cur.puntuacion >= 3}checked{/if}>
									  <label for="rating{$cur.encuesta_onlineid}"><span>3</span></label>
									</div>
									<input id="rating{$cur.encuesta_onlineid}" type="radio" name="rating[{$cur.encuesta_onlineid}]" value="4" disabled {if $cur.puntuacion >= 4}checked{/if}>
									<label for="rating{$cur.encuesta_onlineid}"><span>4</span></label>
								  </div>
								  <input id="rating{$cur.encuesta_onlineid}" type="radio" name="rating[{$cur.encuesta_onlineid}]" value="5" disabled {if $cur.puntuacion >= 5}checked{/if}>
								  <label for="rating{$cur.encuesta_onlineid}"><span>5</span></label>
								</div>
							
							</div>
						</div><!-- /.box-body -->
					</div><!-- /.box -->
					</a>
				</div><!-- /.col -->
				{/foreach}
				
			</div>

	{/if}
{/foreach}

<div class="row" style="width: 1286px;margin: 0px;background-color: #f4f4f4;padding-top: 15px;padding-bottom: 15px;" id="div-cursos">
	
	{foreach item=curso from=$CURSOS}
		{if $curso.titulo}
		<div class="col-lg-3">
			<!-- Success box -->
			<a href="index.php?module=encuesta_online&parenttab=Analytics&action=DetailView&record={$curso.encuesta_onlineid}" alt="{$curso.titulo}" title="{$curso.titulo}">
			<div class="box box-solid box-{$curso.color}" style="height: 300px;">
				<div class="box-header">
					<h3 class="box-title">
						{$curso.titulo|truncate:30:"...":true}
					</h3>
					<div class="box-tools pull-right">
					</div>
				</div>
				<div class="image-curso" style="background-image:url('{$curso.image}');"></div>
				<div class="box-body" style="font-size: 15px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
					<p>{$curso.descripcion|truncate:80:"...":true}</p>
					<div class="curso-bottom">
						Puntuaci&oacute;n: 
						<div class="starRating">
						  <div>
							<div>
							  <div>
								<div>
								  <input id="rating{$curso.encuesta_onlineid}" type="radio" name="rating[{$curso.encuesta_onlineid}]" value="1" disabled {if $curso.puntuacion >= 1}checked{/if}>
								  <label for="rating{$curso.encuesta_onlineid}"><span>1</span></label>
								</div>
								<input id="rating{$curso.encuesta_onlineid}" type="radio" name="rating[{$curso.encuesta_onlineid}]" value="2" disabled {if $curso.puntuacion >= 2}checked{/if}>
								<label for="rating{$curso.encuesta_onlineid}"><span>2</span></label>
							  </div>
							  <input id="rating{$curso.encuesta_onlineid}" type="radio" name="rating[{$curso.encuesta_onlineid}]" value="3" disabled {if $curso.puntuacion >= 3}checked{/if}>
							  <label for="rating{$curso.encuesta_onlineid}"><span>3</span></label>
							</div>
							<input id="rating{$curso.encuesta_onlineid}" type="radio" name="rating[{$curso.encuesta_onlineid}]" value="4" disabled {if $curso.puntuacion >= 4}checked{/if}>
							<label for="rating{$curso.encuesta_onlineid}"><span>4</span></label>
						  </div>
						  <input id="rating{$curso.encuesta_onlineid}" type="radio" name="rating[{$curso.encuesta_onlineid}]" value="5" disabled {if $curso.puntuacion >= 5}checked{/if}>
						  <label for="rating{$curso.encuesta_onlineid}"><span>5</span></label>
						</div>
					
					</div>
				</div><!-- /.box-body -->
			</div><!-- /.box -->
			</a>
		</div><!-- /.col -->
		{else}
		<div class="col-lg-3">
			<!-- Success box -->
			<a href="javascript:void(0)" onclick="OpenCloseFolder({$curso[0].formacion_cur_caid})" style="cursor:pointer;">
			<div class="box box-solid box-info" style="height: 300px;">
				<div class="box-header">
					<h3 class="box-title">
						Carpeta: {$curso[0].categoria|truncate:30:"...":true}
					</h3>
					<div class="box-tools pull-right" style="background-color: #fff;width: 292px;">
					{foreach item=cur key=k from=$curso}
						
						{if $k<=3}
						<div class="col-lg-3" style="width:50%;">
							<!-- Success box -->
							<div class="box box-solid box-{$cur.color}" style="height: 100px;">
								<div class="box-header">
									<h3 class="box-title" style="font-size: 6px;padding: 5px 0px 5px 10px;">
										{$cur.titulo|truncate:30:"...":true}
									</h3>
									<div class="box-tools pull-right">
									</div>
								</div>
								<div class="image-curso" style="background-image:url('{$cur.image}');height:50px;background-size: 110px;"></div>
								<div class="box-body" style="font-size: 5px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
									<p>{$cur.descripcion|truncate:80:"...":true}</p>
								</div><!-- /.box-body -->
							</div><!-- /.box -->
						</div><!-- /.col -->
						{/if}
					{/foreach}
					</div>
				</div>
			</div>
			</a>
		</div><!-- /.col -->
		{/if}
	{/foreach}

</div>
<script>
{literal}
function OpenCloseFolder(folderid){
	jQuery('#div-cursos').toggleClass('blur');
	jQuery('#folders'+folderid).fadeToggle('display');
	
}
{/literal}
</script>