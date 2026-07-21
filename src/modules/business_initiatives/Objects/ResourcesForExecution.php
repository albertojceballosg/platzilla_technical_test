<?php
	require_once ('modules/business_initiatives/Objects/ResourceException.php');
	require_once ('modules/business_initiatives/Objects/ResourceInterface.php');
	
	class ResourcesForExecution implements ResourceInterface {
		
		/** @var float */
		private $contributionFactor;
		
		/** @var integer */
		private $id;
		
		/** @var integer */
		private $idResource;
		
		/** @var array */
		private $resource;
		
		/** @var string */
		private $resourceDescription;
		
		/** @var float */
		private $resourceProgress;
		
		/** @var float */
		private $summaryContribution;
		
		private $summaryFactor;
		
		/** @var float */
		private $totalContribution;
		
		/** @var string */
		private $typeResource;
		
		/** float */
		public function getContributionFactor () {
			return $this->contributionFactor;
		}
		
		/**
		 * @return integer
		 */
		public function getId () {
			return $this->id;
		}
		
		/**
		 * @return integer
		 */
		public function getIdResource () {
			return $this->idResource;
		}
		
		/**
		 * @return array
		 */
		public function getResource () {
			return $this->resource;
		}
		
		/**
		 * @return float
		 */
		public function getSummaryContribution () {
			return $this->summaryContribution;
		}
		
		/**
		 * @return float
		 */
		public function getSummaryFactor () {
			return $this->summaryFactor;
		}
		
		/**
		 * @return string
		 */
		public function getResourceDescription () {
			return $this->resourceDescription;
		}
		
		/**
		 * @return float
		 */
		public function getResourceProgress () {
			return $this->resourceProgress;
		}
		
		/**
		 * @return float
		 */
		public function getTotalContribution () {
			return $this->totalContribution;
		}
		
		/**
		 * @return string
		 */
		public function getTypeResource () {
			return $this->typeResource;
		}
		
		/**
		 * @param float $contributionFactor
		 *
		 * @return ResourcesForExecution
		 */
		public function setContributionFactor ($contributionFactor) {
			$this->contributionFactor = $contributionFactor;
			return $this;
		}
		
		/**
		 * @param integer $id
		 *
		 * @return ResourcesForExecution
		 */
		public function setId ($id) {
			$this->id = $id;
			return $this;
		}
		
		/**
		 * @param integer $idResource
		 *
		 * @return ResourcesForExecution
		 */
		public function setIdResource ($idResource) {
			$this->idResource = $idResource;
			return$this;
		}
		
		/**
		 * @param array $resource
		 *
		 * @return ResourcesForExecution
		 */
		public function setResource ($resource) {
			$this->resource = $resource;
			return $this;
		}
		
		/**
		 * @param $resourceDescription
		 *
		 * @return ResourcesForExecution
		 */
		public function setResourceDescription ($resourceDescription) {
			$this->resourceDescription = $resourceDescription;
			return $this;
		}
		
		/**
		 * @param float $resourceProgress
		 *
		 * @return ResourcesForExecution
		 */
		public function setResourceProgress ($resourceProgress) {
			$this->resourceProgress = $resourceProgress;
			return $this;
		}
		
		/**
		 * @param float $summaryContribution
		 *
		 * @return ResourcesForExecution
		 */
		public function setSummaryContribution ($summaryContribution) {
			$this->summaryContribution = $summaryContribution;
			return $this;
		}
		
		/**
		 * @param float $summaryFactor
		 *
		 * @return ResourcesForExecution
		 */
		public function setSummaryFactor ($summaryFactor) {
			$this->summaryFactor = $summaryFactor;
			return $this;
		}
		
		/**
		 * @param float $totalContribution
		 *
		 * @return ResourcesForExecution
		 */
		public function setTotalContribution ($totalContribution) {
			$this->totalContribution = $totalContribution;
			return $this;
		}
		
		/**
		 * @param $typeResource
		 *
		 * @return ResourcesForExecution
		 */
		public function setTypeResource ($typeResource) {
			$this->typeResource = $typeResource;
			return $this;
		}
		
		/**
		 * @throws ResourceException
		 */
		public function validate () {
			if (empty($this->idResource)) {
				throw new ResourceException(ResourceException::CRM_ID_RESOURCE_EMPTY);
			}
			if (empty($this->id)) {
				throw new ResourceException((ResourceException::RECURSE_INITIATIVE_ID_EMPTY));
			}
			if (empty($this->typeResource)) {
				throw new ResourceException( ResourceException::TYPE_RESOURCE_EMPTY);
			} else if (!in_array ($this->typeResource, array_keys  (ResourceInterface::RESOURCE_MODULES))) {
				throw new ResourceException( ResourceException::TYPE_RESOURCE_INDEFINED);
			}
		}
		
		/**
		 * @return ResourcesForExecution
		 */
		public static function getInstance () {
			return new self ();
		}
		
	}
