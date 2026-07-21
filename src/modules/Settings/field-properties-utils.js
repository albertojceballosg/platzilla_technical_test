(function (jQuery) {
  // Private variables
  var modal = null,
    availableRoles = null,
    moduleFields = null,
    moduleReference = null,
    presence = null,
    referencedModuleFields = null,
    sourceModuleName = null,
    uiType = null,
    totalPicklistValues = -1,
    totalPipelineValues = -100,
    totalReferenceFilters = -1,
    visibilityProperty = false,
    dataPickListRelationship = null,
    dataPicklistValues = null;

  // Private methods
  var destroyModal = function () {
    if (modal === null) {
      return;
    }
    visibilityProperty = false;
    jQuery(this).remove();
    modal = null;
  };

  var onFailureHandler = function (jQueryResponse) {
    jQuery("#btn-field-properties-save").attr("disabled", false);
    jQuery("#properties-loading").remove();
    alert("Se ha presentado un error: " + jQueryResponse.responseText);
  };

  var onGetPropertiesSuccessHandler = function (response) {
    var modalTemplate = jQuery("#field-properties-modal-template");
    if (!response) {
      alert("Se ha recibido una respuesta inesperada. Intenta mas tarde");
      return;
    }

    // Almacenar datos de respuesta para depuración
    window.fieldPropertiesDebugData = response;

    availableRoles = response.hasOwnProperty("availableroles")
      ? response["availableroles"]
      : null;
    moduleFields = response.hasOwnProperty("modulefields")
      ? response["modulefields"]
      : null;
    presence = response.hasOwnProperty("presence")
      ? response["presence"]
      : null;
    moduleReference = response.hasOwnProperty("modulereference")
      ? response["modulereference"]
      : null;
    referencedModuleFields = response.hasOwnProperty("referencedmodulefields")
      ? response["referencedmodulefields"]
      : null;
    uiType = response.hasOwnProperty("uitype") ? response["uitype"] : null;
    modal = jQuery(modalTemplate.html());

    setBasicProperties(response);
    setValidationProperties(response);
    setModuleReferencesProperties(response);
    setPicklistDependenciesProperties(response);
    setPicklistValuesProperties(response);
    setPicklistRelationship(response);
    setPicklistPipelineRelationship(response);
    setPipelineDependenciesProperties(response);
    setPipelineValuesProperties(response);
    setCalculatedFieldProperties(response);
    setFieldVisibilityProperties(response);

    if (visibilityProperty) {
      modal.find("#visibility-values-properties").collapse("toggle");
    }

    modal
      .find("#initial-date")
      .datepicker({ format: "yyyy-mm-dd", language: "es", weekStart: 1 });
    modal
      .find("#maximum-date")
      .datepicker({ format: "yyyy-mm-dd", language: "es", weekStart: 1 });
    modal
      .modal({ backdrop: "static" })
      .on("hide.bs.modal", function () {
        // Mover el foco fuera del modal antes de cerrar para evitar error de accesibilidad
        modal.find(":focus").blur();
      })
      .on("hidden.bs.modal", destroyModal)
      .on("shown.bs.modal", function () {
        var fieldName = modal.find("#field-name").text();
        
        // Buscar por ID primero (campo estático en el encabezado)
        var picklistMotherLabelField = modal.find("#picklistMotherLabel");
        var picklistMotherPipelineLabel = modal.find("#picklistMotherPipelineLabel");
        
        // Si no se encuentran por ID, buscar por clase (campos dinámicos)
        if (picklistMotherLabelField.length === 0) {
          picklistMotherLabelField = modal.find("input.available-mother").first();
        }
        if (picklistMotherPipelineLabel.length === 0) {
          picklistMotherPipelineLabel = modal.find("input.available-mother").eq(1);
        }
        
        if (picklistMotherLabelField.length > 0) {
          picklistMotherLabelField.val(response.label);
        }
        if (picklistMotherPipelineLabel.length > 0) {
          picklistMotherPipelineLabel.val(response.label);
        }
        modal.find(".panel-collapse").not("#basic-properties").collapse("hide");
      });
    visibilityProperty = false;
  };

  var onSavePropertiesSuccessHandler = function () {
    modal.modal("hide");
    window.location.reload();
  };

  var setBasicProperties = function (response) {
    var isMandatoryField = modal.find("#ismandatory"),
      presenceField = modal.find("#presence"),
      defaultValueField = modal.find("#default-value"),
      calculatedSystemId = modal.find("#calculatedSystemId"),
      dateHelpBlock = modal.find("#date-default-help");

    modal.find("#field-name").text(response.label);
    modal.find("#visibility-field-name").text(response.label);
    var fieldName = modal.find("#field-name").text();
    modal.find('input[name="fieldname"]').val(response.name);
    isMandatoryField.prop("checked", response["ismandatory"] ? true : false);
    if (presence === 0) {
      presenceField.prop("checked", true).attr("disabled", "disabled");
    } else if (presence === 2) {
      presenceField.prop("checked", true).removeAttr("disabled");
    } else {
      presenceField.prop("checked", false).removeAttr("disabled");
    }
    calculatedSystemId.val(response["calculationid"]);
    if (uiType == "2206") {
      defaultValueField.val(response["defaultvalue"]).hide();
      dateHelpBlock.hide();
    } else {
      defaultValueField.val(response["defaultvalue"]).show();
      // Mostrar ayuda solo para campos de fecha (uitype 5)
      if (uiType == "5") {
        dateHelpBlock.show();
      } else {
        dateHelpBlock.hide();
      }
    }
    if (jQuery.inArray(uiType, ["1", "7", "9", "71"]) !== -1) {
      modal.find("#field-length-container").show();
      modal.find("#field-length").val(response.length);
    } else {
      modal.find("#field-length-container").hide();
      modal.find("#field-length").val(null);
    }
    if (jQuery.inArray(uiType, ["7", "9", "71"]) !== -1) {
      modal.find("#field-precision-container").show();
      modal.find("#field-precision").val(response.precision);
    } else {
      modal.find("#field-precision-container").hide();
      modal.find("#field-precision").val(null);
    }
    if (visibilityProperty) {
      modal.find("#basic-properties").closest(".panel").addClass("hidden");
    }
  };

  var setCalculatedFieldProperties = function (response) {
    if (response["calculatedSystem"] === null) {
      modal.find("#calculation").closest(".panel").addClass("hidden");
      return false;
    }
    var calculatedList,
      li,
      row = "",
      selectedValue = response["calculationid"],
      template = modal.find("#calculate-template"),
      divList = modal.find(".calculated-list");
    calculatedList = jQuery.parseJSON(response["calculatedSystem"]);
    for (li = 0; li < calculatedList.length; li++) {
      row = template
        .clone()
        .attr("id", "cs-" + li)
        .attr("rel", calculatedList[li]["calculationName"])
        .attr("title", calculatedList[li].description)
        .html(calculatedList[li].name)
        .removeClass("hide");
      if (calculatedList[li]["calculationName"] == selectedValue) {
        row.addClass("active");
      }
      row.appendTo(divList);
    }
    if (visibilityProperty) {
      modal.find("#calculation").closest(".panel").addClass("hidden");
    }
  };

  var setModuleReferenceFilterProperties = function () {
    var filters =
        moduleReference && moduleReference.hasOwnProperty("filters")
          ? moduleReference["filters"]
          : null,
      filtersSection = modal.find("#reference-filters"),
      i,
      filterTemplate,
      j,
      dummies,
      dummy;

    modal
      .find("#module-references-filters .target-module-label")
      .text(modal.find("#module-reference option:selected").text());

    if (!filters) {
      return;
    }

    for (i = 0; i < filters.length; i += 1) {
      addModuleReferenceFilter();
      filterTemplate = filtersSection.find(".filter:last");
      filterTemplate
        .find('.module-fields > option[value="' + filters[i]["field"] + '"]')
        .prop("selected", true);
      filterTemplate
        .find('.comparator > option[value="' + filters[i]["comparator"] + '"]')
        .prop("selected", true);
      filterTemplate
        .find('.filter-type[value="' + filters[i]["valuetype"] + '"]')
        .prop("checked", true)
        .click();
      if (filters[i]["valuetype"] === "SOURCE FIELD") {
        dummies = filterTemplate.find(
          '.filter-fields > option[value="' + filters[i]["value"] + '"]',
        );
        for (j = 0; j < dummies.length; j += 1) {
          dummy = jQuery(dummies[j]);
          if (dummy.data("module-name") === filters[i]["valuemodulename"]) {
            dummy.prop("selected", true);
            break;
          }
        }
      } else {
        filterTemplate.find(".filter-value").val(filters[i]["value"]);
      }
      filterTemplate
        .find('.operator > option[value="' + filters[i]["operator"] + '"]')
        .prop("selected", true);
    }
    if (visibilityProperty) {
      modal
        .find("#module-references-filters")
        .closest(".panel")
        .addClass("hidden");
    }
  };

  var setModuleReferenceRelationshipProperties = function () {
    var relationships =
        moduleReference && moduleReference.hasOwnProperty("relationships")
          ? moduleReference["relationships"]
          : null,
      relationshipsSection = modal.find("#relationships"),
      relationshipTemplateHtml = jQuery("#relationship-template").html(),
      fieldName,
      referencedModuleFieldName,
      moduleFieldOptions,
      referencedModuleFieldOptions,
      relationshipTemplate;

    if (!relationships) {
      return;
    }

    for (referencedModuleFieldName in relationships) {
      if (!relationships.hasOwnProperty(referencedModuleFieldName)) {
        continue;
      }

      moduleFieldOptions = [];
      for (fieldName in moduleFields) {
        if (!moduleFields.hasOwnProperty(fieldName)) {
          continue;
        }

        moduleFieldOptions.push(
          jQuery("<option></option>")
            .text(moduleFields[fieldName].label)
            .val(fieldName)
            .prop(
              "selected",
              relationships[referencedModuleFieldName] === fieldName,
            ),
        );
      }

      referencedModuleFieldOptions = [];
      if (referencedModuleFields) {
        for (fieldName in referencedModuleFields) {
          if (!referencedModuleFields.hasOwnProperty(fieldName)) {
            continue;
          }

          referencedModuleFieldOptions.push(
            jQuery("<option></option>")
              .text(referencedModuleFields[fieldName])
              .val(fieldName)
              .prop("selected", referencedModuleFieldName === fieldName),
          );
        }
      }

      relationshipTemplate = jQuery(relationshipTemplateHtml);
      relationshipTemplate
        .find(".referenced-module-fields")
        .append(referencedModuleFieldOptions);
      relationshipTemplate.find(".module-fields").append(moduleFieldOptions);
      relationshipsSection.append(relationshipTemplate);
    }
  };

  var setModuleReferencesProperties = function () {
    var moduleReferencesProperties = modal.find(
        "#module-references-properties",
      ),
      moduleReferenceFiltersProperties = modal.find(
        "#module-references-filters",
      ),
      referencedModuleName =
        moduleReference && moduleReference.hasOwnProperty("name")
          ? moduleReference["name"]
          : null;
    if (uiType !== "10") {
      moduleReferencesProperties.closest(".panel").addClass("hidden");
      moduleReferenceFiltersProperties.closest(".panel").addClass("hidden");
      return;
    }

    if (moduleReference) {
      moduleReferencesProperties
        .find("#module-reference")
        .val(referencedModuleName);
    }

    setModuleReferenceRelationshipProperties();
    setModuleReferenceFilterProperties();
    moduleReferencesProperties.closest(".panel").removeClass("hidden");
    moduleReferenceFiltersProperties.closest(".panel").removeClass("hidden");
    if (visibilityProperty) {
      moduleReferencesProperties.closest(".panel").addClass("hidden");
      modal
        .find("#module-references-filters")
        .closest(".panel")
        .addClass("hidden");
    }
  };

  var setPicklistDependenciesProperties = function (response) {
    var dependenciesSection = modal.find("#dependencies"),
      dependencyTemplateHtml = jQuery("#dependency-template").html(),
      dependencyTemplate,
      field,
      fieldName,
      mandatoryFieldOptions,
      optionalFieldOptions,
      visibleFieldOptions,
      hiddenFieldOptions,
      value,
      picklistValues,
      picklistValue;

    if (jQuery.inArray(uiType, ["15", "16", "8192"]) === -1) {
      dependenciesSection.find(".dependency").remove();
      modal
        .find("#dependencies-properties")
        .closest(".panel")
        .addClass("hidden");
    }
    if (
      jQuery.inArray(uiType, ["15", "16"]) === -1 ||
      !moduleFields ||
      !response.hasOwnProperty("picklistvalues") ||
      !response["picklistvalues"]
    ) {
      return;
    }

    picklistValues = {
      __NO_SELECTION__: {
        id: 0,
        label: "(Sin selección)",
        value: null,
      },
    };
    for (value in response["picklistvalues"]) {
      if (!response["picklistvalues"].hasOwnProperty(value)) {
        continue;
      }

      picklistValues[value] = response["picklistvalues"][value];
    }

    for (value in picklistValues) {
      if (!picklistValues.hasOwnProperty(value)) {
        continue;
      }

      mandatoryFieldOptions = [];
      optionalFieldOptions = [];
      visibleFieldOptions = [];
      hiddenFieldOptions = [];
      picklistValue = picklistValues[value];
      for (fieldName in moduleFields) {
        if (!moduleFields.hasOwnProperty(fieldName)) {
          continue;
        } else if (fieldName === response.name) {
          continue;
        }

        field = moduleFields[fieldName];
        if (
          field["hiddenfor"] &&
          jQuery.inArray(picklistValue.value, field["hiddenfor"]) !== -1
        ) {
          hiddenFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
          if (field["ismandatory"]) {
            mandatoryFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          } else {
            optionalFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          }
        } else if (
          field["visiblefor"] &&
          jQuery.inArray(picklistValue.value, field["visiblefor"]) !== -1
        ) {
          visibleFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
          if (field["ismandatory"]) {
            mandatoryFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          } else {
            optionalFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          }
        } else if (field["ismandatory"]) {
          mandatoryFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
        } else {
          optionalFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
        }
      }
      dependencyTemplate = jQuery(dependencyTemplateHtml).attr(
        "data-picklist-value-id",
        picklistValue.id,
      );
      dependencyTemplate.find(".picklist-value").val(value);
      dependencyTemplate
        .find(".picklist-label")
        .val(
          picklistValue.hasOwnProperty("label")
            ? picklistValue.label
            : picklistValue.value,
        );
      dependencyTemplate
        .find(".available-fields > .optional-fields")
        .append(optionalFieldOptions);
      dependencyTemplate
        .find(".available-fields > .mandatory-fields")
        .append(mandatoryFieldOptions);
      dependencyTemplate.find(".hidden-fields").append(hiddenFieldOptions);
      dependencyTemplate.find(".visible-fields").append(visibleFieldOptions);
      dependenciesSection.append(dependencyTemplate);
    }
    modal
      .find("#dependencies-properties")
      .closest(".panel")
      .removeClass("hidden");
    if (visibilityProperty) {
      modal
        .find("#dependencies-properties")
        .closest(".panel")
        .addClass("hidden");
    }
  };

  var setPipelineDependenciesProperties = function (response) {
    var dependenciesSection = modal.find("#dependencies"),
      dependencyTemplateHtml = jQuery("#dependency-template").html(),
      dependencyTemplate,
      i,
      pipelineValue,
      field,
      fieldName,
      mandatoryFieldOptions,
      optionalFieldOptions,
      visibleFieldOptions,
      hiddenFieldOptions;

    if (jQuery.inArray(uiType, ["15", "16", "8192"]) === -1) {
      dependenciesSection.find(".dependency").remove();
      modal
        .find("#dependencies-properties")
        .closest(".panel")
        .addClass("hidden");
    }
    if (
      uiType !== "8192" ||
      !moduleFields ||
      !response.hasOwnProperty("pipelinevalues") ||
      !response["pipelinevalues"]
    ) {
      return;
    }

    for (i = 0; i < response["pipelinevalues"].length; i += 1) {
      mandatoryFieldOptions = [];
      optionalFieldOptions = [];
      visibleFieldOptions = [];
      hiddenFieldOptions = [];
      pipelineValue = response["pipelinevalues"][i];
      for (fieldName in moduleFields) {
        if (!moduleFields.hasOwnProperty(fieldName)) {
          continue;
        } else if (fieldName === response.name) {
          continue;
        }

        field = moduleFields[fieldName];
        if (
          field["hiddenfor"] &&
          jQuery.inArray(pipelineValue, field["hiddenfor"]) !== -1
        ) {
          hiddenFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
          if (field["ismandatory"]) {
            mandatoryFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          } else {
            optionalFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          }
        } else if (
          field["visiblefor"] &&
          jQuery.inArray(pipelineValue, field["visiblefor"]) !== -1
        ) {
          visibleFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
          if (field["ismandatory"]) {
            mandatoryFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          } else {
            optionalFieldOptions.push(
              jQuery("<option></option>")
                .text(field.label)
                .val(fieldName)
                .hide(),
            );
          }
        } else if (field["ismandatory"]) {
          mandatoryFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
        } else {
          optionalFieldOptions.push(
            jQuery("<option></option>").text(field.label).val(fieldName),
          );
        }
      }
      dependencyTemplate = jQuery(dependencyTemplateHtml).attr(
        "data-pipeline-value-id",
        i,
      );
      dependencyTemplate.find(".picklist-value").val(pipelineValue);
      dependencyTemplate.find(".picklist-label").val(pipelineValue);
      dependencyTemplate
        .find(".available-fields > .optional-fields")
        .append(optionalFieldOptions);
      dependencyTemplate
        .find(".available-fields > .mandatory-fields")
        .append(mandatoryFieldOptions);
      dependencyTemplate.find(".hidden-fields").append(hiddenFieldOptions);
      dependencyTemplate.find(".visible-fields").append(visibleFieldOptions);
      dependenciesSection.append(dependencyTemplate);
    }
    modal
      .find("#dependencies-properties")
      .closest(".panel")
      .removeClass("hidden");
    if (visibilityProperty) {
      modal
        .find("#dependencies-properties")
        .closest(".panel")
        .addClass("hidden");
    }
  };

  var setPicklistValuesProperties = function (response) {
    var picklistValuesSection = modal.find("#picklist-values"),
      picklistValueHtml = jQuery("#picklist-value-template").html(),
      picklistValues = response.hasOwnProperty("picklistvalues")
        ? response["picklistvalues"]
        : null,
      seq = 0,
      picklistValueTemplate,
      availableRoleId,
      picklistValue,
      hiddenRolesOptions,
      visibleRolesOptions,
      option,
      value;

    picklistValuesSection.find(".picklist-value").remove();
    if (jQuery.inArray(uiType, ["15", "16", "33"]) === -1 || !picklistValues) {
      modal
        .find("#picklist-values-properties")
        .closest(".panel")
        .addClass("hidden");
      return;
    } else if (uiType === "16") {
      modal.find(".add-value-button").prop("disabled", true);
    }

    for (value in picklistValues) {
      if (!picklistValues.hasOwnProperty(value)) {
        continue;
      }
      picklistValue = picklistValues[value];
      if (uiType === "16") {
        picklistValueTemplate = jQuery(picklistValueHtml).attr(
          "data-picklist-value-id",
          picklistValue.id,
        );
        picklistValueTemplate
          .find(".picklist-value-id")
          .val(picklistValue.id)
          .prop("disabled", true);
        picklistValueTemplate.find(".picklist-seq").val(seq.toString());
        picklistValueTemplate
          .find(".picklist-label")
          .val(picklistValue.value)
          .prop("disabled", true);
        picklistValueTemplate.find(".visible-roles").prop("disabled", true);
        picklistValueTemplate.find(".hidden-roles").prop("disabled", true);
        picklistValueTemplate.find(".hide-value-button").prop("disabled", true);
        picklistValueTemplate.find(".show-value-button").prop("disabled", true);
        picklistValueTemplate
          .find(".delete-value-button")
          .prop("disabled", true);
      } else {
        hiddenRolesOptions = [];
        visibleRolesOptions = [];
        for (availableRoleId in availableRoles) {
          if (!availableRoles.hasOwnProperty(availableRoleId)) {
            continue;
          }

          option = jQuery("<option></option>")
            .text(availableRoles[availableRoleId])
            .val(availableRoleId);

          if (jQuery.inArray(availableRoleId, picklistValue.roles) !== -1) {
            visibleRolesOptions.push(option);
          } else {
            hiddenRolesOptions.push(option);
            visibleRolesOptions.push(
              jQuery("<option></option>")
                .text(availableRoles[availableRoleId])
                .val(availableRoleId)
                .hide(),
            );
          }
        }

        picklistValueTemplate = jQuery(picklistValueHtml).attr(
          "data-picklist-value-id",
          picklistValue.id,
        );
        picklistValueTemplate.find(".picklist-value-id").val(picklistValue.id);
        picklistValueTemplate.find(".picklist-seq").val(seq.toString());
        picklistValueTemplate.find(".picklist-label").val(picklistValue.value);
        picklistValueTemplate
          .find(".visible-roles")
          .append(visibleRolesOptions);
        picklistValueTemplate.find(".hidden-roles").append(hiddenRolesOptions);
      }
      picklistValuesSection.append(picklistValueTemplate);
      modal
        .find("#picklist-values-properties")
        .closest(".panel")
        .removeClass("hidden");
      if (visibilityProperty) {
        modal
          .find("#picklist-values-properties")
          .closest(".panel")
          .addClass("hidden");
      }
      seq = parseInt(seq) + 1;
    }
  };

  var setPicklistRelationship = function (response) {
    var relationshipOptionsSection = modal.find("#relationship-tbody"),
      relationshipOptionsHtml = jQuery(
        "#fields-picklist-relationship-template",
      ).html(),
      availableDaughters = modal.find("#available-daughter"),
      relationName = modal.find("#relationship-name"),
      picklistMotherLabel = modal.find("#picklistMotherLabel"),
      dataPickListRelationship = response.hasOwnProperty("relationship")
        ? response["relationship"]
        : null,
      availablePicklist = response.hasOwnProperty("daughtersAvailable")
        ? response["daughtersAvailable"]
        : null,
      daughterList,
      option,
      options,
      relation,
      daughterSelected,
      selectedListValues,
      motherId,
      daughterIds,
      value,
      picklistValue,
      relationshipTemplate,
      motherPicklistId,
      motherPicklistValue,
      shownDaughter,
      hiddenDaughter;


    if (availablePicklist === null) {
      modal.find("#picklist-relationship").closest(".panel").addClass("hidden");
      return;
    }

    dataPicklistValues = response.hasOwnProperty("picklistvalues")
      ? response["picklistvalues"]
      : null;
    if (dataPickListRelationship !== null) {
      for (relation in dataPickListRelationship) {
        relationName.val(dataPickListRelationship[relation].relationname);
        daughterList = dataPickListRelationship[relation].daughter;
        options = dataPickListRelationship[relation].relation;
        relationshipOptionsSection.find(".tr-picklist-relationship").remove();
        availableDaughters.empty();
        if (jQuery.isArray(availablePicklist)) {
          availableDaughters.append(
            jQuery("<option></option>")
              .text("Seleccione un campo lista")
              .val("")
              .attr("list-data", ""),
          );
          availablePicklist.forEach(function (list) {
            daughterSelected = list["fieldname"] === daughterList;
            availableDaughters.append(
              jQuery("<option></option>")
                .text(list["fieldlabel"])
                .val(list["fieldname"])
                .attr("list-data", list["values"])
                .attr("selected", daughterSelected),
            );
          });
        }
        selectedListValues = JSON.parse(
          availableDaughters.find("option:selected").attr("list-data"),
        );
        for (option in options) {
          motherId = option;
          daughterIds = options[option];
          for (value in dataPicklistValues) {
            if (!dataPicklistValues.hasOwnProperty(value)) {
              continue;
            }
            picklistValue = dataPicklistValues[value];
            if (parseInt(picklistValue.id) !== parseInt(motherId)) {
              continue;
            }
            relationshipTemplate = jQuery(relationshipOptionsHtml);
            motherPicklistId = relationshipTemplate
              .find("input")
              .eq(0)
              .val(picklistValue.id);
            motherPicklistValue = relationshipTemplate
              .find("textarea")
              .eq(0)
              .val(picklistValue.value);
            shownDaughter = relationshipTemplate.find("select").eq(0);
            hiddenDaughter = relationshipTemplate.find("select").eq(1);
            jQuery.each(selectedListValues, function (key, value) {
              if (jQuery.inArray(key, daughterIds) !== -1) {
                shownDaughter.append(
                  jQuery("<option></option>").text(value).val(key),
                );
              } else {
                hiddenDaughter.append(
                  jQuery("<option></option>").text(value).val(key),
                );
              }
            });
            relationshipOptionsSection.append(relationshipTemplate);
          }
        }
      }
    } else {
      relationshipOptionsSection.find(".tr-picklist-relationship").remove();
      availableDaughters.empty();
      if (jQuery.isArray(availablePicklist)) {
        availableDaughters.append(
          jQuery("<option></option>")
            .text("Seleccione un campo lista")
            .val("")
            .attr("list-data", ""),
        );
        availablePicklist.forEach(function (list) {
          availableDaughters.append(
            jQuery("<option></option>")
              .text(list["fieldlabel"])
              .val(list["fieldname"])
              .attr("list-data", list["values"]),
          );
        });
      }
    }
    if (visibilityProperty) {
      modal.find("#picklist-relationship").closest(".panel").addClass("hidden");
    }
  };

  var setPicklistPipelineRelationship = function (response) {
    var availablePipelines = modal.find("#available-pipeline");
    var relationshipOptionsSection = modal.find("#pipeline-relationship-tbody");
    var relationshipOptionsHtml = jQuery(
      "#fields-picklist-pipeline-relationship-template",
    ).html();
    var options = response.picklistpipelinerelationship;
    var availablePipelineFields = response.pipelinefields;
    var pipelineInfo = response.pipelineinfo || {};
    var pipelineTranslations = response.pipelinetranslations || {};
    var dataPicklistValues = response.picklistvalues;
    var pipelineSelected = "";
    var relationName = modal.find("#pipeline-relationship-name");
    var picklistMotherPipelineLabel = modal.find("#picklistMotherPipelineLabel");
    var relation,
      pipelineSelected,
      selectedPipelineValues,
      motherId,
      daughterIndices,
      value,
      picklistValue,
      relationshipTemplate,
      motherPicklistId,
      motherPicklistValue,
      shownPipeline,
      hiddenPipeline;

    // Caso 1: El módulo no tiene pipelines - ocultar sección
    if (!pipelineInfo.haspipelines) {
      modal
        .find("#picklist-pipeline-relationship")
        .closest(".panel")
        .addClass("hidden");
      return;
    }

    // Caso 2: No hay pipelines disponibles (todos ya tienen relación con otros picklists)
    if (!pipelineInfo.hasavailablepipelines) {
      // Si el picklist actual ya tiene relación, mostrarla para modificar/eliminar
      if (options !== null) {
        // Mostrar la relación existente (comportamiento normal)
        relationName.val(options.relationname);
        pipelineList = options.pipelinename;
        options = options.relationships;
        relationshipOptionsSection
          .find(".tr-picklist-pipeline-relationship")
          .remove();
        availablePipelines.empty();
        
        // Mostrar solo el pipeline relacionado actualmente
        if (pipelineList) {
          var pipelineFragment = document.createDocumentFragment();
          var option = jQuery("<option></option>")
            .text(options.pipelinename || pipelineList)
            .val(pipelineList)
            .attr("selected", "selected");
          pipelineFragment.appendChild(option[0]);
          availablePipelines.append(pipelineFragment);
        }
        
        // Mostrar filas de relación existente
        dataPicklistValues = response.hasOwnProperty("picklistvalues")
          ? response["picklistvalues"]
          : null;
        window.dataPicklistValues = dataPicklistValues;
        
        for (value in dataPicklistValues) {
          if (!dataPicklistValues.hasOwnProperty(value)) {
            continue;
          }
          picklistValue = dataPicklistValues[value];
          relationshipTemplate = jQuery(relationshipOptionsHtml);
          motherPicklistId = relationshipTemplate
            .find("input")
            .eq(0)
            .val(picklistValue.value);
          motherPicklistValue = relationshipTemplate
            .find("textarea")
            .eq(0)
            .val(picklistValue.value);
          shownPipeline = relationshipTemplate.find("select").eq(0);
          hiddenPipeline = relationshipTemplate.find("select").eq(1);
          if (options && options[picklistValue.value]) {
            selectedPipelineValues = options[picklistValue.value].visible;
            hiddenPipelineValues = options[picklistValue.value].hidden;
            var shownFragment = document.createDocumentFragment();
            jQuery.each(selectedPipelineValues, function (index, val) {
              var option = jQuery("<option></option>").text(val).val(val);
              shownFragment.appendChild(option[0]);
            });
            shownPipeline.append(shownFragment);
            var hiddenFragment = document.createDocumentFragment();
            jQuery.each(hiddenPipelineValues, function (index, val) {
              var option = jQuery("<option></option>").text(val).val(val);
              hiddenFragment.appendChild(option[0]);
            });
            hiddenPipeline.append(hiddenFragment);
          }
          relationshipOptionsSection.append(relationshipTemplate);
        }
      } else {
        // No hay pipelines disponibles y el picklist no tiene relación - mostrar mensaje traducible
        relationshipOptionsSection
          .find(".tr-picklist-pipeline-relationship")
          .remove();
        availablePipelines.empty();
        
        var messageRow = jQuery("<tr></tr>")
          .html('<td colspan="4" class="text-center text-info">' +
                '<i class="fa fa-info-circle"></i> ' +
                (pipelineTranslations.no_available || 'No hay campos Pipeline disponibles para establecer la relación. Todos los campos Pipeline del módulo ya están relacionados con otros campos Picklist. Para establecer una nueva relación, primero elimine la relación existente del Pipeline deseado.') +
                '</td>');
        relationshipOptionsSection.append(messageRow);
      }
      return;
    }

    // Caso 3: Hay pipelines disponibles - comportamiento normal
    dataPicklistValues = response.hasOwnProperty("picklistvalues")
      ? response["picklistvalues"]
      : null;
    // Update global variable for setPipelineDaughter
    window.dataPicklistValues = dataPicklistValues;
    if (options !== null) {
      relationName.val(options.relationname);
      pipelineList = options.pipelinename;
      options = options.relationships;
      relationshipOptionsSection
        .find(".tr-picklist-pipeline-relationship")
        .remove();
      availablePipelines.empty();
      if (jQuery.isArray(availablePipelineFields)) {
        var pipelineFragment = document.createDocumentFragment();
        var defaultOption = jQuery("<option></option>")
          .text("Seleccione un campo Pipeline")
          .val("")
          .attr("list-data", "");
        pipelineFragment.appendChild(defaultOption[0]);
        availablePipelineFields.forEach(function (list) {
          pipelineSelected = list["fieldname"] === pipelineList;
          var option = jQuery("<option></option>")
            .text(list["fieldlabel"])
            .val(list["fieldname"])
            .attr("list-data", JSON.stringify(list["values"]))
            .attr("selected", pipelineSelected);
          pipelineFragment.appendChild(option[0]);
        });
        availablePipelines.append(pipelineFragment);
      }
      for (value in dataPicklistValues) {
        if (!dataPicklistValues.hasOwnProperty(value)) {
          continue;
        }
        picklistValue = dataPicklistValues[value];
        relationshipTemplate = jQuery(relationshipOptionsHtml);
        motherPicklistId = relationshipTemplate
          .find("input")
          .eq(0)
          .val(picklistValue.value);
        motherPicklistValue = relationshipTemplate
          .find("textarea")
          .eq(0)
          .val(picklistValue.value);
        shownPipeline = relationshipTemplate.find("select").eq(0);
        hiddenPipeline = relationshipTemplate.find("select").eq(1);
        if (options && options[picklistValue.value]) {
          selectedPipelineValues = options[picklistValue.value].visible;
          hiddenPipelineValues = options[picklistValue.value].hidden;
          var shownFragment = document.createDocumentFragment();
          jQuery.each(selectedPipelineValues, function (index, val) {
            var option = jQuery("<option></option>").text(val).val(val);
            shownFragment.appendChild(option[0]);
          });
          shownPipeline.append(shownFragment);
          var hiddenFragment = document.createDocumentFragment();
          jQuery.each(hiddenPipelineValues, function (index, val) {
            var option = jQuery("<option></option>").text(val).val(val);
            hiddenFragment.appendChild(option[0]);
          });
          hiddenPipeline.append(hiddenFragment);
        }
        relationshipOptionsSection.append(relationshipTemplate);
      }
    } else {
      relationshipOptionsSection
        .find(".tr-picklist-pipeline-relationship")
        .remove();
      availablePipelines.empty();
      if (jQuery.isArray(availablePipelineFields)) {
        var pipelineFragment = document.createDocumentFragment();
        var defaultOption = jQuery("<option></option>")
          .text("Seleccione un campo Pipeline")
          .val("")
          .attr("list-data", "");
        pipelineFragment.appendChild(defaultOption[0]);
        availablePipelineFields.forEach(function (list) {
          var option = jQuery("<option></option>")
            .text(list["fieldlabel"])
            .val(list["fieldname"])
            .attr("list-data", JSON.stringify(list["values"]));
          pipelineFragment.appendChild(option[0]);
        });
        availablePipelines.append(pipelineFragment);
      }
    }
    if (visibilityProperty) {
      modal
        .find("#picklist-pipeline-relationship")
        .closest(".panel")
        .addClass("hidden");
    }
  };

  var setFieldVisibilityProperties = function (response) {
    var visibilityValuesSection = modal.find("#visibility-values"),
      visibilityValueHtml = jQuery("#fields-visibility-template").html(),
      visibilityValues = response.hasOwnProperty("visibility")
        ? response["visibility"]
        : null,
      visibilityValueTemplate,
      hiddenProfiles,
      shownProfiles,
      object,
      option;

    visibilityValuesSection.find(".visibility-value").remove();
    if (jQuery.isArray(visibilityValues)) {
      visibilityValueTemplate = jQuery(visibilityValueHtml);
      shownProfiles = visibilityValueTemplate.find("select").eq(0);
      hiddenProfiles = visibilityValueTemplate.find("select").eq(1);
      visibilityValues.forEach(function (object) {
        if (object.profileid) {
          option = jQuery("<option></option>")
            .text(object.profilename)
            .val(object.profileid + "@" + object.fieldid + "@" + object.visible)
            .attr("title", object.title);
          if (object.visible === 0 || object.visible === "0") {
            shownProfiles.append(option);
          } else {
            hiddenProfiles.append(option);
          }
        }
      });
      visibilityValuesSection.append(visibilityValueTemplate);
    }
    if (!visibilityProperty) {
      modal
        .find("#visibility-values-properties")
        .closest(".panel")
        .addClass("hidden");
    }
  };

  var setPipelineValuesProperties = function (response) {
    var pipelineValuesSection = modal.find("#pipeline-values"),
      pipelineValueHtml = jQuery("#pipeline-value-template").html(),
      pipelineValues = response.hasOwnProperty("pipelinevalues")
        ? response["pipelinevalues"]
        : null,
      pipelineValueTemplate,
      i;

    pipelineValuesSection.find(".pipeline-value").remove();
    if (uiType !== "8192") {
      modal
        .find("#pipeline-values-properties")
        .closest(".panel")
        .addClass("hidden");
      return;
    }

    for (i = 0; i < pipelineValues.length; i += 1) {
      pipelineValueTemplate = jQuery(pipelineValueHtml).attr(
        "data-pipeline-value-id",
        i,
      );
      if (i === 0) {
        jQuery(pipelineValueTemplate).find("button").eq(1).addClass("hide");
      } else if (i === pipelineValues.length - 1) {
        jQuery(pipelineValueTemplate).find("button").eq(2).addClass("hide");
      }
      pipelineValueTemplate.find(".pipeline-label").val(pipelineValues[i]);
      pipelineValuesSection.append(pipelineValueTemplate);
      modal
        .find("#pipeline-values-properties")
        .closest(".panel")
        .removeClass("hidden");
    }
    if (visibilityProperty) {
      modal
        .find("#pipeline-values-properties")
        .closest(".panel")
        .addClass("hidden");
    }
  };

  var setValidationProperties = function (response) {
    var validationType,
      validations = response.hasOwnProperty("validations")
        ? response["validations"]
        : null;

    if (jQuery.inArray(uiType, ["5", "6"]) !== -1) {
      modal.find(".number-validation").addClass("hidden");
      modal.find(".date-validation").removeClass("hidden");
    } else if (uiType === "7") {
      modal.find(".number-validation").removeClass("hidden");
      modal.find(".date-validation").addClass("hidden");
    } else {
      modal.find(".number-validation").addClass("hidden");
      modal.find(".date-validation").addClass("hidden");
    }

    if (!validations) {
      if (visibilityProperty) {
        modal
          .find("#validation-properties")
          .closest(".panel")
          .addClass("hidden");
      }
      return;
    }
    for (validationType in validations) {
      if (!validations.hasOwnProperty(validationType)) {
        continue;
      }

      if (validationType === "unique") {
        modal.find("#unique").prop("checked", validations.unique);
      } else if (validationType === "date") {
        if (validations.date["initialvalue"] === "today") {
          modal.find("#initial-date-select").val("today");
          modal.find("#initial-date").val(validations.date["initialvalue"]);
        } else if (validations.date["initialvalue"]) {
          modal.find("#initial-date-select").val("custom");
          modal.find("#initial-date").val(validations.date["initialvalue"]);
        } else {
          modal.find("#initial-date-select").val("");
          modal.find("#initial-date").val("");
        }
        setDateValidationFields(modal.find("#initial-date-select"));
        if (validations.date["maximumvalue"] === "today") {
          modal.find("#maximum-date-select").val("today");
          modal.find("#maximum-date").val(validations.date["maximumvalue"]);
        } else if (validations.date["maximumvalue"]) {
          modal.find("#maximum-date-select").val("custom");
          modal.find("#maximum-date").val(validations.date["maximumvalue"]);
        } else {
          modal.find("#maximum-date-select").val("");
          modal.find("#maximum-date").val("");
        }
        setDateValidationFields(modal.find("#maximum-date-select"));
      } else if (validationType === "number") {
        modal.find("#initial-value").val(validations.number["initialvalue"]);
        modal.find("#maximum-value").val(validations.number["maximumvalue"]);
      }
    }
    if (visibilityProperty) {
      modal.find("#validation-properties").closest(".panel").addClass("hidden");
    }
  };

  var updatePipeLineOrder = function (table) {
    var lastTr;
    table.find("tr").each(function (index, tr) {
      lastTr = jQuery(tr);
      lastTr.attr("data-pipeline-value-id", index);
      if (index === 0) {
        lastTr.find("button").eq(1).addClass("hide");
      } else {
        lastTr.find("button").eq(1).removeClass("hide");
        lastTr.find("button").eq(2).removeClass("hide");
      }
    });
    lastTr.find("button").eq(2).addClass("hide");
  };

  var validateProperties = function () {
    var section, rows, row, selectedValues, value, filterType, i;

    if (uiType === "10") {
      section = modal.find("#reference-filters");
      rows = section.find(".filter");
      if (rows.length === 0) {
        return true;
      }

      for (i = 0; i < rows.length; i += 1) {
        row = jQuery(rows[i]);

        value = row.find(".module-fields").val();
        if (value === null || value === undefined || value.trim() === "") {
          alert("Seleciona el campo");
          return false;
        }

        value = row.find(".comparator").val();
        if (value === null || value === undefined || value.trim() === "") {
          alert("Seleciona el operador de comparación");
          return false;
        }

        filterType = row.find(".filter-type:checked").val();
        if (
          filterType === null ||
          filterType === undefined ||
          filterType.trim() === ""
        ) {
          alert("Seleciona el tipo de valor");
          return false;
        }

        if (filterType === "SOURCE FIELD") {
          value = row.find(".filter-fields").val();
          if (value === null || value === undefined || value.trim() === "") {
            alert("Seleciona el campo");
            return false;
          }
        } else if (filterType === "LITERAL") {
          value = row.find(".filter-value").val();
          if (value === null || value === undefined || value.trim() === "") {
            alert("Introduce el valor");
            return false;
          }
        }
      }
    } else if (jQuery.inArray(uiType, ["15", "16", "33"]) !== -1) {
      section = modal.find("#picklist-values");
      rows = section.find(".picklist-value");
      if (rows.length === 0) {
        alert("Debes suministrar las opciones del campo");
        return false;
      }

      selectedValues = [];
      for (i = 0; i < rows.length; i += 1) {
        row = jQuery(rows[i]);
        value = row.find(".picklist-label").val();
        if (jQuery.inArray(value, selectedValues) !== -1) {
          alert(
            "El valor " +
              (value ? '"' + value + '"' : "(vacío)") +
              " está repetido",
          );
          return false;
        }
        selectedValues.push(value);
      }
    } else if (uiType === "8192") {
      section = modal.find("#pipeline-values");
      rows = section.find(".pipeline-value");
      if (rows.length === 0) {
        alert("Debes suministrar las opciones del campo");
        return false;
      }
    }

    return true;
  };

  // Public methods
  var addModuleReferenceFilter = function () {
    var filtersSection = modal.find("#reference-filters"),
      filterTemplateHtml = jQuery("#reference-filter-template").html(),
      referencedModuleName =
        moduleReference && moduleReference.hasOwnProperty("name")
          ? moduleReference["name"]
          : null,
      fieldName,
      moduleFieldOptions,
      referencedModuleFieldOptions,
      filterTemplate;

    moduleFieldOptions = [];
    for (fieldName in moduleFields) {
      if (!moduleFields.hasOwnProperty(fieldName)) {
        continue;
      }

      moduleFieldOptions.push(
        jQuery("<option></option>")
          .attr("data-module-name", sourceModuleName)
          .text(moduleFields[fieldName].label)
          .val(fieldName),
      );
    }

    referencedModuleFieldOptions = [];
    if (referencedModuleFields) {
      for (fieldName in referencedModuleFields) {
        if (!referencedModuleFields.hasOwnProperty(fieldName)) {
          continue;
        }

        referencedModuleFieldOptions.push(
          jQuery("<option></option>")
            .attr("data-module-name", referencedModuleName)
            .text(referencedModuleFields[fieldName])
            .val(fieldName),
        );
      }
    }

    filtersSection.find(".operator:last").prop("disabled", false).show();
    filterTemplate = jQuery(filterTemplateHtml);
    filterTemplate.find(".module-fields").append(referencedModuleFieldOptions);
    filterTemplate
      .find(".filter-type")
      .attr("name", "filtertype" + totalReferenceFilters);
    filterTemplate.find(".filter-fields").append(moduleFieldOptions);
    filtersSection.append(filterTemplate);
    totalReferenceFilters -= 1;
  };

  var addModuleReferenceRelationship = function () {
    var relationshipsSection = modal.find("#relationships"),
      relationshipTemplateHtml = jQuery("#relationship-template").html(),
      fieldName,
      moduleFieldOptions,
      referencedModuleFieldOptions,
      relationshipTemplate;

    moduleFieldOptions = [];
    for (fieldName in moduleFields) {
      if (!moduleFields.hasOwnProperty(fieldName)) {
        continue;
      }

      moduleFieldOptions.push(
        jQuery("<option></option>")
          .text(moduleFields[fieldName].label)
          .val(fieldName),
      );
    }

    referencedModuleFieldOptions = [];
    if (referencedModuleFields) {
      for (fieldName in referencedModuleFields) {
        if (!referencedModuleFields.hasOwnProperty(fieldName)) {
          continue;
        }

        referencedModuleFieldOptions.push(
          jQuery("<option></option>")
            .text(referencedModuleFields[fieldName])
            .val(fieldName),
        );
      }
    }

    relationshipTemplate = jQuery(relationshipTemplateHtml);
    relationshipTemplate
      .find(".referenced-module-fields")
      .append(referencedModuleFieldOptions);
    relationshipTemplate.find(".module-fields").append(moduleFieldOptions);
    relationshipsSection.append(relationshipTemplate);
  };

  var addPicklistValue = function () {
    var picklistValuesSection = modal.find("#picklist-values"),
      dependenciesSection = modal.find("#dependencies"),
      picklistValueHtml = jQuery("#picklist-value-template").html(),
      dependencyTemplateHtml = jQuery("#dependency-template").html(),
      dependencyTemplate,
      field,
      fieldName,
      mandatoryFieldOptions,
      visibleFieldOptions,
      picklistValueTemplate,
      availableRoleId,
      visibleRolesOptions,
      option,
      value;

    visibleRolesOptions = [];
    for (availableRoleId in availableRoles) {
      if (!availableRoles.hasOwnProperty(availableRoleId)) {
        continue;
      }

      option = jQuery("<option></option>")
        .text(availableRoles[availableRoleId])
        .val(availableRoleId);
      visibleRolesOptions.push(option);
    }

    mandatoryFieldOptions = [];
    visibleFieldOptions = [];
    for (fieldName in moduleFields) {
      if (!moduleFields.hasOwnProperty(fieldName)) {
        continue;
      }

      field = moduleFields[fieldName];
      if (field["ismandatory"]) {
        mandatoryFieldOptions.push(
          jQuery("<option></option>").text(field.label).val(fieldName),
        );
      } else {
        visibleFieldOptions.push(
          jQuery("<option></option>").text(field.label).val(fieldName),
        );
      }
    }

    picklistValueTemplate = jQuery(picklistValueHtml).attr(
      "data-picklist-value-id",
      totalPicklistValues,
    );
    picklistValueTemplate.find(".picklist-value-id").val(totalPicklistValues);
    picklistValueTemplate.find(".picklist-label").val();
    picklistValueTemplate.find(".visible-roles").append(visibleRolesOptions);
    picklistValuesSection.append(picklistValueTemplate);

    dependencyTemplate = jQuery(dependencyTemplateHtml).attr(
      "data-picklist-value-id",
      totalPicklistValues,
    );
    dependencyTemplate.find(".picklist-value").val("");
    dependencyTemplate.find(".picklist-label").val("");
    dependencyTemplate
      .find(".available-fields > .optional-fields")
      .append(visibleFieldOptions);
    dependencyTemplate
      .find(".available-fields > .mandatory-fields")
      .append(mandatoryFieldOptions);
    dependenciesSection.append(dependencyTemplate);

    totalPicklistValues -= 1;
  };

  var addPipelineValue = function () {
    var pipelineValuesSection = modal.find("#pipeline-values"),
      dependenciesSection = modal.find("#dependencies"),
      pipelineValueHtml = jQuery("#pipeline-value-template").html(),
      dependencyTemplateHtml = jQuery("#dependency-template").html(),
      dependencyTemplate,
      pipelineValueTemplate,
      mandatoryFieldOptions,
      visibleFieldOptions,
      fieldName,
      field;

    mandatoryFieldOptions = [];
    visibleFieldOptions = [];
    for (fieldName in moduleFields) {
      if (!moduleFields.hasOwnProperty(fieldName)) {
        continue;
      }

      field = moduleFields[fieldName];
      if (field["ismandatory"]) {
        mandatoryFieldOptions.push(
          jQuery("<option></option>").text(field.label).val(fieldName),
        );
      } else {
        visibleFieldOptions.push(
          jQuery("<option></option>").text(field.label).val(fieldName),
        );
      }
    }

    pipelineValueTemplate = jQuery(pipelineValueHtml).attr(
      "data-pipeline-value-id",
      totalPipelineValues,
    );
    pipelineValueTemplate.find(".pipeline-label").val("");
    pipelineValuesSection.append(pipelineValueTemplate);

    dependencyTemplate = jQuery(dependencyTemplateHtml).attr(
      "data-pipeline-value-id",
      totalPipelineValues,
    );
    dependencyTemplate.find(".picklist-value").val("");
    dependencyTemplate.find(".picklist-label").val("");
    dependencyTemplate
      .find(".available-fields > .optional-fields")
      .append(visibleFieldOptions);
    dependencyTemplate
      .find(".available-fields > .mandatory-fields")
      .append(mandatoryFieldOptions);
    dependenciesSection.append(dependencyTemplate);

    totalPipelineValues -= 1;
    updatePipeLineOrder(pipelineValuesSection);
  };

  var deleteModuleReferenceFilter = function (buttonElement) {
    var button = jQuery(buttonElement),
      filtersSection = modal.find("#reference-filters");

    if (!confirm("Vas a eliminar el filtro seleccionado. ¿Estás seguro?")) {
      return;
    }

    button.closest(".filter").remove();
    filtersSection.find(".operator:last").prop("disabled", true).hide();
  };

  var deleteModuleReferenceRelationship = function (buttonElement) {
    var button = jQuery(buttonElement);

    if (!confirm("Vas a eliminar la relación seleccionada. ¿Estás seguro?")) {
      return;
    }

    button.closest(".relationship").remove();
  };

  var deletePicklistValue = function (buttonElement) {
    var button = jQuery(buttonElement),
      picklistValueId = button
        .closest(".picklist-value")
        .attr("data-picklist-value-id");

    if (!confirm("Vas a eliminar el valor seleccionado. ¿Estás seguro?")) {
      return;
    }

    modal
      .find('.dependency[data-picklist-value-id="' + picklistValueId + '"]')
      .remove();
    button.closest(".picklist-value").remove();
  };

  var deletePipelineValue = function (buttonElement) {
    var button = jQuery(buttonElement),
      table = jQuery(button).parent().parent().parent(),
      pipelineValueId = button
        .closest(".pipeline-value")
        .attr("data-pipeline-value-id");

    if (!confirm("Vas a eliminar el valor seleccionado. ¿Estás seguro?")) {
      return;
    }

    modal
      .find('.dependency[data-pipeline-value-id="' + pipelineValueId + '"]')
      .remove();
    button.closest(".pipeline-value").remove();
    updatePipeLineOrder(table);
  };

  var deleteRelationship = function (buttomElement) {
    var button = jQuery(buttomElement),
      modal = jQuery("#field-properties-modal"),
      daughterPicklist = modal.find('select[name="daughterpicklist"]'),
      modalHeader = modal.find(".modal-header"),
      moduleName = modal.find('input[name="modulename"]').val(),
      picklistLabel = modal.find("#picklistMotherLabel").val(),
      relationName = modal.find("#relationship-name"),
      relationshipOptions = modal.find("#relationship-tbody");

    if (relationName.val() === "") {
      alert(
        "¡El campo lista: " +
          picklistLabel +
          " no tiene relación con otro campo lista!",
      );
    } else {
      button.attr("disabled", true);
      if (
        confirm(
          "¿Eliminar la relación del campos lista: " + picklistLabel + " ?",
        )
      ) {
        modalHeader.append(
          '<img id="properties-loading" src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 25%"/>',
        );
        var loading = modal.find("#properties-loading");
        arguments = {
          module: "Settings",
          action: "DeleteRelationshipPicklist",
          Ajax: "true",
          formodule: moduleName,
          relationname: relationName.val(),
        };

        jQuery.post("index.php", arguments, function (data) {
          try {
            var message = JSON.parse(JSON.stringify(data));
            if (message.error !== "OK") {
              throw message.error;
            } else {
              alert("La relación entre campos ha sido eliminada con éxito");
              loading.remove();
              relationshipOptions.find(".tr-picklist-relationship").remove();
              daughterPicklist.val("");
              relationName.val("");
              button.attr("disabled", false);
            }
          } catch (e) {
            alert(e);
            loading.remove();
            button.attr("disabled", false);
          }
        });
      } else {
        button.attr("disabled", false);
      }
    }
  };

  var deletePipelineRelationship = function (buttomElement) {
    var button = jQuery(buttomElement),
      modal = jQuery("#field-properties-modal"),
      pipelineField = modal.find('select[name="pipelinefield"]'),
      modalHeader = modal.find(".modal-header"),
      moduleName = modal.find('input[name="modulename"]').val(),
      picklistFieldName = modal.find('input[name="fieldname"]').val(),
      picklistLabel = modal.find("#field-name").text(),
      relationName = modal.find("#pipeline-relationship-name"),
      relationshipOptions = modal.find("#pipeline-relationship-tbody");

    // Obtener el nombre del campo pipeline del select o de la relación existente
    var pipelineFieldName = "";
    if (pipelineField.val()) {
      pipelineFieldName = pipelineField.val();
    } else {
      // Si el select está vacío, buscar en la tabla de relaciones existentes
      var pipelineNameElement = relationshipOptions.find("td").eq(2).find("select").first();
      if (pipelineNameElement.length > 0 && pipelineNameElement.val()) {
        // El pipeline está en el valor de la opción seleccionada
        var selectedOption = pipelineNameElement.find("option:selected");
        if (selectedOption.length > 0) {
          pipelineFieldName = selectedOption.val();
        }
      }
    }

    if (!pipelineFieldName) {
      alert(
        "¡El campo lista: " +
          picklistLabel +
          " no tiene relación con ningún campo Pipeline!",
      );
      return;
    }

    if (!picklistFieldName) {
      alert(
        "¡No se pudo obtener el nombre del campo Picklist!",
      );
      return;
    }

    button.attr("disabled", true);
    if (
      confirm(
        "¿Eliminar la relación Picklist → Pipeline del campo lista: " +
          picklistLabel +
          " ?",
      )
    ) {
      modalHeader.append(
        '<img id="properties-loading" src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 25%"/>',
      );
      var loading = modal.find("#properties-loading");
      var requestParams = {
        module: "Settings",
        action: "DeletePipelineRelationship",
        Ajax: "true",
        formodule: moduleName,
        picklistfieldname: picklistFieldName,
        pipelinefieldname: pipelineFieldName,
      };

      jQuery.post("index.php", requestParams, function (data) {
        try {
          var message = JSON.parse(JSON.stringify(data));
          if (message.error !== "OK") {
            throw message.error;
          } else {
            alert(
              "La relación Picklist → Pipeline ha sido eliminada con éxito",
            );
            loading.remove();
            relationshipOptions
              .find(".tr-picklist-pipeline-relationship")
              .remove();
            pipelineField.val("");
            relationName.val("");
            button.attr("disabled", false);
          }
        } catch (e) {
          alert(e);
          loading.remove();
          button.attr("disabled", false);
        }
      });
    } else {
      button.attr("disabled", false);
    }
  };

  var editFieldProperties = function (moduleName, fieldName) {
    sourceModuleName = moduleName;
    jQuery
      .ajax(
        "index.php?module=Settings&action=SettingsAjax&file=GetFieldProperties&ajax=true&modulename=" +
          encodeURIComponent(moduleName) +
          "&fieldname=" +
          encodeURIComponent(fieldName),
        {
          dataType: "json",
          method: "get",
        },
      )
      .done(onGetPropertiesSuccessHandler)
      .fail(onFailureHandler);
  };

  var hideDependencyFields = function (buttonElement) {
    var button = jQuery(buttonElement),
      dependency = button.closest(".dependency"),
      availableFields = dependency.find(".available-fields"),
      hiddenFields = dependency.find(".hidden-fields"),
      fields = availableFields.find("option:selected:visible"),
      field,
      i,
      n;

    if (fields.length === 0) {
      return;
    }

    n = fields.length;
    for (i = 0; i < n; i += 1) {
      field = jQuery(fields[i]);
      hiddenFields.append(
        jQuery("<option></option>").text(field.text()).val(field.val()),
      );
      field.removeAttr("selected").hide();
    }
  };

  var hideInPrifile = function (buttonElement) {
    var container = jQuery(buttonElement).closest(".visibility-value"),
      hiddenProfiles = container.find("select").eq(1),
      shownProfiles = container.find("select").eq(0),
      $profiles = shownProfiles.find("option:selected"),
      $profile,
      i,
      n,
      dummy;

    if ($profiles.length === 0) {
      return;
    }

    n = $profiles.length;
    for (i = 0; i < n; i += 1) {
      $profile = jQuery($profiles[i]);
      dummy = $profile.val().split("@");
      hiddenProfiles
        .append(
          jQuery("<option></option>")
            .text($profile.text())
            .val(dummy[0] + "@" + dummy[1] + "@1"),
        )
        .attr("title", $profile[0].title);
      $profile.remove();
    }
  };

  var hiddenOptionDaughter = function (buttonElement) {
    var container = jQuery(buttonElement).closest(".tr-picklist-relationship"),
      hiddenOptions = container.find("select").eq(1),
      shownOptions = container.find("select").eq(0),
      $options = shownOptions.find("option:selected"),
      $option,
      i,
      n;

    n = $options.length;
    if (n === 0) {
      return;
    }

    for (i = 0; i < n; i += 1) {
      $option = jQuery($options[i]);
      hiddenOptions.append(
        jQuery("<option></option>").text($option.text()).val($option.val()),
      );
      $option.remove();
    }
  };

  var hiddenPipelineValue = function (buttonElement) {
    var container = jQuery(buttonElement).closest(
        ".tr-picklist-pipeline-relationship",
      ),
      hiddenOptions = container.find("select").eq(1),
      shownOptions = container.find("select").eq(0),
      $options = shownOptions.find("option:selected"),
      $option,
      i,
      n;

    n = $options.length;
    if (n === 0) {
      return;
    }

    for (i = 0; i < n; i += 1) {
      $option = jQuery($options[i]);
      hiddenOptions.append(
        jQuery("<option></option>").text($option.text()).val($option.val()),
      );
      $option.remove();
    }
  };

  var hidePicklistValues = function (buttonElement) {
    var button = jQuery(buttonElement),
      picklistValueRow = button.closest(".picklist-value"),
      visibleRoles = picklistValueRow.find(".visible-roles"),
      hiddenRoles = picklistValueRow.find(".hidden-roles"),
      roles = visibleRoles.find("option:selected"),
      role,
      i,
      n;

    if (roles.length === 0) {
      return;
    }

    n = roles.length;
    for (i = 0; i < n; i += 1) {
      role = jQuery(roles[i]);
      hiddenRoles.append(
        jQuery("<option></option>").text(role.text()).val(role.val()),
      );
      role.removeAttr("selected").hide();
    }
  };

  var movePickListRowDown = function (btn) {
    var rowToMove = jQuery(btn).parent().parents("tr.picklist-value"),
      idTo = rowToMove.find("input").eq(0),
      next = rowToMove.next("tr.picklist-value"),
      idNext = next.find("input").eq(0),
      tempValue = idNext.val();

    idNext.val(idTo.val());
    idTo.val(tempValue);
    next.after(rowToMove);
  };

  var movePipeLineRowDown = function (btn) {
    var rowToMove = jQuery(btn).parent().parents("tr.pipeline-value"),
      table = jQuery(btn).parent().parent().parent(),
      next = rowToMove.next("tr.pipeline-value");
    next.after(rowToMove);
    updatePipeLineOrder(table);
  };

  var movePickListRowUp = function (btn) {
    var rowToMove = jQuery(btn).parent().parents("tr.picklist-value"),
      idTo = rowToMove.find("input").eq(0),
      prev = rowToMove.prev("tr.picklist-value"),
      idPrev = prev.find("input").eq(0),
      tempValue = idPrev.val();

    idPrev.val(idTo.val());
    idTo.val(tempValue);
    prev.before(rowToMove);
  };
  var movePipeLineRowUp = function (btn) {
    var rowToMove = jQuery(btn).parent().parents("tr.pipeline-value"),
      table = jQuery(btn).parent().parent().parent();
    prev = rowToMove.prev("tr.pipeline-value");
    prev.before(rowToMove);
    updatePipeLineOrder(table);
  };

  var removeHiddenDependencyFields = function (buttonElement) {
    var button = jQuery(buttonElement),
      dependency = button.closest(".dependency"),
      availableFields = dependency.find(".available-fields"),
      hiddenFields = dependency.find(".hidden-fields"),
      fields = hiddenFields.find("option:selected"),
      field,
      i,
      n;

    if (fields.length === 0) {
      return;
    }

    n = fields.length;
    for (i = 0; i < n; i += 1) {
      field = jQuery(fields[i]);
      availableFields.find('option[value="' + field.val() + '"]').show();
      field.remove();
    }
  };

  var removeVisibleDependencyFields = function (buttonElement) {
    var button = jQuery(buttonElement),
      dependency = button.closest(".dependency"),
      availableFields = dependency.find(".available-fields"),
      visibleFields = dependency.find(".visible-fields"),
      fields = visibleFields.find("option:selected"),
      field,
      i,
      n;

    if (fields.length === 0) {
      return;
    }

    n = fields.length;
    for (i = 0; i < n; i += 1) {
      field = jQuery(fields[i]);
      availableFields.find('option[value="' + field.val() + '"]').show();
      field.remove();
    }
  };

  var saveProperties = function (obj) {
    var button = jQuery(obj),
      modal = jQuery("#field-properties-modal"),
      modalHeader = modal.find(".modal-header"),
      fieldName = modal.find('input[name="fieldname"]').val(),
      moduleName = modal.find('input[name="modulename"]').val(),
      calculationId = modal.find('input[name="calculatedSystemId"]').val(),
      basicProperties = modal.find("#basic-properties"),
      validationProperties = modal.find("#validation-properties"),
      moduleReferencesProperties = modal.find("#module-references-properties"),
      moduleReferencesFilters = modal.find("#module-references-filters"),
      dependenciesProperties = modal.find("#dependencies-properties"),
      picklistValuesProperties = modal.find("#picklist-values-properties"),
      pipelineValuesProperties = modal.find("#pipeline-values-properties"),
      visibleProfilesOptions = modal.find("#visibleprofiles option"),
      hiddenProfilesOptions = modal.find("#hiddenprofiles option"),
      visibleProfiles = [],
      hiddenProfiles = [],
      daughterOptions = modal.find(".picklist-daughter-values"),
      picklistRelationshipName = modal.find("#relationship-name"),
      pipelineDaughterOptions = modal.find(".tr-picklist-pipeline-relationship select").eq(0),
      pipelineRelationshipName = modal.find("#pipeline-relationship-name"),
      i,
      j,
      m,
      n,
      data,
      dependencies,
      dependency,
      value,
      hiddenFields,
      visibleFields,
      picklistValueRows,
      picklistValueRow,
      pipelineValueRows,
      pipelineValueRow,
      relationshipRows,
      relationshipRow,
      filterRows,
      filterRow,
      id,
      roles,
      role,
      seq,
      daughterPicklist,
      motherPicklistId = [],
      selectedDaughterOptions = [],
      pipelineDaughterPicklist = [],
      motherPipelinePicklistId = [],
      selectedPipelineDaughterOptions = [];

    button.attr("disabled", true);

    jQuery.each(visibleProfilesOptions, function (i, o) {
      visibleProfiles.push(jQuery(o).val());
    });

    jQuery.each(hiddenProfilesOptions, function (i, o) {
      hiddenProfiles.push(jQuery(o).val());
    });

    jQuery.each(daughterOptions, function (index, object) {
      var relationship,
        options = jQuery(object).find("option"),
        optionsDaughter = [];

      jQuery.each(options, function (i, o) {
        optionsDaughter.push(jQuery(o).val());
      });

      relationship = jQuery(object).closest(".tr-picklist-relationship");
      motherPicklistId.push(
        relationship.find('input[name="motherpicklistid[]"]').val(),
      );
      if (index > 0) {
        selectedDaughterOptions.push(index + ";" + optionsDaughter.join());
      } else {
        selectedDaughterOptions.push(optionsDaughter.join());
      }
    });

    // Process picklist-pipeline relationships
    var pipelineRelationshipRows = modal.find("#pipeline-relationship-tbody .tr-picklist-pipeline-relationship");
    if (pipelineRelationshipRows.length > 0) {
      jQuery.each(pipelineRelationshipRows, function (index, object) {
        var relationship,
          optionsPipeline = [],
          shownPipelineOptions = jQuery(object).find("select").eq(0).find("option");

        jQuery.each(shownPipelineOptions, function (i, o) {
          // Enviar el valor de texto del pipeline en lugar del índice
          optionsPipeline.push(jQuery(o).text());
        });

        relationship = jQuery(object);
        motherPipelinePicklistId.push(
          relationship.find('input[type="hidden"]').eq(0).val(),
        );
        if (index > 0) {
          selectedPipelineDaughterOptions.push(index + ";" + optionsPipeline.join());
        } else {
          selectedPipelineDaughterOptions.push(optionsPipeline.join());
        }
      });
    }

    pipelineDaughterPicklist = modal.find("#available-pipeline").val();

    daughterPicklist = modal.find('select[name="daughterpicklist"]').val();
    if (!validateProperties()) {
      return;
    }

    data = [
      "module=Settings",
      "action=SettingsAjax",
      "file=SaveFieldProperties",
      "Ajax=true",
      "fieldname=" + encodeURIComponent(fieldName),
      "modulename=" + encodeURIComponent(moduleName),
      "calculationid=" + encodeURIComponent(calculationId),
      "hiddenprofiles=" + encodeURIComponent(hiddenProfiles.join()),
      "visibleprofiles=" + encodeURIComponent(visibleProfiles.join()),
      "motherpicklistid=" + encodeURIComponent(motherPicklistId),
      "daughterpicklist=" + encodeURIComponent(daughterPicklist),
      "selecteddaughteroptions=" + encodeURIComponent(selectedDaughterOptions),
      "relationshipname=" + encodeURIComponent(picklistRelationshipName.val()),
      "motherpipelinepicklistid=" + encodeURIComponent(motherPipelinePicklistId),
      "pipelinedaughterpicklist=" + encodeURIComponent(pipelineDaughterPicklist),
      "selectedpipelinedaughteroptions=" + encodeURIComponent(selectedPipelineDaughterOptions),
      "pipelinerelationshipname=" + encodeURIComponent(pipelineRelationshipName.val()),
    ];

    // Propiedades básicas
    if (basicProperties.find("#ismandatory").is(":checked")) {
      data.push("ismandatory=true");
    }
    if (basicProperties.find("#presence").is(":checked")) {
      data.push("presence=" + (presence === 0 ? 0 : 2));
    } else {
      data.push("presence=1");
    }
    data.push(
      "defaultvalue=" +
        encodeURIComponent(basicProperties.find("#default-value").val()),
    );
    if (jQuery.inArray(uiType, ["1", "7", "9", "71"]) !== -1) {
      data.push(
        "length=" +
          encodeURIComponent(basicProperties.find("#field-length").val()),
      );
    }
    if (jQuery.inArray(uiType, ["7", "9", "71"]) !== -1) {
      data.push(
        "precision=" +
          encodeURIComponent(basicProperties.find("#field-precision").val()),
      );
    }

    // Validaciones
    if (validationProperties.find("#unique").is(":checked")) {
      data.push("validationunique=true");
    }

    if (jQuery.inArray(uiType, ["5", "6"]) !== -1) {
      // Validaciones de campos tipo fecha
      data.push(
        "validationdateinitialvalue=" +
          encodeURIComponent(validationProperties.find("#initial-date").val()),
      );
      data.push(
        "validationdatemaximumvalue=" +
          encodeURIComponent(validationProperties.find("#maximum-date").val()),
      );
    } else if (uiType === "7") {
      // Validaciones de campos tipo número
      data.push(
        "validationnumberinitialvalue=" +
          encodeURIComponent(validationProperties.find("#initial-value").val()),
      );
      data.push(
        "validationnumbermaximumvalue=" +
          encodeURIComponent(validationProperties.find("#maximum-value").val()),
      );
    } else if (uiType === "10") {
      // Referencias a módulos
      data.push(
        "modulereference[name]=" +
          encodeURIComponent(
            moduleReferencesProperties.find("#module-reference").val(),
          ),
      );
      relationshipRows = moduleReferencesProperties.find(".relationship");
      if (relationshipRows.length > 0) {
        n = relationshipRows.length;
        for (i = 0; i < n; i += 1) {
          relationshipRow = jQuery(relationshipRows[i]);
          data.push(
            "modulereference[relationships][" +
              encodeURIComponent(
                relationshipRow.find(".referenced-module-fields").val(),
              ) +
              "]=" +
              encodeURIComponent(relationshipRow.find(".module-fields").val()),
          );
        }
      }

      filterRows = moduleReferencesFilters.find(".filter");
      if (filterRows.length > 0) {
        for (i = 0; i < filterRows.length; i += 1) {
          filterRow = jQuery(filterRows[i]);
          data.push(
            "modulereference[filters][" +
              i +
              "][field]=" +
              encodeURIComponent(filterRow.find(".module-fields").val()),
          );
          data.push(
            "modulereference[filters][" +
              i +
              "][comparator]=" +
              encodeURIComponent(filterRow.find(".comparator").val()),
          );
          data.push(
            "modulereference[filters][" +
              i +
              "][valuetype]=" +
              encodeURIComponent(filterRow.find(".filter-type:checked").val()),
          );
          if (filterRow.find(".filter-type:checked").val() === "SOURCE FIELD") {
            data.push(
              "modulereference[filters][" +
                i +
                "][valuemodulename]=" +
                encodeURIComponent(
                  filterRow
                    .find(".filter-fields option:selected")
                    .data("module-name"),
                ),
            );
            data.push(
              "modulereference[filters][" +
                i +
                "][value]=" +
                encodeURIComponent(filterRow.find(".filter-fields").val()),
            );
          } else if (
            filterRow.find(".filter-type:checked").val() === "LITERAL"
          ) {
            data.push(
              "modulereference[filters][" +
                i +
                "][value]=" +
                encodeURIComponent(filterRow.find(".filter-value").val()),
            );
          }
          if (filterRow.find(".operator").prop("disabled") === false) {
            data.push(
              "modulereference[filters][" +
                i +
                "][operator]=" +
                encodeURIComponent(filterRow.find(".operator").val()),
            );
          }
        }
      }
    } else if (jQuery.inArray(uiType, ["15", "16", "33"]) !== -1) {
      // Picklist values
      picklistValueRows = picklistValuesProperties.find(".picklist-value");
      n = picklistValueRows.length;
      for (i = 0; i < n; i += 1) {
        picklistValueRow = jQuery(picklistValueRows[i]);
        id = picklistValueRow.find(".picklist-value-id").val();
        value = picklistValueRow.find(".picklist-label").val();
        roles = picklistValueRow.find("select.visible-roles > option");
        seq = picklistValueRow.find(".picklist-seq").val();
        if (roles.length > 0) {
          m = roles.length;
          for (j = 0; j < m; j += 1) {
            role = jQuery(roles[j]);
            if (role.css("display") !== "none") {
              data.push(
                "picklistvalues[" +
                  id +
                  "][roles][]=" +
                  encodeURIComponent(role.val()),
              );
            }
          }
        }
        data.push(
          "picklistvalues[" + id + "][value]=" + encodeURIComponent(value),
        );
        data.push("picklistvalues[" + id + "][seq]=" + seq);
      }

      // Dependencias
      if (jQuery.inArray(uiType, ["15", "16"]) !== -1) {
        dependencies = dependenciesProperties.find(".dependency");
        n = dependencies.length;
        for (i = 0; i < n; i += 1) {
          dependency = jQuery(dependencies[i]);
          value = dependency.find(".picklist-value").val();
          hiddenFields = dependency.find("select.hidden-fields > option");
          if (hiddenFields.length > 0) {
            m = hiddenFields.length;
            for (j = 0; j < m; j += 1) {
              data.push(
                "hiddenfields[" +
                  (value ? encodeURIComponent(value) : "__EMPTY__") +
                  "][]=" +
                  encodeURIComponent(jQuery(hiddenFields[j]).val()),
              );
            }
          }
          visibleFields = dependency.find("select.visible-fields > option");
          if (visibleFields.length > 0) {
            m = visibleFields.length;
            for (j = 0; j < m; j += 1) {
              data.push(
                "visiblefields[" +
                  (value ? encodeURIComponent(value) : "__EMPTY__") +
                  "][]=" +
                  encodeURIComponent(jQuery(visibleFields[j]).val()),
              );
            }
          }
        }
      }
    } else if (uiType === "8192") {
      // Pipeline values
      pipelineValueRows = pipelineValuesProperties.find(".pipeline-value");
      for (i = 0; i < pipelineValueRows.length; i += 1) {
        pipelineValueRow = jQuery(pipelineValueRows[i]);
        value = pipelineValueRow.find(".pipeline-label").val();
        data.push("pipelinevalues[" + i + "]=" + encodeURIComponent(value));
      }

      // Dependencias
      dependencies = dependenciesProperties.find(".dependency");
      for (i = 0; i < dependencies.length; i += 1) {
        dependency = jQuery(dependencies[i]);
        value = dependency.find(".picklist-value").val();
        hiddenFields = dependency.find("select.hidden-fields > option");
        if (hiddenFields.length > 0) {
          m = hiddenFields.length;
          for (j = 0; j < m; j += 1) {
            data.push(
              "hiddenfields[" +
                (value ? encodeURIComponent(value) : "__EMPTY__") +
                "][]=" +
                encodeURIComponent(jQuery(hiddenFields[j]).val()),
            );
          }
        }
        visibleFields = dependency.find("select.visible-fields > option");
        if (visibleFields.length > 0) {
          m = visibleFields.length;
          for (j = 0; j < m; j += 1) {
            data.push(
              "visiblefields[" +
                (value ? encodeURIComponent(value) : "__EMPTY__") +
                "][]=" +
                encodeURIComponent(jQuery(visibleFields[j]).val()),
            );
          }
        }
      }
    }
    modalHeader.append(
      '<img id="properties-loading" src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"  style="width: 75%;height: 25%"/>',
    );
    jQuery
      .ajax("index.php", {
        data: data.join("&"),
        dataType: "text",
        method: "post",
      })
      .done(onSavePropertiesSuccessHandler)
      .fail(onFailureHandler);
  };

  var searchCalculated = function (obj) {
    var filter = jQuery(obj).val(),
      list = modal.find(".calculated-list");

    if (filter != "") {
      jQuery.expr[":"].Contains = function (a, i, m) {
        return (
          (a.textContent || a.innerText || "")
            .toUpperCase()
            .indexOf(m[3].toUpperCase()) >= 0
        );
      };

      list.find('a:not(:Contains("' + filter + '"))').slideUp();
      list.find('a:Contains("' + filter + '")').slideDown();
    } else {
      list.find("a").slideDown();
    }
    return false;
  };

  var setCalculatedSystem = function (obj) {
    var mySelection = jQuery(obj),
      selectionValues,
      calculatedSystemId = modal.find("#calculatedSystemId");
    mySelection.parent().each(function (index, item) {
      jQuery(item).find("a").removeClass("active");
    });
    mySelection.addClass("active");
    selectionValues = mySelection.attr("rel");
    calculatedSystemId.val(selectionValues);
  };

  var setDateValidationFields = function (selectElement) {
    var select = jQuery(selectElement),
      group = select.closest(".date-validation").find(".custom-date-group"),
      dateChoice = select.val();

    if (dateChoice === "today") {
      group.find("#initial-date").val("today");
      group.hide();
    } else if (dateChoice === "custom") {
      group.find("#initial-date").val("");
      group.show();
    } else {
      group.find("#initial-date").val("");
      group.hide();
    }
  };

  var setPicklistDependencyLabel = function (fieldElement) {
    var field = jQuery(fieldElement),
      picklistValueId = field
        .closest(".picklist-value")
        .attr("data-picklist-value-id"),
      dependency = modal.find(
        '.dependency[data-picklist-value-id="' + picklistValueId + '"]',
      ),
      value = field.val();

    dependency.find(".picklist-value").val(value !== "" ? value : "__EMPTY__");
    dependency.find(".picklist-label").val(value !== "" ? value : "(Vacío)");
  };

  var setPipelineDependencyLabel = function (fieldElement) {
    var field = jQuery(fieldElement),
      pipelineValueId = field
        .closest(".pipeline-value")
        .attr("data-pipeline-value-id"),
      dependency = modal.find(
        '.dependency[data-pipeline-value-id="' + pipelineValueId + '"]',
      ),
      value = field.val();

    dependency.find(".picklist-label").val(value !== "" ? value : "(Vacío)");
  };

  var setFieldVisibility = function (mandatory, moduleName, fieldName) {
    if (mandatory !== "") {
      var infoMandatory = "No se puede ocultar el campo" + "\n";
      infoMandatory += "ya que es un campo obligatorio" + "\n";
      infoMandatory += "Debe editar el campo para cambiar esa condición" + "\n";
      alert(infoMandatory);
      return;
    } else {
      visibilityProperty = true;
      editFieldProperties(moduleName, fieldName);
    }
  };
  var setModuleReferenceFilterType = function (radioElement) {
    var radio = jQuery(radioElement),
      container = radio.closest(".filter-target"),
      type = radio.val();

    if (type === "SOURCE FIELD") {
      container.find(".filter-fields").show();
      container.find(".filter-value").hide();
    } else if (type === "LITERAL") {
      container.find(".filter-value").show();
      container.find(".filter-fields").hide();
    } else {
      container.find(".filter-fields").hide();
      container.find(".filter-value").hide();
    }
  };

  var setModuleReferenceRelationships = function (selectElement) {
    var select = jQuery(selectElement),
      relatedModuleName = select.val(),
      relationshipsSection = select
        .closest(".panel-body")
        .find("#relationships"),
      arguments;

    relationshipsSection.closest(".table-responsive").hide();
    relationshipsSection.find(".relationship").remove();
    if (
      relatedModuleName === null ||
      relatedModuleName === undefined ||
      relatedModuleName.trim() === ""
    ) {
      return;
    }

    arguments = [
      "module=Settings",
      "action=SettingsAjax",
      "file=GetAvailableFieldsData",
      "modulename=" + encodeURIComponent(relatedModuleName),
      "Ajax=true",
    ];
    jQuery
      .ajax("index.php", {
        data: arguments.join("&"),
        dataType: "json",
        method: "get",
      })
      .done(function (response) {
        referencedModuleFields = response;
        relationshipsSection.closest(".table-responsive").show();
      })
      .fail(onFailureHandler);
  };

  var setPicklistDaughter = function (obj) {
    var daughterSelected = jQuery(obj),
      relationshipOptionsSection = modal.find("#relationship-tbody"),
      relationshipOptionsHtml = jQuery(
        "#fields-picklist-relationship-template",
      ).html(),
      option,
      selectedListValues,
      relationshipTemplate,
      motherPicklistId,
      motherPicklistValue,
      shownDaughter,
      value,
      picklistValue;
    if (daughterSelected.val() === "") {
      relationshipOptionsSection.find(".tr-picklist-relationship").remove();
    } else {
      relationshipOptionsSection.find(".tr-picklist-relationship").remove();
      selectedListValues = JSON.parse(
        daughterSelected.find("option:selected").attr("list-data"),
      );
      if (dataPicklistValues !== null) {
        for (value in dataPicklistValues) {
          if (!dataPicklistValues.hasOwnProperty(value)) {
            continue;
          }
          picklistValue = dataPicklistValues[value];
          relationshipTemplate = jQuery(relationshipOptionsHtml);
          motherPicklistId = relationshipTemplate
            .find("input")
            .eq(0)
            .val(picklistValue.value);
          motherPicklistValue = relationshipTemplate
            .find("textarea")
            .eq(0)
            .val(picklistValue.value);
          shownDaughter = relationshipTemplate.find("select").eq(0);
          jQuery.each(selectedListValues, function (key, value) {
            shownDaughter.append(
              jQuery("<option></option>").text(value).val(key),
            );
          });
          relationshipOptionsSection.append(relationshipTemplate);
        }
      }
    }
  };

  var setPipelineDaughter = function (obj) {
    var pipelineSelected = jQuery(obj),
      relationshipOptionsSection = modal.find("#pipeline-relationship-tbody"),
      relationshipOptionsHtml = jQuery(
        "#fields-picklist-pipeline-relationship-template",
      ).html(),
      selectedPipelineValues,
      relationshipTemplate,
      motherPicklistId,
      motherPicklistValue,
      shownPipelineValues,
      value,
      picklistValue;
    if (pipelineSelected.val() === "") {
      relationshipOptionsSection
        .find(".tr-picklist-pipeline-relationship")
        .remove();
    } else {
      relationshipOptionsSection
        .find(".tr-picklist-pipeline-relationship")
        .remove();
      selectedPipelineValues = JSON.parse(
        pipelineSelected.find("option:selected").attr("list-data"),
      );
      if (window.dataPicklistValues !== null) {
        for (value in window.dataPicklistValues) {
          if (!window.dataPicklistValues.hasOwnProperty(value)) {
            continue;
          }
          picklistValue = window.dataPicklistValues[value];
          relationshipTemplate = jQuery(relationshipOptionsHtml);
          motherPicklistId = relationshipTemplate
            .find("input")
            .eq(0)
            .val(picklistValue.value);
          motherPicklistValue = relationshipTemplate
            .find("textarea")
            .eq(0)
            .val(picklistValue.value);
          shownPipelineValues = relationshipTemplate.find("select").eq(0);
          jQuery.each(selectedPipelineValues, function (index, value) {
            shownPipelineValues.append(
              jQuery("<option></option>").text(value).val(index),
            );
          });
          relationshipOptionsSection.append(relationshipTemplate);
        }
      }
    }
  };

  var showDependencyFields = function (buttonElement) {
    var button = jQuery(buttonElement),
      dependency = button.closest(".dependency"),
      availableFields = dependency.find(".available-fields"),
      visibleFields = dependency.find(".visible-fields"),
      fields = availableFields.find("option:selected:visible"),
      field,
      i,
      n;

    if (fields.length === 0) {
      return;
    }

    n = fields.length;
    for (i = 0; i < n; i += 1) {
      field = jQuery(fields[i]);
      visibleFields.append(
        jQuery("<option></option>").text(field.text()).val(field.val()),
      );
      field.removeAttr("selected").hide();
    }
  };

  var showOptionDaughter = function (buttonElement) {
    var container = jQuery(buttonElement).closest(".tr-picklist-relationship"),
      hiddenOptions = container.find("select").eq(1),
      shownOptions = container.find("select").eq(0),
      $options = hiddenOptions.find("option:selected"),
      $option,
      i,
      n;

    n = $options.length;
    if (n === 0) {
      return;
    }

    for (i = 0; i < n; i += 1) {
      $option = jQuery($options[i]);
      shownOptions.append(
        jQuery("<option></option>").text($option.text()).val($option.val()),
      );
      $option.remove();
    }
  };

  var showPipelineValue = function (buttonElement) {
    var container = jQuery(buttonElement).closest(
        ".tr-picklist-pipeline-relationship",
      ),
      hiddenOptions = container.find("select").eq(1),
      shownOptions = container.find("select").eq(0),
      $options = hiddenOptions.find("option:selected"),
      $option,
      i,
      n;

    n = $options.length;
    if (n === 0) {
      return;
    }

    for (i = 0; i < n; i += 1) {
      $option = jQuery($options[i]);
      shownOptions.append(
        jQuery("<option></option>").text($option.text()).val($option.val()),
      );
      $option.remove();
    }
  };

  var showInPrifile = function (buttonElement) {
    var container = jQuery(buttonElement).closest(".visibility-value"),
      hiddenProfiles = container.find("select").eq(1),
      shownProfiles = container.find("select").eq(0),
      $profiles = hiddenProfiles.find("option:selected"),
      $profile,
      i,
      n,
      dummy;

    if ($profiles.length === 0) {
      return;
    }

    n = $profiles.length;
    for (i = 0; i < n; i += 1) {
      $profile = jQuery($profiles[i]);
      dummy = $profile.val().split("@");
      shownProfiles
        .append(
          jQuery("<option></option>")
            .text($profile.text())
            .val(dummy[0] + "@" + dummy[1] + "@0"),
        )
        .attr("title", $profile[0].title);
      $profile.remove();
    }
  };

  var showPicklistValues = function (buttonElement) {
    var button = jQuery(buttonElement),
      picklistValueRow = button.closest(".picklist-value"),
      visibleRoles = picklistValueRow.find(".visible-roles"),
      hiddenRoles = picklistValueRow.find(".hidden-roles"),
      roles = hiddenRoles.find("option:selected"),
      role,
      i,
      n;

    if (roles.length === 0) {
      return;
    }

    n = roles.length;
    for (i = 0; i < n; i += 1) {
      role = jQuery(roles[i]);
      visibleRoles.find('option[value="' + role.val() + '"]').show();
      role.remove();
    }
  };

  var showUnmodifiableReasons = function (reasons) {
    var message, objectType, objectLabel, i;

    if (reasons === null || reasons === undefined) {
      return;
    }

    message = "";
    for (objectType in reasons) {
      if (!reasons.hasOwnProperty(objectType)) {
        continue;
      }

      switch (objectType) {
        case "backgroundtasksfilters":
          objectLabel = "Filtro de la tarea oculta";
          break;
        case "backgroundtasksparameters":
          objectLabel = "Parámetro de la tarea oculta";
          break;
        case "calendarviews":
          objectLabel = "Vista calendario";
          break;
        case "charts":
          objectLabel = "Gráfico";
          break;
        case "calculate_field":
          objectLabel = "Elemento de cálculo";
          break;
        case "calculate_system":
          objectLabel = "Cálculo en el sistema";
          break;
        case "reports":
          objectLabel = "Informe";
          break;
        case "indicators":
          objectLabel = "Indicador de gestión";
          break;
        case "modules":
          objectLabel = "Vinculado al módulo";
          break;
        case "entityname":
          objectLabel = "Identificador del módulo";
          break;
        default:
          objectLabel = "";
          break;
      }

      for (i = 0; i < reasons[objectType].length; i += 1) {
        message += "+ " + objectLabel + ' "' + reasons[objectType][i] + '"\n';
      }
    }

    if (message.trim() !== "") {
      alert(
        "El campo no puede ser eliminado, pues forma parte de:\n\n" + message,
      );
    }
  };

  window.FieldPropertiesUtils = {
    addModuleReferenceFilter: addModuleReferenceFilter,
    addModuleReferenceRelationship: addModuleReferenceRelationship,
    addPicklistValue: addPicklistValue,
    addPipelineValue: addPipelineValue,
    deleteModuleReferenceRelationship: deleteModuleReferenceRelationship,
    deleteModuleReferenceFilter: deleteModuleReferenceFilter,
    deletePicklistValue: deletePicklistValue,
    deletePipelineValue: deletePipelineValue,
    deleteRelationship: deleteRelationship,
    deletePipelineRelationship: deletePipelineRelationship,
    editFieldProperties: editFieldProperties,
    hideDependencyFields: hideDependencyFields,
    hideInPrifile: hideInPrifile,
    hiddenOptionDaughter: hiddenOptionDaughter,
    hiddenPipelineValue: hiddenPipelineValue,
    hidePicklistValues: hidePicklistValues,
    movePickListRowDown: movePickListRowDown,
    movePipeLineRowDown: movePipeLineRowDown,
    movePickListRowUp: movePickListRowUp,
    movePipeLineRowUp: movePipeLineRowUp,
    removeHiddenDependencyFields: removeHiddenDependencyFields,
    removeVisibleDependencyFields: removeVisibleDependencyFields,
    saveProperties: saveProperties,
    searchCalculated: searchCalculated,
    setCalculatedSystem: setCalculatedSystem,
    setDateValidationFields: setDateValidationFields,
    setModuleReferenceFilterType: setModuleReferenceFilterType,
    setModuleReferenceRelationships: setModuleReferenceRelationships,
    setPicklistDaughter: setPicklistDaughter,
    setPipelineDaughter: setPipelineDaughter,
    setPicklistDependencyLabel: setPicklistDependencyLabel,
    setPipelineDependencyLabel: setPipelineDependencyLabel,
    setFieldVisibility: setFieldVisibility,
    showDependencyFields: showDependencyFields,
    showInPrifile: showInPrifile,
    showOptionDaughter: showOptionDaughter,
    showPipelineValue: showPipelineValue,
    showPicklistValues: showPicklistValues,
    showUnmodifiableReasons: showUnmodifiableReasons,
  };
})(jQuery);
