<?php
/**
 *
 * Pacifist. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2025, redrun45
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace redrun45\pacifist\acp;

/**
 * Pacifist ACP module info.
 */
class main_info
{
	public function module()
	{
		return [
			'filename'	=> '\redrun45\pacifist\acp\main_module',
			'title'		=> 'ACP_PACIFIST_TITLE',
			'modes'		=> [
				'settings'	=> [
					'title'	=> 'ACP_PACIFIST',
					'auth'	=> 'ext_redrun45/pacifist && acl_a_board',
					'cat'	=> ['ACP_PACIFIST_TITLE'],
				],
			],
		];
	}
}
