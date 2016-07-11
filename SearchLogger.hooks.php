<?php
/**
 * Curse Inc.
 * Search Logger
 * Search Logger Hooks
 *
 * @author 		Alex Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		Search Logger
 * @link		https://github.com/HydraWiki/SearchLogger
 *
**/

class SearchLoggerHooks {
	/**
	 * Hooks Initialized
	 *
	 * @var		boolean
	 */
	private static $initialized = false;

	/**
	 * Database Object Pointer
	 *
	 * @var		object
	 */
	private static $DB;

	/**
	 * Initiates some needed classes.
	 *
	 * @access	public
	 * @return	void
	 */
	static public function init() {
		if (!self::$initialized) {
			self::$DB = wfGetDB(DB_MASTER);
			self::$initialized = true;
		}
	}

	/**
	 * Setups and Modifies Database Information
	 *
	 * @access	public
	 * @param	object	[Optional] DatabaseUpdater Object
	 * @return	boolean	true
	 */
	static function onLoadExtensionSchemaUpdates($updater = null) {
		$extDir = dirname(__FILE__);

		if ($updater === null) {
			//Fresh Installation
			global $wgExtNewTables, $wgExtNewFields, $wgExtPGNewFields, $wgExtPGAlteredFields, $wgExtNewIndexes, $wgDBtype;

			$wgExtNewTables[]	= array('search_log', "{$extDir}/install/sql/searchlogger_table_search_log.sql");
		} else {
			//Updates
			$updater->addExtensionUpdate(array('addTable', 'search_log', "{$extDir}/install/sql/searchlogger_table_search_log.sql", true));
		}
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
		self::init();

		//Remove when PHP finally becauses 100% multibyte by default.
		if (function_exists('mb_strtolower')) {
			$search_term = mb_strtolower($query, 'UTF-8');
		} else {
			$search_term = strtolower($query);
		}
		$search_term = trim($search_term);

		if (!empty($_REQUEST['go'])) {
			$search_method = 'go';
		} elseif (!empty($_REQUEST['fulltext'])) {
			$search_method = 'fulltext';
		} elseif ($_REQUEST['suggest']) {
			$search_method = 'ajax';
		} else {
			$search_method = 'other';
		}

		self::$DB->insert(
			'search_log',
			['search_term' => $search_term, 'search_method' => $search_method, 'timestamp' => time()],
			__METHOD__
		);
		return true;
	}
}
?>