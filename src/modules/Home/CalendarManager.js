/**
 * CalendarManager.js
 * Gestión integrada del calendario para Platzilla
 * ÚLTIMA MODIFICACIÓN: 2026-01-23 12:40
 */

(function (jQuery) {
  "use strict";
  
  //console.log("=== CalendarManager.js CARGADO - Versión 2026-01-23 12:40 ===");

  // Variables globales para almacenar fechas originales de eventos arrastrados
  var draggedEventOriginalStart = null;
  var draggedEventOriginalEnd = null;

  const CalendarManager = {
    init: function (config) {
      this.currentModule = config.currentModule;
      this.currentViewId = config.currentViewId;
      this.currentLangCode = config.currentLangCode || "es";
      this.currentType = config.type;
      this.config = config;
      this.initCalendar();
      this.bindEvents();
      //console.log(this.config);
    },

    toggleEmptyState: function () {
      let hasEvents = false;
      if (this.$calendarEl && this.$calendarEl.fullCalendar) {
        const events = this.$calendarEl.fullCalendar('clientEvents');
        hasEvents = Array.isArray(events) && events.length > 0;
      } else {
        hasEvents = Array.isArray(this.config.events) && this.config.events.length > 0;
      }
      if (hasEvents) {
        this.$emptyState.addClass('calendar-empty-hidden');
        this.$calendarEl.removeClass('calendar-empty-hidden');
      } else {
        this.$calendarEl.addClass('calendar-empty-hidden');
        this.$emptyState.removeClass("calendar-empty-hidden");
      }
    },

    // Inicialización del calendario
    initCalendar: function () {
      const self = this;
      this.$calendarEl = jQuery("#calendar-" + this.currentViewId);
      this.$emptyState = jQuery("#calendar-empty-" + this.currentViewId);

      this.$calendarEl.fullCalendar({
        firstDay: 1,
        header: {
          left: "prev,next today",
          center: "title",
          right: "month,agendaWeek,agendaDay",
        },
        lang: this.currentLangCode,
        isRTL: jQuery("body").hasClass("rtl"),
        monthNames: [
          "Enero",
          "Febrero",
          "Marzo",
          "Abril",
          "Mayo",
          "Junio",
          "Julio",
          "Agosto",
          "Septiembre",
          "Octubre",
          "Noviembre",
          "Diciembre",
        ],
        monthNamesShort: [
          "Ene",
          "Feb",
          "Mar",
          "Abr",
          "May",
          "Jun",
          "Jul",
          "Ago",
          "Sep",
          "Oct",
          "Nov",
          "Dec",
        ],
        dayNames: [
          "Domingo",
          "Lunes",
          "Martes",
          "Miércoles",
          "Jueves",
          "Viernes",
          "Sábado",
        ],
        dayNamesShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
        buttonText: {
          prev: "<span class='fc-text-arrow'>&lsaquo;</span>",
          next: "<span class='fc-text-arrow'>&rsaquo;</span>",
          prevYear: "<span class='fc-text-arrow'>&laquo;</span>",
          nextYear: "<span class='fc-text-arrow'>&raquo;</span>",
          today: "Hoy",
          month: "Mes",
          week: "Semana",
          day: "Día",
        },
        selectable: true,
        selectHelper: true,
        editable: true,
        droppable: true,
        events: this.config.events || [],
        eventAfterAllRender: function () {
          self.toggleEmptyState();
        },

        // Guardar fechas originales al iniciar drag
        eventDragStart: function (event) {
          draggedEventOriginalStart = event.start
            ? new Date(event.start.getTime())
            : null;
          draggedEventOriginalEnd = event.end
            ? new Date(event.end.getTime())
            : null;
        },

        // Crear nueva actividad
        select: function (start, end) {
          //console.log("[CalendarManager] FullCalendar select triggered", {
          //  currentType: self.currentType,
          //  currentViewId: self.currentViewId,
          //  currentModule: self.currentModule,
         //   rawStart: start ? start.toISOString() : null,
         //   rawEnd: end ? end.toISOString() : null,
         // });
          if (self.currentType === "task") {
            ///console.log("[CalendarManager] Proceeding to task creation modal");
            self.handleActivityCreation(start, end);
          } else {
            //console.log("[CalendarManager] Opening EditView for non-task type", { module: self.currentModule });
            var url =
              "index.php?module=" +
              self.currentModule +
              "&action=EditView&return_action=DetailView&parenttab=Control&mode=create";
            window.open(url, "_blank");
          }
        },

        // Actualizar al arrastrar
        eventDrop: function (event, dayDelta, minuteDelta, allDay, revertFunc) {
          self.handleEventDrop(event, dayDelta, revertFunc);
        },

        // Actualizar al redimensionar
        eventResize: function (event, dayDelta, minuteDelta, revertFunc) {
          self.handleEventResize(event, dayDelta, revertFunc);
        },
      });
    },

    // Manejador para crear actividad
    handleActivityCreation: function (start, end) {
      //console.log("[CalendarManager] handleActivityCreation ENTRY", {
       // start: start ? start.toISOString() : null,
       // end: end ? end.toISOString() : null,
       // currentViewId: this.currentViewId,
      //});

      const modalId = "#activity-modal-" + this.currentViewId;
      const startDate = moment(start).format("YYYY-MM-DD");
      const endDate = moment(end).format("YYYY-MM-DD");
      const self = this;

      //console.log("[CalendarManager] Dates prepared", {
      //  modalId,
      //  startDate,
      //  endDate,
      //});

      // Establecer las fechas en los campos
      //console.log("[CalendarManager] Setting date fields", {
      //  dateStartSelector: "#date_start-" + this.currentViewId,
      //  dueDateSelector: "#due_date-" + this.currentViewId,
      //  startDate,
      //  endDate,
      //});
      jQuery("#date_start-" + this.currentViewId).val(startDate);
      jQuery("#due_date-" + this.currentViewId).val(endDate);

      // Configurar el modal
      //console.log("[CalendarManager] Initializing modal", { modalId });
      jQuery(modalId)
        .modal({
          backdrop: "static",
          keyboard: false,
          show: true,
        })
        .on("shown.bs.modal", function () {
          //console.log("[CalendarManager] Modal shown.bs.modal fired", {
          //  modalId,
          //  viewId: self.currentViewId,
         // });
          const $modal = jQuery(this);
          const $estTime = jQuery("#estimated_time-" + self.currentViewId);
          const $estCost = jQuery("#estimated_cost-" + self.currentViewId);

         // console.log("[CalendarManager] Modal shown, applying localized formatting", {
         //   modalId,
         //   estimatedTimeValue: $estTime.val(),
         //   estimatedCostValue: $estCost.val(),
         // });

          try {
            // Formatear valores numéricos según preferencias del usuario
            // CalendarManager.formatLocalizedNumericFields($modal); // Function not implemented
            //console.log("[CalendarManager] Numeric fields formatted");

            //console.log("[CalendarManager] Modal values after formatting", {
            //  formattedEstimatedTime: $estTime.val(),
            //  formattedEstimatedCost: $estCost.val(),
            //});
            //console.log("[CalendarManager] Modal display complete");

            // Aplicar dirección inteligente a dropdowns
            CalendarManager.applySmartDropdownDirection($modal);
            //console.log("[CalendarManager] Smart dropdown direction applied");

            // Inicializar datepickers
            jQuery(".datepickerDate-" + self.currentViewId).datepicker({
              format: "yyyy-mm-dd",
              autoclose: true,
              todayHighlight: true,
              language: "es",
              startDate: moment().format("YYYY-MM-DD"),
              container: modalId,
            });
            //console.log("[CalendarManager] Datepickers initialized");

            // Configurar validación de fechas
            const $dateStart = jQuery("#date_start-" + self.currentViewId);
            const $dueDate = jQuery("#due_date-" + self.currentViewId);
            const $timeStart = jQuery("#start_time-" + self.currentViewId);
            const $activityType = jQuery("#activitytype-" + self.currentViewId);

            // Función para manejar la visibilidad de los campos según el tipo de actividad
            function handleActivityTypeChange() {
              const isAssignment = $activityType.val() === "Assignment";
              const $timeStartGroup = $timeStart.parent();
              const $dueDateGroup = $dueDate.parent();

              if (isAssignment) {
                $timeStartGroup.addClass("hidden");
                $dueDateGroup.removeClass("hidden");
                $timeStart.val(""); // Limpiar el valor cuando se oculta
              } else {
                $timeStartGroup.removeClass("hidden");
                $dueDateGroup.addClass("hidden");
                $dueDate.val(""); // Limpiar el valor cuando se oculta
              }
            }

            // Manejar el cambio de tipo de actividad
            $activityType
              .off("change.activityType")
              .on("change.activityType", handleActivityTypeChange);

            // Ejecutar la lógica inicial inmediatamente
            handleActivityTypeChange();
            //console.log("[CalendarManager] Activity type handler configured");

            // Validación de fechas para el datepicker
            $dateStart.on("changeDate", function (e) {
              const startDate = moment(e.date);
              const dueDate = moment($dueDate.val(), "YYYY-MM-DD");

              if (dueDate.isBefore(startDate)) {
                $dueDate.datepicker("setDate", startDate.toDate());
              }

              $dueDate.datepicker("setStartDate", startDate.toDate());
            });
            //console.log("[CalendarManager] Date validation configured");
          } catch (error) {
            console.error("[CalendarManager] ERROR during modal initialization:", error);
          }

          // Implementar la funcionalidad para el menú de usuarios
          const $userMenu = jQuery(
            "#detailview-task-user-" + self.currentViewId
          );
          const $inviteesField = jQuery("#inviteesid-" + self.currentViewId);
          const $helpUserText = jQuery("#help-user-" + self.currentViewId);
          const $importance = jQuery(
            "#detailview-task-importance-" + self.currentViewId
          );
          const $taskImport = jQuery("#taskImport-" + self.currentViewId);
          const $priority = jQuery(
            "#detailview-task-priority-" + self.currentViewId
          );
          const $taskPriority = jQuery("#taskpriority-" + self.currentViewId);
          const $categories = jQuery(
            "#detailview-task-categories-" + self.currentViewId
          );
          const $categoryId = jQuery("#categoryid-" + self.currentViewId);
          const $categoryName = jQuery("#categoryname-" + self.currentViewId);
          const $select = jQuery("#reported_task_module-" + self.currentViewId);

          /*console.log("[CalendarManager] Dropdown elements found", {
            userMenu: $userMenu.length,
            importance: $importance.length,
            priority: $priority.length,
            categories: $categories.length,
          });
          */

          // Manejar clic en los elementos de usuarios sin cerrar el menú, usando namespacing
          $userMenu
            .off("click")
            .on("click.calendarUserMenu", "a", function (e) {
              e.preventDefault();
              e.stopPropagation();
              /*console.log("[CalendarManager] User menu item clicked", {
                target: e.target,
                currentTarget: e.currentTarget,
              });*/
              const $link = jQuery(this);
              const $listItem = $link.parent("li");

              // Solo si está dentro de la modal
              if (
                $link.closest("#activity-modal-" + self.currentViewId).length
              ) {
                /*console.log("[CalendarManager] User selection processing", {
                  userId: $link.attr("rel"),
                  userName: $link.attr("title"),
                });*/
                // Toggle selección
                $listItem.toggleClass("active");

                // Actualizar el color de fondo del elemento seleccionado
                if ($listItem.hasClass("active")) {
                  $listItem.css("background-color", "#0165a8");
                } else {
                  $listItem.css("background-color", "");
                }

                // Recopilar todos los IDs seleccionados
                const selectedUserIds = [];
                const selectedUserTitles = [];
                $userMenu.find("li.active a").each(function () {
                  const id = jQuery(this).attr("rel");
                  const title = jQuery(this).attr("title");
                  if (id) {
                    selectedUserIds.push(id);
                  }
                  if (title) {
                    selectedUserTitles.push(title);
                  }
                });

                // Actualizar el campo de entrada con los IDs seleccionados
                $inviteesField.val(selectedUserIds.join(";"));

                // Actualizar el texto de ayuda con los títulos seleccionados
                if (selectedUserTitles.length > 0) {
                  $helpUserText.html(selectedUserTitles.join("'"));
                } else {
                  $helpUserText.html("");
                }

                // Actualizar el estado del botón según si hay selecciones
                const $userBtn = jQuery(
                  "#btn-group-user-" + self.currentViewId
                );
                if (selectedUserIds.length > 0) {
                  $userBtn.addClass("btn-selected");
                } else {
                  $userBtn.removeClass("btn-selected");
                }

                CalendarManager.updateDropdownLabel(
                  $userBtn,
                  CalendarManager.formatUserDropdownLabel(selectedUserTitles)
                );
                /*console.log("[CalendarManager] User selection complete", {
                  selectedIds: selectedUserIds.join(";"),
                  selectedNames: selectedUserTitles,
                });*/
              }
            });

          $importance.off("click").on("click", "a", function (e) {
            e.preventDefault();
            /*console.log("[CalendarManager] Importance menu item clicked", {
              target: e.target,
            });*/
            const $link = jQuery(this);
            const $listItem = $link.parent("li");
            const $btnImportance = jQuery(
              "#btn-group-importance-" + self.currentViewId
            );

            if ($link.closest("#activity-modal-" + self.currentViewId).length) {
              /*console.log("[CalendarManager] Importance selection processing", {
                value: $link.attr("rel"),
                text: $link.text().trim(),
              });*/
              $listItem.toggleClass("active");
              $taskImport.val($link.attr("rel"));
              $importance.find("li").not($listItem).removeClass("active");

              if ($listItem.hasClass("active")) {
                $btnImportance.addClass("btn-selected");
              } else {
                $btnImportance.removeClass("btn-selected");
                $taskImport.val("");
              }

              CalendarManager.updateDropdownLabel(
                $btnImportance,
                $listItem.hasClass("active")
                  ? $link.text().trim() || $link.attr("title")
                  : null
              );
              //console.log("[CalendarManager] Importance selection complete", {
              //  selectedValue: $taskImport.val(),
              //  isActive: $listItem.hasClass("active"),
              //});
              // Cerrar el dropdown después de la selección
              $btnImportance.dropdown("toggle");
            }
          });

          $priority.off("click").on("click", "a", function (e) {
            e.preventDefault();
            /*console.log("[CalendarManager] Priority menu item clicked", {
              target: e.target,
            });*/
            const $link = jQuery(this);
            const $listItem = $link.parent("li");
            const $btnPriority = jQuery(
              "#btn-group-priority-" + self.currentViewId
            );

            if ($link.closest("#activity-modal-" + self.currentViewId).length) {
              /*console.log("[CalendarManager] Priority selection processing", {
                value: $link.attr("rel"),
                text: $link.text().trim(),
              });*/
              $listItem.toggleClass("active");
              $taskPriority.val($link.attr("rel"));
              $priority.find("li").not($listItem).removeClass("active");

              if ($listItem.hasClass("active")) {
                $btnPriority.addClass("btn-selected");
              } else {
                $btnPriority.removeClass("btn-selected");
                $taskPriority.val("");
              }

              CalendarManager.updateDropdownLabel(
                $btnPriority,
                $listItem.hasClass("active")
                  ? $link.text().trim() || $link.attr("title")
                  : null
              );
              /*console.log("[CalendarManager] Priority selection complete", {
                selectedValue: $taskPriority.val(),
                isActive: $listItem.hasClass("active"),
              });*/
              // Cerrar el dropdown después de la selección
              $btnPriority.dropdown("toggle");
            }
          });

          $categories.off("click").on("click", "a", function (e) {
            e.preventDefault();
            /*console.log("[CalendarManager] Category menu item clicked", {
              target: e.target,
            });*/
            const $link = jQuery(this);
            const $listItem = $link.parent("li");
            const $btnCategories = jQuery(
              "#btn-group-task-categories-" + self.currentViewId
            );

            if ($link.closest("#activity-modal-" + self.currentViewId).length) {
              /*console.log("[CalendarManager] Category selection processing", {
                value: $link.attr("rel"),
                text: $link.text().trim(),
              });*/
              $listItem.toggleClass("active");
              $categoryId.val($link.attr("rel"));
              $categories.find("li").not($listItem).removeClass("active");

              if ($listItem.hasClass("active")) {
                $btnCategories.addClass("btn-selected");
              } else {
                $btnCategories.removeClass("btn-selected");
                $categoryId.val("");
              }
              if ($categoryId.val() === "0") {
                $categoryName.removeClass("hide");
              } else {
                $categoryName.addClass("hide");
                $categoryName.val("");
              }

              const categoryLabel =
                $categoryId.val() === "0"
                  ? CalendarManager.translateLabel("Crear grupo")
                  : $link.text().trim() || $link.attr("title");
              CalendarManager.updateDropdownLabel(
                $btnCategories,
                $listItem.hasClass("active") ? categoryLabel : null
              );
              /*console.log("[CalendarManager] Category selection complete", {
                selectedValue: $categoryId.val(),
                isActive: $listItem.hasClass("active"),
              });*/
              // Cerrar el dropdown después de la selección
              $btnCategories.dropdown("toggle");
            }
          });

          //console.log("[CalendarManager] All dropdown event handlers registered");

          // Cerrar dropdowns solo dentro de la modal al hacer clic fuera
          jQuery(document)
            .off("click.closeDropdowns")
            .on("click.closeDropdowns", function (e) {
              // Solo si no se ha hecho clic en un elemento .btn-group dentro de nuestra modal
              if (
                !jQuery(e.target).closest(
                  "#activity-modal-" + self.currentViewId + " .btn-group"
                ).length
              ) {
                $modal.find(".dropdown-menu").removeClass("show");
                $modal.find(".dropdown-toggle").removeClass("active");
              }
            });

          if ($select) {
            $select.off("change").on("change", function (e) {
              var module = jQuery(this),
                forModule = jQuery("#formodule-" + self.currentViewId),
                record = jQuery("#module_related_record" + self.currentViewId),
                tabName = jQuery("#module_related-" + self.currentViewId),
                dummy,
                label;

              if (module.val() !== "") {
                dummy = module.val().split("@");
                //console.log(dummy);
                module.attr("data-referenced-module", dummy[0]);
                label = module.find("option:selected").text();
                module.attr("data-title", label);
                tabName.val(dummy[0]);
                forModule.val(dummy[0]);
                record.val("");
                RelatedModuleModalUtils.openModal(this);
              } else {
                module.attr("data-referenced-module", "");
                module.attr("data-title", "");
                record.val("");
                forModule.val("");
              }
            });
          }

          // Listener para actualizar el display cuando se selecciona un registro relacionado
          jQuery(document).off("relatedModuleRecordSelected.calendarManager").on("relatedModuleRecordSelected.calendarManager", function(event, title, displayFieldId, dataFieldId) {
            var $displayField = jQuery("#" + displayFieldId);
            var $dataField = jQuery("#" + dataFieldId);
            
            if ($displayField.length > 0 && $dataField.length > 0) {
              var recordName = $displayField.val() || $displayField.text();
              var $selectedRecordSpan = jQuery("#selected_record_display-" + self.currentViewId);
              
              if ($selectedRecordSpan.length > 0 && recordName) {
                $selectedRecordSpan.text(recordName).attr("title", recordName);
              }
            }
          });

          CalendarManager.applySmartDropdownDirection($modal);
        })
        .on("hidden.bs.modal", function () {
          const $modal = jQuery(this);

          // Resetear labels y estilos de dropdowns
          CalendarManager.resetDropdownLabels($modal);

          // Destruir los datepickers
          jQuery(".datepickerDate-" + self.currentViewId).datepicker("destroy");

          // Limpiar eventos
          jQuery(document).off("click.closeDropdowns");
          jQuery("#activitytype-" + self.currentViewId).off(
            "change.activityType"
          );

          // Resetear el formulario excepto los campos que se actualizan por defecto
          const $form = $modal.find("form");
          const $dateStart = jQuery("#date_start-" + self.currentViewId);
          const $dueDate = jQuery("#due_date-" + self.currentViewId);
          const startVal = $dateStart.val();
          const dueVal = $dueDate.val();

          $form[0].reset();

          // Restaurar formato numérico para valores por defecto
          // CalendarManager.formatLocalizedNumericFields($modal); // Function not implemented

          // Restaurar las fechas
          $dateStart.val(startVal);
          $dueDate.val(dueVal);

          // Resetear los campos de entrada ocultos específicamente
          jQuery("#inviteesid-" + self.currentViewId).val("");
          jQuery("#taskImport-" + self.currentViewId).val("");
          jQuery("#taskpriority-" + self.currentViewId).val("");
          jQuery("#categoryid-" + self.currentViewId).val("");
          
          // Resetear el display del registro seleccionado
          jQuery("#selected_record_display-" + self.currentViewId).text("").attr("title", "");
          jQuery("#module_related_record-" + self.currentViewId).val("");
          jQuery("#reported_task_module-" + self.currentViewId).val("").attr("data-referenced-module", "").attr("data-title", "");
          jQuery("#formodule-" + self.currentViewId).val("");
          
          // Limpiar el listener del evento de selección de registro
          jQuery(document).off("relatedModuleRecordSelected.calendarManager");

          // Resetear cualquier texto de ayuda
          jQuery("#help-user-" + self.currentViewId).html("");
          jQuery("#help-priority-" + self.currentViewId).html("");
          jQuery("#help-status-" + self.currentViewId).html("");
          jQuery("#help-group-" + self.currentViewId).html("");

          // Resetear estados visuales de los elementos seleccionados
          $modal
            .find(".dropdown-menu li")
            .removeClass("active")
            .css("background-color", "");
          $modal.find(".dropdown-toggle").css({
            "background-color": "",
            color: "",
          });

          // Limpiar clases de los dropdowns
          $modal.find(".dropdown-menu").removeClass("show");
          $modal.find(".dropdown-toggle").removeClass("active");
          $modal.find('[data-toggle="dropdown"]').dropdown();
          CalendarManager.resetDropdownLabels($modal);
        });
    },

    // Manejar arrastre de evento
    handleEventDrop: function (event, delta, revertFunc) {
      this.updateEventDates(
        {
          activity_id: event.id,
          crmid_entity: event.crmid,
          calendar_type: this.currentType,
          fl_module: this.currentModule,
          start_date: draggedEventOriginalStart
            ? moment(draggedEventOriginalStart).format("YYYY-MM-DD")
            : "",
          due_date: draggedEventOriginalEnd
            ? moment(draggedEventOriginalEnd).format("YYYY-MM-DD")
            : "",
          new_start_date: event.start
            ? moment(event.start).format("YYYY-MM-DD")
            : "",
          new_due_date: event.end ? moment(event.end).format("YYYY-MM-DD") : "",
        },
        revertFunc
      );
      draggedEventOriginalStart = null;
      draggedEventOriginalEnd = null;
    },

    // Manejar redimensionamiento de evento
    handleEventResize: function (event, delta, revertFunc) {
      this.updateEventDates(
        {
          activity_id: event.id,
          new_start_date: event.start
            ? moment(event.start).format("YYYY-MM-DD")
            : "",
          new_due_date: event.end ? moment(event.end).format("YYYY-MM-DD") : "",
        },
        revertFunc
      );
    },

    // Mostrar notificación en la celda
    showCellNotification: function (event, message, isError) {
      const calendar = jQuery("#calendar-" + this.currentViewId);

      // Buscar el elemento del evento
      let eventElement = calendar.find('.fc-event[data-id="' + event.id + '"]');
      if (eventElement.length === 0) {
        eventElement = calendar.find(
          '.fc-event[data-event-id="' + event.id + '"]'
        );
      }
      if (eventElement.length === 0) {
        eventElement = calendar.find(
          '.fc-event:contains("' + event.title + '")'
        );
      }

      if (eventElement.length > 0) {
        // Remover cualquier notificación existente
        eventElement.find(".calendar-notification").remove();

        // Crear el contenedor de la notificación
        const notificationContainer = jQuery("<div>")
          .addClass("calendar-notification-container")
          .css({
            width: "100%",
            position: "relative",
            "margin-top": "2px",
          });

        // Crear el elemento de notificación
        const notification = jQuery("<div>")
          .addClass("calendar-notification")
          .css({
            color: isError ? "#FF0000" : "#008000",
            background: "rgba(255, 255, 255, 0.9)",
            padding: "2px 4px",
            "text-align": "center",
            "font-size": "11px",
            "border-radius": "2px",
            "margin-top": "2px",
            "white-space": "normal",
            "line-height": "1.2",
          })
          .text(message);

        // Agregar la notificación al contenedor
        notificationContainer.append(notification);

        // Encontrar el contenedor del título del evento
        const titleContainer = eventElement.find(".fc-title");

        // Si encontramos el contenedor del título, insertamos después de él
        if (titleContainer.length > 0) {
          titleContainer.after(notificationContainer);
        } else {
          // Si no hay contenedor de título, lo agregamos al final del evento
          eventElement.append(notificationContainer);
        }

        // Remover la notificación después de 3 segundos
        setTimeout(function () {
          notificationContainer.fadeOut(400, function () {
            notificationContainer.remove();

            // Restaurar la altura original del evento si es necesario
            eventElement.css("height", "");
            eventElement.find(".fc-content").css("height", "");
          });
        }, 3000);
      } else {
        // Si no encontramos el elemento, mostrar un alert como fallback
        alert(message);
      }
    },

    // Actualizar fechas del evento
    updateEventDates: function (data, revertFunc) {
      const self = this;
      jQuery.ajax({
        url: "index.php",
        type: "POST",
        data: {
          module: "Home",
          action: "AjaxDeskUtils",
          function: "CALENDAR_UPDATE_DATE",
          Ajax: "true",
          ...data,
        },
        success: function (response) {
          if (response.error !== "OK") {
            if (typeof revertFunc === "function") {
              revertFunc();
            }
            // Mostrar mensaje de error en la celda
            const calendar = jQuery("#calendar-" + self.currentViewId);
            const event = calendar.fullCalendar(
              "clientEvents",
              data.activity_id
            )[0];
            self.showCellNotification(event, "Error: " + response.error, true);
          } else {
            // Mostrar mensaje de éxito en la celda
            const calendar = jQuery("#calendar-" + self.currentViewId);
            const event = calendar.fullCalendar(
              "clientEvents",
              data.activity_id
            )[0];
            self.showCellNotification(
              event,
              response.message || "Actualizado con éxito",
              false
            );
          }
        },
        error: function () {
          if (typeof revertFunc === "function") {
            revertFunc();
          }
          // Mostrar mensaje de error en la celda
          const calendar = jQuery("#calendar-" + self.currentViewId);
          const event = calendar.fullCalendar(
            "clientEvents",
            data.activity_id
          )[0];
          self.showCellNotification(
            event,
            "Error al actualizar la actividad",
            true
          );
        },
      });
    },

    // Manejadores de eventos del calendario
    bindEvents: function () {
      const self = this;

      // Manejo del guardado de actividad
      jQuery("#save-activity-" + this.currentViewId).on("click", function () {
        self.saveActivity(self.currentViewId);
      });

      jQuery(document)
        .off("click", '[id^="task-create-btn-"]')
        .on("click", '[id^="task-create-btn-"]', function (e) {
          e.preventDefault();
          //console.log("[CalendarManager] Save button clicked");

          const self = CalendarManager;
          const idCalendar = self.currentViewId;
          const form = jQuery("#main_input_box-" + idCalendar);
          //console.log("[CalendarManager] Form found", { formLength: form.length });
          
          let valid = true;
          let firstInvalid = null;
          let fieldNames = {
            subject: "Nombre de la tarea",
            description: "Descripción de la tarea",
            activitytype: "Tipo de actividad",
            date_start: "Fecha de inicio",
            taskImport: "Importancia",
            taskpriority: "Prioridad",
            categoryid: "Grupo",
            relatedcrmids: "Registro del modulo relacionado",
          };
          let filedRequired = Object.keys(fieldNames);

          //console.log("[CalendarManager] Starting validation", { requiredFields: filedRequired });

          // Validar campos obligatorios
          form
            .find('input[type="text"], textarea, select, input[type="hidden"]')
            .filter(function () {
              return filedRequired.includes(jQuery(this).attr("name"));
            })
            .each(function () {
              const el = jQuery(this);
              const name = el.attr("name");
              var fieldValue = el.val();

              /*console.log("[CalendarManager] Validating field", {
                name: name,
                value: fieldValue,
                isEmpty: fieldValue === null || fieldValue === "",
              });*/

              if (fieldValue === null || fieldValue === "") {
                valid = false;
                firstInvalid = firstInvalid || el;
                //console.error("[CalendarManager] Validation failed for field", name);
                alert(
                  'El campo "' +
                    (fieldNames[name] || name) +
                    '" no puede estar vacío.'
                );
                return false;
              }
            });
          if (!valid) {
            //console.error("[CalendarManager] Form validation failed");
            if (firstInvalid) firstInvalid.focus();
            return;
          }

          //console.log("[CalendarManager] Validation passed, proceeding to save");

          // Convertir números al formato de BD antes de enviar
          // CalendarManager.normalizeNumericFields(form);
          // console.log("[CalendarManager] Numeric fields normalized");

          // Enviar por AJAX
          let formData = form.serializeArray();
          // Asegurarse que Ajax=true esté presente
          let hasAjax = formData.some((f) => f.name === "Ajax");
          if (!hasAjax) formData.push({ name: "Ajax", value: "true" });
          
          /*console.log("[CalendarManager] Sending AJAX request", {
            url: "index.php",
            dataLength: formData.length,
            formData: formData
          });*/

          jQuery.ajax({
            url: "index.php",
            type: "POST",
            data: formData,
            dataType: "json",
            success: function (response) {
              //console.log("[CalendarManager] AJAX success response received", response);
              if (response && response.error === "OK") {
                //console.log("[CalendarManager] Save successful");
                // Si el backend devuelve el nuevo evento como objeto JSON
                if (response.event) {
                  // Añadir el evento visualmente al calendario (FullCalendar)
                  jQuery("#calendar-" + idCalendar).fullCalendar(
                    "renderEvent",
                    response.event,
                    true
                  );
                } else {
                  // Si no viene el evento en la respuesta, puedes construirlo desde el formulario:
                  var newEvent = {};
                  jQuery("#calendar-" + idCalendar).fullCalendar(
                    "renderEvent",
                    newEvent,
                    true
                  );
                  if (CalendarManager.config.events) {
                    CalendarManager.config.events.push(newEvent);
                  }
                }
                // Refrescar calendario visual
                CalendarManager.reloadCalendar();
                // Cerrar modal
                jQuery("#activity-modal-" + idCalendar).modal("hide");
              } else {
                console.error("[CalendarManager] Save failed - invalid response", {
                  response: response,
                  errorField: response ? response.error : 'no error field'
                });
                alert(
                  "Error al guardar: " +
                    (response && response.message
                      ? response.message
                      : "Error desconocido")
                );
              }
            },
            error: function (xhr, status, error) {
              console.error("[CalendarManager] AJAX error", {
                status: status,
                error: error,
                statusCode: xhr.status,
                responseText: xhr.responseText,
                xhr: xhr
              });
              alert("Error de red al guardar la tarea: " + status);
            },
          });
        });
    },

    // Funciones de utilidad existentes
    asignaValorCampo: function (id, valor) {
      jQuery("#" + id).val(valor);
    },

    submitForm: function () {
      jQuery("#formCalendar").submit();
    },

    clearForm: function () {
      jQuery("#subject").val("");
      jQuery("#description").val("");
      jQuery("#location").val("");
    },

    toggleAssignType: function (currType) {
      if (currType == "U") {
        jQuery("#assigned_user").css("display", "block");
        jQuery("#assign_team").css("display", "none");
      } else {
        jQuery("#assigned_user").css("display", "none");
        jQuery("#assign_team").css("display", "block");
      }
    },

    viewModule: function (e, obj, module, id) {
      const objA = jQuery(obj);
      const btnViews = jQuery("#btn-" + id);
      const moduleRows = objA.parent().parent();
      const viewRows = jQuery("#rules-" + id + " li");
      const activeClass = module + "-" + id;

      btnViews.html(
        "Vistas de " + objA.html() + '&nbsp;<span class="caret"></span>'
      );
      jQuery("." + activeClass).removeClass("hide");

      viewRows.each(function (i) {
        const li = jQuery(this);
        if (!li.hasClass(activeClass) && i > 0) {
          li.addClass("hide");
        }
      });

      moduleRows.find("li").each(function () {
        jQuery(this).removeClass("active");
      });

      objA.parent().addClass("active");
      e.preventDefault();
      btnViews.parent().addClass("open");
    },

    reloadCalendar: function () {
      jQuery("#calendar-" + this.currentViewId).fullCalendar("refetchEvents");
    },

    updateDropdownLabel: function ($button, text) {
      if (!$button || $button.length === 0) {
        return;
      }
      const defaultLabel = $button.data("default-label") || "";
      const finalLabel = text && text.trim() ? text.trim() : defaultLabel;
      const $label = $button.find(".btn-label");
      if ($label.length) {
        $label.text(finalLabel);
      }
    },

    formatUserDropdownLabel: function (names) {
      if (!names || names.length === 0) {
        return null;
      }
      if (names.length === 1) {
        return names[0];
      }
      if (names.length === 2) {
        return names.join(", ");
      }
      return names.slice(0, 2).join(", ") + " +" + (names.length - 2);
    },

    applySmartDropdownDirection: function ($modal) {
      if (!$modal || $modal.length === 0) {
        return;
      }
      //console.log("[CalendarManager] Forcing all dropdowns to open upward", {
      //  modalId: $modal.attr("id"),
      //});
      // Forzar que todos los dropdowns abran hacia arriba
      $modal.find(".btn-group, .input-group-btn").addClass("dropup");
      //console.log("[CalendarManager] Dropup class applied to all dropdown groups");
    },

    resetDropdownLabels: function ($modal) {
      if (!$modal || $modal.length === 0) {
        return;
      }
      $modal.find("[data-toggle='dropdown']").each(function () {
        CalendarManager.updateDropdownLabel(jQuery(this), null);
      });
      $modal.find(".btn-group, .input-group-btn").removeClass("dropup");
    },

    translateLabel: function (text) {
      return text;
    },
  };

  // Exponer el objeto globalmente
  window.CalendarManager = CalendarManager;
})(jQuery);
