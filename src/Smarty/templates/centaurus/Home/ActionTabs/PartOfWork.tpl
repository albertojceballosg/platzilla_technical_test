{extends file='Home/ActionTabs/Base/PartOfWorkLayout.tpl'}
{strip}
    {block name="css"}
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/libs/datepicker.css"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/list-view.css?v=1.1"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/compiled/pipeline.css?v=1.0"/>
        <link type="text/css" rel="stylesheet" href="modules/instancesdatasharing/instancesdatasharing.css?v=1.0"/>
        <link type="text/css" rel="stylesheet" href="themes/centaurus/css/bootstrap/nifty-component.css"/>
        <link rel="stylesheet" type="text/css" href="modules/Home/daily_matrix.css"/>
        <style type='text/css'>
            .md-modal {
                max-width:75% !important;
                min-width:65% !important;
                z-index: 1010 !important;
            }
            .md-effect-7-2 {
                left:35%!important;
                top:0 !important;
            }
            .modal-footer {
                text-align: center;
            }
            #listview-option-part-work:visited {
                background: #f9f8f7 !important;
            }
        </style>

    {/block}
    {block name="page_header"}
        <div class="row module-buttons">
            <div class="col-lg-12"
                 style="padding-right: 10px;padding-bottom: 0;background-color: transparent">
                <div class="pull-left">
                    <a href="index.php?module=Home&action=index&tab=ACTIVITY_REPORT" title="Informe de actividad" style="text-decoration: none">
                    <h1 style="margin-left: -3px;font-weight: bold">Acciones en curso</h1>
                    </a>
                </div>
                <div class="pull-right">
                </div>
            </div>
        </div>
    {/block}
    {block name = "header_title"}
        <div class="row">
            <div class="col-md-12">
                <h2 style="text-align: center">Parte de trabajo</h2>
            </div>
            <div class="col-md-6 pull-left" style="padding: 0 25px"><span style="font-weight: bold">
                    Fecha:&nbsp;</span>{$PERIOD_DATES['startdate']}&nbsp;al&nbsp;{$PERIOD_DATES['enddate']}
            </div>
            <div class="col-md-6" style="padding: 0 25px">
                <button type="button" class="btn btn-primary pull-right"
                        onclick="DataViewUtils.printPartWork (this, 'part_work')"
                        title="Descargar PDF">
                    &nbsp;<i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;</button>
                <input type="hidden" name="report_data" value="{$REPORT_DATA}">
            </div>
        </div>

    {/block}
    {block name="main_box_class"}main-box clearfix{/block}
    {block name="table_type"}{/block}
    {block name = "table_header"}
            {foreach $FIELDS_HEADER as  $label => $data}
                <th class="{$data.class}"
                    style="width:{$data.width}%;vertical-align: top; text-align: {$data.text_align};"
                    colspan="{$data.colspan}">
                    <div style="display: inline-flex;">
                        <div class="title-overflow">
                            <a href="#" title="{$label}" class="title-link" {*onclick=""*}>
                                <span>{$label}</span>
                                {*<i class="fa fa-caret-up" aria-hidden="true" style="margin-left:.5em;"></i>*}
                            </a>
                        </div>
                    </div>
                </th>
            {/foreach}
        {/block}
        {block name = "table_body"}
            {*$TABLE_ROWS|var_dump*}
            {if $TABLE_ROWS neq NULL}
                {foreach $TABLE_ROWS as $row}
                    <tr>
                        {html_row_table fields=$FIELDS_ROWS row_data=$row url_avatar=$URL_AVATARS list_data=$LIST_ROWS}
                    </tr>
                {/foreach}
            {/if}
        {/block}
    {block name="modal_detalview"}
        <div class="md-modal md-effect-7-2" id="modal-detail-row">
        	<div class="md-content">
        		<div class="modal-header">
        			<button class="md-close close">&times;</button>
        			<h4 class="modal-title">Modal title</h4>
        		</div>
        		<div id="modal-detail-body-part-work" data-status="0"  class="modal-body">
        		</div>
        		<div class="modal-footer">
        			<button type="button" class="btn btn-primary md-close" data-status="0">Cerrar</button>
        		</div>
        	</div>
        </div>
        <div class="md-overlay"></div>
    {/block}
    {block name="js"}
        <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/bootstrap-datepicker.es.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/charts/loader.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/notificationFx.js"></script>
        <script type="text/javascript" src="include/jquery/jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="modules/Home/daily-matriz-utils.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/classie.js"></script>
        <script type="text/javascript" src="themes/centaurus/js/modalDetailOverListView.js"></script>
        <script type="text/javascript" src="include/js/list-view.js"></script>
        <script type="text/javascript" src="include/js/ListView.js"></script>
        <script id="detail-over-listview" data-id-modal="{$idModalDetalView}" type="text/javascript" src="themes/centaurus/js/modal-detail-view.js"></script>
        {/block}
{/strip}