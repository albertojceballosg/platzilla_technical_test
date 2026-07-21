{strip}
    {block name="css"}{/block}
    <div aria-multiselectable="true" class="panel-group" role="tablist" id="accordion">
        {foreach $DAILY_REPORT_SECTIONS as $idSection => $section}
            {math equation= rand() assign= "idPanel"}
            <div class="panel panel-primary">
                <div class="panel-heading" id="panel-heading-{$idPanel}" role="tab">
                    <h4 class="panel-title" style="text-decoration: none!important;">
                        <a style="text-decoration: none!important; text-underline: none ;"
                            aria-controls="{$idSection}-{$idPanel}" aria-expanded="true" data-parent="#accordion"
                            class="daily-report" rel="{$idPanel}" data-toggle="collapse" href="#{$idSection}-{$idPanel}"
                            role="button">
                            {$section}
                        </a>
                    </h4>
                </div>
                <div aria-labelledby="heading1" class="panel-collapse collapse in" id="{$idSection}-{$idPanel}" role="tabpanel">
                    <div class="panel-body">
                        {block name="{$idSection}"}{/block}
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
    {block name="script"}{/block}
    {block name="script_template"}{/block}
{/strip}