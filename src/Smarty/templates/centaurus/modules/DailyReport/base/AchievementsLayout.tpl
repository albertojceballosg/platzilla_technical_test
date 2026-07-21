{strip}
    {math equation= rand() assign= "idAchievements"}
    <div class="row-grid-view justify-content-center">
        <div class="col-md-12" {if $VIEW neq NULL}style="margin-top: 0"{/if}>
            <div class="table-responsive">
                <table id="achievements_day-table" class="table table-bordered tablegridvalidate">
                    <thead>
                    {block name="header_achievements_day_table"}{/block}
                    </thead>
                    <tbody id="{block name="id_tbody_table"}{/block}{$idAchievements}" rowtotal="0">
                    {block name="body_achievements_day_table"}{/block}
                    </tbody>
                    <tfoot id="tfoot-{$idAchievements}" data-field-name="achievements_day" data-realted-summary-field=""
                           data-summary-row=""
                           data-operation-row="">
                    <tr>
                        {block name="add-achievements_day-row"}{/block}
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    {block name="script_template"}{/block}
{/strip}