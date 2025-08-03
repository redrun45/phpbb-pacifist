<?php

/**
 *
 * Pacifist. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, redrun45
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
//
// Some characters you may want to copy&paste:
// ’ » “ ” …
//

$lang = array_merge($lang, [

	'ACP_PACIFIST_EXPLAIN_BAD_BOTS' => 'In the context of this module, a "good bot" is one that <i>identifies itself</i> as a bot.	If you add it
		to your site\'s list of known bots, or disallow it using robots.txt, it generally won\'t cause any problems.	A "bad bot" is one that
		attempts to blend in with your human visitors (usually as a Guest), and ignores robots.txt instructions.',

	'ACP_PACIFIST_EXPLAIN_PERCENT_BUSY' => 'The "Busy" percentage is a very rough estimate based on your server\'s CPU and disk speed, and does
		not account for bandwidth or other limitations.	It takes the highest average usage over the past 1, 5, and 15 minutes.	For reference, the
		current "Busy" percentage is calculated as',

	'ACP_PACIFIST_EXPLAIN_CUSTOM_CODE' => 'The marked features require a little copy-and-paste to set up.	Snippets and full instructions here',
	'ACP_PACIFIST_README_LABEL' => 'Readme - Custom Code',

	'ACP_PACIFIST_SEND_401_ON_AUTH_REQUIRED'	=> 'Send 401 status code when a page requires a log-in? (Recommended)',
	'ACP_PACIFIST_EXPLAIN_SEND_401_ON_AUTH_REQUIRED' => 'Enable this setting to tell all bots:	This login screen is not the content you\'re
		looking for.',

	'ACP_PACIFIST_SUPPRESS_SIDS_IN_LINKS'	=> 'Stop sending tracking links to Guests? (Recommended)',
	'ACP_PACIFIST_EXPLAIN_SUPPRESS_SIDS_IN_LINKS' => 'When a Guest shows up without a cookie, phpBB generates a set of links to track their page
		views.<br />Enable this setting to stop sending these unique links, which bad bots see as new content to scrape.',

	'ACP_PACIFIST_REDIRECT_ON_SID'	=> 'Actively retire old tracking links? (Recommended)',
	'ACP_PACIFIST_EXPLAIN_REDIRECT_ON_SID' => 'Over time, bad bots might collect several tracking links for each page on your site.	We can help
		them figure that out, so they don\'t check back on each one.<br />
		Enable this setting to send a 301 redirect to the actual page, when a tracking link is visited.',

	'ACP_PACIFIST_DISABLE_GUEST_TRACKING' => 'Stop tracking Guests at all? (Experimental)',
	'ACP_PACIFIST_EXPLAIN_DISABLE_GUEST_TRACKING' => 'The Guest-tracking feature was designed for humans, and can\'t keep up with bots.<br />
		Enable this setting to stop sending unique links and session cookies to Guests, and ignore their view stats.	Members and good bots will
		be tracked as usual.	Existing guests may be tracked until their current sessions expire.',

	'ACP_PACIFIST_WARN_DISABLE_GUEST_TRACKING' => 'This option also bypasses parts of phpBB code, which may have unforeseen side-effects.',

	'ACP_PACIFIST_DEFER_KNOWN_BOTS' => 'Ask good bots to back off for a while? (Recommended: 70%)',
	'ACP_PACIFIST_EXPLAIN_DEFER_KNOWN_BOTS' => 'Enable this setting to send good bots a 503 status code, when your site is busy.',

	'ACP_PACIFIST_REQUIRE_LOGIN' => 'Require log-in to view forums? (Recommended: 85%)',
	'ACP_PACIFIST_EXPLAIN_REQUIRE_LOGIN' => 'Enable this setting to block Guest access to all pages, when your site is busy.',

	'ACP_PACIFIST_LABEL_CUSTOM_CODE' => 'Requires a minor addition to phpBB code.',

	'ACP_PACIFIST_NEVER' => 'Disabled',
	'ACP_PACIFIST_ALWAYS' => 'Enabled',
	'ACP_PACIFIST_WHEN_BUSY' => 'When Busy (Percent)',
	'ACP_PACIFIST_LABEL_WHEN_BUSY' => 'Enables this setting when the server is at least x% busy.<br>
		Disabled = 0, Always Enabled = -1',

	'ACP_PACIFIST_SETTING_SAVED'	=> 'Settings have been saved successfully!',

	'MSG_PACIFIST_REQUIRE_LOGIN_WHEN_BUSY' => 'Due to a high volume of traffic, this forum site temporarily requires an account.
		Please sign in, or check back later.'

]);
