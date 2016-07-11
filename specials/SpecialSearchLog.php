<?php
/**
 * Curse Inc.
 * Search Logger
 * Search Log Special Page
 *
 * @author		Alex Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		Search Logger
 * @link		https://github.com/HydraWiki/SearchLogger
 *
**/

class SpecialSearchLog extends SpecialPage {
	/**
	 * Output HTML
	 *
	 * @var		string
	 */
	private $content;

	/**
	 * Main Constructor
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct() {
		global $wgRequest, $wgUser;

		parent::__construct('SearchLog');

		$this->wgRequest	= $wgRequest;
		$this->wgUser		= $wgUser;
		$this->output		= $this->getOutput();

		$this->DB = wfGetDB(DB_MASTER);
	}

	/**
	 * Main Executor
	 *
	 * @access	public
	 * @param	string	Sub page passed in the URL.
	 * @return	void	[Outputs to screen]
	 */
	public function execute($subpage) {
		global $wgServer, $wgScriptPath;
		if (!$this->wgUser->isAllowed('search_log')) {
			throw new PermissionsError('search_log');
			return;
		}

		$this->templateSearchLog = new TemplateSearchLog;

		$this->output->addModules('ext.searchLogger');

		$this->setHeaders();

		$this->searchLog();

		$this->output->addHTML($this->content);
	}

	/**
	 * Search Log List
	 *
	 * @access	public
	 * @return	void	[Outputs to screen]
	 */
	public function searchLog() {
		$start = $this->wgRequest->getInt('st');
		$itemsPerPage = 50;

		$start_date = trim($this->wgRequest->getText('start_date'));
		if ($start_date) {
			$start_timestamp = strtotime($start_date);
			if ($start_timestamp) {
				$where[] = "timestamp > ".$start_timestamp;
			}
		}
		$end_date = trim($this->wgRequest->getText('end_date'));
		if ($end_date) {
			$end_timestamp = strtotime($end_date);
			if ($end_timestamp) {
				$end_timestamp += 86399;
				$where[] = "timestamp < ".$end_timestamp;
			}
		}

		if (count($where)) {
			$where = implode(' AND ', $where);
		} else {
			$where = null;
		}

		$result = $this->DB->select(
			'search_log',
			['*, count(search_term) as hits'],
			$where,
			__METHOD__,
			[
				'OFFSET'	=> $start,
				'LIMIT'		=> $itemsPerPage,
				'GROUP BY'	=> 'search_term, search_method',
				'ORDER BY'	=> 'hits DESC'
			]
		);

		while ($row = $result->fetchRow()) {
			$logs[$row['sid']] = $row;
		}

		$result = $this->DB->select(
			'search_log',
			['count(*) AS total'],
			$where,
			__METHOD__,
			[
				'GROUP BY'	=> 'search_term, search_method'
			]
		);
		$total = $result->numRows();

		$pagination = Curse::generatePaginationHtml($total, $itemsPerPage, $start);

		$this->output->setPageTitle(wfMessage('searchlog')->escaped());
		$this->content = $this->templateSearchLog->searchLog($logs, $pagination);
	}

	/**
	 * Hides special page from SpecialPages special page.
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isListed() {
		if ($this->wgUser->isAllowed('search_log')) {
			return true;
		}
		return false;
	}

	/**
	 * Lets others determine that this special page is restricted.
	 *
	 * @access	public
	 * @return	boolean	True
	 */
	public function isRestricted() {
		return true;
	}
}
?>