{strip}
<script type="text/html" id="related-module-records-modal-template">
	<div class="modal fade" id="related-module-records" tabindex="-1" role="dialog" aria-hidden="false">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<div id="search" class="selection-stuff">
						<form class="form" role="search" onsubmit="RelatedModuleModalUtils.search (this); return false;">
							<div class="input-group">
								<div class="input-group-btn">
									<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">En <span id="selected-search-field-label"></span> <span class="caret"></span></button>
									<ul id="fields-list" class="dropdown-menu" role="menu"></ul>
								</div>
								<input type="search" id="search-keywords" name="search-keywords" class="form-control" placeholder="Buscar..." />
								<div class="input-group-btn">
									<button type="submit" class="btn btn-default"><i class="fa fa-search"></i></button>
								</div>
							</div>
						</form>
					</div>
					<div class="table-responsive selection-stuff">
						<table id="records" class="table">
							<thead></thead>
							<tbody></tbody>
						</table>
					</div>
					<div id="pager-dv" class="text-center selection-stuff">
						<ul id="pager" class="pagination"></ul>
					</div>
					<div id="quick-create" class="quick-create-stuff"></div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary selection-stuff" onclick="RelatedModuleModalUtils.showQuickCreateStuff ();">Creación Rápida</button>
					<button type="button" class="btn btn-default quick-create-stuff" onclick="RelatedModuleModalUtils.showSelectionStuff ();">Cancelar</button>
					<button type="button" class="btn btn-primary quick-create-stuff" onclick="RelatedModuleModalUtils.saveRecord ();">Guardar</button>
					<button type="button" class="btn btn-primary related-records-stuff" onclick="RelatedModuleModalUtils.relateRecords ();">Seleccionar</button>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="related-module-records-search-field-template">
	<li>
		<div class="radio" onclick="RelatedModuleModalUtils.selectSearchField (this);">
			<input type="radio" id="search-field" name="field" class="search-field" />
			<label for="search-field"></label>
		</div>
	</li>
</script>
<script type="text/html" id="related-module-records-pager-template">
	<ul class="pagination text-center" style="width: 100%;">
		<li class="first-page">
			<button type="button" class="btn btn-primary" onclick="RelatedModuleModalUtils.goToPage (this);"><i class="fa fa-step-backward"></i></button>
		</li>
		<li class="previous-page">
			<button type="button" class="btn btn-primary" onclick="RelatedModuleModalUtils.goToPage (this);"><i class="fa fa-chevron-left"></i></button>
		</li>
		<li class="specific-page">
			<button type="button" class="btn btn-primary" onclick="RelatedModuleModalUtils.goToPage (this);"></button>
		</li>
		<li class="next-page">
			<button type="button" class="btn btn-primary" onclick="RelatedModuleModalUtils.goToPage (this);"><i class="fa fa-chevron-right"></i></button>
		</li>
		<li class="last-page">
			<button type="button" class="btn btn-primary" onclick="RelatedModuleModalUtils.goToPage (this);"><i class="fa fa-step-forward"></i></button>
		</li>
	</ul>
</script>
<script type="text/html" id="related-module-records-quick-create-field-template">
	<div class="col-xs-12">
		<div class="col-xs-4 col-md-3 col-lg-2">
			<div class="label-input">
				<label for=""></label>
			</div>
		</div>
		<div class="form-group col-xs-8 col-md-9 col-lg-10 field-container" id="">
			<input type="text" name="" id="" class="form-control quick-create-field" />
		</div>
	</div>
</script>
<script type="text/html" id="related-module-records-quick-create-select-template">
	<div class="col-xs-12">
		<div class="col-xs-4 col-md-3 col-lg-2">
			<div class="label-input">
				<label for=""></label>
			</div>
		</div>
		<div class="form-group col-xs-8 col-md-9 col-lg-10 field-container" id="">
			<select name="" id="" class="form-control quick-create-field"></select>
		</div>
	</div>
</script>
<style>
/* Ajustar tamaño de fuente de la tabla de registros relacionados */
#related-module-records .table {
	font-size: 13px;
}

#related-module-records .table thead th {
	font-size: 13px;
	font-weight: 600;
}

#related-module-records .table tbody td {
	font-size: 13px;
	padding: 8px;
}

#related-module-records .table tbody td a {
	font-size: 13px;
}
</style>
<script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
<script type="text/javascript" src="include/js/related-module-modal.js?v=1.1"></script>
{/strip}