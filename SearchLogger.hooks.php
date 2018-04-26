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
	static function onLoadExtensionSchemaUpdates($updater = null) {
		$extDir = __DIR__;

		$updater->addExtensionUpdate(['addTable', 'search_log', "{$extDir}/install/sql/searchlogger_table_search_log.sql", true]);

		return true;
	}

	/**
	 * Catching searches for logging, does not modify the search.
	 *
	 * @access	public
	 * @param	object	SearchEngine Object
	 * @param	string	Search Term - Might have been modify by another extension's hook into SearchEngine::transformSearchTerm();
	 * @param	string	Parsed over search term
	 * @return	boolean	true
	 */
	static function onSearchEngineReplacePrefixesComplete($searchEngine, $query, &$parsed) {
		$db = wfGetDB(DB_MASTER);

		//Remove when PHP finally becauses 100% multibyte by default.
		if (function_exists('mb_strtolower')) {
			$searchTerm = mb_strtolower($query, 'UTF-8');
		} else {
			$searchTerm = strtolower($query);
		}
		$searchTerm = trim($searchTerm);

		if (!empty($_REQUEST['go'])) {
			$searchMethod = 'go';
		} elseif (!empty($_REQUEST['fulltext'])) {
			$searchMethod = 'fulltext';
		} elseif ($_REQUEST['suggest']) {
			$searchMethod = 'ajax';
		} else {
			$searchMethod = 'other';
		}

		$db->insert(
			'search_log',
			['search_term' => $searchTerm, 'search_method' => $searchMethod, 'timestamp' => time()],
			__METHOD__
		);
		return true;
	}
}
