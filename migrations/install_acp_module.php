<?php

/**
 *
 * Pacifist. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, redrun45
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace redrun45\pacifist\migrations;

class install_acp_module extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return isset($this->config['pacifist_send_401_on_auth_required']);
	}

	public static function depends_on()
	{
		return ['\phpbb\db\migration\data\v320\v320'];
	}

	public function update_data()
	{
		return [
			['config.add', ['pacifist_send_401_on_auth_required', 0]],
			['config.add', ['pacifist_suppress_sids_in_links', 0]],
			['config.add', ['pacifist_redirect_on_sid', 0]],
			['config.add', ['pacifist_disable_guest_tracking', 0]],
			['config.add', ['pacifist_defer_known_bots', 0]],
			['config.add', ['pacifist_require_login', 0]],

			['module.add', [
				'acp',
				'ACP_CAT_DOT_MODS',
				'ACP_PACIFIST_TITLE'
			]],
			['module.add', [
				'acp',
				'ACP_PACIFIST_TITLE',
				[
					'module_basename'	=> '\redrun45\pacifist\acp\main_module',
					'modes'				=> ['settings'],
				],
			]],
		];
	}
}
