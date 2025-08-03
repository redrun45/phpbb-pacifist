# Pacifist

Pacifist is a plugin for phpBB forum software, intended to mitigate the effects of bad crawler bots, without blocking or throttling.
If your forum site is visited by bots that identify themselves properly, you should be looking at phpBB's built-in bot management features[^1]
and creating a robots.txt file[^2] first.

## Installation

1. Copy the extension to: `phpBB/ext/redrun45/pacifist`
2. In the ACP, go to: **Customise → Manage extensions**
3. Enable the **Pacifist Bot Mitigations** extension
4. To enable the options that require modifications to phpBB, read on:

## Custom Code

### Actively retire old tracking links

In `phpBB/phpbb/session.php`, find this bit (as of 3.3.15, starts at line 608):
```php
// Bot user, if they have a SID in the Request URI we need to get rid of it
// otherwise they'll index this page with the SID, duplicate content oh my!
if ($bot && isset($_GET['sid']))
```

1. Paste this **before** those lines:
  ```php
/**
 * Event to override SID redirect rules
 *
 * @event pacifist.sid_redirect_override
 * @var	bool	redirect_other	Whether to redirect away from SID URLs, even if the visitor is not a known bot.
 * @var	mixed	user_id
 */
$redirect_other = false;
$vars = array('redirect_other', 'user_id');
extract($phpbb_dispatcher->trigger_event('pacifist.sid_redirect_override', compact($vars)));
  ```
2. Then edit the `if` line, so that it looks like this:
  ```
if (($bot || $redirect_other) && isset($_GET['sid']))
  ```
Leave the `{` and its following lines unchanged.

### Stop tracking new Guests at all

Also in `phpB/phpbb/session.php`, find this bit (if you inserted the first snippet, around line 775):
```php
// Since we re-create the session id here, the inserted row must be unique. Therefore, we display potential errors.
```

Paste this **before** that line:
```php
/**
 * Event to alter session information before it is written to the database.
 * Setting use_dummy_session to a true-ish value will skip both writing to the database and calling the core.session_create_after event.
 *
 * @event pacifist.session_create_before_db
 * @var	array	sql_ary				Associative array of session properties
 * @var	bool	is_bot				Whether the visitor is known to be a bot (read-only)
 * @var	bool	is_registered		Whether the visitor is a registered user (read-only)
 * @var	bool	use_dummy_session	Whether to create a session that will not be saved to the database (also skips calling core.session_create_after)
 */
$is_bot = $this->data['is_bot'];
$is_registered = $this->data['is_registered'];
$use_dummy_session = false;
$vars = array('sql_ary', 'is_bot', 'is_registered', 'use_dummy_session');
extract($phpbb_dispatcher->trigger_event('pacifist.session_create_before_db', compact($vars)));
unset($session_data);
if ($use_dummy_session)
{
	$this->session_id = $this->data['session_id'] = '';
	return true;
}
```

### Require log-in to view forums

Switching to `phpBB/phpbb/user.php`, find this bit (as of 3.3.15, line 451):
```php
$this->is_setup_flag = true;
```

Paste **after** that line, and before the `return`:
```php
/**
 * Execute code at the end of user setup (late enough to trigger login_box without errors)
 *
 * @event pacifist.user_setup_after_flagged
 */
$phpbb_dispatcher->dispatch('pacifist.user_setup_after_flagged');
```

## License

Licensed under the [GNU General Public License v2](license.txt)

[^1]: https://www.phpbb.com/support/docs/en/3.0/ug/adminguide/system_spiders/
[^2]: https://www.robotstxt.org/robotstxt.html
