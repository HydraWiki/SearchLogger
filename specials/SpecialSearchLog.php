<?php
/**
 * Curse Inc.
 * Search Logger
 * Search Log Special Page
 *
 * @author		Alexia E. Smith
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
		parent::__construct('SearchLog', 'search_log');

		$this->output = $this->getOutput();
	}

	/**
	 * Main Executor
	 *
	 * @access	public
	 * @param	string	Sub page passed in the URL.
	 * @return	void	[Outputs to screen]
	 */
	public function execute($subpage) {
		if (!$this->getUser()->isAllowed('search_log')) {
			throw new PermissionsError('search_log');
			return;
		}

		$this->output->addModuleStyles(['ext.searchLogger.styles']);
		$this->output->addModuleScripts(['ext.searchLogger.scripts']);

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
		$start = $this->getRequest()->getInt('st');
		$itemsPerPage = 50;

		$where = [];

		$startDate = trim($this->getRequest()->getText('start_date'));
		if ($startDate) {
			$startTimestamp = strtotime($startDate);
			if ($startTimestamp) {
				$where[] = "timestamp > ".$startTimestamp;
			}
		}
		$endDate = trim($this->getRequest()->getText('end_date'));
		if ($endDate) {
			$endTimestamp = strtotime($endDate);
			if ($endTimestamp) {
				$endTimestamp += 86399;
				$where[] = "timestamp < ".$endTimestamp;
			}
		}

		$db = wfGetDB(DB_MASTER);
		$result = $db->select(
			['search_log'],
			[
				'*',
				'count(search_term) as hits'
			],
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

		$result = $db->select(
			['search_log'],
			['count(*) AS total'],
			$where,
			__METHOD__,
			[
				'GROUP BY'	=> 'search_term, search_method'
			]
		);
		$total = $result->numRows();

		$pagination = HydraCore::generatePaginationHtml(self::getTitleFor($this->getName()), $total, $itemsPerPage, $start);

		$this->output->setPageTitle(wfMessage('searchlog')->escaped());
		$this->content = TemplateSearchLog::searchLog($logs, $pagination);
	}

	/**
	 * Hides special page from SpecialPages special page.
	 *
	 * @access	public
	 * @return	boolean
	 */
	public function isListed() {
		if ($this->getUser()->isAllowed('search_log')) {
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

	/**
	 * Under which header this special page is listed in Special:SpecialPages.
	 *
	 * @access	protected
	 * @return	string	Group Name
	 */
	protected function getGroupName() {
		return 'search';
	}
}