<script type="text/javascript" src="include/js/smoothscroll.js"></script>
<script type="text/javascript" src="include/js/menu.js"></script>
<script type="text/javascript" src="include/js/menu.js"></script>
<script type="text/javascript" src="themes/centaurus/js/jquery.nestable.maxDepth.js"></script>
<script type="text/javascript" src="modules/Settings/Settings.js"></script>
<form action="index.php?module=Settings&action=SaveEditKpisBoxscore" method="post" id="SaveEditKpisBoxscore" name="index">
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-9 pull-left">
				<h1>
					<a href="index.php?module=Settings&action=kpisBoxscore&parenttab=Settings">{$MOD.LBL_KPIS_BOXSCORE_EDIT} </a>
				</h1>
			</div>
			<div class="col-lg-3 pull-right text-right">
				<button class="btn btn-primary" type="button" id="btnsave" onclick="if (validateForm () == true) {ldelim} validateRepeatData(); {rdelim}">{$MOD.LBL_SAVE}</button>
				<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=kpisBoxscore">{$MOD.LBL_CANCEL_BUTTON}</a>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="main-box no-header">
				<div class="main-box-body clearfix" id="">
					<input type="hidden" id="actionCrud" name="actionCrud" value="Edit" />
					<input type="hidden" id="record" name="record" value="{$KPI.kpisboxscoreid}" />
					<div class="form-group">
						<label for="title" id="label_title">{$MOD.LBL_KPIS_BOXSCORE_TITLE}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" placeholder="{$MOD.LBL_KPIS_BOXSCORE_TITLE}" id="title" name="title" class="form-control" title="{$MOD.LBL_KPIS_BOXSCORE_TITLE}" value="{$KPI.name}" />
					</div>
					<div class="form-group">
						<label for="description" id="label_description">{$MOD.LBL_KPIS_BOXSCORE_DESCRIPCION}</label>&nbsp;<span style="color: red;">*</span>
						<input type="text" placeholder="{$MOD.LBL_KPIS_BOXSCORE_DESCRIPCION}" id="description" name="description" class="form-control" title="{$MOD.LBL_KPIS_BOXSCORE_DESCRIPCION}" value="{$KPI.description}" />
					</div>
					<div class="form-group">
						<label for="modulo">{$MOD.LBL_KPIS_BOXSCORE_MODULE}</label>&nbsp;<span style="color: red;">*</span>
						<select id="modulo" name="modulo" class="form-control">
{foreach item=module from=$MODULESFREE}
							<option value="{$module.name}"{if $module.name eq $KPI.module} selected="selected"{/if}>{$module.tablabel}</option>
{/foreach}
						</select>
					</div>
					<div class="form-group">
						<label for="active">{$MOD.LBL_CUSTOM_BUTTONS_ACTIVE}</label>
						<select id="active" name="active" class="form-control">
							<option value="1"{if $KPI.active eq 1 } selected="selected"{/if}>Activa</option>
							<option value="0"{if $KPI.active eq 0 } selected="selected"{/if}>Inactiva</option>
						</select>
					</div>
					<div class="form-group">
						<label for="query" id="label_query">{$MOD.LBL_KPIS_BOXSCORE_QUERY}</label>&nbsp;
						<textarea placeholder="{$MOD.LBL_KPIS_BOXSCORE_QUERY}" id="query" name="query" class="form-control" title="{$MOD.LBL_KPIS_BOXSCORE_QUERY}">{$KPI.querykpi}</textarea>
					</div>
					<div class="form-group">
						<label for="querysemanal" id="label_querysemanal">{$MOD.LBL_KPIS_BOXSCORE_QUERY_SEMANAL}</label>&nbsp;
						<textarea placeholder="{$MOD.LBL_KPIS_BOXSCORE_QUERY_SEMANAL}" id="querysemanal" name="querysemanal" class="form-control" title="{$MOD.LBL_KPIS_BOXSCORE_QUERY_SEMANAL}">{$KPI.querykpisemanal}</textarea>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-lg-12">
			<div class="col-lg-9 pull-left"></div>
			<div class="col-lg-3 pull-right text-right">
				<button class="btn btn-primary" type="button" id="btnsave" onclick="if (validateForm () == true) {ldelim} validateRepeatData(); {rdelim}">{$MOD.LBL_SAVE}</button>
				<a class="btn btn-warning" type="submit" href="index.php?module=Settings&action=kpisBoxscore">{$MOD.LBL_CANCEL_BUTTON}</a>
			</div>
		</div>
	</div>
</form>
<div id="editdiv" style="display:none; position:absolute; width:400px;"></div>
<div class="md-overlay"></div><!-- the overlay element -->
<script>
{literal}
	function validateForm () {
		var module;
		if (!jQuery ('#title').val ()) {
			alert ('Especifique el título del KPI');
			return false;
		}
		if (!jQuery ('#description').val ()) {
			alert ('Escriba una descripción para el KPI');
			return false;
		}
		module = jQuery ('#modulo');
		if (!module.val ()) {
			alert ('Seleccione el módulo asociado al KPI');
			return false;
		}
		if (!module.val ()) {
			alert ('Seleccione el módulo asociado al KPI');
			return false;
		}
		if (!jQuery ('#query').val ()) {
			alert ('Especifique el query de escala Mensual asociado al KPI');
			return false;
		}
		if (!jQuery ('#querysemanal').val ()) {
			alert ('Especifique el query de escala Semanal asociado al KPI');
			return false;
		}
		return true;
	}

	function validateRepeatData () {
		jQuery ('#SaveEditKpisBoxscore').submit ();
		return true;
	}
{/literal}
</script>

