<?php
	require_once ('data/CRMEntity.php');
	require_once ('modules/model_action_plan/Objects/ModelActionPlanInterface.php');
	
	class ModuleColumnFields implements ModelActionPlanInterface {
		
		/** @var array */
		private $businessDestination;
		
		/** @var array */
		private $businessInitiatives;
		
		/** @var array */
		private $campaignMarketing;
		
		/** @var string */
		private $createDate;
		
		/** @var array */
		private $projects;
		
		/** @var array */
		private $stageProjects;
		
		/** @var array */
		private $workOrder;
		
		/**
		 * ModuleColumnFields constructor.
		 */
		public function __construct () {
			$this->createDate = date ('Y-m-d h:i:s');
		}
		
		/**
		 * @return array
		 */
		public function getBusinessDestination ()  {
			return $this->businessDestination;
		}
		
		/**
		 * @return array
		 */
		public function getBusinessInitiatives () {
			return $this->businessInitiatives;
		}
		
		/**
		 * @return array
		 */
		public function getCampaignMarketing () {
			return $this->campaignMarketing;
		}
		
		/**
		 * @return string
		 */
		public function getCreateDate () {
			return $this->createDate;
		}
		
		/**
		 * @return array
		 */
		public function getProjects () {
			return $this->projects;
		}
		
		/**
		 * @return array
		 */
		public function getStageProjects () {
			return $this->stageProjects;
		}
		
		/**
		 * @return array
		 */
		public function getWorkOrder () {
			return $this->workOrder;
		}
		
		/**
		 * @param array $businessDestination
		 * @param CRMEntity $entity
		 *
		 * @throws Exception
		 */
		public function setBusinessDestination ($businessDestination, $entity) {
			if (!empty($businessDestination)) {
				$this->businessDestination                               = $businessDestination;
				$this->businessDestination ['assigned_user_id']           = 1;
				$this->businessDestination ['cod_business_destination']  = $entity->setModuleSeqNumber ('increment', self::DESTINATION_MODULE);
				$this->businessDestination ['createdtime']               = $this->createDate;
				$this->businessDestination ['modifiedtime']              = $this->createDate;
				$this->businessDestination ['record_id']                 = null;
			} else {
				$this->businessDestination = array ();
			}
			
		}
		
		/**
		 * @param $businessInitiatives
		 * @param CRMEntity $entity
		 *
		 * @throws Exception
		 */
		public function setBusinessInitiatives ($businessInitiatives, $entity) {
			if (!empty ($businessInitiatives)) {
				$this->businessInitiatives                              = $businessInitiatives;
				$this->businessInitiatives ['assigned_user_id']         = 1;
				$this->businessInitiatives ['cod_business_initiatives'] = $entity->setModuleSeqNumber ('increment', self::INITIATIVES_MODULE);
				$this->businessInitiatives ['completion_status']        = null;
				$this->businessInitiatives ['createdtime']              = $this->createDate;
				$this->businessInitiatives ['end_date']                 = null;
				$this->businessInitiatives ['forum_link']               = null;
				$this->businessInitiatives ['init_date']                = date ('Y-m-d');
				$this->businessInitiatives ['initiative_note']          = null;
				$this->businessInitiatives ['initiative_source']        = 'Un Plan de Acción';
				$this->businessInitiatives ['initiative_status']        = 'Creada';
				$this->businessInitiatives ['kr_initiative']            = null;
				$this->businessInitiatives ['modifiedtime']             = $this->createDate;
				$this->businessInitiatives ['progress_initiative']      = 0;
				$this->businessInitiatives ['record_id']                = null;
				$this->businessInitiatives ['specify_others']           = null;
			} else {
				$this->businessInitiatives = array();
			}
		}
		
		/**
		 * @param array $campaignMarketing
		 * @param CRMEntity $entity
		 *
		 * @throws Exception
		 */
		public function setCampaignMarketing ($campaignMarketing, $entity) {
			if (!empty($campaignMarketing)) {
				$this->campaignMarketing                                  = $campaignMarketing;
				$this->campaignMarketing ['achieved_roi']                 = 0;
				$this->campaignMarketing ['assigned_user_id']             = 1;
				$this->campaignMarketing ['briefing_campaign']            = null;
				$this->campaignMarketing ['campaign_internal_client']     = null;
				$this->campaignMarketing ['campaign_origin']              = 'Una Iniciativa';
				$this->campaignMarketing ['cod_campaign_marketing']       = $entity->setModuleSeqNumber ('increment', self::CAMPAIGN_MODULE);
				$this->campaignMarketing ['contrat_campaign']             = null;
				$this->campaignMarketing ['cost_executed']                = 0;
				$this->campaignMarketing ['cost_ratio_campaign']          = 0;
				$this->campaignMarketing ['createdtime']                  = $this->createDate;
				$this->campaignMarketing ['duration_campaign']            = null;
				$this->campaignMarketing ['effort_executed']              = 0;
				$this->campaignMarketing ['effort_ratio_campaign']        = 0;
				$this->campaignMarketing ['end_date_campaign']            = null;
				$this->campaignMarketing ['estimated_effort']             = 0;
				$this->campaignMarketing ['estimated_start_date']         = date ('Y-m-d');
				$this->campaignMarketing ['estimated_total_cost']         = 0;
				$this->campaignMarketing ['event_program_campaign']       = null;
				$this->campaignMarketing ['expected_income']              = 0;
				$this->campaignMarketing ['expected_roi']                 = 0;
				$this->campaignMarketing ['followup_comments']            = null;
				$this->campaignMarketing ['initiative']                   = null;
				$this->campaignMarketing ['manag_opportunities_campaign'] = null;
				$this->campaignMarketing ['matter_campaign']              = null;
				$this->campaignMarketing ['max_num_participants']         = 0;
				$this->campaignMarketing ['measuring_instrument']         = null;
				$this->campaignMarketing ['modifiedtime']                 = $this->createDate;
				$this->campaignMarketing ['oportunity_campaign']          = null;
				$this->campaignMarketing ['order_campaign']               = null;
				$this->campaignMarketing ['overall_progress_perc']        = 0;
				$this->campaignMarketing ['product_campaign']             = null;
				$this->campaignMarketing ['prospect_manag_campaign']      = null;
				$this->campaignMarketing ['real_income']                  = 0;
				$this->campaignMarketing ['reason_cancellation']          = null;
				$this->campaignMarketing ['reason_suspension']            = null;
				$this->campaignMarketing ['recommended_schedule']         = null;
				$this->campaignMarketing ['sales_manag_campaign']         = null;
				$this->campaignMarketing ['sel_customer_campaign']        = null;
				$this->campaignMarketing ['space_equip_requerid']         = null;
				$this->campaignMarketing ['state_campaign']               = 'Planteado';
				$this->campaignMarketing ['termination_form_campaign']    = null;
			} else {
				$this->campaignMarketing = array ();
			}
		}
		
		/**
		 * @param array $projects
		 * @param CRMEntity $entity
		 *
		 * @throws Exception
		 */
		public function setProjects ($projects, $entity) {
			if (!empty($projects)) {
				$this->projects                                 = $projects;
				$this->projects ['assigned_user_id']            = 1;
				$this->projects ['cliente']                     = null;
				$this->projects ['cod_proyectos']               = $entity->setModuleSeqNumber ('increment', self::PROJECT_MODULE);
				$this->projects ['comentarios_seguimient']      = null;
				$this->projects ['costo_general_del_proyect']   = 0;
				$this->projects ['costo_total_estimad']         = 0;
				$this->projects ['createdtime']                 = $this->createDate;
				$this->projects ['esfuerzo_ejecutad']           = 0;
				$this->projects ['esfuerzo_total_estimad']      = 0;
				$this->projects ['etapa']                       = 'Creado';
				$this->projects ['facturacio']                  = null;
				$this->projects ['fecha_de_inicio']             = date ('Y-m-d');
				$this->projects ['fecha_de_terminacion']        = null;
				$this->projects ['fecha_estimada_de_inicio']    = null;
				$this->projects ['forma_de_terminacion']        = null;
				$this->projects ['gestion_de_pedido']           = null;
				$this->projects ['initiative']                  = null;
				$this->projects ['modifiedtime']                = $this->createDate;
				$this->projects ['porcentaje_de_avance_genera'] = 0;
				$this->projects ['proporcion_de_cost']          = 0;
				$this->projects ['proporcion_de_esferzo']       = 0;
				$this->projects ['que_origina_el_proyect']      = 'Una Iniciativa';
				$this->projects ['seleccione_contrat']          = null;
				$this->projects ['seleccione_factur']           = null;
				$this->projects ['seleccione_pedid']            = null;
				$this->projects ['seleccione_vent']             = null;
				$this->projects ['seleccioneasunto']            = null;
				$this->projects [self::PROJECT_TABLE_FIELD]     = null;
			} else {
				$this->projects = array ();
			}
		}
		
		/**
		 * @param array $stageProjects
		 * @param CRMEntity $entity
		 *
		 * @throws Exception
		 */
		public function setStageProjects ($stageProjects, $entity) {
			if (!empty ($stageProjects)) {
				$this->stageProjects                      = $stageProjects;
				$this->stageProjects ['assigned_user_id'] = 1;
				$this->stageProjects ['cod_etapas_proye'] = $entity->setModuleSeqNumber ('increment', self::PROJECT_STEPS_MODULE);
				$this->stageProjects ['createdtime']      = $this->createDate;
				$this->stageProjects ['modifiedtime']     = $this->createDate;
				$this->stageProjects ['record_id']        = null;
			} else {
				$this->stageProjects = array ();
			}
			$this->stageProjects = $stageProjects;
		}
		
		/**
		 * @param array $workOrder
		 * @param CRMEntity $entity
		 *
		 * @throws Exception
		 */
		public function setWorkOrder ($workOrder, $entity) {
			if (!empty ($workOrder)) {
				$this->workOrder                                  = $workOrder;
				$this->workOrder ['asociar_a']                    = 'Iniciativa de Negocio';
				$this->workOrder ['assigned_user_id']             = 1;
				$this->workOrder ['asunto']                       = null;
				$this->workOrder ['ciudad']                       = null;
				$this->workOrder ['cliente']                      = null;
				$this->workOrder ['cod_orden_de_tra']             = $entity->setModuleSeqNumber ('increment', self::WORK_ORDER_MODULE);
				$this->workOrder ['codigo_postal']                = null;
				$this->workOrder ['comentarios_planificacio']     = null;
				$this->workOrder ['comentarios_resultado']        = null;
				$this->workOrder ['contrato_']                    = null;
				$this->workOrder ['createdtime']                  = $this->createDate;
				$this->workOrder ['direccion']                    = null;
				$this->workOrder ['estado_de_la_orden']           = 'Definido';
				$this->workOrder ['factura']                      = null;
				$this->workOrder ['facturacio']                   = null;
				$this->workOrder ['fecha_de_emision']             = date ('Y-m-d');
				$this->workOrder ['fecha_de_inicio']              = null;
				$this->workOrder ['fecha_prevista']               = null;
				$this->workOrder ['fecha_real_de_ci']             = null;
				$this->workOrder ['gestion_de_iniciativa']        = null;
				$this->workOrder ['gestion_de_oportunidade']      = null;
				$this->workOrder ['gestion_de_pedido']            = null;
				$this->workOrder ['gestion_de_planes_de_servici'] = null;
				$this->workOrder ['initiative']                   = null;
				$this->workOrder ['modifiedtime']                 = $this->createDate;
				$this->workOrder ['numero_unidades_planificadas'] = 0;
				$this->workOrder ['overall_progress_perc']        = 0;
				$this->workOrder ['pai']                          = null;
				$this->workOrder ['pedido']                       = null;
				$this->workOrder ['plan_de_servicios']            = null;
				$this->workOrder ['record_id']                    = null;
				$this->workOrder ['seleccione_prospect']          = null;
				$this->workOrder ['unidades_consumidas']          = 0;
				$this->workOrder ['unidades_de_medida']           = null;
				$this->workOrder ['ventas']                       = null;
				$this->workOrder [self::WORK_TABLE_FIELD]         = null;
			} else {
				$this->workOrder = array ();
			}
		}
		
		/**
		 * @param ResourcesForExecution $resources
		 * @param integer $crmId
		 *
		 * @throws Exception
		 * @throws WebServiceException
		 */
		public function updateResourceInitiative ($resources, $crmId) {
			$entity       = CRMEntity::getInstance ($resources->getTypeResource ());
			$entity->id   = $resources->getIdResource ();
			$entity->mode = 'edit';
			$entity->retrieve_entity_info ($resources->getIdResource (), $resources->getTypeResource ());
			$entity->column_fields['initiative'] = $crmId;
			$entity->save ($resources->getTypeResource ());
			unset ($entity);
		}
		
		/**
		 * @return ModuleColumnFields
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}