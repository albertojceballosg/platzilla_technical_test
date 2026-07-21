{*$AVAILABLE_PROCESS|var_dump*}
<style>
        .border{
            border:1px solid #dee2e6!important
        }
    </style>
<div class="col-md-12">
<div class="col-md-6">
    <div class="col-md-4">
        <div class="label-input">
            <label for="process_area" class="">
                <span id="p1_business_processes"></span>&nbsp;Modelo de proceso a seguir</label>
        </div>
    </div>
    <div class="form-group col-md-8 field-container" id="td_business_processes">
        <select id="business_processes border" name="business_processes" class="form-control for-filter" tabindex="">
            <option value="" selected="selected">Modelo de procesos</option>
            {if $AVAILABLE_PROCESS neq NULL}
                {foreach $AVAILABLE_PROCESS  as $process}
                    <option value="{$process.processid}">{$process.process_title}</option>
                {/foreach}
            {/if}
        </select>
    </div>
</div>
    <div class="col-md-6">&nbsp;</div>
</div>