<?php

/**
 *
 * Pacifist. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, redrun45
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace redrun45\pacifist\event;

/**
 * @ignore
 */

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Pacifist Event listener.
 */
class main_listener implements EventSubscriberInterface
{
	/**
	 * Map phpBB core events to the listener methods that should handle those events
	 *
	 * @return array
	 */
	public static function getSubscribedEvents()
	{
		return [
			'core.user_setup' => 'defer_bots_or_load_language',
			'core.login_box_before' => 'send_401_on_auth_required',
			'core.append_sid' => 'suppress_sids_in_links',
			'pacifist.sid_redirect_override' => 'redirect_on_sid',
			'pacifist.session_create_before_db' => 'disable_guest_tracking',
			'pacifist.user_setup_after_flagged' => 'require_login',
		];
	}

	/**
	 * Defer known bots as early as possible - and load language for later events if not.
	 * Run by: core.user_setup
	 *
	 * @param \phpbb\event\data	$event	Event object
	 */
	public function defer_bots_or_load_language($event)
	{
		// See below.	Checks settings before doing anything, but it might trigger an error (intentionally)
		$this->defer_known_bots();

		$lang_set_ext = $event['lang_set_ext'];
		$lang_set_ext[] = [
			'ext_name' => 'redrun45/pacifist',
			'lang_set' => 'common',
		];
		$event['lang_set_ext'] = $lang_set_ext;
	}

	/**
	 * Send '401 Unauthorized' whenever authentication is required.
	 * Run by: core.login_box_before
	 */
	public function send_401_on_auth_required()
	{
		// Do nothing if this feature is not enabled
		if (!isset($this->config['pacifist_send_401_on_auth_required']) or $this->config['pacifist_send_401_on_auth_required'] == 0)
		{
			return;
		}

		if (!defined('IN_LOGIN'))
		{
			send_status_line(401, 'Unauthorized');
		}
	}

	/**
	 * Suppress sids in most links - though they are required for ACP sessions and logout.
	 * Run by: core.append_sid
	 *
	 * @param \phpbb\event\adata\$event> Event object
	 */
	public function suppress_sids_in_links($event)
	{
		// Do nothing if this feature is not enabled
		if (!isset($this->config['pacifist_suppress_sids_in_links']) or $this->config['pacifist_suppress_sids_in_links'] == 0)
		{
			return;
		}

		// Do nothing if the link is to start or continue an ACP session
		if (defined('NEED_SID') or str_starts_with($event['url'], './adm/'))
		{
			return;
		}

		// Do nothing if the link is to logout
		$params = is_array($event['params']) ? implode('&', $event['params']) : $event['params'];
		if (str_contains($params, 'mode=logout'))
		{
			return;
		}

		// If none of the above, we blank out session_id in the context of this function:
		//	 phpBB/includes/functions.php -> append_sid()
		// We'll not use the override, so we don't have to re-implement all the other logic there.
		$event['session_id'] = '';
	}


	/**
	 * Redirect new sessions away from SID links.
	 * Run by: pacifist.sid_redirect_override - add to phpBB/phpbb/session.php
	 *
	 * @param \phpbb\event\adata\$event> Event object
	 */
	public function redirect_on_sid($event)
	{
		// Do nothing if this feature is not enabled
		if (!isset($this->config['pacifist_redirect_on_sid']) or $this->config['pacifist_redirect_on_sid'] == 0)
			return;

		// If we are signed in, user_id will exist.	Otherwise, the visitor might be an unrecognized bot.
		if (!$event['user_id'])
			$event['redirect_other'] = true;
	}


	/**
	 * Skip creating a session for visitors not known to be bots or users.
	 * Run by: pacifist.session_create_before_db - add to phpBB/phpbb/session.php
	 *
	 * @param \phpbb\event\adata\$event> Event object
	 */
	public function disable_guest_tracking($event)
	{
		// Do nothing if this feature is not enabled, or the server load has not reached its threshold
		if (
			!isset($this->config['pacifist_disable_guest_tracking'])
			or $this->config['pacifist_disable_guest_tracking'] == 0
			or ($this->percent_busy and $this->percent_busy < $this->config['pacifist_disable_guest_tracking'])
		)
			return;

		// If we are not a user, and not a known bot.
		if ($event['sql_ary']['session_user_id'] === ANONYMOUS && !$event['is_bot'])
			$event['use_dummy_session'] = true;
	}


	/**
	 * Send '503 Service Unavailable' to known bots, when the server is under heavy load
	 * Run by: core.user_setup (though the immediate call comes from defer_bots_or_load_language, earlier in this file)
	 */
	public function defer_known_bots()
	{
		// Do nothing if this feature is not enabled
		if (
				!isset($this->config['pacifist_defer_known_bots'])
				or $this->config['pacifist_defer_known_bots'] == 0
				or ($this->percent_busy and $this->percent_busy < $this->config['pacifist_defer_known_bots'])
			)
		{
			return;
		}

		// The equivalent of phpBB's built-in action when the server is under load - except that we shut it down for ONLY the known bots.
		// To shut down the forum for unknowns, see require_login() below
		global $user;
    if ($user->data['is_bot'])
    {
			send_status_line(503, 'Unavailable');
			trigger_error('BOARD_UNAVAILABLE');
		}
	}


	/**
	 * Require login on all pages - members and known bots have access, guests do not.
	 * Run by: pacifist.user_setup_after_flagged - the original, core.user_setup_after, runs too early for us to call login_box()
	 *	 without infinite recursion.
	 *
	 * @param \phpbb\event\adata\$event> Event object
	 */
	public function require_login()
	{
		// Do nothing if this feature is not enabled, or the server load has not reached its threshold
		if (
			!isset($this->config['pacifist_require_login'])
			or $this->config['pacifist_require_login'] == 0
			or ($this->percent_busy and $this->percent_busy < $this->config['pacifist_require_login'])
		)
			return;

		global $user;
		global $language;
		if (!$user->data['is_registered'] and !defined('IN_LOGIN') and $user->page['page_name'] != 'ucp.php' and $user->page['page_name'] != 'app.php/help/faq')
			login_box('', $language->lang('MSG_PACIFIST_REQUIRE_LOGIN_WHEN_BUSY'));
	}



/* @var \phpbb\language\language */
	protected $language;

	/* @var \phpbb\config\config */
	protected $config;

	/* @var integer */
	public $percent_busy;

	/**
	 * Constructor
	 *
	 * @param \phpbb\config\config	$config	Config object
	 * @param \phpbb\language\language	$language	Language object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language)
	{
		$this->config = $config;
		$this->language = $language;
		$this->percent_busy = 0;

		// This is partly borrowed from phpBB/phpbb/session.php -> session_begin(), with a bit of help from a php.net submission.
		$loadfunc_exists = false; //function_exists('sys_getloadavg');
    if (($loadfunc_exists or is_readable('/proc/loadavg')) and is_readable('/proc/stat'))
    {

			// See how many processors we have.
			$proc_stat = @file_get_contents('/proc/stat');
			$processor_count = max(substr_count($proc_stat, 'cpu') - 1, 1);

			// Use the highest of the first three numbers returned (the 1-minute, 5-minute, and 15-minute load averages)
			// This lets us respond to bursts of activity, without flip-flopping too much when they let up for a minute.
			$load_avgs = $loadfunc_exists ? sys_getloadavg() : explode(' ', @file_get_contents('/proc/loadavg'));
			$high_load = max(array_values(array_slice($load_avgs, 0, 3)));

			// Convert the chosen load-average into an approximate overall "business" stat
			$this->percent_busy = $high_load / $processor_count * 100;
		}
	}
}
