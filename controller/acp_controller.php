<?php

/**
 *
 * Pacifist. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, redrun45
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace redrun45\pacifist\controller;

use redrun45\pacifist\event\main_listener;

/**
 * Pacifist ACP controller.
 */
class acp_controller
{
	/** @var \phpbb\config\config */
	protected $config;

	/** @var \phpbb\language\language */
	protected $language;

	/** @var \phpbb\log\log */
	protected $log;

	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var string Custom form action */
	protected $u_action;

	/** @var listener */
	protected $listener;

	/**
	 * Constructor.
	 *
	 * @param \phpbb\config\config		$config		Config object
	 * @param \phpbb\language\language	$language	Language object
	 * @param \phpbb\log\log			$log		Log object
	 * @param \phpbb\request\request	$request	Request object
	 * @param \phpbb\template\template	$template	Template object
	 * @param \phpbb\user				$user		User object
	 */
	public function __construct(\phpbb\config\config $config, \phpbb\language\language $language, \phpbb\log\log $log, \phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user)
	{
		$this->config	= $config;
		$this->language	= $language;
		$this->log		= $log;
		$this->request	= $request;
		$this->template	= $template;
		$this->user		= $user;
		$this->listener = new main_listener($config, $language);
	}

	/**
	 * Display the options a user can configure for this extension.
	 *
	 * @return void
	 */
	public function display_options()
	{
		// Add our common language file
		$this->language->add_lang('common', 'redrun45/pacifist');

		// Create a form key for preventing CSRF attacks
		add_form_key('pacifist_acp');

		// Create an array to collect errors that will be output to the user
		$errors = [];

		// Is the form being submitted to us?
		if ($this->request->is_set_post('submit'))
		{
			// Test if the submitted form is valid
			if (!check_form_key('pacifist_acp'))
			{
				$errors[] = $this->language->lang('FORM_INVALID');
			}

			// If no errors, process the form data
			if (empty($errors))
			{
				// Set the options the user configured
				$this->config->set('pacifist_send_401_on_auth_required', $this->request->variable('pacifist_send_401_on_auth_required', 0));
				$this->config->set('pacifist_suppress_sids_in_links', $this->request->variable('pacifist_suppress_sids_in_links', 0));
				$this->config->set('pacifist_redirect_on_sid', $this->request->variable('pacifist_redirect_on_sid', 0));
				$this->config->set('pacifist_disable_guest_tracking', $this->request->variable('pacifist_disable_guest_tracking', 0));
				$this->config->set('pacifist_defer_known_bots', $this->request->variable('pacifist_defer_known_bots', 0));
				$this->config->set('pacifist_require_login', $this->request->variable('pacifist_require_login', 0));

				// Add option settings change action to the admin log
				$this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_ACP_PACIFIST_SETTINGS');

				// Option settings have been updated and logged
				// Confirm this to the user and provide link back to previous page
				trigger_error($this->language->lang('ACP_PACIFIST_SETTING_SAVED') . adm_back_link($this->u_action));
			}
		}

		$s_errors = !empty($errors);

		// Set output variables for display in the template
		$this->template->assign_vars([
			'S_ERROR'		=> $s_errors,
			'ERROR_MSG'		=> $s_errors ? implode('<br>', $errors) : '',

			'U_ACTION'		=> $this->u_action,

			'PACIFIST_SEND_401_ON_AUTH_REQUIRED'	=> (int) $this->config['pacifist_send_401_on_auth_required'],
			'PACIFIST_SUPPRESS_SIDS_IN_LINKS'	=> (int) $this->config['pacifist_suppress_sids_in_links'],
			'PACIFIST_REDIRECT_ON_SID'	=> (int) $this->config['pacifist_redirect_on_sid'],
			'PACIFIST_DISABLE_GUEST_TRACKING'	=> (int) $this->config['pacifist_disable_guest_tracking'],
			'PACIFIST_DEFER_KNOWN_BOTS'	=> (int) $this->config['pacifist_defer_known_bots'],
			'PACIFIST_REQUIRE_LOGIN'	=> (int) $this->config['pacifist_require_login'],
			'PACIFIST_CURRENT_PERCENT_BUSY' => (int) $this->listener->percent_busy,
		]);
	}

	/**
	 * Set custom form action.
	 *
	 * @param string	$u_action	Custom form action
	 * @return void
	 */
	public function set_page_url($u_action)
	{
		$this->u_action = $u_action;
	}
}
