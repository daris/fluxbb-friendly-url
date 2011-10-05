<?php
/**
 * Patcher affected file list
 */


function furl_get_affected_files()
{
	$files = array();
	$directories = array('.', 'include');
	if (file_exists(PUN_ROOT.'include/attach'))
		$directories[] = 'include/attach';
	
	foreach ($directories as $directory)
		$files = array_merge($files, furl_read_dir($directory));

	return $files;
}

function furl_read_dir($directory, $recursive = false)
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
					$files = array_merge($files, furl_read_dir($directory.'/'.$f, $recursive));
			}
			elseif (substr($f, -4) == '.php' && !in_array($f, array('config.php', 'gen.php', 'install.php', 'install_mod.php', 'db_update.php', 'patcher_config.php')))
				$files[] = ($directory == '.' ? '' : $directory.'/').$f;
		}
	}
	return $files;
}


return furl_get_affected_files();
