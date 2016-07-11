<?php
/**
 * Curse Inc.
 * Search Logger
 * Search Logger Mediawiki Settings
 *
 * @author 		Alex Smith
 * @copyright	(c) 2014 Curse Inc.
 * @license		GPL v3.0
 * @package		Search Logger
 * @link		https://github.com/HydraWiki/SearchLogger
 *
**/

/******************************************/
/* Credits                                */
/******************************************/
$wgExtensionCredits['specialpage'][] = [
	'path'				=> __FILE__,
	'name'				=> 'Search Logger',
	'author'			=> 'Alexia E. Smith, Curse Inc&copy;',
	'descriptionmsg'	=> 'searchlogger_description',
	'version'			=> '1.2',
	'license-name'		=> 'GPL-3.0',
	'url'				=> 'https://github.com/HydraWiki/RedisSessions'
];

/******************************************/
/* Language Strings, Page Aliases, Hooks  */
/******************************************/
$extDir = __DIR__;
define('SL_EXT_DIR', __DIR__);

$wgAvailableRights[] = 'search_log';

$wgExtensionMessagesFiles['SearchLoggerMessages']	= "{$extDir}/SearchLogger.i18n.php";
$wgExtensionMessagesFiles['SearchLoggerAliases']	= "{$extDir}/SearchLogger.alias.php";
$wgMessagesDirs['SearchLogger']						= "{$extDir}/i18n";

$wgAutoloadClasses['SearchLoggerHooks']				= "{$extDir}/SearchLogger.hooks.php";
$wgAutoloadClasses['SpecialSearchLog']				= "{$extDir}/specials/SpecialSearchLog.php";

$wgAutoloadClasses['TemplateSearchLog']				= "{$extDir}/templates/TemplateSearchLog.php";

$wgSpecialPages['SearchLog']						= 'SpecialSearchLog';

$wgSpecialPageGroups['SearchLog']					= 'search';

$wgHooks['LoadExtensionSchemaUpdates'][]			= 'SearchLoggerHooks::onLoadExtensionSchemaUpdates';
$wgHooks['SearchEngineReplacePrefixesComplete'][]	= 'SearchLoggerHooks::onSearchEngineReplacePrefixesComplete';

$wgResourceModules['ext.searchLogger'] = [
	'localBasePath'	=> $extDir,
	'remoteExtPath'	=> 'SearchLogger',
	'styles'		=> ['css/searchlogger.css'],
	'scripts'		=> ['js/searchlogger.js'],
	'dependencies'	=> ['jquery.ui.datepicker', 'ext.curse.pagination']
];

$wgGroupPermissions['sysop']['search_log']			= true;
