<?php
/**
 * Curse Inc.
 * Search Logger
 * Search Log Skin
 *
 * @author		Alexia E. Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		Search Logger
 * @link		https://github.com/HydraWiki/SearchLogger
 *
**/

class TemplateSearchLog {
	/**
	 * Wiki List
	 *
	 * @access	public
	 * @param	array	Array of log Information
	 * @param	array	Pagination
	 * @return	string	Built HTML
	 */
	static public function searchLog($logs, $pagination) {
		global $wgRequest;
		$page = Title::newFromText('Special:SearchLog');
		$url = $page->getFullURL();
		$html = "
	{$pagination}
	<div id='search_range'>
		<form id='date_search' method='get' action='{$url}'>
			<fieldset>
				".wfMessage('between_dates')->escaped()."
				<input id='start_date' name='start_date' type='text' value='".htmlentities($wgRequest->getVal('start_date'), ENT_QUOTES)."'/>
				".wfMessage('dates_to')->escaped()."
				<input id='end_date' name='end_date' type='text' value='".htmlentities($wgRequest->getVal('end_date'), ENT_QUOTES)."'/>
				<input id='submit' type='submit' class='mw-ui-button mw-ui-progressive' value='".wfMessage('list_filter')->escaped()."'/>
			</fieldset>
		</form>
	</div>
	<table id='searchlog'>
		<thead>
			<tr>
				<th>".wfMessage('search_term')->escaped()."</th>
				<th>".wfMessage('search_method')->escaped()."</th>
				<th>".wfMessage('hits')->escaped()."</th>
			</tr>
		</thead>
		<tbody>
		";
		if (count($logs)) {
			foreach ($logs as $log) {
				$html .= "
				<tr>
					<td>{$log['search_term']}</td>
					<td>{$log['search_method']}</td>
					<td>{$log['hits']}</td>
				</tr>
";
			}
		} else {
			$html .= "
			<tr>
				<td colspan='3'>".wfMessage('no_log_entries_found')->escaped()."</td>
			</tr>
			";
		}
		$html .= "
		</tbody>
	</table>
	{$pagination}";

		return $html;
	}
}
