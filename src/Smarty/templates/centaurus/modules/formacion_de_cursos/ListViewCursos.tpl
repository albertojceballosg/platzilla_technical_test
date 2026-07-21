{strip}
{foreach item=curso from=$CURSOS}
	{if !$curso.titulo}
<div id="folders{$curso[0].formacion_cur_caid}" class="folder shadow-this">
	<div class="box-header">
		<h3 class="box-title">{$curso[0].categoria}</h3>
		<div class="box-tools pull-right"></div>
	</div>
	<a href="javascript:void(0)" class="close-editor" onclick="OpenCloseFolder({$curso[0].formacion_cur_caid})"></a>
		{foreach item=cur key=k from=$curso}
	<div class="col-lg-3" style="width: 33%;">
		<a href="index.php?module=formacion_cursos&parenttab=Analytics&action=DetailView&record={$cur.formacion_cursosid}" alt="{$cur.titulo}" title="{$cur.titulo}">
			<div class="box box-solid box-{$cur.color}" style="height: 300px;">
				<div class="box-header">
					<h3 class="box-title">{$cur.titulo|truncate:30:"...":true}</h3>
					<div class="box-tools pull-right"></div>
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
											<input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="1" disabled {if $cur.puntuacion >= 1}checked{/if}>
											<label for="rating{$cur.formacion_cursosid}"><span>1</span></label>
										</div>
										<input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="2" disabled {if $cur.puntuacion >= 2}checked{/if}>
										<label for="rating{$cur.formacion_cursosid}"><span>2</span></label>
									</div>
									<input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="3" disabled {if $cur.puntuacion >= 3}checked{/if}>
									<label for="rating{$cur.formacion_cursosid}"><span>3</span></label>
								</div>
								<input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="4" disabled {if $cur.puntuacion >= 4}checked{/if}>
								<label for="rating{$cur.formacion_cursosid}"><span>4</span></label>
							</div>
							<input id="rating{$cur.formacion_cursosid}" type="radio" name="rating[{$cur.formacion_cursosid}]" value="5" disabled {if $cur.puntuacion >= 5}checked{/if}>
							<label for="rating{$cur.formacion_cursosid}"><span>5</span></label>
						</div>
					</div>
				</div>
			</div>
		</a>
	</div>
		{/foreach}
</div>
	{/if}
{/foreach}
<div class="row">
{foreach item=curso from=$CURSOS}
	{if $curso.titulo}
	<div class="col-lg-4 col-md-6 col-sm-6">
		<div class="main-box clearfix project-box gray-box">
			<div class="main-box-body clearfix" id="user-profile">
				<div class="project-box-header gray-bg">
					<div class="name">
						<a href="index.php?module=formacion_cursos&parenttab=Analytics&action=DetailView&record={$curso.formacion_cursosid}" title="{$curso.titulo}">{$curso.titulo|truncate:35:"...":true}</a>
					</div>
				</div>
				<div class="">
					<img src="{$curso.image}" alt="" class="img-responsive center-block" style="height: 250px;">
				</div>
				<div class="project-box-footer clearfix">
					<div style="  padding: 9px;font-size: 0.875em;font-weight: 300;color: #344644;  max-height: 35px;" title="{$curso.descripcion}">{$curso.descripcion|strtolower|ucfirst|truncate:50:"...":true}</div>
				</div>
				<div class="project-box-ultrafooter clearfix">
					<div class="profile-stars pull-left">
						<i class="fa fa-star{if $curso.puntuacion < 1}-o{/if}"></i>
						<i class="fa fa-star{if $curso.puntuacion < 2}-o{/if}"></i>
						<i class="fa fa-star{if $curso.puntuacion < 3}-o{/if}"></i>
						<i class="fa fa-star{if $curso.puntuacion < 4}-o{/if}"></i>
						<i class="fa fa-star{if $curso.puntuacion < 5}-o{/if}"></i>
					</div>
					<div id="newsfeed">
						<div class="story-content pull-right" style="padding-top: 7px;padding-left: 0px;">
							<footer class="story-footer">
								<a href="#" class="story-comments-link"><i class="fa fa-comment fa-lg"></i> 120 Comentarios</a>
								<a href="#" class="story-likes-link"><i class="fa fa-heart fa-lg"></i> 82 Likes</a>
							</footer>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	{else}
	<div class="col-lg-3">
		<a href="javascript:void(0)" onclick="OpenCloseFolder({$curso[0].formacion_cur_caid})" style="cursor:pointer;">
			<div class="box box-solid box-info" style="height: 300px;">
				<div class="box-header">
					<h3 class="box-title">Carpeta: {$curso[0].categoria|truncate:30:"...":true}</h3>
					<div class="box-tools pull-right" style="background-color: #fff;width: 292px;">
		{foreach item=cur key=k from=$curso}
			{if $k<=3}
						<div class="col-lg-3" style="width:50%;">
							<div class="box box-solid box-{$cur.color}" style="height: 100px;">
								<div class="box-header">
									<h3 class="box-title" style="font-size: 6px;padding: 5px 0 5px 10px;">{$cur.titulo|truncate:30:"...":true}</h3>
									<div class="box-tools pull-right"></div>
								</div>
								<div class="image-curso" style="background-image:url('{$cur.image}');height:50px;background-size: 110px;"></div>
								<div class="box-body" style="font-size: 5px;font-family: 'Source Sans Pro', sans-serif;color:#444;">
									<p>{$cur.descripcion|truncate:80:"...":true}</p>
								</div>
							</div>
						</div>
			{/if}
		{/foreach}
					</div>
				</div>
			</div>
		</a>
	</div>
	{/if}
{/foreach}
</div>
<script type="text/javascript">
	function OpenCloseFolder (folderid) {
		jQuery ('#div-cursos').toggleClass ('blur');
		jQuery ('#folders' + folderid).fadeToggle ('display');
	}
</script>
{/strip}