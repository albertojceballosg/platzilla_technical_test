(function (jQuery) {
  // Private variables
  var availableActivityData = null,
    editor,
    excludeElement = ["button", "submit", "hidden", "undefined"],
    actionReport = "create",
    currentReportId = 0,
    initialFormData = {};

  //Private function
  var getJobTitle = function (crmId, id) {
    var arguments = {
        module: "grid_view",
        action: "GridViewAjaxUtils",
        function: "JOB_TITLE",
        formodule: "orden_de_trabajo",
        record: crmId,
        Ajax: true,
      },
      infoText = jQuery("#well-" + id);

    jQuery.post("index.php", arguments, function (data) {
      var message;
      try {
        message = JSON.parse(JSON.stringify(data));
        if (message.error !== "OK") {
          throw message.error;
        } else {
          infoText.parent().parent().removeClass("hidden");
          infoText.html(message.html);
        }
      } catch (e) {
        alert(e);
      }
    });
  };

  var validateForm = function (form) {
    var formElement = jQuery("form[name='" + form.attr("name") + "'] :input"),
      isValidate = true,
      totalBoxes = 0,
      field,
      operationValue,
      value;
    jQuery("span[id ^= gv-]").html("");
    jQuery("div[id ^= gv-div-]").removeClass("has-error");
    formElement.map(function (index, elm) {
      var element = jQuery(elm),
        elementTitle = element.attr("title"),
        elementName = element.attr("name"),
        value = element.val();
      if (elementName === "description") {
        value = CKEDITOR.instances.description.getData();
      } else if (elementName === "feedback") {
        value = CKEDITOR.instances.feedback.getData();
      }
      if (
        jQuery.inArray(elm.type, excludeElement) === -1 &&
        elementTitle !== "" &&
        elementTitle !== undefined
      ) {
        if (value === null || value === undefined || value.trim() === "") {
          element.parent().addClass("has-error");
          if (element.parent().find(".help-block").length) {
            element
              .parent()
              .find(".help-block")
              .html(elementTitle + " requerido");
          } else {
            element
              .parent()
              .parent()
              .find(".help-block")
              .html(elementTitle + " requerido");
          }
          isValidate = false;
        }
      }
    });
    return isValidate;
  };

  var loadCkEditor = function (inputId) {
    //console.log("cargando editor");

    // Verificar si la instancia ya existe y destruirla antes de crear una nueva
    if (CKEDITOR.instances[inputId]) {
      try {
        CKEDITOR.instances[inputId].destroy();
      } catch (e) {
        // Instancia obsoleta (p. ej. de un modal reabierto) apuntando a nodos ya
        // removidos del DOM; ignorar y forzar su eliminación del registro para
        // no impedir la creación de la nueva instancia.
        delete CKEDITOR.instances[inputId];
      }
    }

    var options = {
      contentsCss: ["themes/centaurus/css/bootstrap/bootstrap.min.css"],
      entities: false,
      language: "es",
      removePlugins: "elementspath",
      height: 90,
      //smiley_images: ['Onion--1.gif','Onion--2.gif','angel_smile.gif', 'angry_smile.gif', 'broken_heart.gif', 'confused_smile.gif', 'cry_smile.gif', 'devil_smile.gif', 'embaressed_smile.gif', 'envelope.gif', 'heart.gif', 'kiss.gif', 'lightbulb.gif', 'omg_smile.gif', 'regular_smile.gif', 'sad_smile.gif', 'shades_smile.gif', 'teeth_smile.gif', 'thumbs_down.gif', 'thumbs_up.gif', 'tounge_smile.gif', 'whatchutalkingabout_smile.gif', 'wink_smile.gif'],
      smiley_descriptions: [
        "",
        ":(",
        "",
        "",
        ":~",
        ":'(",
        "",
        "",
        "",
        "",
        "",
        "",
        ":-O",
        ":-)",
        ":-(",
        "8-)",
        ":D",
        "",
        "",
        ":-P",
        ":|",
        ";-)",
      ],
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
          "Smiley",
          "UniversalKey",
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

  // public function
  var setActivity = function (obj) {
    var activity = jQuery(obj),
      description = jQuery("#activity-description"),
      progress = jQuery("#progress"),
      attachmentsSection = jQuery("#attachments-section-report"),
      timedurationLabel = jQuery("#timeduration-label"),
      selectedActivity;

    if (activity.val() !== "") {
      selectedActivity = availableActivityData[activity.val()];
      progress.val(selectedActivity.progress);
      if (description.hasClass("hidden")) {
        description.removeClass("hidden");
      }
      // Truncar la descripción a 600 caracteres
      var truncatedDescription = selectedActivity.description;
      if (truncatedDescription.length > 600) {
        truncatedDescription = truncatedDescription.substring(0, 600) + "...";
      }
      description.find(".well").html(truncatedDescription);

      // Actualizar la etiqueta con la unidad de medida
      var timeUnit = selectedActivity.timeUnit || "";
      var labelText =
        '<span style="color: red;">*</span>&nbsp;Unidades utilizadas';
      if (timeUnit && timeUnit !== "") {
        labelText += " [" + timeUnit + "]";
      }
      labelText += ":";
      timedurationLabel.html(labelText);

      // Mostrar la sección de adjuntos si estaba oculta
      if (attachmentsSection.hasClass("hidden")) {
        attachmentsSection.removeClass("hidden");
      }

      // No cargar adjuntos automáticamente en modo edición
      // Los adjuntos ya se cargan desde el backend y la carga automática está causando errores JSON
      // if (actionReport === 'edit') {
      //   loadTaskAttachments(activity.val());
      // }
    } else {
      if (!description.hasClass("hidden")) {
        description.addClass("hidden");
      }
      description.find(".well").html("");
      if (!attachmentsSection.hasClass("hidden")) {
        attachmentsSection.addClass("hidden");
      }
    }
    setProgress(progress);
    //console.log(availableActivityData[activity.val()]);
  };

  var init = function (activityData, reportAction, reportId) {
    var progress = jQuery("#progress"),
      progressDisplay = jQuery("progress-display");
    progressDisplay.html(progress.val());
    availableActivityData = activityData;
    actionReport = reportAction || "create";
    currentReportId = parseInt(reportId) || 0;

    loadCkEditor("description");
    loadCkEditor("feedback");

    // Formatear los campos numéricos al cargar el formulario (con formato completo del usuario)
    var timedurationField = jQuery("#timeduration");
    var actualcostField = jQuery("#actualcost");
    if (timedurationField.length && timedurationField.val() !== "") {
      formatNumberOnBlur(timedurationField[0]);
    }
    if (actualcostField.length && actualcostField.val() !== "") {
      formatNumberOnBlur(actualcostField[0]);
    }

    // Capturar estado inicial del formulario para detectar cambios
    // Esperar un momento para que CKEditor se inicialice completamente
    setTimeout(function () {
      jQuery('form[name^="activity-report-form-"]').each(function () {
        var formId = jQuery(this).attr("id");
        if (formId) {
          // Actualizar el contenido del CKEditor antes de serializar
          if (CKEDITOR.instances.description) {
            jQuery("#description").val(
              CKEDITOR.instances.description.getData(),
            );
          }
          initialFormData[formId] = jQuery(this).serialize();
        }
      });
    }, 1000);
  };

  var saveFeedback = function (obj, id) {
    var form = jQuery("#arctity-feedback-form-" + id),
      sendButton = jQuery(obj);
    sendButton.attr("disabled", "disabled");
    if (validateForm(form)) {
      jQuery("#feedback").val(CKEDITOR.instances.feedback.getData());
      var arguments = form.serialize();
      jQuery.post("index.php", arguments, function (data) {
        var message;
        try {
          message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            throw message.error;
          } else {
            // Close the feedback modal
            jQuery(".ekko-lightbox").modal("hide");

            // Get the parent record information from the form
            var recordId = jQuery('input[name="record"]').val();
            var moduleName = jQuery('input[name="fl_module"]').val();

            // Redirect to the DetailView of the parent record
            if (recordId && moduleName) {
              window.location.href =
                "index.php?module=" +
                moduleName +
                "&action=DetailView&record=" +
                recordId;
            } else {
              location.reload();
            }

            alert("El feedback de actividad se ha guardado con éxito.");
          }
        } catch (e) {
          alert(e);
          sendButton.removeAttr("disabled");
        }
      });
    }
    //sendButton.removeAttr('disabled');
  };

  var saveReport = function (obj, id) {
    var form = jQuery("#arctity-report-form-" + id),
      sendButton = jQuery(obj);
    sendButton.attr("disabled", "disabled");
    if (validateForm(form)) {
      jQuery("#description").val(CKEDITOR.instances.description.getData());

      // Convertir campos numéricos formateados a formato de BD (punto decimal) antes de serializar
      var numberFormat =
        jQuery("#timeduration").attr("data-format") || "AMERICAN_FORMAT";
      var timedurationField = jQuery("#timeduration");
      var actualcostField = jQuery("#actualcost");
      if (timedurationField.length && timedurationField.val() !== "") {
        timedurationField.val(
          convertToDBFormat(timedurationField.val(), numberFormat),
        );
      }
      if (actualcostField.length && actualcostField.val() !== "") {
        actualcostField.val(
          convertToDBFormat(actualcostField.val(), numberFormat),
        );
      }

      var arguments = form.serialize();
      jQuery.post("index.php", arguments, function (data) {
        var message;
        try {
          message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            throw message.error;
          } else {
            // Mostrar mensaje de éxito
            alert("El informe de actividad se ha guardado con éxito.");

            // Cerrar todas las modales abiertas (incluyendo ekko-lightbox)
            // Cerrar ekko-lightbox específicamente
            jQuery('[id^="ekkoLightbox-"]').modal("hide");
            jQuery(".ekko-lightbox").modal("hide");
            // Cerrar cualquier otra modal
            jQuery(".modal").modal("hide");
            // Limpiar todos los backdrops
            jQuery(".modal-backdrop").remove();
            // Restaurar el body
            jQuery("body").removeClass("modal-open");
            jQuery("body").css("padding-right", "");
            jQuery("body").css("overflow", "");

            // Get the parent record information from the form
            var recordId = jQuery('input[name="record"]').val();
            var moduleName = jQuery('input[name="fl_module"]').val();

            // Esperar a que las modales se cierren antes de redirigir
            setTimeout(function () {
              // Redirect to the DetailView of the parent record
              if (recordId && moduleName) {
                window.location.href =
                  "index.php?module=" +
                  moduleName +
                  "&action=DetailView&record=" +
                  recordId;
              } else {
                location.reload();
              }
            }, 500);
          }
        } catch (e) {
          alert(e);
          sendButton.removeAttr("disabled");
        }
      });
    } else {
      // Reactivar el botón si la validación falla
      sendButton.removeAttr("disabled");
    }
  };

  var setProgress = function (obj) {
    var progress = jQuery(obj),
      display = jQuery("#progress-display");
    display.text(progress.val());
  };

  var selectedActivity = function (obj) {
    var activity = jQuery(obj),
      description = jQuery("#activity-description-feedback"),
      reports = jQuery("#taskreport"),
      selectedActivity,
      infoText = "",
      totalReports = 0;

    reports.find("option").each(function (index, op) {
      var thisOption = jQuery(op);
      totalReports += 1;
      if (thisOption.hasClass("hidden")) {
        thisOption.removeClass("hidden");
      }
    });
    if (totalReports > 1) {
      totalReports -= 1;
    }
    reports.val("");
    if (activity.val() !== "") {
      selectedActivity = availableActivityData[activity.val()];
      if (description.hasClass("hidden")) {
        description.removeClass("hidden");
      }

      reports.find("option").each(function (index, op) {
        var thisOption = jQuery(op);
        if (
          thisOption.attr("data-activity") != activity.val() &&
          thisOption.val() !== ""
        ) {
          thisOption.addClass("hidden");
          totalReports -= 1;
        }
      });
      if (totalReports === 0) {
        infoText =
          '<br><small style="color: red"><b>No hay reportes en la actividad</b></small>';
      } else {
        infoText =
          "<br><small><b>Total de reportes en la actividad: </b>" +
          totalReports +
          "</small>";
      }
      // Truncar la descripción a 600 caracteres
      var truncatedDescription = selectedActivity.description;
      if (truncatedDescription.length > 600) {
        truncatedDescription = truncatedDescription.substring(0, 600) + "...";
      }
      description.find(".well").html(truncatedDescription + infoText);
    } else {
      description.find(".well").html("");
      if (!description.hasClass("hidden")) {
        description.addClass("hidden");
      }
    }
    setProgress(progress);
    //console.log(availableActivityData[activity.val()]);
  };

  var normalizeReportTime = function (fieldElement, e) {
    var fieldLength = jQuery(fieldElement);
    //console.log(e.keyCode);
    // Permitir: Ctrl, Meta, Shift, teclas de navegación, Backspace, Tab
    // Permitir: punto (190, 110) y coma (188) como separadores decimales
    if (
      e.ctrlKey === true ||
      e.metaKey === true ||
      e.keyCode === 16 ||
      (e.keyCode <= 47 && e.keyCode !== 8 && e.keyCode !== 9) ||
      (e.keyCode >= 58 &&
        e.keyCode !== 110 &&
        e.keyCode !== 188 &&
        e.keyCode !== 190)
    ) {
      e.preventDefault();
    }
  };

  // Función para formatear número según formato del usuario al abandonar el campo
  var formatNumberOnBlur = function (fieldElement) {
    var field = jQuery(fieldElement);
    var value = field.val();
    if (!value || value === "") return;

    // Obtener formato numérico del usuario del atributo data-format
    var numberFormat = field.attr("data-format") || "AMERICAN_FORMAT";

    // Limpiar caracteres no numéricos excepto punto y coma
    var cleanValue = value.replace(/[^\d.,-]/g, "");
    if (cleanValue === "") {
      field.val("");
      return;
    }

    // Convertir a número
    var hasComma = cleanValue.indexOf(",") !== -1;
    var hasDot = cleanValue.indexOf(".") !== -1;
    var numValue;

    if (hasComma && hasDot) {
      // Tiene ambos separadores: determinar cuál es decimal por posición
      // El separador decimal es el que aparece después del separador de miles
      var lastCommaIndex = cleanValue.lastIndexOf(",");
      var lastDotIndex = cleanValue.lastIndexOf(".");
      if (lastCommaIndex > lastDotIndex) {
        // Coma está después del punto: formato europeo (1.234,56)
        numValue = parseFloat(cleanValue.replace(/\./g, "").replace(",", "."));
      } else {
        // Punto está después de la coma: formato americano (1,234.56)
        numValue = parseFloat(cleanValue.replace(/,/g, ""));
      }
    } else if (hasComma) {
      // Solo coma: depende del formato del usuario
      if (numberFormat === "EUROPEAN_FORMAT") {
        // En formato europeo, la coma es decimal
        numValue = parseFloat(cleanValue.replace(",", "."));
      } else {
        // En formato americano, la coma es separador de miles
        numValue = parseFloat(cleanValue.replace(/,/g, ""));
      }
    } else {
      // Solo punto o ninguno: ya está en formato correcto
      numValue = parseFloat(cleanValue);
    }

    if (isNaN(numValue)) {
      field.val("");
      return;
    }

    // Formatear según preferencias del usuario
    if (numberFormat === "EUROPEAN_FORMAT") {
      // Formato europeo: 1.234,56
      var parts = numValue.toFixed(2).split(".");
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
      field.val(parts.join(","));
    } else {
      // Formato americano: 1,234.56
      var parts = numValue.toFixed(2).split(".");
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      field.val(parts.join("."));
    }
  };

  // Función para limpiar formato numérico al enfocar el campo (para edición)
  var cleanNumberOnFocus = function (fieldElement) {
    var field = jQuery(fieldElement);
    var value = field.val();
    if (!value || value === "") return;

    // Obtener formato numérico del usuario
    var numberFormat = field.attr("data-format") || "AMERICAN_FORMAT";

    // Limpiar caracteres no numéricos excepto punto y coma
    var cleanValue = value.replace(/[^\d.,-]/g, "");

    // Convertir a formato simple (sin separadores de miles, con punto decimal)
    var hasComma = cleanValue.indexOf(",") !== -1;
    var hasDot = cleanValue.indexOf(".") !== -1;
    var numValue;

    if (hasComma && hasDot) {
      var lastCommaIndex = cleanValue.lastIndexOf(",");
      var lastDotIndex = cleanValue.lastIndexOf(".");
      if (lastCommaIndex > lastDotIndex) {
        numValue = parseFloat(cleanValue.replace(/\./g, "").replace(",", "."));
      } else {
        numValue = parseFloat(cleanValue.replace(/,/g, ""));
      }
    } else if (hasComma) {
      if (numberFormat === "EUROPEAN_FORMAT") {
        numValue = parseFloat(cleanValue.replace(",", "."));
      } else {
        numValue = parseFloat(cleanValue.replace(/,/g, ""));
      }
    } else {
      numValue = parseFloat(cleanValue);
    }

    if (isNaN(numValue)) {
      field.val("");
      return;
    }

    // Mostrar sin separadores de miles, pero con el separador decimal preferido del usuario
    var numStr = numValue.toString();
    if (numberFormat === "EUROPEAN_FORMAT") {
      // Formato europeo: reemplazar punto decimal por coma
      numStr = numStr.replace(".", ",");
    }
    field.val(numStr);
  };

  // Función para convertir número formateado a valor de BD (para guardar)
  var convertToDBFormat = function (formattedValue, numberFormat) {
    if (!formattedValue || formattedValue === "") return 0;

    var cleanValue = formattedValue.toString().replace(/[^\d.,-]/g, "");
    if (cleanValue === "") return 0;

    var hasComma = cleanValue.indexOf(",") !== -1;
    var hasDot = cleanValue.indexOf(".") !== -1;
    var numValue;

    if (hasComma && hasDot) {
      var lastCommaIndex = cleanValue.lastIndexOf(",");
      var lastDotIndex = cleanValue.lastIndexOf(".");
      if (lastCommaIndex > lastDotIndex) {
        numValue = parseFloat(cleanValue.replace(/\./g, "").replace(",", "."));
      } else {
        numValue = parseFloat(cleanValue.replace(/,/g, ""));
      }
    } else if (hasComma) {
      if (numberFormat === "EUROPEAN_FORMAT") {
        numValue = parseFloat(cleanValue.replace(",", "."));
      } else {
        numValue = parseFloat(cleanValue.replace(/,/g, ""));
      }
    } else {
      numValue = parseFloat(cleanValue);
    }

    return isNaN(numValue) ? 0 : numValue;
  };

  var reportOn = function (obj, id) {
    var activity = jQuery("#task-" + id),
      record = jQuery("#record-" + id),
      reportAbout = jQuery(obj),
      infoText = jQuery("#well-" + id),
      myTitle;

    // Solo limpiar el valor si NO estamos en modo edición con una tarea ya seleccionada
    var currentActivityValue = activity.val();
    var isEditingWithTask =
      actionReport === "edit" &&
      currentActivityValue &&
      currentActivityValue !== "";

    if (!isEditingWithTask) {
      activity.val("");
    } else {
    }

    infoText.html = "";
    infoText.parent().parent().addClass("hidden");
    if (reportAbout.val() === "JOB") {
      activity.find("option").each(function (index, obj) {
        var option = jQuery(obj);
        option.attr("disabled", "");
      });
      activity.attr("title", "");
      getJobTitle(record.val(), id);
    } else if (reportAbout.val() === "TASK") {
      activity.find("option").each(function (index, obj) {
        var option = jQuery(obj);
        option.removeAttr("disabled");
      });
      activity.attr("title", "La tarea");
    } else {
      activity.find("option").each(function (index, obj) {
        var option = jQuery(obj);
        option.attr("disabled", "");
      });
    }
  };

  var hasFormChanges = function (formId) {
    var form = jQuery("#" + formId);
    if (form.length === 0) {
      return false;
    }

    // Sincronizar el contenido del CKEditor antes de comparar
    if (CKEDITOR.instances.description) {
      jQuery("#description").val(CKEDITOR.instances.description.getData());
    }

    var currentData = form.serialize();
    var initialData = initialFormData[formId];

    // Si no hay datos iniciales guardados, no hay cambios
    if (!initialData) {
      return false;
    }

    var hasChanges = currentData !== initialData;
    return hasChanges;
  };

  var saveReportBeforeAttachment = function (formId, callback) {
    var form = jQuery("#" + formId);
    if (form.length === 0) {
      if (callback) callback(false);
      return;
    }

    // Sincronizar el contenido del CKEditor antes de guardar
    if (CKEDITOR.instances.description) {
      jQuery("#description").val(CKEDITOR.instances.description.getData());
    }

    // Validar formulario antes de guardar
    if (!validateForm(form)) {
      if (callback) callback(false);
      return;
    }

    // Convertir campos numéricos a formato de BD antes de serializar
    var numberFormat =
      jQuery("#timeduration").attr("data-format") || "AMERICAN_FORMAT";
    var timedurationField = jQuery("#timeduration");
    var actualcostField = jQuery("#actualcost");
    if (timedurationField.length && timedurationField.val() !== "") {
      timedurationField.val(
        convertToDBFormat(timedurationField.val(), numberFormat),
      );
    }
    if (actualcostField.length && actualcostField.val() !== "") {
      actualcostField.val(
        convertToDBFormat(actualcostField.val(), numberFormat),
      );
    }

    // Mostrar indicador de carga
    var loadingMsg = jQuery(
      '<div class="alert alert-info" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);"><i class="fa fa-spinner fa-spin"></i> Guardando cambios del reporte...</div>',
    );
    jQuery("body").append(loadingMsg);

    jQuery.ajax({
      url: "index.php",
      type: "POST",
      data: form.serialize(),
      success: function (response) {
        loadingMsg.remove();
        try {
          var result =
            typeof response === "string" ? JSON.parse(response) : response;
          if (result.error !== "OK") {
            alert("Error al guardar el reporte: " + result.error);
            if (callback) callback(false);
            return;
          }
          // Actualizar datos iniciales después de guardar exitosamente
          initialFormData[formId] = form.serialize();
          if (callback) callback(true);
        } catch (e) {
          alert("Error al procesar la respuesta del servidor");
          if (callback) callback(false);
        }
      },
      error: function () {
        loadingMsg.remove();
        alert("Error al guardar el reporte. Por favor, intenta nuevamente.");
        if (callback) callback(false);
      },
    });
  };

  var uploadTaskAttachments = function (taskSelectId) {
    var activityId = jQuery("#" + taskSelectId).val();
    if (!activityId || activityId === "" || !jQuery.isNumeric(activityId)) {
      alert("Primero selecciona una tarea válida");
      return;
    }

    // Determinar el ID del formulario
    var formId = taskSelectId.replace("task-", "activity-report-form-");

    // Verificar si hay cambios sin guardar
    if (hasFormChanges(formId)) {
      // Mostrar advertencia al usuario
      if (
        !confirm(
          "Tienes cambios sin guardar en el reporte.\n\nPara adjuntar evidencias, es necesario guardar estos cambios primero.\n\n¿Deseas guardar los cambios y continuar?",
        )
      ) {
        // Usuario canceló, permanecer en el formulario de edición
        return;
      }

      // Usuario confirmó, guardar y luego abrir modal de adjuntos
      saveReportBeforeAttachment(formId, function (success) {
        if (success) {
          openAttachmentsModal(activityId);
        } else {
        }
      });
    } else {
      // No hay cambios, abrir modal directamente
      openAttachmentsModal(activityId);
    }
  };

  var openAttachmentsModal = function (activityId) {
    var url =
      "index.php?module=daily_report&action=AjaxEditViewUtils&function=ATTACHMENT_DOC&record=" +
      activityId +
      "&formodule=Calendar&reportId=" +
      currentReportId +
      "&Ajax=true";

    var ekkoLightBox = jQuery(
      '<a href="' +
        url +
        '" data-width="950" data-toggle="lightbox" data-gallery="remoteload" data-title="Adjuntos de la tarea:">&nbsp;</a>',
    );

    ekkoLightBox.ekkoLightbox({
      loadingMessage: "Cargando...",
      onShown: function () {
        // Modal abierto - ajustar z-index para que aparezca encima del modal de edición
        jQuery(".ekko-lightbox").css("z-index", "10500");
        jQuery(".modal-backdrop").last().css("z-index", "10499");
      },
      onHidden: function () {
        // Recargar la lista de adjuntos cuando se cierra el modal
        loadTaskAttachments(activityId);
        // Cerrar todas las ventanas modales abiertas (modal de edición de reporte)
        jQuery("#task-report-overlay").css("display", "none");
        jQuery(".modal").modal("hide");
        jQuery(".modal-backdrop").remove();
        jQuery("body").removeClass("modal-open");
      },
    });
  };

  var loadTaskAttachments = function (activityId) {
    if (!activityId || activityId === "") {
      return;
    }

    var arguments = {
      module: "daily_report",
      action: "AjaxEditViewUtils",
      function: "FETCH_TASK_EVIDENCES",
      activityId: activityId,
      reportId: currentReportId,
      Ajax: true,
    };

    jQuery
      .post("index.php", arguments, function (data) {
        try {
          // Validar que la respuesta no esté vacía antes de parsear
          if (!data || (typeof data === "string" && data.trim() === "")) {
            var attachmentsList = jQuery("#task-attachments-list");
            attachmentsList.html(
              '<li style="color: #6c757d; font-style: italic;">No hay adjuntos</li>',
            );
            return;
          }

          var response = typeof data === "string" ? JSON.parse(data) : data;
          var attachments = response.attachments || [];
          var attachmentsList = jQuery("#task-attachments-list");
          attachmentsList.empty();

          if (attachments && attachments.length > 0) {
            attachments.forEach(function (attachment) {
              var sizeKB = (attachment.size / 1024).toFixed(2);
              var listItem =
                '<li style="border: 1px solid #dee2e6; border-radius: 4px; padding: 8px 12px; margin-bottom: 5px; background-color: #fff;">' +
                '<a href="' +
                attachment.uri +
                '" title="' +
                attachment.name +
                '" target="_blank" style="text-decoration: none; color: #007bff;">' +
                '<i class="fa fa-file-o"></i> ' +
                "<span>" +
                attachment.name +
                "</span> " +
                '<span style="color: #6c757d; font-size: 0.9em;">(' +
                sizeKB +
                " KB)</span>" +
                "</a>" +
                "</li>";
              attachmentsList.append(listItem);
            });
          } else {
            attachmentsList.html(
              '<li style="color: #6c757d; font-style: italic;">No hay adjuntos</li>',
            );
          }
        } catch (e) {
          console.error(
            "[ReprtActivityUtils.loadTaskAttachments] ERROR al procesar adjuntos:",
            e,
            data,
          );
          // Mostrar mensaje de error en la UI
          var attachmentsList = jQuery("#task-attachments-list");
          attachmentsList.html(
            '<li style="color: #dc3545; font-style: italic;">Error al cargar adjuntos</li>',
          );
        }
      })
      .fail(function (xhr, status, error) {
        console.error(
          "[ReprtActivityUtils.loadTaskAttachments] ERROR en la petición AJAX:",
          status,
          error,
        );
        var attachmentsList = jQuery("#task-attachments-list");
        attachmentsList.html(
          '<li style="color: #dc3545; font-style: italic;">Error al cargar adjuntos</li>',
        );
      });
  };

  window.ReprtActivityUtils = {
    init: init,
    normalizeReportTime: normalizeReportTime,
    formatNumberOnBlur: formatNumberOnBlur,
    cleanNumberOnFocus: cleanNumberOnFocus,
    convertToDBFormat: convertToDBFormat,
    reportOn: reportOn,
    saveFeedback: saveFeedback,
    saveReport: saveReport,
    selectedActivity: selectedActivity,
    setActivity: setActivity,
    setProgress: setProgress,
    uploadTaskAttachments: uploadTaskAttachments,
    loadTaskAttachments: loadTaskAttachments,
  };
})(jQuery);
