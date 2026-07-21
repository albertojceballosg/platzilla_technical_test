<?php
	
	interface ModelActionPlanInterface {
		
		const ACTION_PLAN_MODULE   = 'action_plan';
		const CAMPAIGN_MODULE      = 'campaign_marketing';
		const CAMPAIGN_MODULE_TASK = 'task_campaign';
		const CRMID_ACTION_PLAN    = 'business_destinationtfid';
		const DESTINATION_MODULE   = 'business_destination';
		const ID_ACTION_PLAN       = 'destination_action_planid';
		const INITIATIVES_MODULE   = 'business_initiatives';
		const INSTANCE_STATUS      = 'verified';
		const PROJECT_MODULE       = 'proyectos';
		const PROJECT_TABLE_FIELD  = 'task_project';
		const PROJECT_STEPS_MODULE = 'etapas_proyecto';
		const WORK_ORDER_MODULE    = 'orden_de_trabajo';
		const WORK_TABLE_FIELD     = 'task_work';
		
		/**
		 * Available Table Field
		 *  modules business_destination, action_plan
		 *
		 */
		const PLAN_DESTINATION   = 'plan_destination';
		
		const PLAN_DIRECTIVES    = 'plan_directives';
		
		const PLAN_INITIATIVES   = 'plan_initiatives';
		
	}
