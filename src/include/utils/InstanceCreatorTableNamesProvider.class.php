<?php

	abstract class InstanceCreatorTableNamesProvider {

		public static function getBaseTableNames () {
			return array (
				'vtiger_org_share_action2tab',
				'vtiger_org_share_action_mapping',
				'vtiger_audit_modduplication',
				'vtiger_audit_trial',
				'vtiger_datashare_grp2grp',
				'vtiger_datashare_grp2role',
				'vtiger_datashare_grp2rs',
				'vtiger_datashare_module_rel',
				'vtiger_datashare_module_rel_seq',
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
				'vtiger_inventoryproductrel',
				'vtiger_inventoryshippingrel',
				'vtiger_inventorysubproductrel',
				'vtiger_lar',
				'vtiger_ownernotify',
				'vtiger_products',
				'vtiger_producttaxrel',
				'vtiger_productcf',
				'vtiger_productcurrencyrel',

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
				'vtiger_variables', 'vtiger_variables_instancias',

				'vtiger_seproductsrel',
				'vtiger_config_applications',
				'vtiger_configapps_tab',

				'vtiger_calendarviews',
				'vtiger_calendarviews_rules',
			);
		}

		public static function getCatalogTableNames () {
			return array (
				'vtiger_actionmapping',
				'vtiger_categoriaot',
				'vtiger_cron_task',
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
			);
		}

		public static function getCoreTableNames () {
			return array (
				'vtiger_crmentity',
				'vtiger_crmentityrel',
				'vtiger_tab',
				'vtiger_tab_info',
				'vtiger_blocks',
				'vtiger_blocks_properties',
				'vtiger_custombuttons',
				'vtiger_customview',
				'vtiger_cvadvfilter',
				'vtiger_cvadvfilter_grouping',
				'vtiger_cvadvcolor',
				'vtiger_cvadvcolor_grouping',
				'vtiger_cvcolumnlist',
				'vtiger_cvstdfilter',
				'vtiger_customaction',

				'vtiger_entityname',
				'vtiger_field',
				'vtiger_fieldformulas',
				'vtiger_fieldmodulerel',
				'vtiger_field_dependency',
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
				'vtiger_picklist_seq',
				'vtiger_relatedlists',
				'vtiger_relatedlists_properties',
				'vtiger_relatedlists_rb',
				'vtiger_role2picklist',
				'vtiger_soapservice',
				'vtiger_subfields',
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
			);
		}

		public static function getSecurityTableNames () {
			return array (
				'vtiger_role',
				'vtiger_role2profile',
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
			);
		}

		public static function getSequenceTableNames () {
			return array (
				'vtiger_audit_trial_seq',
				'vtiger_blocks_seq',
				'vtiger_crmentity_seq',
				'vtiger_customview_seq',
				'vtiger_field_seq',
				'vtiger_links_seq',
				'vtiger_modentity_num_seq',
				'vtiger_picklistvalues_seq',
				'vtiger_profile_seq',
				'vtiger_relatedlists_seq',
				'vtiger_seactivityrel_seq',
				'vtiger_settings_blocks_seq',
				'vtiger_settings_field_seq',
				'vtiger_systems_seq',
			);
		}

		public static function getSettingsTableNames () {
			return array (
				'vtiger_settings_blocks',
				'vtiger_settings_field',
				'vtiger_currency_info',
				'vtiger_currency_info_seq',
				'vtiger_inventorytaxinfo',
				'vtiger_inventorytaxinfo_seq',
				'vtiger_shippingtaxinfo',
				'vtiger_shippingtaxinfo_seq',
			);
		}

	}