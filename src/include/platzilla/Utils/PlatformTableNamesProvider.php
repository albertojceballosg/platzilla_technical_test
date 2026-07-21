<?php

	abstract class PlatformTableNamesProvider {

		public static function getApplicationTableNames () {
			return array (
				'vtiger_config_applications',
				'vtiger_configapps_tab',
			);
		}

		public static function getBaseTableNames () {
			return array (
				'vtiger_org_share_action2tab',
				'vtiger_org_share_action_mapping',
				'vtiger_audit_trial',
				'vtiger_datashare_grp2grp',
				'vtiger_datashare_grp2role',
				'vtiger_datashare_grp2rs',
				'vtiger_datashare_module_rel',
				'vtiger_datashare_relatedmodules',
				'vtiger_datashare_relatedmodules_seq',
				'vtiger_datashare_relatedmodule_permission',
				'vtiger_datashare_role2group',
				'vtiger_datashare_role2role',
				'vtiger_datashare_role2rs',
				'vtiger_datashare_rs2grp',
				'vtiger_datashare_rs2role',
				'vtiger_datashare_rs2rs',
				'vtiger_def_org_field',
				'vtiger_def_org_share',
				'vtiger_def_org_share_seq',

				'vtiger_files',
				'vtiger_ownernotify',

				'vtiger_tmp_read_group_rel_sharing_per',
				'vtiger_tmp_read_group_sharing_per',
				'vtiger_tmp_read_user_rel_sharing_per',
				'vtiger_tmp_read_user_sharing_per',
				'vtiger_tmp_write_group_rel_sharing_per',
				'vtiger_tmp_write_group_sharing_per',
				'vtiger_tmp_write_user_rel_sharing_per',
				'vtiger_tmp_write_user_sharing_per',
				'vtiger_tracker',
				'vtiger_user_module_preferences',
				'vtiger_user2mergefields',
				'vtiger_variables',
				'vtiger_variables_instancias',

				'vtiger_calendarviews',
				'vtiger_calendarviews_rules',
				'vtiger_calendarviews_applications',

				'vtiger_calculated_equation',
				'vtiger_calculated_fields',
				'vtiger_calculated_system',

				'vtiger_boxscore',
				'vtiger_boxscorecf',
				'vtiger_boxscore_blocks',
				'vtiger_boxscore_operacion',
				'vtiger_boxscore_operation',
				'vtiger_boxscore_privileges',
				'vtiger_boxsoperation_privileges',
				'vtiger_box_score_data',
				'vtiger_box_score_data_cump',
				'vtiger_box_score_data_semanal',
				'vtiger_box_score_data_weekly',
				'vtiger_box_score_objective',
				'vtiger_user2boxscore',
				
				// System Alerts
				'vtiger_systemalerts',
				'vtiger_systemalerts_occurrences',
				'vtiger_systemalerts_users',
				'vtiger_systemalerts_filtergroups',
				'vtiger_systemalerts_filters',

				// Background Tasks
				'vtiger_bgtasks_data',
				'vtiger_bgtasks_data_actions',
				'vtiger_bgtasks_data_parameters',
				'vtiger_bgtasks_data_filtergroups',
				'vtiger_bgtasks_data_filters',

				// Gráficos
				'vtiger_graficos',
				//Editable Fields Button

				'vtiger_editablefields_buttons',
				'vtiger_editablefields_fields',
			);
		}

		public static function getCatalogTableNames () {
			return array (
				'vtiger_actionmapping',
				'vtiger_categoriaot',
				'vtiger_currencies',
				'vtiger_currencies_seq',
				'vtiger_emailtemplates',
				'vtiger_emailtemplates_seq',
				'vtiger_eventhandlers',
				'vtiger_eventhandlers_seq',
				'vtiger_eventhandler_module',
				'vtiger_eventhandler_module_seq',
				'vtiger_language',
				'vtiger_language_seq',
				'vtiger_paises',
				'vtiger_version',
				'vtiger_version_seq',
				'vtiger_ws_entity',
				'vtiger_ws_entity_fieldtype',
				'vtiger_ws_entity_fieldtype_seq',
				'vtiger_ws_entity_name',
				'vtiger_ws_entity_referencetype',
				'vtiger_ws_entity_seq',
				'vtiger_ws_entity_tables',
				'vtiger_ws_fieldinfo',
				'vtiger_ws_fieldtype',
				'vtiger_ws_operation',
				'vtiger_ws_operation_parameters',
				'vtiger_ws_operation_seq',
				'vtiger_ws_referencetype',
				'vtiger_ws_userauthtoken',
				'vtiger_category_apps',

				// Users and Calendar tables
				'vtiger_activity_view',
				'vtiger_currency_decimal_separator',
				'vtiger_currency_grouping_pattern',
				'vtiger_currency_grouping_separator',
				'vtiger_currency_symbol_placement',
				'vtiger_date_format',
				'vtiger_duration_minutes',
				'vtiger_recurringtype',
				'vtiger_reminder_interval',
				'vtiger_time_zone',
				'vtiger_visibility',

				// Background Tasks
				'vtiger_bgtasks_cfg_categories',
				'vtiger_bgtasks_cfg_events',
				'vtiger_bgtasks_cfg_actions',
				'vtiger_bgtasks_cfg_parameters',
				'vtiger_bgtasks_cfg_parameteroptions',

				// Global picklists
				'vtiger_globalpicklists',
				'vtiger_globalpicklists_values',
				// Global listview
				'vtiger_default_listview',
				'vtiger_how_use',
				'vtiger_how_use_views',
				//App field
				'vtiger_application_fields',
			);
		}

		public static function getCoreTableNames () {
			return array (
				'vtiger_crmentity',
				'vtiger_crmentityrel',
				'vtiger_crmentityutils',
				'vtiger_crmentity_comments',
				'vtiger_tab',
				'vtiger_tab_info',
				'vtiger_blocks',
				'vtiger_blocks_properties',
				'vtiger_custombuttons',
				'vtiger_customview',
				'vtiger_cvadvfilter',
				'vtiger_cvadvfilter_grouping',
				'vtiger_cvcolumnlist',
				'vtiger_cvstdfilter',
				'vtiger_customaction',
				'vtiger_cvadvcolor',
				'vtiger_cvadvcolor_grouping',
				'vtiger_entityname',
				'vtiger_field',
				'vtiger_fieldformulas',
				'vtiger_fieldmodulerel',
				'vtiger_fieldmodulerel_relationships',
				'vtiger_fieldmodulerel_filters',
				'vtiger_fielddependencies',
				'vtiger_field_validation',
				'vtiger_links',
				'vtiger_modentity_num',
				'vtiger_module_report',
				'vtiger_organizationdetails',
				'vtiger_organizationdetails_seq',
				'vtiger_parenttab',
				'vtiger_parenttabrel',
				'vtiger_picklist',
				'vtiger_picklist_dependency',
				'vtiger_relatedlists',
				'vtiger_relatedlists_properties',
				'vtiger_relatedlists_rb',
				'vtiger_relatedlists_field',
				'vtiger_role2picklist',
				'vtiger_soapservice',
				'vtiger_subfields_special',
				'vtiger_subfields_values',
				'vtiger_deletedelements',
				'vtiger_import_maps',
				'vtiger_table_field',

				// Pipelines
				'vtiger_pipelines',
			);
		}

		public static function getProfileTableNames () {
			return array (
				'vtiger_profile',
				'vtiger_profile2field',
				'vtiger_profile2folders',
				'vtiger_profile2globalpermissions',
				'vtiger_profile2standardpermissions',
				'vtiger_profile2tab',
				'vtiger_profile2utility',
				'vtiger_profile2customview',
				'vtiger_role2profile',
			);
		}

		public static function getSecurityTableNames () {
			return array (
				'vtiger_role',
				'vtiger_role_seq',
				'vtiger_groups',
				'vtiger_group2role',
				'vtiger_group2grouprel',
				'vtiger_group2rs',
				'vtiger_systems',
				'vtiger_users',
				'vtiger_users2group',
				'vtiger_users_last_import',
				'vtiger_user2role',
				'vtiger_users_seq',
				'vtiger_loginhistory',
			);
		}

		public static function getSequenceTableNames () {
			return array (
				'vtiger_activity_categories_seq',
				'vtiger_blocks_seq',
				'vtiger_crmentity_seq',
				'vtiger_customview_seq',
				'vtiger_field_seq',
				'vtiger_links_seq',
				'vtiger_modentity_num_seq',
				'vtiger_picklist_seq',
				'vtiger_picklistvalues_seq',
				'vtiger_profile_seq',
				'vtiger_relatedlists_seq',
				'vtiger_seactivityrel_seq',
				'vtiger_settings_blocks_seq',
				'vtiger_settings_field_seq',
				'vtiger_systems_seq',
				'vtiger_selectquery_seq',
			);
		}

		public static function getSettingsTableNames () {
			return array (
				'vtiger_settings_blocks',
				'vtiger_settings_field',
				'vtiger_currency_info',
				'vtiger_currency_info_seq',
			);
		}

		public static function getSpecialModuleTableNames () {
			return array (
				// Documents
				'vtiger_notes',
				'vtiger_notescf',
				'vtiger_senotesrel',
				'vtiger_crmentitynotesrel',
				'vtiger_attachments',
				'vtiger_attachmentsfolder',
				'vtiger_attachmentsfolder_seq',
				'vtiger_seattachmentsrel',
				'vtiger_salesmanattachmentsrel',

				// Calendar
				'vtiger_activity',
				'vtiger_activitycf',
				'vtiger_activity_reminder',
				'vtiger_activity_reminder_popup',
				'vtiger_activity_categories',
				'vtiger_invitees',
				'vtiger_recurringevents',
				'vtiger_priority',
				'vtiger_seactivityrel',
				'vtiger_sharedcalendar',
				'vtiger_salesmanactivityrel',
				'vtiger_activity_feedback',
				'vtiger_activity_report2feedback',

				// ModTracker
				'vtiger_modtracker_tabs',

				// notifications
				'vtiger_notifications',
				'vtiger_notifications_filters',
				'vtiger_notifications_disabled',
				'vtiger_notifications_modal',

				// reportmanager
				'vtiger_report_template',
				'vtiger_report2module',
				'vtiger_reportsharing',

				// admin_widgets
				'vtiger_widgets',

				// Reports
				'vtiger_selectquery',
				'vtiger_selectcolumn',
				'vtiger_relcriteria',
				'vtiger_relcriteria_grouping',
				'vtiger_report',
				'vtiger_reportdatefilter',
				'vtiger_reportfilters',
				'vtiger_reportfolder',
				'vtiger_reportgroupbycolumn',
				'vtiger_reportmodules',
				'vtiger_reportsortcol',
				'vtiger_reportsummary',
				'vtiger_scheduled_reports',

				// Formación
				'vtiger_formacion_preguntas_respuestas',
				'vtiger_courses_seen',
				'vtiger_lessons_seen',

				// Conversaciones
				'vtiger_parley',
				'vtiger_parley2users',
				'vtiger_parley_history',

				// Kanban
				'vtiger_kanbanviews',
				'vtiger_kanbanfield_config',
				'vtiger_kanbanfield_card_config',
				'vtiger_user_kanbanview_preferences',
				'vtiger_kvadvfilter',
				'vtiger_kvadvfilter_grouping',
				'vtiger_kvstdfilter',

				// Permisologías read only
				'vtiger_module_editpermissions_filtergroups',
				'vtiger_module_editpermissions_filters',

				// Instances Data sharing
				'vtiger_instancesdatasharing_rules',
				'vtiger_instancesdatasharing_rulesdetails',
				'vtiger_instancesdatasharing_requests',
				'vtiger_instancesdatasharing_syncs',
				'vtiger_relationsship_plat',
				'vtiger_relationsship_plat_fields',
				'vtiger_relationsship_plat_modules',

				// Webmail
				'vtiger_emailsreceived',
				'vtiger_emailssent',
				'vtiger_webmail_providers',
				'vtiger_webmail_accounts',
				'vtiger_webmail_accountshistory',
				'vtiger_oauth2_providers',
				'vtiger_oauth2_resources',

				// News
				'vtiger_news',
				'vtiger_news_sharing',

				//Grid field
				'vtiger_grid_related_grid',

				// Graphics
				'vtiger_user2graphics',

				//instances tabs
				'vtiger_disabled_tab',

				//Picklist Relationship
				'vtiger_picklist2picklist',
				'vtiger_master_picklist_relationship',

				// GridView
				'vtiger_grid_view',
				'vtiger_grid_view_box',
				'vtiger_activity_report',

				// custom view
				'vtiger_customview_master_group',

				// Documents download
				'vtiger_files_donwload',
				
				//Questionaire
				'vtiger_question',
				'vtiger_question_group',
				'vtiger_question2answeres',
				'vtiger_question2image',
				'vtiger_questionannaire_stages',
			);
		}

	}
