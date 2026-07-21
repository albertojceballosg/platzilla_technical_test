{strip}
    {math equation= rand() assign= "idBoxScore"}
    {block name="css"}{/block}
    <div class="md-content" style="max-height: 85vh; overflow-y: auto;">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true"
                        onclick="jQuery ('#addIndicators').removeClass ('md-show'); jQuery ('.md-overlay').css ({ldelim} opacity: 0.0, visibility: 'hidden' {rdelim}); jQuery ('#addIndicators').html(''); return false;">
                    ×
                </button>
                <h4 class="modal-title">{if (isset ($RECORD))}{$MOD.MESS_EDIT_BOX_SCORE}{else}{$MOD.MESS_ADD_BOX_SCORE}{/if}</h4>
            </div>
            <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                <div class="row">
                    <form role="form" name="{$MODULE}" id="bs-form-{$idBoxScore}" action="index.php" method="post">
                        <input type="hidden" name="type"
                               value="{if ($boxScoreType)}{$boxScoreType}{else}{$TYPE}{/if}">
                        <input type="hidden" name="record" id="record" value="{$record}">
                        <input type="hidden" name="boxscoreid" value="{$ACCOUNT_ID}">
                        <input type="hidden" name="monthsearch" value="{$MONTH_SEARCH}">
                        <input type="hidden" name="box_score_objectiveid" value="{$boxScoreObjectiveId}">
                        <input type="hidden" id="create_indicator" name="create_indicator" value="1">
                        <input type="hidden" id="action" name="action" value="SaveBox">
                        <input type="hidden" id="module" name="module" value="{$MODULE}">
                        <input type="hidden" id="app" name="app" value="{$CODE_APP}">
                        <input type="hidden" id="mode" name="mode" value="{$MODE}">
                        <input type="hidden" id="box_score_name" name="box_score_name" value="{$boxScoreName}">
                        <input type="hidden" id="viewScale" name="viewScale" value="{$VIEW_SEARCH}">
                        <input type="hidden" id="objetive_scale-{$idBoxScore}" name="objetive_scale" value="{$objectiveScale}">
                        {if $IS_HOME neq NULL}
                            <input type="hidden" name="is_home" value="1">
                        {/if}
                        <div id="identification-{$idBoxScore}" class="col-lg-12 col-md-12 col-sm-12">
                            {block name="identification"}{/block}
                        </div>
                        <div id="selected_ranges-{$idBoxScore}" class="col-lg-12 col-md-12 col-sm-12 border-dark"
                             style="background-color: #eeeeee;padding:3px 0">
                            {block name="selected_ranges"}{/block}
                        </div>
                        <div id="date_ranges-{$idBoxScore}" class="col-lg-12 col-md-12 col-sm-12">
                            {block name="date_ranges"}{/block}
                        </div>
                        <div id="objective_goals-{$idBoxScore}" class="col-lg-12 col-md-12 col-sm-12">
                            {block name="objective_goals"}{/block}
                        </div>
                        <div id="loading_method-{$idBoxScore}" class="col-lg-12 col-md-12 col-sm-12">
                            {block name="loading_method"}{/block}
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    {block name="modal-footer"}{/block}
                </div>
            </div>
        </div>
    </div>
    {block name="js"}{/block}
    {block name="script_template"}{/block}
{/strip}