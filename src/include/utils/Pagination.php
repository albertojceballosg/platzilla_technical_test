<?php

	class Pagination {
		/** @var string  */
		private $attributes = '';

		/** @var string  */
		private $baseUrl = '';

		/** @var integer  */
		public $currentPage = 0;

		/** @var string  */
		private $currentTagClose = '</strong>';

		/** @var string  */
		private $currentTagOpen = '<strong>';

		/** @var string  */
		private $dataPageAttr = 'data-pagination-page';

		/** @var bool  */
		private $displayPages = true;

		/** @var string  */
		private $firstTagClose = '';

		/** @var string  */
		private $firstTagOpen = '';

		/** @var string  */
		private $firstLink = '&laquo;';

		/** @var string  */
		private $firstUrl = '';

		/** @var string  */
		private $fullTagClose = '';

		/** @var string  */
		private $fullTagOpen = '';

		/** @var string  */
		private $lastLink = '&raquo;';

		/** @var string  */
		private $lastTagClose = '';

		/** @var string  */
		private $lastTagOpen = '';

		/** @var array  */
		private $linkTypes = array();

		/** @var string  */
		private $nextLink = '&rsaquo;';

		/** @var string  */
		private $nextTagClose = '';

		/** @var string  */
		private $nextTagOpen = '';

		/** @var integer  */
		private $numLinks = 2;

		/** @var string  */
		private $numTagClose = '';

		/** @var string  */
		private $numTagOpen = '';

		/** @var integer  */
		public $perPage = 10;

		/** @var string  */
		private $prefix = '';

		/** @var string  */
		private $prevLink = '&lsaquo;';

		/** @var string  */
		private $prevTagClose = '';

		/** @var string  */
		private $prevTagOpen = '';

		/** @var string  */
		private $queryStringSegment = 'page';

		/** @var string  */
		private $suffix = '';

		/** @var integer  */
		private $totalRows = 0;

		/** @var boolean  */
		private $usePageNumbers = true;

		/**
		 * @param string $output
		 * @param integer $start
		 * @param integer $end
		 * @param string $basePage
		 * @param string $firstUrl
		 * @param string $baseUrl
		 */
		private function getOutput (&$output, $start, $end, $basePage, $firstUrl, $baseUrl) {
			for ($loop = ($start - 1); $loop <= $end; $loop++) {
				$i          = ($this->usePageNumbers) ? $loop : (($loop * $this->perPage) - $this->perPage);
				$attributes = sprintf ('%s %s="%d"', $this->attributes, $this->dataPageAttr, $loop);

				if ($i >= $basePage) {
					if ($this->currentPage === $loop) {
						$output .= $this->currentTagOpen.$loop.$this->currentTagClose;
					} else if ($i === $basePage) {
						$output .= $this->numTagOpen.'<a href="'.$firstUrl.'"'.$attributes.$this->setAttrRel('start').'>'
							.$loop.'</a>'.$this->numTagClose;
					} else {
						$append  = $this->prefix.$i.$this->suffix;
						$output .= $this->numTagOpen.'<a href="'.$baseUrl.$append.'"'.$attributes.'>'
							.$loop.'</a>'.$this->numTagClose;
					}
				}
			}
		}

		/**
		 * @param string $type
		 *
		 * @return string
		 */
		private function setAttrRel ($type) {
			if (isset ($this->linkTypes [$type])) {
				unset ($this->linkTypes [$type]);
				return 'rel="'. $type . '"';
			}
			return '';
		}

		/**
		 * @param $attributes
		 */
		private function parseAttributes ($attributes) {
			$this->linkTypes = ($attributes ['rel']) ? array ('start' => 'start', 'prev' => 'prev', 'next' => 'next') : array ();
			unset ($attributes ['rel']);

			$this->attributes = '';
			foreach ($attributes as $key => $value) {
				$this->attributes .= ' '.$key.'="'.$value.'"';
			}
		}

		/**
		 * @param array $params
		 *
		 * @return Pagination
		 */
		public function initialize (array $params = array ()) {
			if (is_array ($params ['attributes'])) {
				$this->parseAttributes ($params ['attributes']);
				unset ($params ['attributes']);
			}

			foreach ($params as $key => $val) {
				if (property_exists ($this, $key)) {
					$this->$key = $val;
				}
			}
			return $this;
		}

		/**
		 * @return string
		 */
		public function createLinks () {
			if ($this->totalRows == 0 || $this->perPage == 0) {
				return '';
			}

			$numberPages    = (int) ceil ($this->totalRows / $this->perPage);
			$this->numLinks = (int) $this->numLinks;

			if ($this->numLinks < 0 || $numberPages === 1) {
				return '';
			}

			$get      = array ();
			$baseUrl  = trim ($this->baseUrl);
			$firstUrl = $this->firstUrl;

			$queryStringSep = (strpos ($baseUrl, '?') === false) ? '?' : '&amp;';

			if ($firstUrl === '') {
				$firstUrl = $baseUrl;

				if (!empty ($get)) {
					$firstUrl .= $queryStringSep.http_build_query ($get);
				}
			}

			$baseUrl .= $queryStringSep.http_build_query (array_merge($get, array ($this->queryStringSegment => '')));

			$basePage = ($this->usePageNumbers) ? 1 : 0;

			if (!$this->currentPage) {
				$this->currentPage = $_REQUEST ['page'];
				$this->currentPage = (string) $this->currentPage;

				if (!ctype_digit ($this->currentPage) || ($this->usePageNumbers && (int) $this->currentPage === 0)) {
					$this->currentPage = $basePage;
				} else {
					$this->currentPage = (int) $this->currentPage;
				}
			}

			if ($this->usePageNumbers) {
				if ($this->currentPage > $numberPages) {
					$this->currentPage = $numberPages;
				}
			} else if ($this->currentPage > $this->totalRows) {
				$this->currentPage = (($numberPages - 1) * $this->perPage);
			}

			$uriPageNumber = $this->currentPage;

			if (!$this->usePageNumbers) {
				$this->currentPage = (int) floor (($this->currentPage/$this->perPage) + 1);
			}

			$this->currentPage = (int) $this->currentPage;
			$start	= (($this->currentPage - $this->numLinks) > 0) ? ($this->currentPage - ($this->numLinks - 1)) : 1;
			$end	= (($this->currentPage + $this->numLinks) < $numberPages) ? ($this->currentPage + $this->numLinks) : $numberPages;
			$output = '';
			if ($this->firstLink !== false && $this->currentPage > ($this->numLinks + 1 + ! $this->numLinks)) {
				$attributes = sprintf ('%s %s="%d"', $this->attributes, $this->dataPageAttr, 1);
				$output    .= $this->firstTagOpen.'<a href="'.$firstUrl.'"'.$attributes.$this->setAttrRel ('start').'>'
					.$this->firstLink.'</a>'.$this->firstTagClose;
			}

			if ($this->prevLink !== false && $this->currentPage !== 1) {
				$i          = ($this->usePageNumbers) ? ($uriPageNumber - 1) : ($uriPageNumber - $this->perPage);
				$attributes = sprintf ('%s %s="%d"', $this->attributes, $this->dataPageAttr, ($this->currentPage - 1));

				if ($i === $basePage) {
					$output .= $this->prevTagOpen.'<a href="'.$firstUrl.'"'.$attributes.$this->setAttrRel('prev').'>'
						.$this->prevLink.'</a>'.$this->prevTagClose;
				} else {
					$append  = $this->prefix.$i.$this->suffix;
					$output .= $this->prevTagOpen.'<a href="'.$baseUrl.$append.'"'.$attributes.$this->setAttrRel('prev').'>'
						.$this->prevLink.'</a>'.$this->prevTagClose;
				}
			}

			if ($this->displayPages !== false) {
				$this->getOutput ($output, $start, $end, $basePage, $firstUrl, $baseUrl);
			}

			if ($this->nextLink !== false && $this->currentPage < $numberPages) {
				$i          = ($this->usePageNumbers) ? ($this->currentPage + 1) : ($this->currentPage * $this->perPage);
				$attributes = sprintf ('%s %s="%d"', $this->attributes, $this->dataPageAttr, ($this->currentPage + 1));
				$output    .= $this->nextTagOpen.'<a href="'.$baseUrl.$this->prefix.$i.$this->suffix.'"'.$attributes
					.$this->setAttrRel ('next').'>'.$this->nextLink.'</a>'.$this->nextTagClose;
			}

			if ($this->lastLink !== false && ($this->currentPage + $this->numLinks + ! $this->numLinks) < $numberPages) {
				$i          = ($this->usePageNumbers) ? $numberPages : (($numberPages * $this->perPage) - $this->perPage);
				$attributes = sprintf ('%s %s="%d"', $this->attributes, $this->dataPageAttr, $numberPages);
				$output    .= $this->lastTagOpen.'<a href="'.$baseUrl.$this->prefix.$i.$this->suffix.'"'.$attributes.'>'
					.$this->lastLink.'</a>'.$this->lastTagClose;
			}

			$output = preg_replace ('#([^:"])//+#', '\\1/', $output);
			return $this->fullTagOpen.$output.$this->fullTagClose;
		}

		/**
		 * @return Pagination
		 */
		public static function getInstance () {
			return new self ();
		}

	}
