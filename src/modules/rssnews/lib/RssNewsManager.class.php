<?php
	require_once ('data/CRMEntity.php');
	require_once ('include/simplepie/autoloader.php');
	require_once ('include/utils/PlatformUtils.class.php');

	class RssNewsManager {
		private static $INSTANCE = null;
		/** @var PearDatabase */
		private $adb;

		private function createNews (SimplePie_Item $item, $subscription, $keywords) {
			$url = $item->get_link ();
			$result = $this->adb->pquery (
				'SELECT
					n.*
				FROM
					vtiger_rssnews n
					INNER JOIN vtiger_crmentity crme ON crme.crmid=n.rssnewsid AND crme.deleted=0
				WHERE
					n.url=?',
				array ($url)
			);
			if (($result) && ($this->adb->num_rows ($result) > 0)) {
				return;
			}

			global $adb;
			$adb = $this->adb;
			/** @var rssnews|CRMEntity $news */
			$news = CRMEntity::getInstance ('rssnews');
			$news->column_fields ['headline'] = $item->get_title ();
			$news->column_fields ['keywords'] = join ("\n", $keywords);
			$news->column_fields ['media'] = $subscription ['medios_bdiid'];
			$news->column_fields ['publicationdate'] = $item->get_date ('Y-m-d');
			$news->column_fields ['url'] = $item->get_link ();
			$news->column_fields ['assigned_user_id'] = $subscription ['assigned_user_id'];
			$news->save ('rssnews');
		}

		private function fixUrlScheme ($url) {
			$scheme = parse_url ($url, PHP_URL_SCHEME);
			return !empty ($scheme) ? $url : "http://{$url}";
		}

		private function getMatchingKeywords (SimplePie_Item $item, $keywords) {
			$matchingKeywords = array ();
			foreach ($keywords as $keyword) {
				if (empty ($keyword)) {
					continue;
				}
				$itemCategories = $item->get_categories ();
				if (!empty ($itemCategories)) {
					$categories = array_map (
						function (SimplePie_Category $category) {
							return strtolower ($category->get_term ());
						},
						$itemCategories
					);
				} else {
					$categories = null;
				}
				$keyword = strtolower ($keyword);
				if (
					((!empty ($categories)) && (in_array ($keyword, $categories))) ||
					(strpos (strtolower ($item->get_content ()), $keyword) !== false) ||
					(strpos (strtolower ($item->get_description ()), $keyword) !== false) ||
					(strpos (strtolower ($item->get_title ()), $keyword) !== false)
				) {
					$matchingKeywords [] = $keyword;
				}
			}
			return $matchingKeywords;
		}

		private function getRssKeywords () {
			$result = $this->adb->query ("SELECT palabras_clave FROM vtiger_clientes_bdi WHERE palabras_clave IS NOT NULL AND TRIM(palabras_clave)<>''");
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}

			$keywords = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$keywords = array_merge ($keywords, explode ("\n", $row ['palabras_clave']));
			}

			return array_unique ($keywords);
		}

		private function getSubscriptions () {
			$result = $this->adb->query (
				"SELECT
					rsss.*
				FROM
					vtiger_medios_bdi_rss rsss
				WHERE
					rsss.status='Activa'"
			);
			if ((!$result) || ($this->adb->num_rows ($result) == 0)) {
				return null;
			}
			$subscriptions = array ();
			while ($row = $this->adb->fetchByAssoc ($result, -1, false)) {
				$row ['url'] = $this->fixUrlScheme ($row ['url']);
				$subscriptions [] = $row;
			}
			return $subscriptions;
		}

		private function findSubscriptionByUrl ($subscriptions, $url) {
			foreach ($subscriptions as $subscription) {
				if ($subscription ['url'] == $url) {
					return $subscription;
				}
			}
			return null;
		}

		public function process (PearDatabase $adb) {
			$this->adb = $adb;

			if (!PlatformUtils::isModuleEnabled ($this->adb, 'rssnews')) {
				throw new Exception ('El módulo rssnews no se encuentra habilitado');
			}

			$subscriptions = $this->getSubscriptions ();
			if (empty ($subscriptions)) {
				throw new Exception ('No se encuentran registradas suscripciones activas');
			}

			$urls = array_map (
				function ($subscription) {
					return $subscription ['url'];
				},
				$subscriptions
			);

			$keywords = $this->getRssKeywords ();
			if (!$keywords) {
				throw new Exception ('No se encuentran registradas palabras claves de búsqueda');
			}

			$reader = new SimplePie ();
			$reader->set_feed_url ($urls);
			$result = $reader->init ();
			if (!$result) {
				throw new Exception ('No se encuentran noticias en los medios');
			}

			$subscription = null;
			/** @var SimplePie_Item $item */
			foreach ($reader->get_items () as $item) {
				if ((!isset ($subscription)) || ($subscription ['url'] != $item->get_feed ()->feed_url)) {
					$subscription = $this->findSubscriptionByUrl ($subscriptions, $item->get_feed ()->feed_url);
				}
				$matchingKeywords = $this->getMatchingKeywords ($item, $keywords);
				if (!empty ($matchingKeywords)) {
					$this->createNews ($item, $subscription, $matchingKeywords);
				}
			}
		}

		public static function getInstance () {
			if (self::$INSTANCE == null) {
				self::$INSTANCE = new RssNewsManager ();
			}
			return self::$INSTANCE;
		}

	}
