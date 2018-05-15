<?php
/**
 * Curse Inc.
 * Search Logger
 * Search Logger Hooks
 *
 * @author 		Alexia E. Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		Search Logger
 * @link		https://github.com/HydraWiki/SearchLogger
 *
**/

class SearchLoggerHooks {
	/**
	 * Setups and Modifies Database Information
	 *
	 * @access	public
	 * @param	object	[Optional] DatabaseUpdater Object
	 * @return	boolean	true
	 */
	static public function onLoadExtensionSchemaUpdates($updater = null) {
		$extDir = __DIR__;

		$updater->addExtensionUpdate(['addTable', 'search_log', "{$extDir}/install/sql/searchlogger_table_search_log.sql", true]);

		$updater->addExtensionUpdate(['modifyField', 'search_log', 'sid', "{$extDir}/upgrade/sql/search_log/modify_sid_auto_increment.sql", true]);

		return true;
	}

	/**
	 * Catching searches for logging.
	 *
	 * @access	public
	 * @param	object	SpecialSearch
	 * @param	object	OutputPage
	 * @param	string	Transformed Search Term
	 * @return	boolean	True
	 */
	static public function onSpecialSearchResultsPrepend($searchPage, $output, $query) {
		$db = wfGetDB(DB_MASTER);

		$searchTerm = trim(mb_strtolower($query, 'UTF-8'));

		if (!empty($searchPage->getRequest()->getVal('go'))) {
			$searchMethod = 'go';
		} elseif (!empty($searchPage->getRequest()->getVal('fulltext'))) {
			$searchMethod = 'fulltext';
		} elseif (!empty($searchPage->getRequest()->getVal('suggest'))) {
			$searchMethod = 'ajax';
		} else {
			$searchMethod = 'other';
		}

		$db->insert(
			'search_log',
			[
				'search_term' => $searchTerm,
				'search_method' => $searchMethod,
				'timestamp' => time()
			],
			__METHOD__
		);
		return true;
	}

	/**
	 * Return the top searchs by rank for the given time period.
	 *
	 * @access	public
	 * @param	integer	[Optional] Start Timestamp, Unix Style
	 * @param	integer	[Optional] End Timestamp, Unix Style
	 * @return	array	Top search terms by rank in descending order.  [[rank, term], [rank, term]]
	 */
	static public function getTopSearchTerms($startTimestamp = null, $endTimestamp = null, $limit = null) {
		if ($startTimestamp === null) {
			$startTimestamp = 0;
		}
		if ($endTimestamp === null) {
			$endTimestamp = time();
		}

		$options = [
			'GROUP BY'	=> 'search_term',
			'ORDER BY'	=> 'total DESC'
		];

		if ($limit !== null) {
			$options['LIMIT'] = intval($limit);
		}

		$db = wfGetDB(DB_SLAVE);
		$result = $db->select(
			['search_log'],
			['count(*) as total', 'search_term'],
			[
				"timestamp > ".$db->strencode($startTimestamp),
				"timestamp < ".$db->strencode($endTimestamp)
			],
			__METHOD__,
			$options
		);

		$terms = [];
		while ($row = $result->fetchRow()) {
			$terms[] = [$row['total'], $row['search_term']];
		}
		return $terms;
	}
}
