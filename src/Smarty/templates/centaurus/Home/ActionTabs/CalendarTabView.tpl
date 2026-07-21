{strip}
    {math equation= rand() assign= "idCalendar"}
    <div class="row" id="calendrer-tab-view-{$idCalendar}">
        {*   calendario     *}
        <div class="col-md-12">
           <div class="main-box" style="margin-top: 0; overflow-x: hidden">
                <div class="main-box-body clearfix" style="padding: 0; overflow-x: hidden">
                    <hr>
                    <div class="calendar-empty-state calendar-empty-hidden" id="calendar-empty-{$idCalendar}">
                        <div class="calendar-empty-icon">
                            <i class="fa fa-calendar-times-o" aria-hidden="true"></i>
                        </div>
                        <h4>{$APP.LBL_HOME_CALENDAR_EMPTY_TITLE|default:'Sin datos para mostrar'}</h4>
                        <p>{$APP.LBL_HOME_CALENDAR_EMPTY_DESC|default:'Ajusta los filtros, el período o asigna nuevos registros para ver eventos en este calendario.'}</p>
                    </div>
                    <div class="fc fc-ltr" id="calendar-{$idCalendar}"></div>
                </div>
           </div>
        </div>
    </div>
    {*Modal para crear/editar actividad*}
    <div class="modal fade" id="activity-modal-{$idCalendar}" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="width: {$MODAL_DIMENSIONS['width']|default:'850px'}; max-width: 95%;">
            <div class="modal-content" style="height: {$MODAL_DIMENSIONS['height']|default:'auto'};">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{if $FLMODULE eq 'orden_de_trabajo'}Nueva tarea{else}Nueva Acción{/if}</h4>
                </div>
                <div class="modal-body">
                    {include file='Home/ActionTabs/ActivityModal.tpl'}
                </div>
                <div class="modal-footer">
                    <input type="button" value="Guardar" id="task-create-btn-{$idCalendar}"
                                       style="font-size: 15px!important;;margin-left: 0.1em"
                                        class="btn btn-primary add_button">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
    {* Estilos específicos para el datepicker en la modal *}
    <style>
        .calendar-empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1rem;
            min-height: 45vh;
            background: #f8fafc;
            border: 1px dashed #cbd5e0;
            border-radius: 10px;
            color: #4a5568;
            margin: 1rem auto;
            max-width: 100%;
        }
        .calendar-empty-state h4 {
            font-weight: 600;
            margin-top: 1rem;
            margin-bottom: .25rem;
            color: #1f3a56;
        }
        .calendar-empty-state p {
            max-width: 420px;
            margin: 0 auto;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .calendar-empty-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: #e2ecf7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #1f7bd8;
        }
        .calendar-empty-hidden {
            display: none !important;
        }
        #calendrer-tab-view-{$idCalendar} .main-box-body {
            min-height: 55vh;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: #e8e8e8 transparent;
        }
        #calendrer-tab-view-{$idCalendar} .main-box-body::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        #calendrer-tab-view-{$idCalendar} .main-box-body::-webkit-scrollbar-track {
            background: transparent;
        }
        #calendrer-tab-view-{$idCalendar} .main-box-body::-webkit-scrollbar-thumb {
            background: #e8e8e8;
            border-radius: 4px;
        }
        #calendrer-tab-view-{$idCalendar} .main-box-body::-webkit-scrollbar-thumb:hover {
            background: #d0d0d0;
        }
        #calendrer-tab-view-{$idCalendar} .main-box-body::-webkit-scrollbar-corner {
            background: transparent;
        }
        #calendar-{$idCalendar}.calendar-empty-hidden {
            min-height: 0;
            height: 0;
            overflow: hidden;
        }
        #activity-modal-{$idCalendar} .datepicker {
            z-index: 1060 !important; /* Asegurar que aparezca sobre la modal */
        }
        #activity-modal-{$idCalendar} .datepicker-dropdown {
            margin-top: 0;
        }
        /* Prevenir que los campos se oculten */
        #activity-modal-{$idCalendar} .datepickerDate-{$idCalendar} {
            display: block !important;
        }
        #activity-modal-{$idCalendar} .input-group {
            display: inline-flex !important;
            margin: 0 !important;
        }
        /* Mantener el espacio para los campos ocultos */
        #activity-modal-{$idCalendar} .activity-date-{$idCalendar},
        #activity-modal-{$idCalendar} .activity-time-{$idCalendar} {
           /* visibility: hidden;
            opacity: 0;
            transition: opacity 0.2s;*/
            position: absolute;

        }
        #activity-modal-{$idCalendar} .activity-date-{$idCalendar}.show,
        #activity-modal-{$idCalendar} .activity-time-{$idCalendar}.show {
            /*visibility: visible;
            opacity: 1;*/
            position: relative;
        }
    </style>
    <script>
        {literal}
    jQuery(document).ready(function() {});
        if (window.CalendarManager) {
            CalendarManager.init({
                currentModule: '{/literal}{$MODULE}{literal}',
                currentViewId: '{/literal}{$idCalendar}{literal}',
                type: '{/literal}{$CALENDAR_TYPE}{literal}',
                currentLangCode: 'es',
                events: {/literal}{$CALENDER_DATA|json_encode}{literal}
            });
        }
        {/literal}
    </script>
{/strip}