<?php

if (!defined('PUN_ROOT'))
{
	define('PUN_ROOT', dirname(__FILE__).'/');
	require PUN_ROOT.'include/common.php';

	if ($pun_user['g_id'] != PUN_ADMIN)
		message($lang_common['No permission']);

	echo '<style>body {font-family: "Arial", "Helvetica", sans-serif; font-size: 81.25%;}</style>';
}

global $forum_url;
if (!isset($forum_url))
{
	if (file_exists(PUN_ROOT.'include/url/Folder_based_(fancy)/forum_urls.php'))
		require PUN_ROOT.'include/url/Folder_based_(fancy)/forum_urls.php';
	else
		require MODS_DIR.'friendly-url/files/include/url/Folder_based_(fancy)/forum_urls.php';
}

// When running inside Patcher
if (defined('PATCHER_ROOT') && isset($GLOBALS['patcher']) && strtolower(get_class($GLOBALS['patcher'])) == 'patcher' && $GLOBALS['patcher']->action->command == 'RUN' && $GLOBALS['patcher']->action->code == 'gen.php')
{
	require_once PUN_ROOT.'include/friendly_url.php';
	// As we modified generate_quickjump_cache function before (using readme.txt) we have to regenerate cache
	if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
		require PUN_ROOT.'include/cache.php';
	generate_config_cache();
	generate_quickjump_cache();

	if ($GLOBALS['patcher']->actionType == 'uninstall')
		return;
	$changes = url_get_changes();

	$curReadme = $GLOBALS['patcher']->mod->id.'/files/gen.php';
	$GLOBALS['patcher']->steps[$curReadme] = urlGetSteps($changes);
}

// Running outside Patcher
elseif (!defined('PATCHER_ROOT'))
{
	if (isset($_POST['install']))
	{
		$changes = url_get_changes(true);
		foreach ($changes as $curFileName => $change_list)
			echo '<strong style="color: green">Patching file '.pun_htmlspecialchars($curFileName).'</strong>... ('.count($change_list).' changes)<br />';
		echo 'Done';

		// As we modified generate_quickjump_cache function before (using readme.txt) we have to regenerate cache
		if (!defined('FORUM_CACHE_FUNCTIONS_LOADED'))
			require PUN_ROOT.'include/cache.php';
		generate_quickjump_cache();
	}
	else
	{
		$checking_failed = false;

		$files = url_get_files();
		$info = array();
		foreach ($files as $curFile)
		{
			if (is_writable(PUN_ROOT.$curFile))
				$curInfo = '<span style="color: green">writable</span>';
			else
			{
				if (!$checking_failed)
					$checking_failed = true;
				$curInfo = '<strong style="color: #AA0000">not writable</strong>';
			}
			$info[] = pun_htmlspecialchars($curFile).'... '.$curInfo;
		}

		if (!$checking_failed)
			echo '<strong style="color: green">Friendly URL is ready to install.</strong><br />Click the following button to do it.<br /><form method="post" action=""><input type="submit" name="install" value="Install"></form>';
		else
			echo '<strong style="color: #AA0000">Checking failed, see list below.</strong><br />Refresh this page when you correct these permissions.<br /><br />';

		echo 'Details:<br />';
		echo implode('<br />'."\n", $info);
	}
}


function urlGetSteps($changes)
{
	$steps = array();
	foreach ($changes as $curFileName => $list)
	{
		$steps[] = array('command' => 'OPEN', 'code' => $curFileName);
		foreach ($list as $curChange)
		{
			$steps[] = array('command' => 'FIND', 'code' => $curChange[0]);
			$steps[] = array('command' => 'REPLACE', 'code' => $curChange[1]);
		}
	}
	return $steps;
}


function url_get_changes($save = false)
{
	global $curFile;

	$steps = $changes = $files = array();
	$files = url_get_files();

	foreach ($files as $curFileName)
	{
		$curFile = file_get_contents(PUN_ROOT.$curFileName);
		$curFile_before = $curFile;

		$curFile = urlReplaceFile($curFileName, $curFile, $changes);

		if ($save && $curFile != $curFile_before && !empty($curFile))
			file_put_contents(PUN_ROOT.$curFileName, $curFile);
	}

	return $changes;
}

function urlReplaceFile($curFileName, $curFile, &$changes)
{
	$expressions = array(
		'#(href|src|action)(=")(.*?)(".{0,30})#',
		'#(Location)(: )(.*?)(\);)#',
		'#(redirect\()(\')(.*?)(\'*[,\)].*)#',
		'#(get_base_url\(\)\.\'?/?)()(.*?\(.*?\?.*?:.*?\))([\),])#',
		'#(get_base_url\(\)\.\'?/?)()(.*?)([\),])#',
		'#(\\\\\'\'?.?get_base_url\(true\)\.\'?/?)()(.*?)(\\\\?\'?[\),])#',
		'#(pun_htmlspecialchars\(get_base_url\(true\)\.\'?/?)()(.*?)(\))#',
	);
	$manual_changes = array();

	if (basename($curFileName) != 'functions.php') // do not touch function paginate()
		$expressions[] = '#(paginate\()(.*?,.*?,\s*\'?)(.*)(\'?\)[;\.].*)#';

	if (basename($curFileName) == 'extern.php')
	{
		$expressions[] = '#(\$pun_config\[\'o_base_url\'\]\.\'?/?)()([^"]*?)(\'?[,;])#';
		$expressions[] = '#(get_base_url\(true\)\.\'?/?)()([^"]*?)(\'?[,;])#';
		$expressions[] = '#(\'link\'\s*=>\s*)()(\'?/?[^"]*?\'?)([,;])#'; // 'link' => '/index.php',
		$expressions[] = '#(\[\'uri\'\]\s*=\s*)()(\'?/?[^"]*?\'?)([,;])#'; // ['uri'] = '/index.php',

		$manual_changes[] = array(
			'\'/viewtopic.php?id=\'.$cur_topic[\'id\'].($order_posted ? \'\' : \'&action=new\')',
			'forum_link($GLOBALS[\'forum_url\'][\'topic\'.($order_posted ? \'_new_posts\' : \'\')], array($cur_topic[\'id\'], sef_friendly($cur_topic[\'subject\'])))',
		);
	}

	// Do not define PUN_ROOT twice
	if (basename($curFileName) != 'rewrite.php' && !preg_match('#if \(!defined\(\'PUN_ROOT\'\)\)\s*define\(\'PUN_ROOT\',#si', $curFile) && strpos($curFile, 'define(\'PUN_ROOT\',') !== false)
		$manual_changes[] = array('define(\'PUN_ROOT\',', 'if (!defined(\'PUN_ROOT\'))'."\n\t".'define(\'PUN_ROOT\',');

	// login.php referer fix
	if (basename($curFileName) == 'login.php')
	{
		$manual_changes[] = array(
			'if (!empty($_SERVER[\'HTTP_REFERER\']))'."\n".'{'."\n\t".'$referrer = parse_url($_SERVER[\'HTTP_REFERER\']);',
			'if (!empty($_SERVER[\'HTTP_REFERER_REWRITTEN\']))'."\n".'{'."\n\t".'$referrer = parse_url($_SERVER[\'HTTP_REFERER_REWRITTEN\']);'
		);
		$manual_changes[] = array(
			'if ($referrer[\'host\'] == $valid[\'host\'] && preg_match(\'%^\'.preg_quote($valid[\'path\'], \'%\').\'/(.*?)\.php%i\', $referrer[\'path\']))'."\n\t\t".'$redirect_url = $_SERVER[\'HTTP_REFERER\'];',
			'if ($referrer[\'host\'] == $valid[\'host\'] && preg_match(\'%^\'.preg_quote($valid[\'path\'], \'%\').\'/%i\', $referrer[\'path\']))'."\n\t\t".'$redirect_url = $_SERVER[\'HTTP_REFERER\'];',
		);
	}
	foreach ($manual_changes as $curChange)
	{
		$curFile = str_replace($curChange[0], $curChange[1], $curFile);

		if (!isset($changes[$curFileName]))
			$changes[$curFileName] = array();

		$changes[$curFileName][] = array($curChange[0], $curChange[1]);
	}

	foreach ($expressions as $exp)
	{
		preg_match_all($exp, $curFile, $matches, PREG_SET_ORDER);
		$curChanges = array();
		foreach ($matches as $match)
		{
			$replace = url_replace($match, $curFile, $curFileName);
			if (!$replace)
				continue;

			$pos = strpos($curFile, $match[0]);
			$curFile = substr_replace($curFile, $replace, $pos, strlen($match[0]));

			$curChanges[$pos] = array($match[0], $replace);
		}

		if (count($curChanges) == 0)
			continue;

		if (!isset($changes[$curFileName]))
			$changes[$curFileName] = array();

		foreach ($curChanges as $pos => $curChange)
			$changes[$curFileName][$pos] = $curChange;
	}

	if (isset($changes[$curFileName]))
	{
		ksort($changes[$curFileName]);
		$changes[$curFileName] = array_values($changes[$curFileName]);
	}

	return $curFile;
}

function url_get_files()
{
	// Get the files of PUN_ROOT and include directory (get include recursive)
	return array_merge(url_read_dir('.'), url_read_dir('include', true));
}


function url_read_dir($directory, $recursive = false)
{
	$files = array();
	$d = dir($directory);
	while ($f = $d->read())
	{
		if (substr($f, 0, 1) != '.')
		{
			if (is_dir($directory.'/'.$f))
			{
				if ($recursive)
					$files = array_merge($files, url_read_dir($directory.'/'.$f, $recursive));
			}
			elseif (substr($f, -4) == '.php' && substr($f, 0, 6) != 'admin_' && !in_array($f, array('config.php', 'gen.php', 'install.php', 'install_mod.php', 'db_update.php', 'patcher_config.php')))
				$files[] = ($directory == '.' ? '' : $directory.'/').$f;
		}
	}
	return $files;
}


function url_replace($matches, $curFile, $curFileName)
{
	global $forum_url;

	$ending = $tmp_action = '';
	$rewrite = true;
	$paginate = false;

	$url = $matches[3];

	// Nothing to do?
	if (preg_match('/^[a-z]+:/', $url) || // http://
		preg_match('/^\$[0-9]+/', $url) || // $1, $2 - used in include/parser.php
		strpos($matches[0], 'forum_link') !== false || // forum_link()
		strpos($matches[0], 'forum_sublink') !== false || // forum_sublink()
		strpos($matches[3], 'get_base_url') !== false || // get_base_url()
		strpos($matches[0], '[\'forum_url\']') !== false || // $GLOBALS['forum_url'] (paginate function)
		$matches[3] == '$pun_config[\'o_base_url\'].\'/' || // base_url (eg. used in help.php)
		$matches[3] == '$pun_config[\'o_base_url\']' || // base_url
		basename($curFileName) == 'common.php' && $matches[3] == 'install.php') // exclude install.php link in include/common.php
		return false;

	if (strpos($matches[1], 'pun_htmlspecialchars(get_base_url(true)') !== false)
		$matches[4] = '';

	if (strpos($matches[1], '$pun_config[\'o_base_url\'].') !== false || strpos($matches[1], 'get_base_url') !== false)
		$matches[1] = '';

	// Do some url cleaning
	$url = str_replace('$pun_config[\'o_base_url\'].\'/', '\'', $url);
	$url = preg_replace('#pun_htmlspecialchars\(\'?/?(.*)\)#', '$1', $url);
	$url = str_replace(array(
		'$pun_config[\'o_base_url\']',
		'pun_htmlspecialchars(get_base_url(true)).',
		'pun_htmlspecialchars(get_base_url()).',
		'get_base_url(true).',
		'get_base_url(true) ?>',
		'get_base_url().',
		'pun_htmlspecialchars(\'/\')',
	), '', $url);

	$url = preg_replace('/<\?php echo\s*/', '\'.', $url);
	$url = preg_replace('/\s*\?>/', '.\'', $url);
	$url = ltrim($url, '\'./');
	$url = trim($url);

	if (($pos = strpos($url, '&p=\'.')) !== false || ($pos = strpos($url, '?p=\'.')) !== false)
	{
		$paginate = substr($url, $pos + 5);
		if (($pos = strpos($paginate, ':')) !== false)
			$paginate = substr($paginate, 0, $pos);
		else
			$paginate = substr($paginate, 0, strpos($paginate, '.'));

		$paginate = trim($paginate);

		if (strpos($paginate, 'intval(') !== false)
			$paginate = preg_replace('#intval\((.*)\)#', '$1', $paginate);

		if (strpos($url, 'isset('.$paginate.')') !== false)
			$url = substr($url, 0, strpos($url, 'isset('.$paginate.')'));
		elseif (strpos($url, $paginate) !== false)
			$url = substr($url, 0, strpos($url, $paginate));

		$url = rtrim($url, '(');
	}

	if ((strpos($url, 'viewtopic.php') !== false || strpos($url, 'help.php') !== false) && strpos($url, '#') !== false)
	{
		$url = substr($url, 0, strpos($url, '#'));
		if ($url == '')
			return false;
	}

	if ($matches[1] == 'Location' && substr($url, -1) == '\'')
		$url = substr($url, 0, -1);

	// Determine that current url is inside html tags
	$is_html = false;

	// We are checking orginal line from $curFile
	if (preg_match('#\n.*?'.preg_quote($matches[0], '#').'#', $curFile, $l_matches) && substr(trim($l_matches[0]), 0, 1) == '<')
		$is_html = true;

	if ($is_html)
	{
		$tags = preg_split('/(<\?php|\?>)/', $l_matches[0], -1, PREG_SPLIT_DELIM_CAPTURE);

		foreach ($tags as $key => $tag)
		{
			$str = $matches[3];
			if (strpos($matches[3], '<'))
				$str = substr($matches[3], 0, strpos($matches[3], '<'));

			if ($str != '' && strpos($tag, $str) !== false) // We got it :)
			{
				if ($key - 1 > 0 && isset($tags[$key - 1]))
					$is_html = ($tags[$key - 1] == '?>');
			}
		}
	}

	$args = array();
	$query = array();

	$url_parts = explode('?', $url);

	$link = $url_parts[0];

	$link = substr($link, 0, strpos($link, '.php'));
	$link = str_replace('view', '', $link);

	// Parse url for $_GET values
	if (isset($url_parts[1]))
	{
		$query_string = $url_parts[1];

		$params = array();
		$query_string = str_replace('&amp;', '&', $query_string);

		$query_items = explode('&', $query_string);
		foreach ($query_items as $item)
		{
			$value = explode('=', $item);
			$query[$value[0]] = isset($value[1]) ? $value[1] : '';
		}
	}

	// Convert file name to its equivalent of $forum_url variable
	if ($link == 'profile')
	{
		if (isset($query['section']))
		{
			$link = 'profile_'.$query['section'];
			unset($query['section']);
		}
		elseif (isset($query['action']))
		{
			if ($query['action'] == 'change_pass')
				$link = 'change_password';
			elseif (in_array($query['action'], array('change_email', 'upload_avatar', 'delete_avatar')))
				$link = $query['action'];

			if (isset($query['key']))
				$link .= '_key';

			unset($query['action']);
		}
		elseif (isset($query['id']) && count($query) == 1)
			$link = 'user';
	}

	elseif ($link == 'post')
	{
		if (isset($query['tid']))
		{
			if (isset($query['qid']))
				$link = 'quote';
			else
				$link = 'new_reply';
		}
		elseif (isset($query['fid']))
			$link = 'new_topic';

		if (isset($query['action'])) // don't rewrite post.php?action=*
		{
			$tmp_action = $query['action'];
			unset($query['action']);
		}
	}

	elseif ($link == 'topic')
	{
		if (count($query) > 2)
			$rewrite = false;
		elseif (isset($query['pid']))
			$link = 'post';
		else
		{
			$var = '$cur_topic';
			// Get variable name from id (eg. $cur_search['id'])
			if (isset($query['id']) && preg_match('#(\$[a-zA-Z0-9_]+)\[#', $query['id'], $m))
				$var = $m[1];
			elseif (basename($curFileName) == 'post.php')
				$var = '$cur_posting';

			$var .= '[\'subject\']';
			if ($curFileName == 'help.php' && isset($query['id']) && $query['id'] == 1)
				$var = '$lang_help[\'Test topic\']';

			$query['subject'] = '(isset('.$var.') ? sef_friendly('.$var.') : sef_name(\'t\', '.printable_var($query['id']).'))';

			if ($curFileName == 'include/parser.php')
				$query['subject'] = 'sef_name(\'t\', '.printable_var($query['id']).')';
		}

		if (isset($query['action']))
		{
			if ($query['action'] == 'new')
				$link = 'topic_new_posts';
			if (trim($query['action'], '\'') == 'last') // ?action=last'
				$link = 'topic_last_post';

			unset($query['action']);
		}
	}

	elseif ($link == 'forum')
	{
		if (isset($query['id']))
		{
			$var = '$cur_forum[\'forum_name\']';
			// Get variable name from id (eg. $cur_search['id'])
			if (preg_match('#(\$[a-zA-Z0-9_]+)(\[\'.*?\'\])#', $query['id'], $m))
			{
				if (isset($m[2]) && $m[2] == '[\'parent_forum_id\']')
					$var = $m[1].'[\'parent_forum\']';
				else
					$var = $m[1].'[\'forum_name\']';
			}
			if ($curFileName == 'help.php' && isset($query['id']) && $query['id'] == 1)
				$var = '$lang_help[\'Test forum\']';

			$query['name'] = '(isset('.$var.') ? sef_friendly('.$var.') : sef_name(\'f\', '.printable_var($query['id']).'))';
			if ($curFileName == 'include/parser.php')
				$query['name'] = 'sef_name(\'f\', '.printable_var($query['id']).')';
		}
		else
			$rewrite = false;
	}

	elseif ($link == 'search')
	{
		if (isset($query['search_id']))
			$link .= '_results';

		elseif (isset($query['action']))
		{
			if (substr($query['action'], 0, 2) == '\'.')
				$link = 'pun_htmlspecialchars(str_replace(\'show_\', \'search_\', $search_type[1]))';
			elseif ($query['action'] == 'new' || $query['action'] == 'show_new')
			{
				$link .= '_new';
				if (isset($query['fid']))
					$link .= '_forum';
			}
			elseif ($query['action'] == 'show_recent')
				$link .= '_recent';
			elseif ($query['action'] == 'show_replies')
				$link .= '_replies';
			elseif ($query['action'] == 'show_unanswered')
				$link .= '_unanswered';
			elseif ($query['action'] == 'show_subscriptions')
			{
				$link .= '_subscriptions';
				if (!isset($query['user_id']))
					$query['user_id'] = '$pun_user[\'id\']';
			}
			elseif ($query['action'] == 'show_user_posts')
			{
				$link .= '_user_posts';
				if (!isset($query['user_id']))
					$query['user_id'] = '$pun_user[\'id\']';
			}
			elseif ($query['action'] == 'show_user_topics')
			{
				$link .= '_user_topics';
				if (!isset($query['user_id']))
					$query['user_id'] = '$pun_user[\'id\']';
			}
			elseif ($query['action'] == 'search')
				$rewrite = false;

			unset($query['action']);
		}
	}
	elseif ($link == 'misc')
	{
		if (isset($query['action']))
		{
			if ($query['action'] == 'markread')
				$link = 'mark_read';
			elseif ($query['action'] == 'markforumread')
				$link = 'mark_forum_read';
			elseif ($query['action'] == 'rules')
				$link = 'rules';

			if (in_array($query['action'], array('subscribe', 'unsubscribe')))
			{
				$link = $query['action'];
				if (isset($query['tid']))
					$link .= '_topic';
				elseif (isset($query['fid']))
					$link .= '_forum';
			}

			unset($query['action']);
		}

		elseif (isset($query['report']))
			$link = 'report';

		elseif (isset($query['email']))
			$link = 'email';
	}

	elseif ($link == 'userlist')
	{
		$link = 'users';
		if (count($query) > 0)
		{
			// We need orginal order
			$query = array(
				'username' => isset($query['username']) ? $query['username'] : '',
				'show_group' => isset($query['show_group']) ? $query['show_group'] : -1,
				'sort_by' => isset($query['sort_by']) ? $query['sort_by'] : 'username',
				'sort_dir' => isset($query['sort_dir']) ? $query['sort_dir'] : 'ASC',
			);
			$link .= '_browse';
		}
	}

	elseif ($link == 'login' && isset($query['action']))
	{
		if ($query['action'] == 'in')
			$link = 'login';
		elseif ($query['action'] == 'out')
			$link = 'logout';
		elseif ($query['action'] == 'forget')
			$link = 'request_password';

		if ($matches[1] == 'action') // don't rewrite login.php?action=in for <form action="'.forum_link('*').'"> because there isn't such link in $forum_url
			$tmp_action = $query['action'];

		unset($query['action']);
	}

	elseif ($link == 'register' && isset($query['action']))
	{
		if ($matches[1] == 'action') // don't rewrite register.php?action=in for <form action="'.forum_link('*').'"> because there isn't such link in $forum_url
		{
			$tmp_action = $query['action'];
			unset($query['action']);
		}
	}

	elseif ($link == 'extern' && isset($query['action']) && $query['action'] == 'feed')
	{
		if (isset($query['fid']))
			$link = 'forum';
		elseif (isset($query['tid']))
			$link = 'topic';
		else
			$link = 'index';

		$link .= '_'.$query['type'];

		unset($query['action']);
		unset($query['type']);
	}

	elseif ($link == 'moderate')
	{
		$link = 'moderate';
		if (isset($query['get_host']))
			$link = 'get_host';
		elseif (isset($query['move_topics']))
			$link = 'move';
		elseif (isset($query['open']))
			$link = 'open';
		elseif (isset($query['close']))
			$link = 'close';
		elseif (isset($query['stick']))
			$link = 'stick';
		elseif (isset($query['unstick']))
			$link = 'unstick';

		elseif (isset($query['tid']))
			$link .= '_topic';
		elseif (isset($query['fid']))
			$link .= '_forum';
	}

	elseif ($link == 'help' && $matches[3] != '' && strpos($matches[3], '#'))
		$ending = substr($matches[3], strpos($matches[3], '#'));


	foreach ($query as $key => $value)
	{
		$value = trim($value, "'.");
		$value = trim($value);

		if ($key == 'p')
			continue;

		if ((substr($value, 0, 1) != '$' && strpos($value, '(') === false) || substr($value, 0, 1) == '$' && is_numeric(substr($value, 1)))
			$value = '\''.$value.'\'';

		$args[] = $value;
	}

	if ((isset($forum_url[$link]) || strpos($link, 'profile_') !== false || strpos($link, 'str_replace') !== false) && $rewrite) // $link = profile_'.$section or search_$type
	{
		if (strpos($link, 'str_replace') !== false)
			$link_p = $link;
		else
			$link_p = '\''.$link.'\'';

		// Using $GLOBALS, cause the link could be inside function
		$link_p = '$GLOBALS[\'forum_url\']['.$link_p.']';

		if (strpos($link, 'profile_') !== false) // there might be .'' at end
			$link_p = str_replace('.\'\'', '', $link_p);

		if ($tmp_action != '' && $rewrite)
			$link_p .= '.\'?action='.rtrim($tmp_action, '\'').'\'';

		if (count($args) == 1)
			$link_p .= ', '.$args[0];
		elseif (count($args) > 1)
			$link_p .= ', array('.implode(', ', $args).')';
	}
	else
	{
		$link_p = correct_apostr($url);

		// Add temp action (after #)
		if ($tmp_action != '' && $rewrite)
			$link_p .= '.\'?action='.rtrim($tmp_action, '\'').'\'';
	}

	if ($link_p == '')
		return false;

	if ($matches[1] == 'paginate(')
		$matches[2] = rtrim($matches[2], "'.");

	elseif (strpos($matches[1], '\'link\'') !== false) // extern.php
		$link_p = trim($link_p, '\'');

	else
	{
		if ($paginate)
		{
			$forum_url_var = $args = '';
			if (strpos($link_p, ',') !== false)
			{
				$forum_url_var = substr($link_p, 0, strpos($link_p, ','));
				$args = substr($link_p, strpos($link_p, ','));
			}
			else
				$forum_url_var = $link_p;

			if ($paginate != '$p')
				$paginate = '($page = intval('.$paginate.')) > 1 ? $page : 1';
			$link_p = 'forum_sublink('.$forum_url_var.', $GLOBALS[\'forum_url\'][\'page\'], '.$paginate.$args.')';
		}
		else
			$link_p = 'forum_link('.$link_p.')';

		if ($matches[1] != '')
		{
			if ($is_html) // html
				$link_p = '<?php echo '.$link_p.' ?>';
			else // php
				$link_p = '\'.'.$link_p.'.\'';
		}
	}

	if ($matches[1] == 'Location') // header(Location: '.forum_link('*')) function
		$link_p = rtrim($link_p, "'.");

	if (strpos($matches[1], '[\'uri\']') !== false) // extern.php
		$link_p = trim($link_p, '\'.');

	if ($matches[1] == 'redirect(') // redirect function
	{
		$link_p = trim($link_p, "'.");
		if ($matches[2] == '\'')
			$matches[2] = '';

		if (substr($matches[4], 0, 1) == '\'')
			$matches[4] = substr($matches[4], 1);
	}
	if ($matches[1] == '' && substr($matches[4], 0, 1) == '\'')
		$matches[4] = substr($matches[4], 1);

	if (strpos($matches[0], 'get_base_url(') !== false && substr($matches[4], 0, 2) == '\\\'')
	{
		$matches[4] = substr($matches[4], 2);
		$link_p = str_replace('\'', '\\\'', $link_p);
	}

	$result = $matches[1].$matches[2].$link_p.$ending.$matches[4];
	$result = str_replace(array('\'\'.', '.\'\''), '', $result);

	if ($result == $matches[0])
		return false;

	return $result;
}

function printable_var($var)
{
	if (substr($var, 0, 1) == '$' && is_numeric(substr($var, 1)))
		return '\''.trim($var, "'.").'\'';

	return (strpos($var, '$') !== false || is_numeric(trim($var, "'."))) ? trim($var, "'.") : $var;
}

function correct_apostr($str)
{
	$str = trim($str, '\'.');
	$parts = preg_split('#([\'"]+|(\$[a-zA-Z0-9_\[\]\']+)|PUN_[^\.]+|[a-zA-Z0-9_]+\(.*?\))\s*\.\s*([\'"]+|(\$[a-zA-Z0-9_\[\]\']+)|PUN_[^\.]+|[a-zA-Z0-9_]+\(.*?\))#i', $str, -1, PREG_SPLIT_DELIM_CAPTURE);

	if (count($parts) == 0 || trim($str) == '')
		return $str;

	$first = $parts[0];
	// Remove first value if empty
	if (trim($parts[0]) == '')
	{
		unset($parts[0]);
		$first = count($parts) > 0 ? $parts[1] : '';
	}

	// Remove last value if empty
	if (trim($parts[count($parts) - 1]) == '')
		unset($parts[count($parts) - 1]);

	if (strpos($first, '\'') !== false)
		$first = substr($first, 0, strpos($first, '\''));

	if ((substr($first, 0, 1) != '$' || (substr($first, 0, 1) == '$' && is_numeric(substr($first, 1, 1)))) && // variable (allow $1)
		substr($first, 0, 1) != '\'' && // string
		substr($first, 0, 4) != 'PUN_' && // constant
		strpos($first, '(') === false) // function
		$str = '\''.$str;

	$last = $parts[count($parts) - 1];
	if ((count($parts) > 1 && substr($parts[count($parts) - 2], 0, 1) == '\'') || // is string (apostrof in last-1 index)
		(substr(ltrim($last, '.'), 0, 1) != '$' && // variable
		substr($last, 0, 4) != 'PUN_' && // constant
		strpos($last, '(') === false && // function
		strpos($last, ')') === false)) // function
		$str .= '\'';

	return $str;
}
