{extends file='Home/ActionTabs/Base/PartOfWorkLayout.tpl'}
{strip}
    {block name="css"}{/block}
    {block name="page_header"}{/block}
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
                        onclick="DataViewUtils.printPartWork ('part_work')"
                        title="Descargar PDF">
                    &nbsp;<i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;
                </button>
                <input type="hidden" name="report_data" value="{$REPORT_DATA}">
            </div>
        </div>
    {/block}
    {block name="main_box_class"}{/block}
    {block name="table_type"}table-bordered{/block}
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
        {if $TABLE_ROWS neq NULL}
            {foreach $TABLE_ROWS as $row}
                <tr class="part-work-pdf">
                    {html_row_table fields=$FIELDS_ROWS row_data=$row url_avatar=$URL_AVATARS list_data=$LIST_ROWS}
                </tr>
                {* row to fill by user *}
                <tr class="part-work-pdf">
                    <td colspan="6" style="text-align: left; background-color:#f9f8f7"><strong>PARTE DE TRABAJO</strong></td>
                </tr>
                <tr class="part-work-pdf">
                    <td colspan="6" style="margin: 0;padding: 0!important;">
                        <table style="width: 100%;margin: 0; padding: 0;font-size: small">
                            <tr>
                                <td style="text-align: center; width: 4%">
                                </td>
                                <td style="text-align: left;width: 10%">Tiempo</td>
                                <td style="text-align: left;width: 37%">Materiales</td>
                                <td style="text-align: left;width: 37%">Observaciones</td>
                                <td style="text-align: left;width: 12%">Firma</td>
                            </tr>
                            <tr>
                                <td style="text-align: center; width: 4%">
                                    <span><i class="fa fa-square-o" aria-hidden="true"></i></span>
                                </td>
                                <td style="text-align: left;width: 10%"><br><br><br><br><br></td>
                                <td style="text-align: left;width: 37%"><br><br><br><br><br></td>
                                <td style="text-align: left;width: 37%"><br><br><br><br><br></td>
                                <td style="text-align: left;width: 12%"><br><br><br><br><br></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            {/foreach}
        {/if}
    {/block}
    {block name="js"}{/block}
{/strip}