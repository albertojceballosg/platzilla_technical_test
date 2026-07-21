(function (jQuery) {
  var wizard, checkInstance;

  function logWizard(action, payload) {
    if (typeof window !== "undefined" && window.console && window.console.log) {
      if (typeof payload === "undefined") {
        window.console.log("[NotificationsWizard] " + action);
      } else {
        window.console.log("[NotificationsWizard] " + action, payload);
      }
    }
  }
  var EVENT_ALWAYS = "ALWAYS";
  var fLabels = [];
  var hfLabels = [];
  var typeofdata = [];
  var cloneGroup = "";
  var moduleData = "";
  var lastModule = jQuery("#module-name").val();

  // Private functions
  var filterButtonByModule = function () {
    var selectedModule = jQuery("#module-name").val(),
      upDateButton = jQuery("#sp-custom-button"),
      selectedOption = "",
      totalButtons = 0;
    jQuery("#custom-button > option").each(function (i) {
      jQuery(this).show();
      totalButtons = i + 1;
    });
    jQuery("#custom-button > option").each(function (i) {
      selectedOption = jQuery(this).attr("data-module");
      if (selectedModule === "") {
        jQuery(this).show();
      } else if (
        selectedOption !== "" &&
        selectedOption !== selectedModule &&
        jQuery(this).val() != ""
      ) {
        jQuery(this).hide();
        totalButtons -= 1;
      }
    });

    return totalButtons;
  };

  var loadCkEditor = function (inputId, additionalOptions) {
    // Destroy existing instance if it exists
    if (CKEDITOR.instances[inputId]) {
      CKEDITOR.instances[inputId].destroy(true);
    }

    var options = {
      contentsCss: [
        "themes/centaurus/css/bootstrap/bootstrap.min.css",
        "themes/centaurus/css/libs/font-awesome.css",
        "themes/centaurus/css/compiled/theme_styles.css",
        "themes/centaurus/css/compiled/theme_custom.css",
        "//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300|Titillium+Web:200,300,400",
      ],
      entities: false,
      language: "es",
      removePlugins: "elementspath",
      height: 220,
    };
    jQuery.extend(options, additionalOptions);
    return CKEDITOR.replace(inputId, options);
  };

  var upDateContent = function (style, reason) {
    if (typeof style === "undefined" || typeof reason === "undefined") {
      return;
    }
    var simpleTemplate = "",
      reasonTemplate = "alert-" + reason.toLowerCase(),
      reasonMessage = "!Cuidado!",
      type = jQuery("#notification-type").val(),
      buttons = jQuery("#custom-button"),
      inputText = jQuery("#modal-imput-text"),
      exitText = jQuery("#modal-exit-text"),
      html = checkInstance.getData();

    checkInstance.setData(simpleTemplate);

    if (reason === "SUCCESS") {
      reasonMessage = "Muy bien";
    } else if (reason === "INFO") {
      reasonMessage = "Atento";
    } else if (reason === "WARNING") {
      reasonMessage = "Cuidado";
    } else {
      reasonMessage = "Error";
    }
    if (type === "ALERT") {
      simpleTemplate = jQuery("#simple-alert-template")
        .html()
        .replace(/__ACTION__/g, reasonTemplate);
    } else if (type === "NOTIFY") {
      if (style === "SIMPLE") {
        simpleTemplate = jQuery("#simple-template")
          .html()
          .replace(/__ACTION__/g, reasonTemplate)
          .replace(/__MESSAGE__/g, reasonMessage);
      } else if (style === "EXPANDABLE") {
        simpleTemplate = jQuery("#simple-template-collapse")
          .html()
          .replace(/__ACTION__/g, reasonTemplate)
          .replace(/__MESSAGE__/g, reasonMessage);
      }
    } else if (type === "MODAL") {
      if (
        jQuery(html).find("#div-input-text").html() !== "__INPUT_TEXT__" &&
        jQuery(html).find("#div-input-text").html() != undefined &&
        html !== ""
      ) {
        inputText.val(jQuery(html).find("#div-input-text").html());
      }
      if (
        jQuery(html).find("#div-exit-text").html() !== "__EXIT_TEXT__" &&
        jQuery(html).find("#div-exit-text").html() != undefined &&
        html !== ""
      ) {
        exitText.val(jQuery(html).find("#div-exit-text").html());
      }
      if (buttons.val() !== null) {
        var buttonLabel, repalceLabel, buttonAction, replaceAction;
        switch (buttons.val().length) {
          case 1:
            simpleTemplate = jQuery("#simple-modal-1").html();
            break;
          case 2:
            simpleTemplate = jQuery("#simple-modal-2").html();
            break;
          case 3:
            simpleTemplate = jQuery("#simple-modal-3").html();
            break;
          case 4:
            simpleTemplate = jQuery("#simple-modal-4").html();
            break;
          default:
            simpleTemplate = jQuery("#simple-modal-0").html();
            break;
        }
        buttons.children("option:selected").each(function (i) {
          buttonLabel = jQuery(this).text();
          repalceLabel = "__LABEL" + (i + 1) + "__";
          buttonAction = jQuery(this).attr("data-style");
          replaceAction = "__ACTION" + (i + 1) + "__";
          simpleTemplate = simpleTemplate
            .replace(replaceAction, buttonAction)
            .replace(repalceLabel, buttonLabel);
        });
      } else {
        simpleTemplate = jQuery("#simple-modal-0").html();
        // Cargar sin botones
      }
      simpleTemplate = simpleTemplate
        .replace(/__INPUT_TEXT__/g, inputText.val())
        .replace(/__EXIT_TEXT__/g, exitText.val());
      if (reason === "INPUT_TEXT") {
        simpleTemplate = simpleTemplate
          .replace(/__EXIT_DISPLAY__/g, "none")
          .replace(/__INPUT_DISPLAY__/g, "block");
        simpleTemplate = simpleTemplate.replace(/__BUTTON_DISPLAY__/g, "block");
      } else {
        simpleTemplate = simpleTemplate
          .replace(/__EXIT_DISPLAY__/g, "block")
          .replace(/__INPUT_DISPLAY__/g, "none");
        simpleTemplate = simpleTemplate.replace(/__BUTTON_DISPLAY__/g, "none");
      }
    }

    checkInstance.setData(simpleTemplate);
  };

  var onGetModuleColumnsSuccessHandler = function (responseText) {
    responseText = responseText.replace(
      /^[\s\ufeff\xA0]+|[\s\uFEFF\xA0]+$/g,
      ""
    );
    var fieldSelect = jQuery("#filter-column"),
      tableAlias = "",
      fields = JSON.parse(responseText);

    if (fields === null || fields === undefined || fields.length === 0) {
      // If no fields returned, show error message
      fieldSelect.empty();
      fieldSelect.append(
        jQuery("<option>", {
          value: "",
          text: "No hay campos disponibles para este módulo",
        })
      );
      return;
    }

    moduleData = responseText;

    // FILTROS DE PERÍODO: Solo mostrar fecha de creación y modificación
    // El select #filter-column es para filtros de período, no para filtros avanzados
    fieldSelect.empty();
    fieldSelect.append(
      jQuery("<option>", {
        value: "",
        text: "Seleccionar un campo",
      })
    );

    // Para filtros de período, SIEMPRE mostrar solo fechas de creación y modificación
    fieldSelect.append(
      jQuery("<option>", {
        value: "crm.createdtime",
        text: "Fecha de Creación",
      })
    );
    fieldSelect.append(
      jQuery("<option>", {
        value: "crm.modifiedtime",
        text: "Fecha de Modificación",
      })
    );

    // No procesar más campos para el select de período
    // Los filtros avanzados usan una función diferente (setFieldsOptions)
  };

  var onAjaxFailureHandler = function (jQueryResponse) {
    alert("Se ha presentado un error. Intenta más tarde");
  };

  var setFieldsOptions = function (obj) {
    var fieldSelect,
      fields = JSON.parse(moduleData);
    if (fields === null || fields === undefined) {
      return;
    }
    fieldSelect = obj.find("select").eq(0);

    jQuery.each(fields, function (i, field) {
      if (
        field === null ||
        field === undefined ||
        !(field instanceof Object) ||
        jQuery.isEmptyObject(field)
      ) {
        return;
      }
      if (field.typeofdata != "") {
        // Build field label with module info if available
        var fieldLabel = field.label;
        if (field.module && field.module !== "Users") {
          // Add module name to label for non-Users fields
          fieldLabel = field.label + " (" + field.module + ")";
        }
        var option = jQuery("<option>", {
          value: field.fieldname,
          text: fieldLabel,
        })
          .attr("data-type", field.typeofdata)
          .attr("data-uitype", field.uitype);
        // Add module info if available
        if (field.module) {
          option.attr("data-module", field.module);
        }
        // Add helpinfo if available
        if (field.helpinfo && field.helpinfo.length > 0) {
          option.attr("data-helpinfo", field.helpinfo);
          option.attr("title", field.helpinfo);
          console.log(
            '[NotificationsWizard] setFieldsOptions - Field "' +
              field.label +
              '" (module: ' +
              (field.module || "N/A") +
              ") has helpinfo:",
            field.helpinfo
          );
        } else {
          console.log(
            '[NotificationsWizard] setFieldsOptions - Field "' +
              field.label +
              '" (module: ' +
              (field.module || "N/A") +
              ") has no helpinfo"
          );
        }
        fieldSelect.append(option);
      }
    });
  };

  var closeNotifyWizard = function () {
    if (wizard) {
      wizard.reset().close().trigger("closed");
    }
  };

  var destroyWizard = function () {
    wizard = null;
    window.location.reload();
  };

  var hideBackButton = function (card) {
    // Hide back button for first step only
    wizard.backButton.hide();
    setWindowsSize(card);
  };

  var enableBackButton = function (card) {
    // Show back button for steps 2, 3, and 4
    wizard.backButton.show();
    setWindowsSize(card);
  };

  var setExistingNotifyData = function (card) {
    var wizardAction, requestParams, notificationId;
    // Don't hide button here - it's handled by step-specific functions
    setWindowsSize(card);
    if (card.alreadyVisited()) {
      return;
    }

    wizardAction = wizard.cards["start"].el.find(
      'input[name="wizardaction"]:checked'
    );
    if (wizardAction.attr("id") === "wizard-action-duplicate-from-pattern") {
      notificationId = wizard.cards["start"].el
        .find("#notify-pattern-id")
        .val();
    } else if (
      jQuery.inArray(wizardAction.attr("id"), [
        "wizard-action-duplicate",
        "wizard-action-edit",
      ]) !== -1
    ) {
      notificationId = card.el
        .closest("form")
        .find('input[name="record"]')
        .val();
    } else {
      notificationId = null;
    }

    wizard.cards["setp-1"].el
      .find(".data-section")
      .empty()
      .append('<div class="text-center">Cargando...</div>');
    wizard.cards["setp-2"].el.find(".data-section").empty();
    wizard.cards["setp-3"].el.find(".data-section").empty();

    requestParams = [
      "module=notifications",
      "action=" + encodeURIComponent(wizardAction.val()),
    ];
    if (notificationId) {
      requestParams.push("record=" + encodeURIComponent(notificationId));
    }
    jQuery
      .ajax("index.php", {
        data: requestParams.join("&"),
        dataType: "html",
        method: "get",
      })
      .done(function (response) {
        var notificationData = jQuery(response),
          steps = jQuery(notificationData).find("fieldset");
        steps.each(function (i) {
          wizard.cards["setp-" + (i + 1)].el
            .find(".data-section")
            .empty()
            .append(jQuery(this).find(".data-section"));
        });
        // Don't disable 'start' card - allow user to navigate back with "Atrás" button
        // wizard.cards['start'].disable(true);
        checkInstance = init("contents");
        onDocumentReadyHandler();
      })
      .fail(function (jQueryResponse) {
        alert(
          "Se ha presentado un error. Notifica al administrador de la aplicación"
        );
      });
  };

  var setWindowsSize = function (card) {
    var cardName = card.name,
      footerTop = 0,
      cardContainer = jQuery(".wizard-card-container"),
      wizardCards = jQuery(".wizard-cards"),
      wizardModal = jQuery(".wizard-modal");
    if (cardName === "setp-1") {
      cardContainer.animate(
        {
          height: "65vh",
        },
        "slow"
      );
      wizardModal.animate(
        {
          height: "65vh",
        },
        "slow"
      );
      cardContainer.css("min-height", "60vh");
      jQuery("#setp-1-section").css("height", "59vh");
    } else if (cardName === "setp-2") {
      cardContainer.animate(
        {
          height: "65vh",
        },
        "slow"
      );
      wizardModal.animate(
        {
          height: "65vh",
        },
        "slow"
      );
      cardContainer.css("min-height", "60vh");
      jQuery("#setp-2-section").css("min-height", "59vh");
    } else if (cardName === "setp-3") {
      cardContainer.animate(
        {
          height: "100%",
        },
        "slow"
      );
      cardContainer.css("min-height", "60vh");
      wizardModal.animate(
        {
          height: "100%",
        },
        "slow"
      );
      wizardCards.css("min-height", "65vh");
      jQuery("#setp-3-section").css("min-height", "95%");
    }
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

    // DEBUG: Log card state to understand why button might not be enabled
    if (typeof window !== "undefined" && window.console && window.console.log) {
      var totalCards = Object.keys(cards).length;
      var isLastCard = index >= totalCards - 1;
      window.console.log("[NotificationsWizard] updateProgressBar:", {
        currentCard: activeCard ? activeCard.name : "none",
        currentIndex: index,
        totalCards: totalCards,
        isLastCard: isLastCard,
        _readyToSubmit: wizard._readyToSubmit,
        nextButtonText: wizard.nextButton
          ? wizard.nextButton.text()
          : "unknown",
        nextButtonDisabled: wizard.nextButton
          ? wizard.nextButton.prop("disabled")
          : "unknown",
      });
    }
  };

  var validateStart = function (card) {
    var dataForm = jQuery("#" + card.name + "-section > .data-section"),
      error = true,
      field,
      value,
      infoText = "";

    jQuery("span[id ^= sp-n-]").html("");
    jQuery("div[id ^= dv-n-]").removeClass("has-error");

    wizardAction = wizard.cards["start"].el.find(
      'input[name="wizardaction"]:checked'
    );
    if (wizardAction.attr("id") === "wizard-action-duplicate-from-pattern") {
      field = dataForm.find("#notify-pattern-id");
      value = field.val();
      if (value === null || value === undefined || value.length === 0) {
        jQuery("#sp-n-notify-pattern-id").html("Selecciona el patrón");
        jQuery("#dv-n-notify-pattern-id").addClass("has-error");
        error = false;
      }
    }
    return error;
  };

  var validateSetpOne = function (card) {
    console.log("[NotificationsWizard] validateSetpOne START");
    var dataForm = jQuery("#" + card.name + "-section > .data-section"),
      error = true,
      field,
      value,
      infoText = "",
      moduleField = "",
      moduleValue = "",
      selectedType = "";

    jQuery("span[id ^= sp-n-]").html("");
    jQuery("div[id ^= dv-n-]").removeClass("has-error");

    field = dataForm.find("#module-name");
    value = field.val();
    moduleValue = value;
    moduleField = field;
    if (value === null || value === undefined || value.length === 0) {
      jQuery("#sp-n-module-name").html("Selecciona el módulo objetivo");
      jQuery("#dv-n-module-name").addClass("has-error");
      error = false;
    }

    field = dataForm.find("#notification-type");
    value = field.val();
    selectedType = value;
    if (value === null || value === undefined || value.trim() === "") {
      jQuery("#sp-n-notification-type").html(
        "Selecciona el tipo de comunicación"
      );
      jQuery("#dv-n-notification-type").addClass("has-error");
      error = false;
    }

    field = dataForm.find("#event");
    value = field.val();
    if (value === null || value === undefined || value.trim() === "") {
      jQuery("#sp-n-event").html("Selecciona el evento");
      jQuery("#dv-n-event").addClass("has-error");
      error = false;
    } else if (jQuery.inArray(value, ["TOTAL RECORDS REACHED"]) !== -1) {
      field = dataForm.find("#event-parameter");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        jQuery("#sp-n-event-parameter").html("Introduce el parámetro");
        jQuery("#dv-n-event-parameter").addClass("has-error");
        error = false;
      }
    }

    field = dataForm.find("#notification-from");
    value = field.val();
    if (value === null || value === undefined || value.length === 0) {
      jQuery("#sp-n-notification-from").html(
        "Selecciona para quién es la notificación"
      );
      jQuery("#dv-n-notification-from").addClass("has-error");
      error = false;
    }

    if (selectedType === "MODAL" && moduleValue !== "" && error) {
      moduleValue = moduleField.children("option:selected").text();
      var totalButtons = filterButtonByModule();
      console.log("[NotificationsWizard] validateSetpOne - MODAL check: totalButtons=" + totalButtons);
      if (totalButtons < 1) {
        console.log("[NotificationsWizard] validateSetpOne - No custom buttons, showing confirm dialog");
        if (
          !confirm(
            'No hay botones personalizados para el módulo "' +
              moduleValue +
              '" ¿Desea continuar?'
          )
        ) {
          console.log("[NotificationsWizard] validateSetpOne - User cancelled, setting error=false");
          error = false;
        }
      }
    }

    console.log("[NotificationsWizard] validateSetpOne END - returning error=" + error);
    return error;
  };

  var validateSetpTwo = function (card) {
    var field,
      value,
      isValidate = true,
      error = true,
      dataForm = jQuery("#" + card.name + "-section > .data-section"),
      debugRows = [],
      idfield;

    jQuery("span[id ^= sp-n-]").html("");
    jQuery("div[id ^= dv-n-]").removeClass("has-error");

    // Validación SIMPLE: Si hay filas de filtros Y algún valor está vacío → ERROR
    var totalFilterRows = jQuery("li[id ^= row-]").length;
    var hasIncompleteFilters = false;
    var incompleteFilterMessage = "";
    var emptyValueRows = 0;

    logWizard("validateSetpTwo.start", { totalFilterRows: totalFilterRows });

    // Si hay filas de filtros (> 0), validar que no haya valores vacíos
    if (totalFilterRows > 0) {
      jQuery("li[id ^= row-]").each(function (index, item) {
        var picklistSelect = jQuery(item).find("select.filter-value-picklist"),
          inputValue = "";

        // Get value from picklist select if it exists and is visible, otherwise from input
        if (picklistSelect.length > 0 && !picklistSelect.hasClass("hide")) {
          inputValue = picklistSelect.val();
        } else {
          inputValue = jQuery(item).find("input").eq(0).val();
        }

        // Clear previous error messages
        jQuery(item).find("span").eq(2).html("");
        if (picklistSelect.length > 0) {
          picklistSelect.parent().removeClass("has-error");
        }
        jQuery(item)
          .find("input")
          .eq(0)
          .parent()
          .parent()
          .removeClass("has-error");

        // VALIDACIÓN SIMPLE: Si el valor está vacío → ERROR
        if (
          inputValue === "" ||
          inputValue === null ||
          inputValue === "Ninguno" ||
          inputValue === "-Ninguno-"
        ) {
          emptyValueRows++;
          hasIncompleteFilters = true;
          isValidate = false;

          // Mostrar error en el campo de valor
          if (picklistSelect.length > 0 && !picklistSelect.hasClass("hide")) {
            jQuery(item)
              .find("span")
              .eq(2)
              .html("Debe completar el valor del filtro o eliminar la fila");
            picklistSelect.parent().addClass("has-error");
          } else {
            jQuery(item)
              .find("span")
              .eq(2)
              .html("Debe completar el valor del filtro o eliminar la fila");
            jQuery(item)
              .find("input")
              .eq(0)
              .parent()
              .parent()
              .addClass("has-error");
          }

          debugRows.push({
            index: index,
            value: inputValue,
            issues: ["empty-filter-value"],
            status: "incomplete",
          });
        } else {
          // Valor completo
          debugRows.push({
            index: index,
            value: inputValue,
            issues: [],
            status: "complete",
          });
        }
      });
    }

    logWizard("validateSetpTwo.analysis", {
      totalRows: totalFilterRows,
      emptyValueRows: emptyValueRows,
      hasIncompleteFilters: hasIncompleteFilters,
      debugRows: debugRows,
    });

    if (hasIncompleteFilters) {
      if (emptyValueRows === 1) {
        incompleteFilterMessage =
          "Hay 1 filtro con variable seleccionada pero sin valor. Complete el valor o elimine la fila.";
      } else {
        incompleteFilterMessage =
          "Hay " +
          emptyValueRows +
          " filtros con variables seleccionadas pero sin valores. Complete los valores o elimine las filas.";
      }

      // Show error message at the top of advanced filters section
      jQuery('.nav-tabs a[href="#advanced"]').tab("show");

      // Create or update error message container
      var errorContainer = jQuery("#advanced-filter-error");
      if (errorContainer.length === 0) {
        // Create error container if it doesn't exist
        errorContainer = jQuery(
          '<div id="advanced-filter-error" class="alert alert-danger" style="margin-bottom: 15px;"></div>'
        );
        jQuery("#advanced .tab-pane").prepend(errorContainer);
      }
      errorContainer.html(
        "<strong>Error en Filtros Avanzados:</strong> " +
          incompleteFilterMessage
      );
      errorContainer.show();

      logWizard("validateSetpTwo.incompleteFilters", {
        emptyValues: emptyValueRows,
        message: incompleteFilterMessage,
        debugRows: debugRows,
      });

      return false; // Stop validation - do not allow saving
    } else {
      // Remove error message if validation passes
      jQuery("#advanced-filter-error").hide();
    }

    var periodIssues = [];
    var currentEvent = dataForm.find("#event").val();
    var notificationType = jQuery("#notification-type").val();
    var periodValue = "";
    var columnValue = "";

    // FILTROS DE PERÍODO SON OPCIONALES PARA TODOS LOS TIPOS Y EVENTOS
    // Solo validar si el usuario decidió usar filtros de período (seleccionó campo o duración)
    field = dataForm.find("#filter-period");
    value = field.val();
    periodValue = value;

    var columnField = dataForm.find("#filter-column");
    columnValue = columnField.val();

    // Determinar si el usuario está intentando usar filtros de período
    var isPeriodFilterUsed =
      (periodValue !== null &&
        periodValue !== undefined &&
        periodValue.length > 0) ||
      (columnValue !== null &&
        columnValue !== undefined &&
        columnValue.length > 0);

    if (isPeriodFilterUsed) {
      // Si el usuario está usando filtros de período, validar que estén completos

      // Validar duración
      if (
        periodValue === null ||
        periodValue === undefined ||
        periodValue.length === 0
      ) {
        jQuery("#sp-n-filter-period").html("Selecciona la duración");
        jQuery("#dv-n-filter-period").addClass("has-error");
        error = false;
        periodIssues.push("missing-period");
      } else {
        jQuery("#sp-n-filter-period").html("");
        jQuery("#dv-n-filter-period").removeClass("has-error");
      }

      // Validar campo
      if (
        columnValue === null ||
        columnValue === undefined ||
        columnValue.length === 0
      ) {
        jQuery("#sp-n-filter-column").html("Selecciona un campo");
        jQuery("#dv-n-filter-column").addClass("has-error");
        error = false;
        periodIssues.push("missing-column");
      } else {
        jQuery("#sp-n-filter-column").html("");
        jQuery("#dv-n-filter-column").removeClass("has-error");
      }

      // Si seleccionó período personalizado, validar fechas
      if (periodValue === "custom") {
        field = dataForm.find("#filter-start-date");
        value = field.val();
        if (value === null || value === undefined || value.length === 0) {
          jQuery("#sp-n-filter-start-date").html(
            "Selecciona la fecha de inicio"
          );
          jQuery("#dv-n-filter-start-date").addClass("has-error");
          error = false;
          periodIssues.push("missing-start");
        } else {
          jQuery("#sp-n-filter-start-date").html("");
          jQuery("#dv-n-filter-start-date").removeClass("has-error");
        }
        field = dataForm.find("#filter-end-date");
        value = field.val();
        if (value === null || value === undefined || value.length === 0) {
          jQuery("#sp-n-filter-end-date").html("Selecciona la fecha de fin");
          jQuery("#dv-n-filter-end-date").addClass("has-error");
          error = false;
          periodIssues.push("missing-end");
        } else {
          jQuery("#sp-n-filter-end-date").html("");
          jQuery("#dv-n-filter-end-date").removeClass("has-error");
        }
      }
    } else {
      // No se están usando filtros de período - limpiar cualquier error de validación
      jQuery("#sp-n-filter-period").html("");
      jQuery("#dv-n-filter-period").removeClass("has-error");
      jQuery("#sp-n-filter-column").html("");
      jQuery("#dv-n-filter-column").removeClass("has-error");
      jQuery("#sp-n-filter-start-date").html("");
      jQuery("#dv-n-filter-start-date").removeClass("has-error");
      jQuery("#sp-n-filter-end-date").html("");
      jQuery("#dv-n-filter-end-date").removeClass("has-error");
    }

    logWizard("validateSetpTwo.rows", debugRows);
    logWizard("validateSetpTwo.period", {
      period: dataForm.find("#filter-period").val(),
      periodIssues: periodIssues,
    });

    if (!error) {
      jQuery('.nav-tabs a[href="#period"]').tab("show");
      logWizard("validateSetpTwo.result", {
        result: false,
        reason: "period",
        periodIssues: periodIssues,
      });
      return error;
    }

    if (!isValidate) {
      jQuery('.nav-tabs a[href="#advanced"]').tab("show");
      logWizard("validateSetpTwo.result", {
        result: false,
        reason: "advanced",
        rows: debugRows,
      });
      return isValidate;
    }

    logWizard("validateSetpTwo.result", { result: true });
    return true;
  };

  var validateSetpThree = function (card) {
    // DEBUG: Log validation start
    if (typeof window !== "undefined" && window.console && window.console.log) {
      window.console.log(
        "[NotificationsWizard] validateSetpThree: Starting validation"
      );
    }

    var dataForm = jQuery("#" + card.name + "-section > .data-section"),
      selectedType = jQuery("#notification-type").val(),
      inputText = jQuery("#modal-imput-text"),
      exitText = jQuery("#modal-exit-text"),
      error = true,
      field,
      value,
      infoText = "";

    field = dataForm.find("#notification-name");
    value = field.val();
    if (value === null || value === undefined || value.trim() === "") {
      jQuery("#sp-n-notification-name").html("Introduce el nombre");
      jQuery("#dv-n-notification-name").addClass("has-error");
      error = false;
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: FAILED - notification-name is empty"
        );
      }
    } else {
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: PASSED - notification-name =",
          value
        );
      }
    }

    field = dataForm.find("#notification-description");
    value = field.val();
    if (value === null || value === undefined || value.trim() === "") {
      jQuery("#sp-n-notification-description").html("Introduce la descripción");
      jQuery("#dv-n-notification-description").addClass("has-error");
      error = false;
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: FAILED - notification-description is empty"
        );
      }
    } else {
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: PASSED - notification-description =",
          value
        );
      }
    }

    field = dataForm.find("#notification-users");
    value = field.val();
    if (value === null || value === undefined || value.length === 0) {
      jQuery("#sp-n-notification-users").html(
        "Selecciona los Usuarios o la opción todos"
      );
      jQuery("#dv-n-notification-users").addClass("has-error");
      error = false;
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: FAILED - notification-users is empty"
        );
      }
    } else {
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: PASSED - notification-users =",
          value
        );
      }
    }

    field = dataForm.find("#notification-status");
    value = field.val();
    if (value === null || value === undefined || value.trim() === "") {
      jQuery("#sp-n-notification-status").html("Selecciona el estatus");
      jQuery("#dv-n-notification-status").addClass("has-error");
      error = false;
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: FAILED - notification-status is empty"
        );
      }
    } else {
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: PASSED - notification-status =",
          value
        );
      }
    }

    field = dataForm.find("#contents");
    value = checkInstance.getData();
    if (value === null || value === undefined || value.trim() === "<br />") {
      jQuery("#sp-n-contents").html(
        "Introduce el contenido de la notificación"
      );
      jQuery("#dv-n-contents").addClass("has-error");
      error = false;
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: FAILED - contents is empty or only <br />"
        );
      }
    } else {
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: PASSED - contents has value"
        );
      }
    }

    if (selectedType === "ALERT") {
      // Validate action for ALERT style (required)
      // Search in the entire wizard, not just in the current card's dataForm
      field = wizard.el.find("#notification-action");
      if (field.length === 0) {
        // Try to find it in step 1 or step 3 data section
        field = wizard.cards["setp-1"].el.find("#notification-action");
        if (field.length === 0) {
          field = wizard.cards["setp-3"].el.find("#notification-action");
        }
      }
      value = field.length > 0 ? field.val() : null;
      if (value === null || value === undefined || value.trim() === "") {
        var errorContainer = wizard.cards["setp-1"].el.find(
          "#sp-n-notification-action"
        );
        if (errorContainer.length === 0) {
          errorContainer = wizard.cards["setp-3"].el.find(
            "#sp-n-notification-action"
          );
        }
        if (errorContainer.length === 0) {
          errorContainer = jQuery("#sp-n-notification-action");
        }
        errorContainer.html("Selecciona el motivo de la comunicación");
        var errorDiv = wizard.cards["setp-1"].el.find(
          "#dv-n-notification-action"
        );
        if (errorDiv.length === 0) {
          errorDiv = wizard.cards["setp-3"].el.find(
            "#dv-n-notification-action"
          );
        }
        if (errorDiv.length === 0) {
          errorDiv = jQuery("#dv-n-notification-action");
        }
        errorDiv.addClass("has-error");
        error = false;
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] validateSetpThree: FAILED - notification-action is empty (ALERT type)"
          );
        }
      } else {
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] validateSetpThree: PASSED - notification-action =",
            value,
            "(ALERT type)"
          );
        }
      }

      // Validate event for ALERT style (required)
      // Search in the entire wizard, not just in the current card's dataForm
      field = wizard.el.find("#event");
      if (field.length === 0) {
        // Try to find it in step 1 data section
        field = wizard.cards["setp-1"].el.find("#event");
      }
      value = field.length > 0 ? field.val() : null;
      if (value === null || value === undefined || value.trim() === "") {
        var errorContainer = wizard.cards["setp-1"].el.find("#sp-n-event");
        if (errorContainer.length === 0) {
          errorContainer = jQuery("#sp-n-event");
        }
        errorContainer.html("Selecciona un evento");
        var errorDiv = wizard.cards["setp-1"].el.find("#dv-n-event");
        if (errorDiv.length === 0) {
          errorDiv = jQuery("#dv-n-event");
        }
        errorDiv.addClass("has-error");
        error = false;
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] validateSetpThree: FAILED - event is empty (ALERT type)"
          );
        }
      } else {
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] validateSetpThree: PASSED - event =",
            value,
            "(ALERT type)"
          );
        }
      }

      // Module validation for ALERT is done in Step 1 (setp-1)
      // module-name is validated in validateSetpOne, not here
      // The module-name value from Step 1 will be used for both modulefilter and modulenames[]
      if (
        typeof window !== "undefined" &&
        window.console &&
        window.console.log
      ) {
        var moduleNameValue =
          wizard.el.find("#module-name").val() ||
          wizard.cards["setp-1"].el.find("#module-name").val();
        window.console.log(
          "[NotificationsWizard] validateSetpThree: ALERT type uses module-name from Step 1 =",
          moduleNameValue
        );
      }
    } else if (selectedType === "NOTIFY") {
      field = dataForm.find("#notification-veiw");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        jQuery("#sp-n-notification-veiw").html("Ubicación de la comunicación");
        jQuery("#dv-n-notification-veiw").addClass("has-error");
        error = false;
      }

      // Validate module name for NOTIFY (required) - module is already selected in step 1
      // Search in the entire wizard for the module-name field
      field = wizard.el.find("#module-name");
      if (field.length === 0) {
        // Try to find it in the step 1 data section
        field = wizard.cards["setp-1"].el.find("#module-name");
      }
      value = field.length > 0 ? field.val() : null;
      if (value === null || value === undefined || value.trim() === "") {
        jQuery("#sp-n-module-name").html(
          "Selecciona el módulo donde se mostrará la notificación"
        );
        jQuery("#dv-n-module-name").addClass("has-error");
        error = false;
      }

      // NOTE: module-names (multiple select) is NOT validated for NOTIFY
      // because it only appears in step 3 for ALERT type, not for NOTIFY

      field = dataForm.find("#notification-action");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        jQuery("#sp-n-notification-action").html(
          "Selecciona el motivo de la comunicación"
        );
        jQuery("#dv-n-notification-action").addClass("has-error");
        error = false;
      }

      field = dataForm.find("#notification-html");
      value = field.val();
      if (value === null || value === undefined || value.trim() === "") {
        jQuery("#sp-n-notification-html").html(
          "Selecciona el estilo de la notificación"
        );
        jQuery("#dv-n-notification-html").addClass("has-error");
        error = false;
      }
    } else if (selectedType === "MODAL") {
      // Validate module name for MODAL (required)
      // Search in the entire wizard, not just in the current card's dataForm
      field = wizard.el.find("#module-name");
      if (field.length === 0) {
        // Try to find it in the step 1 data section
        field = wizard.cards["setp-1"].el.find("#module-name");
      }
      value = field.length > 0 ? field.val() : null;
      if (value === null || value === undefined || value.trim() === "") {
        // Show error in step 1 if module-name is there, otherwise in current step
        var errorContainer =
          wizard.cards["setp-1"].el.find("#sp-n-module-name");
        if (errorContainer.length === 0) {
          errorContainer = jQuery("#sp-n-module-name");
        }
        errorContainer.html("Selecciona el módulo donde se mostrará el modal");
        var errorDiv = wizard.cards["setp-1"].el.find("#dv-n-module-name");
        if (errorDiv.length === 0) {
          errorDiv = jQuery("#dv-n-module-name");
        }
        errorDiv.addClass("has-error");
        error = false;
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] validateSetpThree: FAILED - module-name is empty (MODAL type)"
          );
        }
      } else {
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] validateSetpThree: PASSED - module-name =",
            value,
            "(MODAL type)"
          );
        }
      }

      // Validate modal content - must have both input text and exit text
      field = dataForm.find("#contents");
      value = checkInstance.getData();
      if (value !== null && value !== undefined && value.trim() !== "<br />") {
        var inputTextValue = jQuery(value).find("#div-input-text").html();
        var exitTextValue = jQuery(value).find("#div-exit-text").html();

        inputText.val(inputTextValue);
        exitText.val(exitTextValue);

        // Validate that exit text exists (required for MODAL)
        if (
          exitTextValue === null ||
          exitTextValue === undefined ||
          exitTextValue.trim() === ""
        ) {
          jQuery("#sp-n-contents").html(
            "Introduce el texto de salida del modal"
          );
          jQuery("#dv-n-contents").addClass("has-error");
          error = false;
          if (
            typeof window !== "undefined" &&
            window.console &&
            window.console.log
          ) {
            window.console.log(
              "[NotificationsWizard] validateSetpThree: FAILED - modal-exit-text is empty (MODAL type)"
            );
          }
        } else {
          if (
            typeof window !== "undefined" &&
            window.console &&
            window.console.log
          ) {
            window.console.log(
              "[NotificationsWizard] validateSetpThree: PASSED - modal-exit-text has value (MODAL type)"
            );
          }
        }

        // Validate that input text exists (required for MODAL when modaltext is INPUT_TEXT)
        var modalTextType = dataForm.find("#modal-text").val();
        if (
          modalTextType === "INPUT_TEXT" &&
          (inputTextValue === null ||
            inputTextValue === undefined ||
            inputTextValue.trim() === "")
        ) {
          jQuery("#sp-n-contents").html(
            "Introduce el texto de entrada del modal"
          );
          jQuery("#dv-n-contents").addClass("has-error");
          error = false;
          if (
            typeof window !== "undefined" &&
            window.console &&
            window.console.log
          ) {
            window.console.log(
              "[NotificationsWizard] validateSetpThree: FAILED - modal-input-text is empty (MODAL type, INPUT_TEXT)"
            );
          }
        } else if (modalTextType === "INPUT_TEXT") {
          if (
            typeof window !== "undefined" &&
            window.console &&
            window.console.log
          ) {
            window.console.log(
              "[NotificationsWizard] validateSetpThree: PASSED - modal-input-text has value (MODAL type, INPUT_TEXT)"
            );
          }
        }
      } else {
        // Contents is empty or only <br />
        error = false;
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] validateSetpThree: FAILED - contents is empty or only <br /> (MODAL type)"
          );
        }
      }
    }

    // DEBUG: Log selected type
    if (typeof window !== "undefined" && window.console && window.console.log) {
      window.console.log(
        "[NotificationsWizard] validateSetpThree: selectedType =",
        selectedType
      );
    }

    // DEBUG: Log validation result
    if (typeof window !== "undefined" && window.console && window.console.log) {
      window.console.log(
        "[NotificationsWizard] validateSetpThree: Validation result =",
        error
      );
      if (!error) {
        window.console.log(
          "[NotificationsWizard] validateSetpThree: Validation FAILED - check error messages above"
        );
      }
    }

    return error;
  };

  var filterPatternByType = function (obj) {
    var element = jQuery(obj),
      type = element.val(),
      pattern = jQuery("#notify-pattern-id"),
      selectedType;
    jQuery(pattern).val("");
    jQuery("#notify-pattern-id > option").each(function (i) {
      jQuery(this).show();
    });
    jQuery("#notify-pattern-id > option").each(function (i) {
      selectedType = jQuery(this).attr("data-type");
      if (type === "") {
        jQuery(this).show();
      } else if (selectedType !== "" && selectedType !== type) {
        jQuery(this).hide();
      }
    });
  };

  var openModalWizard = function (notificationId) {
    var template = jQuery("#notifications-wizard-template");
    wizard = jQuery(template.html()).wizard({
      backdrop: "static",
      showCancel: true,
      buttons: {
        cancelText: "Cancelar",
        nextText: "Siguiente →",
        backText: "← Atrás",
        submitText: "Guardar",
        submittingText: "Guardando...",
      },
      baseHeight: 0,
      onNext: function(card, wizard) {
        console.log("[NotificationsWizard] onNext triggered for card:", card.name);
        
        // Manually trigger validation since the library doesn't always do it when loading via AJAX
        var validationResult = true;
        
        if (card.name === 'start') {
          validationResult = validateStart(card);
          console.log("[NotificationsWizard] onNext - validateStart returned:", validationResult);
        } else if (card.name === 'setp-1') {
          validationResult = validateSetpOne(card);
          console.log("[NotificationsWizard] onNext - validateSetpOne returned:", validationResult);
        } else if (card.name === 'setp-2') {
          validationResult = validateSetpTwo(card);
          console.log("[NotificationsWizard] onNext - validateSetpTwo returned:", validationResult);
        }
        
        return validationResult;
      },
    });

    // Hide back button initially (we're on the first step)
    wizard.backButton.hide();

    if (notificationId) {
      wizard.cards["start"].el
        .closest("form")
        .find('input[name="record"]')
        .val(notificationId);
      wizard.cards["start"].el
        .find("#new-notify-options")
        .hide()
        .find('input[name="wizardaction"]')
        .prop("disabled", true);
      wizard.cards["start"].el
        .find("#existing-notify-options")
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
        .find("#existing-notify-options")
        .hide()
        .find('input[name="wizardaction"]')
        .prop("disabled", true);
      wizard.cards["start"].el
        .find("#new-notify-options")
        .show()
        .find('input[name="wizardaction"]')
        .prop("disabled", false)
        .first()
        .prop("checked", true);
    }

    // Configure button visibility for each step
    // Step 0 (start): Hide back button - it's the first step
    wizard.cards["start"]
      .on("validate", validateStart)
      .on("selected", hideBackButton);

    // Step 1 (setp-1): Show back button + load existing data if needed
    wizard.cards["setp-1"]
      .on("validate", validateSetpOne)
      .on("selected", function (card) {
        enableBackButton(card);
        setExistingNotifyData(card);
      });

    // Step 2 (setp-2): Show back button
    wizard.cards["setp-2"]
      .on("validate", validateSetpTwo)
      .on("selected", enableBackButton);

    // Step 3 (setp-3): Show back button
    wizard.cards["setp-3"]
      .on("validate", validateSetpThree)
      .on("selected", enableBackButton);
    wizard
      .on("submit", submitWizard)
      .on("closed", destroyWizard)
      .on("incrementCard", updateProgressBar)
      .on("decrementCard", updateProgressBar)
      .on("readySubmit", function () {
        // Log when ready to submit
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] readySubmit: Button should be enabled now"
          );
        }
      });

    // Add click event to submit button to log before submit
    // Wait for wizard to be fully initialized
    setTimeout(function () {
      if (wizard && wizard.nextButton) {
        wizard.nextButton.on("click", function (e) {
          var currentCard = wizard.getActiveCard();
          var isReadyToSubmit = wizard._readyToSubmit;
          var buttonElement = jQuery(this);

          // Log button state before submit
          if (
            typeof window !== "undefined" &&
            window.console &&
            window.console.log
          ) {
            window.console.log("[NotificationsWizard] Submit button clicked");
            window.console.log(
              "[NotificationsWizard] Current card:",
              currentCard ? currentCard.name : "none"
            );
            window.console.log(
              "[NotificationsWizard] _readyToSubmit:",
              isReadyToSubmit
            );
            window.console.log(
              "[NotificationsWizard] Total cards:",
              wizard._cards ? wizard._cards.length : "unknown"
            );
            window.console.log(
              "[NotificationsWizard] Current card index:",
              currentCard ? currentCard.index : "unknown"
            );
            window.console.log("[NotificationsWizard] Button DOM state:", {
              disabled: buttonElement.prop("disabled"),
              hasClassDisabled: buttonElement.hasClass("disabled"),
              text: buttonElement.text(),
              isVisible: buttonElement.is(":visible"),
            });

            // Log period filter values before submit
            var columnPeriod = wizard.el.find("#filter-column").val();
            var filterPeriod = wizard.el.find("#filter-period").val();
            var startDate = wizard.el.find("#filter-start-date").val();
            var endDate = wizard.el.find("#filter-end-date").val();
            window.console.log(
              "[NotificationsWizard] Period filter values before submit:",
              {
                columnPeriod: columnPeriod,
                filterPeriod: filterPeriod,
                startDate: startDate,
                endDate: endDate,
              }
            );

            // Log all form data (DISABLED - can cause browser hang with large forms)
            // var allFormData = wizard.el
            //   .find("input, select, textarea")
            //   .serialize();
            // window.console.log(
            //   "[NotificationsWizard] All form data before submit:",
            //   allFormData
            // );
          }
        });
      }
    }, 500);

    wizard.show();
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
      }
    );
  };

  var setWizardAction = function (obj) {
    var radio = jQuery(obj),
      action = radio.val(),
      patternSection = radio
        .closest(".wizard-input-section")
        .find("#notify-pattern");

    if (radio.attr("id") === "wizard-action-duplicate-from-pattern") {
      patternSection.find("#notify-type").prop("disabled", false);
      patternSection.find("#notify-pattern-id").prop("disabled", false);
      patternSection.show();
    } else {
      patternSection.hide();
      patternSection.find("#notify-type").val("").prop("disabled", true);
      patternSection.find("#notify-pattern-id").val("").prop("disabled", true);
    }
  };

  var submitWizard = function (wizard) {
    var field = jQuery("#contents");
    field.val(checkInstance.getData());

    // For NOTIFY and MODAL types, copy module-name value to module-names array
    // because module-names select is hidden in step 3 for these types
    var notificationType = wizard.el.find("#notification-type").val();
    if (notificationType === "NOTIFY" || notificationType === "MODAL") {
      var moduleName = wizard.el.find("#module-name").val();
      var moduleNamesSelect = wizard.el.find("#module-names");
      if (moduleName && moduleNamesSelect.length > 0) {
        // Clear any existing selections
        moduleNamesSelect.val([]);
        // Set the module-name value as the only selected option
        moduleNamesSelect
          .find('option[value="' + moduleName + '"]')
          .prop("selected", true);
        if (
          typeof window !== "undefined" &&
          window.console &&
          window.console.log
        ) {
          window.console.log(
            "[NotificationsWizard] submitWizard: Copied module-name to module-names for " +
              notificationType +
              " type:",
            moduleName
          );
        }
      }
    }

    // Serialize all form fields manually since wizard doesn't have a <form> element
    // Get all inputs, selects, and textareas within the wizard
    var formData = wizard.el.find("input, select, textarea").serialize();

    // DEBUG: Log what we're sending
    if (typeof window !== "undefined" && window.console && window.console.log) {
      window.console.log(
        "[NotificationsWizard] submitWizard: formData =",
        formData
      );
      // Log period filter values specifically
      var columnPeriod = wizard.el.find("#filter-column").val();
      var filterPeriod = wizard.el.find("#filter-period").val();
      var startDate = wizard.el.find("#filter-start-date").val();
      var endDate = wizard.el.find("#filter-end-date").val();
      window.console.log("[NotificationsWizard] Period filter values:", {
        columnPeriod: columnPeriod,
        filterPeriod: filterPeriod,
        startDate: startDate,
        endDate: endDate,
      });
    }

    // Ensure action=Save is included
    // Check if action is already in the data
    if (
      formData.indexOf("action=Save") === -1 &&
      formData.indexOf("action=Save&") === -1
    ) {
      // If not, add it explicitly
      if (formData.length > 0) {
        formData += "&action=Save";
      } else {
        formData = "action=Save";
      }
    }

    // Ensure module=notifications is included
    if (
      formData.indexOf("module=notifications") === -1 &&
      formData.indexOf("module=notifications&") === -1
    ) {
      if (formData.length > 0) {
        formData += "&module=notifications";
      } else {
        formData = "module=notifications";
      }
    }

    // DEBUG: Log final data
    if (typeof window !== "undefined" && window.console && window.console.log) {
      window.console.log(
        "[NotificationsWizard] submitWizard: final formData =",
        formData
      );
    }

    jQuery
      .ajax("index.php", {
        data: formData,
        dataType: "json",
        method: "post",
      })
      .done(function () {
        wizard.submitSuccess();
        wizard.hideButtons();
      })
      .fail(function (jQueryResponse) {
        var errorMessage = "Error al guardar la notificación";
        if (jQueryResponse.responseJSON) {
          errorMessage = jQueryResponse.responseJSON;
        } else if (jQueryResponse.responseText) {
          errorMessage = jQueryResponse.responseText;
        }
        wizard.el.find(".wizard-failure .message").text(errorMessage);
        wizard.submitFailure();
        wizard.hideButtons();
      });
  };

  // public methods
  var action = function (obj) {
    var reason = jQuery(obj).val(),
      style = jQuery("#notification-html").val();
    upDateContent(style, reason);
  };

  var init = function (textareaId) {
    return loadCkEditor(textareaId, {
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
        ["Styles", "Format", "Font", "FontSize", "-", "-", "Source"],
      ],
    });
  };

  var addFilterGroup = function (obj) {
    var dataForm = jQuery("#setp-1-section > .data-section"),
      module = dataForm.find("#module-name"),
      group = "";
    if (module.val() == "") {
      module.parent().addClass("has-error");
      module.parent().find(".help-block").html("Selecciona el modulo");
      return false;
    }
    if (moduleData === "") {
      lastModule = module.val();
      hasGroup = false;
      getModuleColumns(module);
    }

    var conditionGroups = jQuery(".action-bar"),
      conditionGroupTemplate = jQuery(
        jQuery("#condition-group-template")
          .html()
          .replace(/__GROUP_ID__/g, totalFilterGroup)
      ),
      conditionTemplate = jQuery("#condition-template")
        .html()
        .replace(/__GROUP_ID__/g, totalFilterGroup); //.replace(/__CONDITION_ID__/g, -1)
    conditionGroupTemplate.find(".conditions").append(conditionTemplate);
    conditionGroups.before(conditionGroupTemplate);
    group = jQuery("#group-" + totalFilterGroup);
    totalFilterGroup += 1;
    totalFilterRow += 1;
    hasGroup = true;
    jQuery(obj).attr("data-group", totalFilterGroup);

    setFieldsOptions(group);
    if (totalFilterGroup > 1) {
      jQuery("#group-" + (totalFilterGroup - 2))
        .find(".operator")
        .removeClass("hidden")
        .removeAttr("disabled");
    }
  };

  var createNotification = function (obj) {
    var form = jQuery(obj);
    if (validateNotification(form) && validateFilters(form)) {
      return true;
    } else {
      return false;
    }
  };

  var eraseFilterGroup = function (obj) {
    var elementGroup,
      thisGroup,
      idGroup,
      lastGroup,
      infoTexto =
        "¿Esás seguro de borrar el grupo de condiciones seleccionado?";
    thisGroup = jQuery(obj).parent().parent().parent().parent();
    idGroup = thisGroup.attr("id");
    var r = confirm(infoTexto);
    if (r == true) {
      lastGroup = jQuery("div.filter_goup").last().attr("id");
      if (idGroup == lastGroup) {
        thisGroup
          .prev()
          .find(".operator")
          .addClass("hidden")
          .attr("disabled", "disabled");
        totalFilterGroup -= 1;
      }
      thisGroup.remove();
    }
  };

  var eraseFilterRow = function (obj) {
    var prevElementRow,
      thisRow,
      thisId,
      lastRowId,
      infoTexto = "¿Esás seguro de borrar la condición seleccionada?";
    var r = confirm(infoTexto);
    if (r == true) {
      thisRow = jQuery(obj).parent().parent().parent();
      lastRowId = thisRow.parent().find("li:last-child").attr("id");
      thisId = thisRow.attr("id");
      prevElementRow = thisRow.prev();
      if (thisId == lastRowId) {
        prevElementRow
          .find("select")
          .eq(2)
          .addClass("hidden")
          .attr("disabled", "disabled");
      }
      thisRow.remove();
    }
  };

  var eraseFilterValue = function (obj) {
    var elementRow = "";
    elementRow = jQuery(obj).parent();
    elementRow.find("input").eq(0).val("");
  };

  var getModuleColumns = function (obj) {
    var module = jQuery(obj),
      moduleName = module.val(),
      infoTexto = "Esta operación borrará los filtros, ¿Desea continuar?",
      button = jQuery("#custom-button"),
      requestParams;
    if (module.val() != "") {
      module.parent().removeClass("has-error");
      module.parent().find(".help-block").html("");
    }

    if (
      (totalFilterGroup >= 1 && hasGroup) ||
      (button.val() !== null && lastModule !== moduleName)
    ) {
      if (button.val() !== "") {
        infoTexto =
          "Esta operación borrará los filtros y los botones de la notificación al centro con fondo opaco, ¿Desea continuar? ";
      }
      var r = confirm(infoTexto);
      if (r == true) {
        jQuery("div[id ^= group-]").each(function (index, item) {
          jQuery(item).remove();
          totalFilterGroup = 0;
          hasGroup = false;
        });
        button.val("");
        upDateContent("SIMPLE", "");
      } else {
        module.val(lastModule);
        return;
      }
    }
    hasGroup = false;

    if (
      moduleName === null ||
      moduleName === undefined ||
      moduleName.trim() === ""
    ) {
      return;
    }
    lastModule = moduleName;
    requestParams = [
      "module=notifications",
      "action=AjaxActions",
      "function=getColumns",
      "Ajax=true",
      "fld_module=" + encodeURIComponent(moduleName),
    ];
    jQuery
      .ajax("index.php", {
        data: requestParams.join("&"),
        dataType: "text",
        method: "post",
        async: false,
      })
      .done(onGetModuleColumnsSuccessHandler)
      .fail(onAjaxFailureHandler);
  };

  var getPattern = function (obj) {
    var pattem = jQuery(obj).val();
    if (pattem !== "") {
      jQuery("span[id ^= sp-n-]").html("");
      jQuery("div[id ^= dv-n-]").removeClass("has-error");
    }
  };

  var setAmbit = function (obj) {
    var ambit = jQuery(obj).val(),
      user = jQuery("#notification-users"),
      users = jQuery("#notification-users > option");
    if (ambit === "SYSTEM") {
      user.val("0");
      jQuery("#notification-users option:not(:selected)").attr(
        "disabled",
        true
      );
    } else {
      jQuery("#notification-users option:not(:selected)").attr(
        "disabled",
        false
      );
      users.attr("selected", false);
    }
  };

  var setButton = function (obj) {
    var buttons = jQuery(obj);
    if (buttons.val().length > 4) {
      buttons.children("option:selected:last").removeAttr("selected");
    }
  };

  var setEvent = function (selectElement) {
    var select = jQuery(selectElement),
      event = select.val(),
      periodTab = jQuery('.nav-tabs a[href="#period"]'),
      periodTabPane = jQuery("#period"),
      periodRow = jQuery("#dv-n-filter-column").closest(".row");

    if (jQuery.inArray(event, ["TOTAL RECORDS REACHED"]) !== -1) {
      select.closest("form").find("#event-parameter-container").show();
    } else {
      select.closest("form").find("#event-parameter-container").hide();
    }

    // Period filters are now available for ALL events including ALWAYS
    // Use cases for ALWAYS + period:
    // 1. Show notification always during a specific date range (e.g., Christmas campaign)
    // 2. Show notification always for records matching a period condition (e.g., created in last 7 days)
    // Always show period section - let the user decide if they want to use it
    periodRow.removeClass("hide");
    periodTabPane.removeClass("hide");
    periodTab.parent().removeClass("hide");
  };

  var setFilterOperators = function (obj) {
    var filterRow = "",
      selectedType = "",
      thisOperator = "",
      thisInput = "",
      thisSelect = "",
      selectedOption = "",
      helpinfo = "",
      fieldDescription = "",
      fieldValue = "",
      selectedFieldValue = "";
    selectedOption = jQuery(obj).children("option:selected");
    selectedType = selectedOption.attr("data-type");
    helpinfo = selectedOption.attr("data-helpinfo");
    fieldValue = selectedOption.val();
    filterRow = jQuery(obj).parent().parent();
    thisOperator = filterRow.find("select").eq(1);
    thisInput = filterRow.find("input").eq(0);
    thisSelect = filterRow.find("select.filter-value-picklist");
    fieldDescription = filterRow.find(".field-description");
    // Save current input value before clearing (in case it's a picklist and we need to restore it)
    var currentInputValue = thisInput.val();
    thisInput.val("");
    if (thisSelect.length > 0) {
      thisSelect.val("");
    }
    // Get module info if available
    var fieldModule = selectedOption.attr("data-module") || "";
    // Get uitype to detect picklists
    var fieldUitype = parseInt(selectedOption.attr("data-uitype")) || 0;
    // Debug logs
    console.log("[NotificationsWizard] setFilterOperators called");
    console.log(
      "[NotificationsWizard] Selected option:",
      selectedOption.text()
    );
    console.log("[NotificationsWizard] Selected type:", selectedType);
    console.log("[NotificationsWizard] Field uitype:", fieldUitype);
    console.log("[NotificationsWizard] Field module:", fieldModule);
    console.log("[NotificationsWizard] Field value:", fieldValue);
    console.log("[NotificationsWizard] Helpinfo:", helpinfo);
    // Show field description if available
    if (helpinfo && helpinfo.length > 0) {
      // Clean HTML tags if any remain (should be cleaned in backend, but just in case)
      var cleanHelpinfo = jQuery("<div>").html(helpinfo).text();
      // Add module info if available
      if (fieldModule && fieldModule !== "Users") {
        cleanHelpinfo = "[" + fieldModule + "] " + cleanHelpinfo;
      }
      console.log("[NotificationsWizard] Showing helpinfo:", cleanHelpinfo);
      fieldDescription.text(cleanHelpinfo).show();
    } else {
      console.log("[NotificationsWizard] No helpinfo, hiding description");
      fieldDescription.text("").hide();
    }

    // List of picklist uitypes according to is_uitype() in utils.php
    // Picklist uitypes: 15, 16, 52, 53, 54, 55, 59, 62, 63, 66, 68, 76, 77, 78, 80, 98, 101, 115, 357
    // 15 = PickList, 16 = Global PickList, 52 = Users List, 53 = Multi-Select, 54 = Multi-Select (custom),
    // 55 = Multi-Select (custom), 59 = Multi-Select (custom), 62 = Multi-Select (custom),
    // 63 = Multi-Select (custom), 66 = Multi-Select (custom), 68 = Multi-Select (custom),
    // 76 = Multi-Select (custom), 77 = Multi-Select (custom), 78 = Multi-Select (custom),
    // 80 = Multi-Select (custom), 98 = Role (PickList), 101 = Multi-Select (custom),
    // 115 = PickList special (like status), 357 = Multi-Select (custom)
    var picklistUitypes = [
      15, 16, 52, 53, 54, 55, 59, 62, 63, 66, 68, 76, 77, 78, 80, 98, 101, 115,
      357,
    ];
    console.log("[NotificationsWizard] Checking if field is picklist:", {
      fieldName: selectedOption.text(),
      fieldValue: fieldValue,
      uitype: fieldUitype,
      isPicklist: jQuery.inArray(fieldUitype, picklistUitypes) !== -1,
      fieldValueLength: fieldValue ? fieldValue.length : 0,
    });
    if (
      jQuery.inArray(fieldUitype, picklistUitypes) !== -1 &&
      fieldValue &&
      fieldValue.length > 0
    ) {
      console.log(
        "[NotificationsWizard] Field is a picklist, creating/showing select for:",
        {
          fieldName: selectedOption.text(),
          fieldValue: fieldValue,
          uitype: fieldUitype,
          selectExists: thisSelect.length > 0,
        }
      );
      // Hide input and show select (currentInputValue was already saved at the beginning of the function)
      thisInput.closest(".input-group").addClass("hide");
      if (thisSelect.length === 0) {
        console.log(
          "[NotificationsWizard] Creating new select element for picklist"
        );
        // Create select if it doesn't exist
        thisSelect = jQuery("<select>", {
          class: "form-control filter-value-picklist",
          name: "filterValue[]",
        });
        thisInput.closest(".input-group").after(thisSelect);
        console.log(
          "[NotificationsWizard] Select element created and inserted after input"
        );
      } else {
        console.log(
          "[NotificationsWizard] Select element already exists, showing it"
        );
        thisSelect.removeClass("hide");
      }
      // Extract field name from value (e.g., "tq.status" -> "status", "vtiger_users.status" -> "status")
      var fieldName = fieldValue;
      if (fieldName.indexOf(".") !== -1) {
        var parts = fieldName.split(".");
        fieldName = parts[parts.length - 1];
      }
      // Load picklist options via AJAX
      console.log(
        "[NotificationsWizard] Loading picklist options for field:",
        fieldName
      );
      var ajaxArgs = [
        "module=notifications",
        "action=AjaxActions",
        "function=FETCH-PICKLIST",
        "fieldname=" + encodeURIComponent(fieldValue), // Send full field value (with table alias)
        "flmodule=" + encodeURIComponent(fieldModule || ""),
        "Ajax=true",
      ];
      jQuery
        .ajax("index.php", {
          data: ajaxArgs.join("&"),
          dataType: "text",
          method: "post",
        })
        .done(function (data) {
          var message;
          try {
            // Parse JSON response
            message = JSON.parse(data);
            if (message.error !== "OK") {
              console.error(
                "[NotificationsWizard] Error loading picklist:",
                message.error
              );
              // Show input if picklist loading fails
              thisInput.closest(".input-group").removeClass("hide");
              thisSelect.addClass("hide");
            } else {
              console.log(
                "[NotificationsWizard] Picklist options loaded successfully"
              );
              thisSelect.empty();
              thisSelect.append('<option value="">-Seleccione-</option>');
              thisSelect.append(message.html);
              // Restore previously selected value if editing, or use current input value
              var valueToSet = selectedFieldValue || currentInputValue;
              if (valueToSet) {
                thisSelect.val(valueToSet);
                // If value doesn't match any option, try case-insensitive match
                if (!thisSelect.val() && valueToSet) {
                  thisSelect.find("option").each(function () {
                    if (
                      jQuery(this).val().toLowerCase() ===
                      valueToSet.toLowerCase()
                    ) {
                      thisSelect.val(jQuery(this).val());
                      return false; // break loop
                    }
                  });
                }
              }
            }
          } catch (e) {
            console.error(
              "[NotificationsWizard] Error parsing picklist response:",
              e
            );
            console.error("[NotificationsWizard] Response data:", data);
            // Show input if picklist loading fails
            thisInput.closest(".input-group").removeClass("hide");
            thisSelect.addClass("hide");
          }
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
          console.error(
            "[NotificationsWizard] AJAX request failed:",
            textStatus,
            errorThrown
          );
          console.error("[NotificationsWizard] Response:", jqXHR.responseText);
          // Show input if picklist loading fails
          thisInput.closest(".input-group").removeClass("hide");
          thisSelect.addClass("hide");
        });
    } else {
      // Not a picklist, show input and hide select
      thisInput.closest(".input-group").removeClass("hide");
      if (thisSelect.length > 0) {
        thisSelect.addClass("hide");
      }
      if (selectedType != null && selectedType.length != 0) {
        if (jQuery.inArray(selectedType, ["T", "D", "DT"]) !== -1) {
          filterRow.find(".is-date").removeClass("hide");
          thisInput.attr("readonly", true);
          thisInput.datepicker({
            format: "yyyy-mm-dd",
            language: "es",
            weekStart: 1,
          });
        } else {
          filterRow.find(".is-date").addClass("hide");
          thisInput.attr("readonly", false);
          thisInput.datepicker("remove");
        }
      }
    }
    if (selectedType != null && selectedType.length != 0) {
      ops = typeofdata[selectedType];
      if (ops != null) {
        thisOperator.empty();
        jQuery(thisOperator).append(
          jQuery("<option>", {
            value: "",
            text: "-Ninguno-",
          })
        );
        for (var i = 0; i < ops.length; i++) {
          var label = fLabels[ops[i]];
          if (label == null) {
            continue;
          }
          jQuery(thisOperator).append(
            jQuery("<option>", {
              value: ops[i],
              text: label,
            })
          );
        }
      }
    } else {
      if (selectedType == "") {
        thisOperator.options[0].selected = true;
      }
    }
  };

  var setFilterRow = function (obj) {
    var elementRow, newElementRow, numRow, fieldSelect, totalRow;
    elementRow = jQuery(obj).parent().parent().parent().find("li:last-child");
    newElementRow = elementRow.clone().attr("id", "row-" + totalFilterRow);
    elementRow
      .find("select")
      .eq(2)
      .removeClass("hidden")
      .removeAttr("disabled");
    newElementRow.find("button").eq(0).removeClass("hidden");
    newElementRow.find("select").eq(0).val("");
    newElementRow.find("select").eq(1).val("");
    newElementRow.find("input").eq(0).val("");
    newElementRow.appendTo(elementRow.parent());
    totalFilterRow += 1;
  };

  var setHelpToField = function (obj) {
    var elementRow = "",
      selectedOperator = "";
    selectedOperator = jQuery(obj).val();
    elementRow = jQuery(obj).parent().parent();
    elementRow.find("input").eq(0).val("");
    elementRow
      .find("input")
      .eq(0)
      .attr("placeholder", hfLabels[selectedOperator]);
  };

  var setModalText = function (obj) {
    var modalText = jQuery(obj).val(),
      button = jQuery("#custom-button");
    if (button.val() !== "") {
      upDateContent("BUTTON", modalText);
    } else {
      upDateContent("SIMPLE", modalText);
    }
  };

  var setPeriod = function (obj) {
    var period = jQuery(obj).val(),
      startDate = jQuery("#filter-start-date"),
      endDate = jQuery("#filter-end-date");

    if (period === "custom") {
      startDate.attr("disabled", false);
      endDate.attr("disabled", false);
      jQuery(".custom-filter-date").show();
    } else {
      jQuery(".custom-filter-date").hide();
      startDate.val("");
      endDate.val("");
      startDate.attr("disabled", true);
      endDate.attr("disabled", true);
    }
  };

  var selectedStyle = function (obj) {
    var style = jQuery(obj).val(),
      reason = jQuery("#notification-action").val();
    upDateContent(style, reason);
  };

  var selectedType = function (obj) {
    var type = jQuery(obj).val(),
      html = jQuery("#notification-html"),
      modules = jQuery("#module-names > option"),
      view = jQuery("#notification-veiw"),
      reason = jQuery("#notification-action").val(),
      event = jQuery("#event"),
      modalText = jQuery("#modal-text").val(),
      eventParameter = jQuery("#event-parameter-container");

    event.val("");
    eventParameter.hide();
    event.children("option:not(:selected)").attr("disabled", true);
    if (type === "NOTIFY") {
      view.val("");
      jQuery("#notification-html option:not(:selected)").attr(
        "disabled",
        false
      );
      jQuery("#notification-system").removeClass("hide");
      jQuery("#notification-alert-style").removeClass("hide");
      jQuery("#notification-modal").addClass("hide");
      // Hide module names multiple select for NOTIFY (only shows in step 1)
      jQuery("#notification-alert-modules").addClass("hide");
      // Show module name field and make it required for NOTIFY
      jQuery("#dv-n-module-name").removeClass("hide");
      jQuery("#module-name").attr("required", true);
      jQuery("#module-required-indicator").show();

      // Show elegant info box for NOTIFY
      jQuery("#notification-type-info-text").html(
        "<strong>Notificaciones del Sistema:</strong> Aparecen en la esquina superior derecha de la pantalla. " +
          "Puedes configurar en qué ubicación específica (Lista, Detalle, Edición) se mostrarán. " +
          "Son ideales para mensajes informativos que no interrumpen el flujo de trabajo."
      );
      jQuery("#notification-type-info").slideDown(300);

      event.children().each(function (i) {
        if (
          jQuery.inArray(jQuery(this).val(), [
            "TOTAL RECORDS REACHED",
            "ALWAYS",
            "RECORD_NO_CREATE",
            "CANCEL RECORD",
            "CREATE RECORD",
            "EDIT RECORD",
            "SAVE RECORD",
            "FIRST TIME",
            "FROM BACKGROUNDTASK",
          ]) !== -1
        ) {
          jQuery(this).attr("disabled", false);
        }
      });
      upDateContent(html.val(), reason);
    } else if (type === "ALERT") {
      html.val("SIMPLE");
      jQuery("#notification-html option:not(:selected)").attr("disabled", true);
      view.val("");
      modules.attr("selected", false);
      jQuery("#notification-system").addClass("hide");
      jQuery("#notification-modal").addClass("hide");
      jQuery("#notification-alert-style").removeClass("hide");
      // ALERT type: Module selection is done in Step 1 (module-name), not in Step 3
      // Keep notification-alert-modules hidden in Step 3
      jQuery("#notification-alert-modules").addClass("hide");
      // Show module name field in Step 1 for ALERT (can be global or module-specific)
      jQuery("#dv-n-module-name").removeClass("hide");
      jQuery("#module-name").removeAttr("required");
      jQuery("#module-required-indicator").hide();
      // For ALERT type, period filters are optional
      // Show period section but allow it to be empty
      jQuery("#dv-n-filter-column").closest(".row").removeClass("hide");
      jQuery("#dv-n-filter-period").closest(".row").removeClass("hide");
      jQuery("#advanced").removeClass("hide");
      // For ALERT type, only show creation and modification date options
      var fieldSelect = jQuery("#filter-column");
      fieldSelect.empty();
      fieldSelect.append(
        jQuery("<option>", { value: "", text: "Seleccionar un campo" })
      );
      fieldSelect.append(
        jQuery("<option>", {
          value: "crm.createdtime",
          text: "Fecha de Creación",
        })
      );
      fieldSelect.append(
        jQuery("<option>", {
          value: "crm.modifiedtime",
          text: "Fecha de Modificación",
        })
      );
      // Set default to "Users" (global) if no module is selected
      var currentModule = jQuery("#module-name").val();
      if (currentModule === "" || currentModule === null) {
        jQuery("#module-name").val("Users");
      }

      // Show elegant info box for ALERT
      jQuery("#notification-type-info-text").html(
        "<strong>Alertas en Barra Superior:</strong> Se muestran en una barra fija en la parte superior del CRM. " +
          "Pueden ser <strong>globales</strong> (Todos los módulos) o <strong>específicas</strong> (un módulo). " +
          'Si seleccionas "Todos los módulos", la alerta aparecerá en cualquier módulo. ' +
          "Son perfectas para avisos importantes que necesitan máxima visibilidad."
      );
      jQuery("#notification-type-info").slideDown(300);

      // Enable all events for ALERT (same as NOTIFY)
      event.children().each(function (i) {
        if (
          jQuery.inArray(jQuery(this).val(), [
            "ALWAYS",
            "TOTAL RECORDS REACHED",
            "RECORD_NO_CREATE",
            "CANCEL RECORD",
            "CREATE RECORD",
            "EDIT RECORD",
            "SAVE RECORD",
            "FIRST TIME",
            "FROM BACKGROUNDTASK",
          ]) !== -1
        ) {
          jQuery(this).attr("disabled", false);
        } else {
          jQuery(this).attr("disabled", true);
        }
      });
      // Update module columns when module changes
      jQuery("#module-name")
        .off("change.alert")
        .on("change.alert", function () {
          getModuleColumns(this);
        });
      upDateContent(html.val(), reason);
    } else if (type === "MODAL") {
      html.val("");
      jQuery("#notification-html option:not(:selected)").attr("disabled", true);
      view.val("");
      modules.attr("selected", false);
      jQuery("#notification-system").addClass("hide");
      jQuery("#notification-alert-style").addClass("hide");
      jQuery("#notification-modal").removeClass("hide");
      // Hide module names multiple select for MODAL (only shows in step 1)
      jQuery("#notification-alert-modules").addClass("hide");
      // Show module name field and make it required for MODAL
      jQuery("#dv-n-module-name").removeClass("hide");
      jQuery("#module-name").attr("required", true);
      jQuery("#module-required-indicator").show();

      // Show elegant info box for MODAL
      jQuery("#notification-type-info-text").html(
        "<strong>Ventanas Emergentes (Modales):</strong> Se muestran como ventanas emergentes con fondo opaco que requieren la atención del usuario. " +
          "Bloquean la interacción hasta que el usuario las cierre. " +
          "Son ideales para mensajes críticos, anuncios importantes o información que debe leerse antes de continuar trabajando."
      );
      jQuery("#notification-type-info").slideDown(300);

      // For MODAL, only enable events with mode mapping
      event.children().each(function (i) {
        if (
          jQuery.inArray(jQuery(this).val(), [
            "CREATE RECORD",
            "EDIT RECORD",
            "SAVE RECORD",
            "CANCEL RECORD",
            "ALWAYS",
            "FIRST TIME",
            "FROM BACKGROUNDTASK",
          ]) !== -1
        ) {
          jQuery(this).attr("disabled", false);
        } else {
          jQuery(this).attr("disabled", true);
        }
      });
      checkInstance.setData("");
    } else {
      // No type selected - hide info box
      html.val("");
      jQuery("#notification-html option:not(:selected)").attr(
        "disabled",
        false
      );
      jQuery("#notification-system").addClass("hide");
      // Hide module names multiple select when no type selected
      jQuery("#notification-alert-modules").addClass("hide");
      // Hide module name field if no type selected
      jQuery("#dv-n-module-name").addClass("hide");
      // Hide info box
      jQuery("#notification-type-info").slideUp(300);
    }

    // Update period visibility based on current event selection
    if (event.val() !== "") {
      setEvent(event[0]);
    }

    // For ALERT type, clear period values if event is ALWAYS
    // This ensures period fields are empty when not needed
    if (type === "ALERT" && event.val() === EVENT_ALWAYS) {
      jQuery("#filter-period").val("");
      jQuery("#filter-column").val("");
      jQuery("#filter-start-date").val("");
      jQuery("#filter-end-date").val("");
    }
  };

  var setModalContenet = function () {
    var modalText = jQuery("#modal-text").val();
    upDateContent("", modalText);
  };

  window.NotificationUtils = {
    filterPatternByType: filterPatternByType,
    openModalWizard: openModalWizard,
    setWizardAction: setWizardAction,
    action: action,
    addFilterGroup: addFilterGroup,
    closeNotifyWizard: closeNotifyWizard,
    createNotification: createNotification,
    eraseFilterGroup: eraseFilterGroup,
    eraseFilterRow: eraseFilterRow,
    eraseFilterValue: eraseFilterValue,
    getModuleColumns: getModuleColumns,
    getPattern: getPattern,
    setAmbit: setAmbit,
    setButton: setButton,
    setEvent: setEvent,
    setFilterOperators: setFilterOperators,
    setFilterRow: setFilterRow,
    setHelpToField: setHelpToField,
    setModalText: setModalText,
    setPeriod: setPeriod,
    selectedStyle: selectedStyle,
    selectedType: selectedType,
    setModalContenet: setModalContenet,
  };

  var defaultFilterLabels = {
    l: "Less than",
    g: "Greater than",
    m: "Less or equals",
    h: "Greater or equals",
    e: "Equals",
    n: "Not equals",
    s: "Starts with",
    ew: "Ends with",
    c: "Contains",
    k: "Does not contain",
    bw: "Between",
    b: "Before",
    a: "After",
  };

  var defaultHelpLabels = {
    e: "Text or value to compare",
    n: "Text or value to compare",
    s: "Starts with the text?",
    ew: "Ends with the text?",
    c: "Contains the text?",
    k: "Does not contain the text?",
    l: "Value or yyyy-mm-dd if date",
    g: "Value or yyyy-mm-dd if date",
    m: "Value or yyyy-mm-dd if date",
    h: "Value or yyyy-mm-dd if date",
    bw: "lower,upper or dates yyyy-mm-dd,yyyy-mm-dd",
    b: "Before yyyy-mm-dd",
    a: "After yyyy-mm-dd",
  };

  if (typeof alert_arr !== "undefined") {
    jQuery.extend(defaultFilterLabels, {
      e: alert_arr.EQUALS,
      n: alert_arr.NOT_EQUALS_TO,
      s: alert_arr.STARTS_WITH,
      ew: alert_arr.ENDS_WITH,
      c: alert_arr.CONTAINS,
      k: alert_arr.DOES_NOT_CONTAINS,
      h: alert_arr.GREATER_OR_EQUALS,
      bw: alert_arr.BETWEEN,
      b: alert_arr.BEFORE,
      a: alert_arr.AFTER,
    });
    jQuery.extend(defaultHelpLabels, {
      e: "texto o valor para comparar",
      n: "texto o valor para comparar",
      s: "Comienza con el texto?",
      ew: "Termina con el texto?",
      c: "Contiene el texto?",
      k: "No contiene el texto?",
      l: "Valor o aaaa-mm-dd si es fecha",
      g: "Valor o aaaa-mm-dd si es fecha",
      m: "Valor o aaaa-mm-dd si es fecha",
      h: "Valor o aaaa-mm-dd si es fecha",
      bw: "inferior,superior o fechas: aaaa-mm-dd,aaaa-mm-dd",
      b: "antes de aaaa-mm-dd",
      a: "despues de aaaa-mm-dd",
    });
  }

  jQuery.each(defaultFilterLabels, function (key, value) {
    fLabels[key] = value;
  });

  jQuery.each(defaultHelpLabels, function (key, value) {
    hfLabels[key] = value;
  });

  function initializeTypeOfData() {
    typeofdata.V = ["e", "n", "s", "ew", "c", "k"];
    typeofdata.N = ["e", "n", "l", "g", "m", "h"];
    typeofdata.T = ["e", "b", "a"];
    typeofdata.I = ["e", "n", "l", "g", "m", "h"];
    typeofdata.C = ["e", "n"];
    typeofdata.D = ["e", "b", "a"];
    typeofdata.DT = ["e", "b", "a"];
    typeofdata.NN = ["e", "n", "l", "g", "m", "h"];
    typeofdata.E = ["e", "n", "s", "ew", "c", "k"];
  }

  function prepareAdvancedConditions() {
    var groups = jQuery("#advanced").find(".condition-group");
    if (groups.length === 0) {
      return;
    }
    jQuery("li[id^=row-]").each(function () {
      var selectField = jQuery(this).find("select").eq(0);
      var dataType = selectField.children("option:selected").attr("data-type");
      var inputField = jQuery(this).find("input").eq(0);
      if (jQuery.inArray(dataType, ["T", "D", "DT"]) !== -1) {
        selectField.parent().parent().find(".is-date").removeClass("hide");
        inputField.attr("readonly", true);
        inputField.datepicker({
          format: "yyyy-mm-dd",
          language: "es",
          weekStart: 1,
        });
      }
    });
  }

  function configureEventOptions(eventSelect, notificationType) {
    if (eventSelect.val() !== "") {
      eventSelect.children("option:not(:selected)").attr("disabled", true);
    }
    if (notificationType === "MODAL") {
      // For MODAL notifications, only these events are supported:
      // - CREATE RECORD, EDIT RECORD, SAVE RECORD, CANCEL RECORD (have corresponding modes)
      // - ALWAYS (always displayed)
      // - FIRST TIME (displayed once per user)
      // - FROM BACKGROUNDTASK (triggered from background tasks)
      // NOT supported: TOTAL RECORDS REACHED, RECORD_NO_CREATE (no mode mapping)
      var allowedModalEvents = [
        "CREATE RECORD",
        "EDIT RECORD",
        "SAVE RECORD",
        "CANCEL RECORD",
        "ALWAYS",
        "FIRST TIME",
        "FROM BACKGROUNDTASK",
        "",
      ];
      eventSelect.children().each(function () {
        if (jQuery.inArray(jQuery(this).val(), allowedModalEvents) !== -1) {
          jQuery(this).attr("disabled", false);
        } else {
          jQuery(this).attr("disabled", true);
        }
      });
    } else if (notificationType !== "") {
      // For ALERT and NOTIFY, all events are supported
      eventSelect.children().each(function () {
        // Enable all events for ALERT and NOTIFY types
        jQuery(this).attr("disabled", false);
      });
    }
  }

  jQuery("#event-parameter").keydown(function (e) {
    if (
      jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
      (e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) ||
      (e.keyCode >= 35 && e.keyCode <= 40) ||
      e.keyCode === 188 ||
      e.keyCode === 190
    ) {
      return;
    }
    if (
      (e.shiftKey || e.keyCode < 48 || e.keyCode > 57) &&
      (e.keyCode < 96 || e.keyCode > 105)
    ) {
      e.preventDefault();
    }
  });

  function onDocumentReadyHandler() {
    var eventSelect = jQuery("#event");
    var notificationType = jQuery("#notification-type").val();

    initializeTypeOfData();
    prepareAdvancedConditions();
    configureEventOptions(eventSelect, notificationType);

    jQuery("#filter-start-date").datepicker({
      format: "yyyy-mm-dd",
      language: "es",
      weekStart: 1,
    });
    jQuery("#filter-end-date").datepicker({
      format: "yyyy-mm-dd",
      language: "es",
      weekStart: 1,
    });
    lastModule = jQuery("#module-name").val();

    // Update period visibility based on current event selection on page load
    if (eventSelect.val() !== "") {
      setEvent(eventSelect[0]);
    }
  }

  (function ($) {
    $(document).ready(onDocumentReadyHandler);
  })(jQuery);
})(jQuery);
