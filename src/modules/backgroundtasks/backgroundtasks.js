(function (jQuery) {
  // Private constants
  var SCOPE_SYSTEM = "SYSTEM",
    TRIGGER_EVENT = "EVENT",
    TRIGGER_DAILY_SCHEDULE = "DAILY SCHEDULE",
    TRIGGER_TIMED_SCHEDULE = "TIMED SCHEDULE";

  // Private variables
  var wizard = null,
    modal = null,
    modalTriggerButton = null,
    totalFilterGroups = -1;

  // Private functions
  var destroyWizard = function () {
    wizard = null;
    window.location.reload();
  };

  var enableBackButton = function () {
    wizard.backButton.show();
  };

  var getActionsDataError = function (form) {
    var field, value, actions, action, i, j, parameters, parameter;

    actions = form.find(".action");
    if (actions.length === 0) {
      return {
        field: actions.closest(".actions"),
        message: "Debes agregar al menos una acción",
      };
    }

    for (i = 0; i < actions.length; i += 1) {
      action = jQuery(actions[i]);

      field = action.find(".actionname");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        return {
          field: field,
          message: "Introduce el nombre de la acción",
        };
      }

      field = action.find(".actiontype");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        return {
          field: field,
          message: "Selecciona el tipo de la acción",
        };
      }

      parameters = action.find(".parameter.mandatory");
      if (parameters.length === 0) {
        continue;
      }

      for (j = 0; j < parameters.length; j += 1) {
        parameter = jQuery(parameters[j]);

        field = parameter.find(".parametertype");
        if (field.length > 0) {
          value = field.val();
          if (value === null || value === undefined || value.trim() === "") {
            return {
              field: field,
              message: "Selecciona el tipo del parámetro",
            };
          }
        }

        field = parameter.find(".parametervalue:not([disabled])");
        value = field.val();
        if (value === null || value === undefined || value.trim() === "") {
          return {
            field: field,
            message: "Introduce el valor del parámetro",
          };
        }
      }
    }

    return null;
  };

  var getBasicDataError = function (form) {
    var field, value;

    field = form.find(".taskname");
    value = field.val();
    if (value === null || value === undefined || value.trim() === "") {
      return {
        field: field,
        message: "Introduce el nombre de la tarea",
      };
    }

    field = form.find(".taskstatus");
    value = field.val();
    if (value === null || value === undefined || value.trim() === "") {
      return {
        field: field,
        message: "Selecciona el status de la tarea",
      };
    }

    return null;
  };

  var getEventDataError = function (form) {
    var scope = form.find("#scope").val(),
      field,
      trigger,
      value;

    if (scope !== SCOPE_SYSTEM) {
      field = form.find(".modulename");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        return {
          field: field,
          message: "Selecciona el módulo",
        };
      }
    }

    field = form.find(".trigger");
    trigger = field.val();
    if (trigger === null || trigger === undefined || trigger.trim() === "") {
      return {
        field: field,
        message: "Selecciona el modo de ejecución",
      };
    }

    if (trigger === TRIGGER_EVENT) {
      field = form.find(".event");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        return {
          field: field,
          message: "Selecciona el evento",
        };
      }

      field = form.find(".eventinstant");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        return {
          field: field,
          message: "Selecciona el instante",
        };
      }
    } else if (trigger === TRIGGER_DAILY_SCHEDULE) {
      field = form.find("#daily-frequency");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        return {
          field: field,
          message: "Introduce la hora de ejecución",
        };
      }
    } else if (trigger === TRIGGER_TIMED_SCHEDULE) {
      field = form.find("#timed-frequency");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        return {
          field: field,
          message:
            "Introduce la frecuencia de ejecución de la tarea (en segundos)",
        };
      }
    }

    return null;
  };

  var getFiltersDataError = function (form) {
    var field, value, groups, group, filters, filter, i, j;

    groups = form.find(".filter-group-container");
    if (groups.length === 0) {
      return null;
    }

    for (i = 0; i < groups.length; i++) {
      group = jQuery(groups[i]);
      filters = group.find(".filter");
      if (filters.length === 0) {
        return {
          field: group.closest(".filter-groups"),
          message: "El grupo de condiciones no puede estar vacío",
        };
      }

      for (j = 0; j < filters.length; j++) {
        filter = jQuery(filters[j]);
        field = filter.find(".filter-field");
        value = field.val();
        if (value === undefined || value === null || value.trim() === "") {
          return {
            field: field,
            message: "Selecciona el campo",
          };
        }

        field = filter.find(".comparator");
        value = field.val();
        if (value === undefined || value === null || value.trim() === "") {
          return {
            field: field,
            message: "Selecciona el operador",
          };
        }
      }
    }

    return null;
  };

  var getFilterId = function (filterGroup) {
    var filters = filterGroup.find(".filter"),
      filterId = 0,
      filter,
      i;
    if (filters.length > 0) {
      for (i = 0; i < filters.length; i += 1) {
        filter = jQuery(filters[i]);
        if (parseInt(filter.attr("data-id")) < filterId) {
          filterId = parseInt(filter.attr("data-id"));
        }
      }
    }
    return filterId - 1;
  };

  var loadCkEditor = function (inputId) {
    console.log("cargando editor");
    var options = {
      contentsCss: ["themes/centaurus/css/bootstrap/bootstrap.min.css"],
      entities: false,
      language: "es",
      removePlugins: "elementspath",
      height: 90,
      toolbar: [
        [
          "Bold",
          "Italic",
          "Underline",
          "Strike",
          "-",
          "Subscript",
          "Superscript",
        ],
        ["NumberedList", "BulletedList", "-", "Outdent", "Indent"],
        ["JustifyLeft", "JustifyCenter", "JustifyRight", "JustifyBlock"],
        [
          "Link",
          "Unlink",
          "Anchor",
          "-",
          "Undo",
          "Redo",
          "-",
          "Find",
          "Replace",
          "-",
          "SelectAll",
          "RemoveFormat",
          "-",
          "Image",
          "Table",
          "HorizontalRule",
          "SpecialChar",
          "PageBreak",
          "TextColor",
          "BGColor",
        ],
        "/",
        [
          "Styles",
          "Format",
          "Font",
          "FontSize",
          "-",
          "EmailTemplateVariables",
          "-",
          "Source",
        ],
      ],
    };
    return CKEDITOR.replace(inputId, options);
  };

  var setExistingTaskData = function (card) {
    var wizardAction, arguments, taskId;
    wizard.backButton.hide();
    if (card.alreadyVisited()) {
      return;
    }

    wizardAction = wizard.cards["start"].el.find(
      'input[name="wizardaction"]:checked',
    );
    if (
      wizardAction.attr("id") === "wizard-action-duplicate-task-from-pattern"
    ) {
      taskId = wizard.cards["start"].el.find("#task-pattern-id").val();
    } else if (
      jQuery.inArray(wizardAction.attr("id"), [
        "wizard-action-duplicate-task",
        "wizard-action-edit-task",
      ]) !== -1
    ) {
      taskId = card.el.closest("form").find('input[name="record"]').val();
    } else {
      taskId = null;
    }

    wizard.cards["basic"].el
      .find(".data-section")
      .empty()
      .append('<div class="text-center">Cargando...</div>');
    wizard.cards["event"].el.find(".data-section").empty();
    wizard.cards["filters"].el.find(".data-section").empty();
    wizard.cards["actions"].el.find(".data-section").empty();

    arguments = [
      "module=backgroundtasks",
      "action=" + encodeURIComponent(wizardAction.val()),
    ];
    if (taskId) {
      arguments.push("record=" + encodeURIComponent(taskId));
    }
    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "html",
        method: "get",
      })
      .done(function (response) {
        var duplicatedTask = jQuery(response);
        wizard.cards["basic"].el
          .find(".data-section")
          .empty()
          .append(duplicatedTask.find("#basic-section > .data-section"));
        wizard.cards["event"].el
          .find(".data-section")
          .append(duplicatedTask.find("#event-section > .data-section"));
        wizard.cards["event"].el.find(".time").timepicker({
          minuteStep: 5,
          showSeconds: true,
          showMeridian: false,
          disableFocus: false,
          showWidget: true,
        });
        wizard.cards["filters"].el
          .find(".data-section")
          .append(duplicatedTask.find("#filters-section > .data-section"));
        wizard.cards["actions"].el
          .find(".data-section")
          .append(duplicatedTask.find("#actions-section > .data-section"));
        wizard.cards["start"].disable(true);
        loadCkEditor("description");
      })
      .fail(function (jQueryResponse) {
        alert(
          "Se ha presentado un error. Notifica al administrador de la aplicación",
        );
        console.log(jQueryResponse);
      });
  };

  var submitWizard = function () {
    jQuery
      .ajax("index.php", {
        data: wizard.serialize(),
        dataType: "json",
        method: "post",
      })
      .done(function () {
        wizard.submitSuccess();
        wizard.hideButtons();
      })
      .fail(function (jQueryResponse) {
        wizard.el
          .find(".wizard-failure .message")
          .text(jQueryResponse.responseJSON);
        wizard.submitFailure();
        wizard.hideButtons();
      });
  };

  var updateProgressBar = function () {
    var cards = wizard.cards,
      activeCard = wizard.getActiveCard(),
      index = 0,
      cardName;
    for (cardName in cards) {
      if (cardName === activeCard.name) {
        break;
      }
      index += 1;
    }
    wizard.updateProgressBar((index * 100) / Object.keys(wizard.cards).length);
  };

  var validateActionsCard = function (card) {
    var error = getActionsDataError(card.el.closest("form"));

    card.wizard.hidePopovers();
    if (error !== null) {
      card.wizard.errorPopover(error.field, error.message);
      error.field.focus();
      return false;
    }

    return true;
  };

  var validateBasicCard = function (card) {
    var error = getBasicDataError(card.el.closest("form"));

    card.wizard.hidePopovers();
    if (error !== null) {
      card.wizard.errorPopover(error.field, error.message);
      error.field.focus();
      return false;
    }

    return true;
  };

  var validateEventCard = function (card) {
    var error = getEventDataError(card.el.closest("form"));

    card.wizard.hidePopovers();
    if (error !== null) {
      card.wizard.errorPopover(error.field, error.message);
      error.field.focus();
      return false;
    }
    return true;
  };

  var validateFiltersCard = function (card) {
    var error = getFiltersDataError(card.el.closest("form"));

    card.wizard.hidePopovers();
    if (error !== null) {
      card.wizard.errorPopover(error.field, error.message);
      error.field.focus();
      return false;
    }

    return true;
  };

  // Public functions
  var addAction = function (buttonElement) {
    var button = jQuery(buttonElement),
      actions = button.closest("#actions-section").find(".actions"),
      scope = button.closest("form").find("#scope").val(),
      actionId = actions.find(".action").length + 1,
      arguments = [
        "module=backgroundtasks",
        "action=GetActionTemplate",
        "actionid=" + actionId,
        "scope=" + encodeURIComponent(scope),
        "Ajax=true",
      ];
    while (true) {
      if (jQuery("#action-" + actionId).length > 0) {
        actionId += 1;
      } else {
        break;
      }
    }
    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "html",
        method: "get",
      })
      .done(function (response) {
        var action = jQuery(response);
        action.find("[data-scope]").each(function (index, element) {
          var dummy = jQuery(element);
          if (scope === "SYSTEM" || dummy.attr("data-scope") === scope) {
            dummy.show();
          } else {
            dummy.prop("selected", false).hide();
          }
        });
        actions.append(action);
      })
      .fail(function (jQueryResponse) {
        alert(
          "Se ha presentado un error. Notifica al administrador de la aplicación",
        );
        console.log(jQueryResponse);
      });
  };

  var addFilter = function (buttonElement) {
    var button = jQuery(buttonElement),
      group = button.closest(".filter-group"),
      moduleName = button.closest("form").find("#modulename").val(),
      groupId = group.attr("data-id"),
      filters = group.find(".filters"),
      filterId = getFilterId(group),
      arguments = [
        "module=backgroundtasks",
        "action=GetFilterTemplate",
        "modulename=" + encodeURIComponent(moduleName),
        "groupid=" + groupId,
        "filterid=" + filterId,
        "Ajax=true",
      ];
    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "html",
        method: "get",
      })
      .done(function (response) {
        filters.find(".operator:last").prop("disabled", false).show();
        filters.append(response);
      })
      .fail(function (jQueryResponse) {
        alert(
          "Se ha presentado un error. Notifica al administrador de la aplicación",
        );
        console.log(jQueryResponse);
      });
  };

  var addFilterGroup = function (buttonElement) {
    var button = jQuery(buttonElement),
      filterGroups = button.closest("#filters-section").find(".filter-groups"),
      moduleName = button.closest("form").find("#modulename").val(),
      arguments = [
        "module=backgroundtasks",
        "action=GetFilterGroupTemplate",
        "modulename=" + encodeURIComponent(moduleName),
        "groupid=" + totalFilterGroups,
        "Ajax=true",
      ];
    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "html",
        method: "get",
      })
      .done(function (response) {
        filterGroups
          .find(".filter-group-operator:last > .operator")
          .prop("disabled", false)
          .show();
        filterGroups.append(response);
        totalFilterGroups -= 1;
      })
      .fail(function (jQueryResponse) {
        alert(
          "Se ha presentado un error. Notifica al administrador de la aplicación",
        );
        console.log(jQueryResponse);
      });
  };

  var closeTaskWizard = function () {
    if (wizard) {
      wizard.reset().close().trigger("closed");
    }
  };

  var deleteAction = function (buttonElement) {
    if (
      !confirm("¿Estás seguro que quieres eliminar la acción seleccionada?")
    ) {
      return;
    }
    jQuery(buttonElement).closest(".panel").remove();
  };

  var deleteFilter = function (buttonElement) {
    var button = jQuery(buttonElement),
      group = button.closest(".filter-group-container"),
      filter = button.closest(".filter");
    if (!confirm("¿Estás seguro de borrar el filtro seleccionado?")) {
      return;
    }
    filter.remove();
    group.find(".filter:last .operator").prop("disabled", true).hide();
  };

  var deleteFilterGroup = function (buttonElement) {
    var group = jQuery(buttonElement).closest(".filter-group-container"),
      groups = group.closest(".filter-groups");
    if (!confirm("¿Estás seguro de borrar el grupo de filtros seleccionado?")) {
      return;
    }
    group.remove();
    groups
      .find(".filter-group-operator:last > .operator")
      .prop("disabled", true)
      .hide();
  };

  var deleteTask = function (id, taskName) {
    return confirm(
      '¿Estás seguro que quieres eliminar la tarea "' + taskName + '"?',
    );
  };

  var filterByCategory = function (selectElement) {
    var element = jQuery(selectElement),
      category = element.val(),
      tab = element.closest(".tab-pane");
    if (category === "") {
      tab.find(".task-row").show();
    } else {
      tab.find('.task-row[data-category!="' + category + '"]').hide();
      tab.find('.task-row[data-category="' + category + '"]').show();
    }
  };

  var filterPatternByCategory = function (selectElement) {
    var element = jQuery(selectElement),
      category = element.val(),
      pattern = element.closest("#task-pattern").find("#task-pattern-id"),
      selectedCategoryTasks;

    pattern
      .find('option[data-category!="' + category + '"]')
      .removeAttr("selected")
      .hide();
    selectedCategoryTasks = pattern.find(
      'option[data-category="' + category + '"]',
    );
    selectedCategoryTasks.removeAttr("selected").show();
    if (selectedCategoryTasks.length === 0) {
      element.closest("form").find('[type="submit"]').prop("disabled", true);
      pattern.val("");
    } else {
      element.closest("form").find('[type="submit"]').prop("disabled", false);
      pattern.val(jQuery(selectedCategoryTasks[0]).val());
    }
  };

  var openTaskWizard = function (taskId) {
    var template = jQuery("#background-task-wizard-template");
    wizard = jQuery(template.html()).wizard({
      backdrop: "static",
      showCancel: false,
      buttons: {
        cancelText: "Cancelar",
        nextText: "Siguiente →",
        backText: "← Atrás",
        submitText: "Guardar",
        submittingText: "Guardando...",
      },
    });

    if (taskId) {
      wizard.cards["start"].el
        .closest("form")
        .find('input[name="record"]')
        .val(taskId);
      wizard.cards["start"].el
        .find("#new-task-options")
        .hide()
        .find('input[name="wizardaction"]')
        .prop("disabled", true);
      wizard.cards["start"].el
        .find("#existing-task-options")
        .show()
        .find('input[name="wizardaction"]')
        .prop("disabled", false)
        .first()
        .prop("checked", true);
    } else {
      wizard.cards["start"].el
        .closest("form")
        .find('input[name="record"]')
        .val("");
      wizard.cards["start"].el
        .find("#existing-task-options")
        .hide()
        .find('input[name="wizardaction"]')
        .prop("disabled", true);
      wizard.cards["start"].el
        .find("#new-task-options")
        .show()
        .find('input[name="wizardaction"]')
        .prop("disabled", false)
        .first()
        .prop("checked", true);
    }
    wizard.cards["basic"]
      .on("validate", validateBasicCard)
      .on("selected", setExistingTaskData);
    wizard.cards["event"]
      .on("validate", validateEventCard)
      .on("selected", enableBackButton);
    wizard.cards["filters"]
      .on("validate", validateFiltersCard)
      .on("selected", enableBackButton);
    wizard.cards["actions"].on("validate", validateActionsCard);
    wizard
      .on("submit", submitWizard)
      .on("closed", destroyWizard)
      .on("incrementCard", updateProgressBar)
      .on("decrementCard", updateProgressBar)
      .show();

    jQuery(".wizard-modal .wizard-nav-item > .wizard-nav-link").on(
      "click",
      function (evt) {
        var link = jQuery(this),
          links = link.closest(".nav-list").find(".wizard-nav-item"),
          i,
          requestedCardIndex,
          activeCardIndex;
        if (
          !link.closest(".wizard-nav-item").hasClass("already-visited") ||
          links.length === 0
        ) {
          return;
        }

        requestedCardIndex = null;
        for (i = 0; i < links.length; i += 1) {
          if (link.text() === jQuery(links[i]).text()) {
            requestedCardIndex = i;
            break;
          }
        }

        evt.preventDefault();
        evt.stopPropagation();
        if (!requestedCardIndex) {
          return;
        }

        activeCardIndex = wizard.getActiveCard().index;
        if (activeCardIndex > requestedCardIndex) {
          for (i = activeCardIndex; i > requestedCardIndex; i -= 1) {
            wizard.decrementCard();
          }
        } else if (activeCardIndex < requestedCardIndex) {
          for (i = activeCardIndex; i < requestedCardIndex; i += 1) {
            wizard.incrementCard();
          }
        }
      },
    );
  };

  var openTaskInNewWindow = function (buttonElement) {
    var button = jQuery(buttonElement),
      form = button.closest("form"),
      wizardAction = form.find('input[name="wizardaction"]:checked'),
      taskId;
    form.find('input[name="action"]').val(wizardAction.val());
    if (
      wizardAction.attr("id") === "wizard-action-duplicate-task-from-pattern"
    ) {
      taskId = form.find("#task-pattern-id").val();
      form.find('input[name="record"]').val(taskId);
    } else if (
      jQuery.inArray(wizardAction.attr("id"), [
        "wizard-action-duplicate-task",
        "wizard-action-edit-task",
      ]) !== -1
    ) {
      taskId = form.find('input[name="record"]').val();
      form.find('input[name="record"]').val(taskId);
    } else {
      form.find('input[name="record"]').remove();
    }

    form.find('input[name="wizardaction"]').remove();
    form.find('input[name="wizardlocation"]').remove();
    form.find('input[name="Ajax"]').remove();
    button.closest("form").attr("method", "get").submit();
  };

  var refreshFields = function (inputElement) {
    var input = jQuery(inputElement),
      dummyId = input.attr("id").split("-"),
      moduleName = input.closest("form").find("#modulename").val(),
      scope = input.closest("form").find("#scope").val(),
      action = input.closest(".action"),
      actionType = action.find(".actiontype").val(),
      parameters = action.find(".parametervalue"),
      arguments = [
        "module=backgroundtasks",
        "action=GetParameterTemplate",
        "modulename=" + encodeURIComponent(moduleName),
        "scope=" + encodeURIComponent(scope),
        "Ajax=true",
        action.find(".actiontype").serialize(),
      ];
    if (actionType === "SEND NOTIFICATION") {
      var notifications = jQuery("#NOTIFICATIONS-" + dummyId[1]);
      notifications.empty().append(
        jQuery("<option>", {
          value: "",
          text: "Cargando notificaciones",
        }),
      );
    }
    jQuery
      .ajax("index.php", {
        data: arguments.join("&") + "&" + parameters.serialize(),
        dataType: "html",
        method: "get",
      })
      .done(function (response) {
        var dummy = jQuery(response),
          actions,
          i;
        dummy
          .find(".date")
          .not('[data-type="FORMULA"]')
          .datepicker({ format: "yyyy-mm-dd", language: "es", weekStart: 1 });
        if (actionType === "SEND NOTIFICATION") {
          var dummyId = input.attr("id").split("-"),
            notifications = jQuery("#NOTIFICATIONS-" + dummyId[1]);
          notifications.empty().append(dummy);
        } else {
          actions = action.closest(".actions").find(".action");
          for (i = 0; i < actions.length; i += 1) {
            if (i < actions.length - 1) {
              dummy
                .find(".parametervalue.previousoutput")
                .append(
                  jQuery("<option></option>")
                    .text(jQuery(actions[i]).find(".actionname").val())
                    .val(jQuery(actions[i]).find(".actionname").val()),
                );
            }
          }
          action.find(".parameters").empty().append(dummy);
        }
      })
      .fail(function (jQueryResponse) {
        alert(
          "Se ha presentado un error. Notifica al administrador de la aplicación",
        );
        console.log(jQueryResponse);
      });
  };

  var setFilterField = function (selectElement) {
    var filterField = jQuery(selectElement),
      selectedFilterField = filterField.find("option:selected"),
      selectedFilterFieldDataType = selectedFilterField.attr("data-type"),
      selectedDataType = selectedFilterFieldDataType
        ? selectedFilterFieldDataType
        : "TEXT",
      comparator = filterField.closest(".filter").find(".comparator"),
      comparatorOptions = comparator.find("option"),
      valueFields = filterField.closest(".filter").find(".value");

    comparatorOptions.each(function (index, optionElement) {
      var option = jQuery(optionElement),
        dummy = option.attr("data-type"),
        dataTypes = dummy ? JSON.parse(dummy.split("'").join('"')) : ["TEXT"];
      if (jQuery.inArray(selectedDataType, dataTypes) !== -1) {
        option.show();
      } else {
        option.prop("selected", false).hide();
      }
    });
    valueFields.each(function (index, valueElement) {
      var valueField = jQuery(valueElement),
        dummy = valueField.attr("data-type"),
        dataTypes = dummy ? JSON.parse(dummy.split("'").join('"')) : ["TEXT"],
        selectedComparator;

      if (selectedDataType === "DATE") {
        selectedComparator = comparator.find("option:selected");
        if (
          selectedComparator.val() !== "" &&
          selectedComparator.hasClass("days") &&
          valueField.hasClass("days")
        ) {
          valueField.prop("disabled", false).show();
        } else if (
          selectedComparator.val() === "" &&
          valueField.hasClass("date") &&
          !valueField.is('[data-type="FORMULA"]')
        ) {
          valueField.prop("disabled", false).show();
          if (
            valueField.hasClass("date") &&
            !valueField.is('[data-type="FORMULA"]')
          ) {
            if (
              selectedDataType === "DATE" &&
              !valueField.hasOwnProperty("datepicker")
            ) {
              valueField.datepicker({
                format: "yyyy-mm-dd",
                language: "es",
                weekStart: 1,
              });
            } else {
              valueField.datepicker("remove");
            }
          }
        } else {
          valueField.prop("disabled", true).hide();
        }
      } else {
        if (jQuery.inArray(selectedDataType, dataTypes) !== -1) {
          valueField.prop("disabled", false).show();
        } else {
          valueField.prop("disabled", true).hide();
        }
      }
    });
  };

  var setFilterComparator = function (selectElement) {
    var comparator = jQuery(selectElement),
      filterField = comparator.closest(".filter").find(".filter-field"),
      selectedFilterField = filterField.find("option:selected"),
      selectedFilterFieldDataType = selectedFilterField.attr("data-type"),
      selectedDataType = selectedFilterFieldDataType
        ? selectedFilterFieldDataType
        : "TEXT",
      valueFields = filterField.closest(".filter").find(".value");

    valueFields.each(function (index, valueElement) {
      var valueField = jQuery(valueElement),
        dummy = valueField.attr("data-type"),
        dataTypes = dummy ? JSON.parse(dummy.split("'").join('"')) : ["TEXT"],
        selectedComparator;

      if (selectedDataType === "DATE") {
        selectedComparator = comparator.find("option:selected");
        if (
          selectedComparator.val() !== "" &&
          selectedComparator.hasClass("days") &&
          valueField.hasClass("days")
        ) {
          valueField.prop("disabled", false).show();
        } else if (
          (selectedComparator.val() === "" &&
            valueField.hasClass("date") &&
            !valueField.is('[data-type="FORMULA"]')) ||
          (selectedComparator.val() !== "" &&
            !selectedComparator.hasClass("days") &&
            valueField.hasClass("date") &&
            !valueField.is('[data-type="FORMULA"]'))
        ) {
          valueField.prop("disabled", false).show();
          if (
            valueField.hasClass("date") &&
            !valueField.is('[data-type="FORMULA"]')
          ) {
            if (
              selectedDataType === "DATE" &&
              !valueField.hasOwnProperty("datepicker")
            ) {
              valueField.datepicker({
                format: "yyyy-mm-dd",
                language: "es",
                weekStart: 1,
              });
            } else {
              valueField.datepicker("remove");
            }
          }
        } else {
          valueField.prop("disabled", true).hide();
        }
      } else {
        if (jQuery.inArray(selectedDataType, dataTypes) !== -1) {
          valueField.prop("disabled", false).show();
        } else {
          valueField.prop("disabled", true).hide();
        }
      }
    });
  };

  var setLocation = function (checkboxElement) {
    var checkbox = jQuery(checkboxElement);

    if (checkbox.is(":checked")) {
      wizard.cards["basic"]
        .disable()
        .el.find(".form-control")
        .prop("disabled", true);
      wizard.cards["event"]
        .disable()
        .el.find(".form-control")
        .prop("disabled", true);
      wizard.cards["filters"]
        .disable()
        .el.find(".form-control")
        .prop("disabled", true);
      wizard.cards["actions"]
        .disable()
        .el.find(".form-control")
        .prop("disabled", true);
      checkbox.closest(".checkbox").find(".btn").show();
      wizard.hideButtons();
    } else {
      wizard.cards["basic"]
        .enable()
        .deselect()
        .el.find(".form-control")
        .prop("disabled", false);
      wizard.cards["event"]
        .enable()
        .deselect()
        .el.find(".form-control")
        .prop("disabled", false);
      wizard.cards["filters"]
        .enable()
        .deselect()
        .el.find(".form-control")
        .prop("disabled", false);
      wizard.cards["actions"]
        .enable()
        .deselect()
        .el.find(".form-control")
        .prop("disabled", false);
      checkbox.closest(".checkbox").find(".btn").hide();
      wizard.showButtons();
    }
  };

  var setModuleName = function (selectElement) {
    var select = jQuery(selectElement),
      form = select.closest("form"),
      moduleName = select.val(),
      filtersSection = form.find("#filters-section"),
      actionsSection = form.find("#actions-section");

    filtersSection.find(".filter-groups").empty();
    actionsSection.find(".actions").empty();
    if (wizard !== null) {
      return;
    }

    if (
      moduleName === undefined ||
      moduleName === null ||
      moduleName.trim() === ""
    ) {
      filtersSection.hide();
      actionsSection.hide();
    } else {
      filtersSection.show();
      actionsSection.show();
    }
  };

  var showFormulaHelpModal = function (moduleName, availableFields, availableVariables) {
    var modalId = "formula-help-modal";
    var modalContent = jQuery("#" + modalId);
    
    // Construir HTML dinámico para campos fecha disponibles
    var fieldsHtml = '';
    if (availableFields && availableFields.length > 0) {
      fieldsHtml = '<ul>';
      for (var i = 0; i < availableFields.length; i++) {
        fieldsHtml += '<li><code>|' + availableFields[i].fieldname + '|</code> - ' + availableFields[i].fieldlabel + '</li>';
      }
      fieldsHtml += '</ul>';
    } else {
      fieldsHtml = '<p><em>Ningún campo de fecha disponible</em></p>';
    }
    
    // Construir HTML dinámico para variables disponibles
    var variablesHtml = '';
    if (availableVariables && Object.keys(availableVariables).length > 0) {
      variablesHtml = '<ul>';
      for (var varName in availableVariables) {
        variablesHtml += '<li><code>{' + varName + '}</code> - ' + availableVariables[varName] + '</li>';
      }
      variablesHtml += '</ul>';
    } else {
      variablesHtml = '<p><em>Ninguna variable disponible</em></p>';
    }
    
    var modalHtml = 
        '<div class="modal fade" id="' + modalId + '" tabindex="-1" role="dialog">' +
        '<div class="modal-dialog" role="document" style="width: 700px;">' +
        '<div class="modal-content">' +
        '<div class="modal-header">' +
        '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
        '<span aria-hidden="true">&times;</span>' +
        '</button>' +
        '<h4 class="modal-title">Ayuda de Fórmulas para Campos de Fecha</h4>' +
        '</div>' +
        '<div class="modal-body">' +
        '<p>Las fórmulas de fecha te permiten calcular fechas basadas en otros campos del módulo origen.</p>' +
        '<h5>Sintaxis:</h5>' +
        '<ul>' +
        '<li>Usa <code>|nombre_campo|</code> para referenciar un campo de fecha del módulo origen</li>' +
        '<li>Usa <code>{variable}</code> para referenciar variables del sistema</li>' +
        '<li>Operadores: <code>+</code>, <code>-</code>, <code>*</code>, <code>/</code></li>' +
        '<li>Unidades: <code>días</code>, <code>meses</code>, <code>años</code></li>' +
        '</ul>' +
        '<h5>Campos de fecha disponibles del módulo <em>' + (moduleName || '') + '</em>:</h5>' +
        fieldsHtml +
        '<h5>Variables del sistema disponibles:</h5>' +
        variablesHtml +
        '<h5>Ejemplos:</h5>' +
        '<ul>' +
        '<li><code>|fecha_inicio| + 7 días</code> - 7 días después de la fecha de inicio</li>' +
        '<li><code>|fecha_fin| - 1 mes</code> - 1 mes antes de la fecha de fin</li>' +
        '<li><code>|fecha_creacion| + 30 días</code> - 30 días después de la fecha de creación</li>' +
        '<li><code>{CURRENT_DATE} + 15 días</code> - 15 días después de la fecha actual</li>' +
        '<li><code>{CURRENT_DATE} - 1 año</code> - 1 año antes de la fecha actual</li>' +
        '</ul>' +
        '</div>' +
        '<div class="modal-footer">' +
        '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
        '</div>' +
        '</div>' +
        '</div>' +
        '</div>';
    
    if (modalContent.length > 0) {
      modalContent.remove();
    }
    
    modalContent = jQuery(modalHtml);
    jQuery("body").append(modalContent);
    jQuery("#" + modalId).modal("show");
  };

  var showConcatHelpModal = function (
    moduleName,
    availableFields,
    availableVariables,
  ) {
    var modalId = "concat-help-modal";
    var modalContent = jQuery("#" + modalId);

    // Construir HTML dinámico para campos disponibles
    var fieldsHtml = "";
    if (availableFields && availableFields.length > 0) {
      fieldsHtml = "<ul>";
      for (var i = 0; i < availableFields.length; i++) {
        fieldsHtml +=
          "<li><code>|" +
          availableFields[i].fieldname +
          "|</code> - " +
          availableFields[i].fieldlabel +
          "</li>";
      }
      fieldsHtml += "</ul>";
    } else {
      fieldsHtml = "<p><em>Ningún campo disponible</em></p>";
    }

    // Construir HTML dinámico para variables disponibles
    var variablesHtml = "";
    if (availableVariables && Object.keys(availableVariables).length > 0) {
      variablesHtml = "<ul>";
      for (var varName in availableVariables) {
        variablesHtml +=
          "<li><code>{" +
          varName +
          "}</code> - " +
          availableVariables[varName] +
          "</li>";
      }
      variablesHtml += "</ul>";
    } else {
      variablesHtml = "<p><em>Ninguna variable disponible</em></p>";
    }

    var modalHtml =
      '<div class="modal fade" id="' +
      modalId +
      '" tabindex="-1" role="dialog">' +
      '<div class="modal-dialog" role="document" style="width: 700px;">' +
      '<div class="modal-content">' +
      '<div class="modal-header">' +
      '<button type="button" class="close" data-dismiss="modal" aria-label="Close">' +
      '<span aria-hidden="true">&times;</span>' +
      "</button>" +
      '<h4 class="modal-title">Ayuda de Concatenación para Campos de Texto</h4>' +
      "</div>" +
      '<div class="modal-body">' +
      "<p>La concatenación te permite combinar valores de diferentes campos y variables en un solo campo de texto.</p>" +
      "<h5>Sintaxis:</h5>" +
      "<ul>" +
      "<li>Usa <code>|nombre_campo|</code> para referenciar un campo del módulo origen</li>" +
      "<li>Usa <code>{variable}</code> para referenciar variables del sistema</li>" +
      "<li>Puedes combinar múltiples campos y variables con texto libre</li>" +
      "</ul>" +
      "<h5>Campos disponibles del módulo <em>" +
      (moduleName || "") +
      "</em>:</h5>" +
      fieldsHtml +
      "<h5>Variables del sistema disponibles:</h5>" +
      variablesHtml +
      "<h5>Ejemplos:</h5>" +
      "<ul>" +
      "<li><code>|nombre| |apellido|</code> - Combina nombre y apellido</li>" +
      "<li><code>Asunto: |asunto| - {CURRENT_DATE}</code> - Combina campo con variable</li>" +
      "<li><code>Cliente |nombre_cliente| - Pedido |numero_pedido|</code> - Combina múltiples campos con texto</li>" +
      "</ul>" +
      "</div>" +
      '<div class="modal-footer">' +
      '<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>' +
      "</div>" +
      "</div>" +
      "</div>" +
      "</div>";

    if (modalContent.length > 0) {
      modalContent.remove();
    }

    modalContent = jQuery(modalHtml);
    jQuery("body").append(modalContent);
    jQuery("#" + modalId).modal("show");
  };

  var setParameterValue = function (selectElement) {
    var element = jQuery(selectElement),
      type = element.val(),
      parameter = element.closest(".row"),
      tabName = [],
      tabLabel = [],
      uiTypeAndSeq = element.attr("data-uitype").split("@"),
      myModule = jQuery("#modulename-" + uiTypeAndSeq[1]).val(),
      mainModule = jQuery("#modulename").find("option:selected").text();
    console.log(type);
    if (uiTypeAndSeq[0] === "10" && type === "VARIABLE") {
      var variableSelect = parameter.find(
        '.parametervalue[data-type="' + type + '"]',
      );
      variableSelect.empty();
      variableSelect.append(
        jQuery("<option>", { value: "", text: "Seleccionar.." }),
      );
      variableSelect.append(
        jQuery("<option>", {
          value: "{RECORD_ID}",
          text: "ID del registro que se está procesando: " + mainModule,
        }),
      );
      jQuery("select[id ^= 'modulename-']")
        .get()
        .forEach(function (entity, index) {
          var theModule = jQuery(entity).val();
          if (
            index <= parseInt(uiTypeAndSeq[1]) - 1 &&
            theModule !== myModule
          ) {
            variableSelect.append(
              jQuery("<option>", {
                value: theModule + "@RECORD_ID",
                text:
                  "ID del registro de: " +
                  jQuery(entity).find("option:selected").text() +
                  "creado en acción previa",
              }),
            );
          }
        });
    }
    if (type === "NOTIFICATIONS") {
      jQuery("#NOTIFICATIONS-" + uiTypeAndSeq[1]).removeClass("hide");
    } else {
      parameter
        .find('.parametervalue[data-type!="' + type + '"]')
        .prop("disabled", true)
        .hide()
        .closest(".variable")
        .hide();
      parameter
        .find('.parametervalue[data-type="' + type + '"]')
        .prop("disabled", false)
        .show()
        .closest(".variable")
        .show();
      parameter
        .find(".parametervalue.date")
        .datepicker("remove")
        .prop("readonly", false);
      if (type !== "FORMULA") {
        parameter
          .find('.parametervalue.date[data-type="' + type + '"]')
          .datepicker({ format: "yyyy-mm-dd", language: "es", weekStart: 1 })
          .prop("readonly", true);
        parameter.find(".formula-help").hide();
      } else {
        parameter
          .find('.parametervalue.date[data-type="LITERAL"]')
          .prop("disabled", true);
        parameter.find(".formula-help").show();
      }
    }
  };

  var setParameters = function (selectElement) {
    var element = jQuery(selectElement),
      moduleName = element.closest("form").find("#modulename").val(),
      scope = element.closest("form").find("#scope").val(),
      actionType = element.val(),
      action = element.closest(".action"),
      arguments;
    if (
      actionType === null ||
      actionType === undefined ||
      actionType.trim() === ""
    ) {
      return;
    }

    arguments = [
      "module=backgroundtasks",
      "action=GetParameterTemplate",
      "modulename=" + encodeURIComponent(moduleName),
      "scope=" + encodeURIComponent(scope),
      "Ajax=true",
      action.find(".actiontype").serialize(),
    ];
    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "html",
        method: "get",
      })
      .done(function (response) {
        var dummy = jQuery(response),
          actions,
          i;
        dummy
          .find(".date")
          .not('[data-type="FORMULA"]')
          .datepicker({ format: "yyyy-mm-dd", language: "es", weekStart: 1 });
        actions = action.closest(".actions").find(".action");
        for (i = 0; i < actions.length; i += 1) {
          if (i < actions.length - 1) {
            dummy
              .find(".parametervalue.previousoutput")
              .append(
                jQuery("<option></option>")
                  .text(jQuery(actions[i]).find(".actionname").val())
                  .val(jQuery(actions[i]).find(".actionname").val()),
              );
          }
        }
        action.find(".parameters").empty().append(dummy);
      })
      .fail(function (jQueryResponse) {
        alert(
          "Se ha presentado un error. Notifica al administrador de la aplicación",
        );
        console.log(jQueryResponse);
      });
  };

  var setScope = function (selectElement) {
    var select = jQuery(selectElement),
      scope = select.val(),
      elements = select.closest("form").find("[data-scope]");
    elements.each(function (index, element) {
      var dummy = jQuery(element);
      if (scope === "SYSTEM" || dummy.attr("data-scope") === scope) {
        dummy.show();
      } else {
        dummy.prop("selected", false).hide();
      }
    });
  };

  var setTrigger = function (selectElement) {
    var trigger = jQuery(selectElement),
      value = trigger.val(),
      eventData = trigger.closest(".data-section").find(".event-data"),
      scheduleData = trigger.closest(".data-section").find(".schedule-data");
    if (value === TRIGGER_EVENT) {
      eventData.show();
      scheduleData
        .find(".daily-schedule-data #frequency")
        .prop("disabled", true);
      scheduleData.find(".daily-schedule-data").hide();
      scheduleData
        .find(".timed-schedule-data #frequency")
        .prop("disabled", true);
      scheduleData.find(".timed-schedule-data").hide();
      scheduleData.hide();
    } else if (value === TRIGGER_DAILY_SCHEDULE) {
      eventData.hide();
      scheduleData.find(".daily-schedule-data").show();
      scheduleData.find(".timed-schedule-data").hide();
      scheduleData.show();
    } else if (value === TRIGGER_TIMED_SCHEDULE) {
      eventData.hide();
      scheduleData.find(".daily-schedule-data").hide();
      scheduleData.find(".timed-schedule-data").show();
      scheduleData.show();
    } else {
      eventData.hide();
      scheduleData.find(".daily-schedule-data").hide();
      scheduleData.find(".timed-schedule-data").hide();
      scheduleData.hide();
    }
  };

  var setVariableValue = function (value) {
    var field;

    if (modalTriggerButton === null) {
      return;
    }

    field = modalTriggerButton.closest(".variable").find(".parametervalue");
    field.val(field.val() + value);
    modal.modal("hide");
  };

  var setWizardAction = function (radioElement) {
    var radio = jQuery(radioElement),
      action = radio.val(),
      patternSection = radio
        .closest(".wizard-input-section")
        .find("#task-pattern");

    if (radio.attr("id") === "wizard-action-duplicate-task-from-pattern") {
      patternSection.find("#category").prop("disabled", false);
      patternSection.find("#task-pattern-id").prop("disabled", false);
      patternSection.show();
    } else {
      patternSection.hide();
      patternSection.find("#category").prop("disabled", true);
      patternSection.find("#task-pattern-id").prop("disabled", true);
    }
  };

  var updatePanelTitle = function (fieldElement) {
    var field = jQuery(fieldElement),
      action = field.closest(".action"),
      panel = field.closest(".panel"),
      name = field.hasClass("actionname")
        ? field.val()
        : action.find(".actionname").val(),
      type = field.hasClass("actiontype")
        ? field.find("option:selected").text()
        : action.find(".actiontype option:selected").text(),
      panelTitle = panel.find(".panel-title");
    panelTitle.find(".actionname").html(name !== "" ? name : "Sin nombre");
    panelTitle.find(".actiontype").html(type !== "" ? type : "Sin tipo");
  };

  var validateTask = function (formElement) {
    var form = jQuery(formElement),
      error;

    error = getBasicDataError(form);
    if (error !== null) {
      alert(error.message);
      error.field.focus();
      return false;
    }

    error = getEventDataError(form);
    if (error !== null) {
      alert(error.message);
      error.field.focus();
      return false;
    }

    error = getFiltersDataError(form);
    if (error !== null) {
      alert(error.message);
      error.field.focus();
      return false;
    }

    error = getActionsDataError(form);
    if (error !== null) {
      alert(error.message);
      error.field.focus();
      return false;
    }

    return true;
  };

  window.BackgroundTasksUtils = {
    addAction: addAction,
    addFilter: addFilter,
    addFilterGroup: addFilterGroup,
    closeTaskWizard: closeTaskWizard,
    deleteAction: deleteAction,
    deleteFilter: deleteFilter,
    deleteFilterGroup: deleteFilterGroup,
    deleteTask: deleteTask,
    filterByCategory: filterByCategory,
    filterPatternByCategory: filterPatternByCategory,
    openTaskWizard: openTaskWizard,
    openTaskInNewWindow: openTaskInNewWindow,
    refreshFields: refreshFields,
    setFilterComparator: setFilterComparator,
    setFilterField: setFilterField,
    setLocation: setLocation,
    setModuleName: setModuleName,
    setParameters: setParameters,
    setParameterValue: setParameterValue,
    setScope: setScope,
    setTrigger: setTrigger,
    setVariableValue: setVariableValue,
    setWizardAction: setWizardAction,
    showFormulaHelpModal: showFormulaHelpModal,
    showConcatHelpModal: showConcatHelpModal,
    updatePanelTitle: updatePanelTitle,
    validateTask: validateTask,
  };
  jQuery(document).on("ready", function () {
    jQuery(".time").timepicker({
      minuteStep: 5,
      showSeconds: true,
      showMeridian: false,
      disableFocus: false,
      showWidget: true,
    });
    //description
    loadCkEditor("description");
  });
})(jQuery);
