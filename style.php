<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';

require_once($phpbb_root_path . 'includes/startup.php');

if (!defined('PHPBB_INSTALLED'))
{
	exit;
}

if (isset($_GET['mtime']))
{
	$mtime = intval($_GET['mtime']);
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') == $mtime)
	{
		http_response_code(304);
		die();
	}
	header('Etag: "' . $mtime . '"');
}

require_once($phpbb_root_path . 'includes/acm/acm_' . $acm_type . '.php');
require_once($phpbb_root_path . 'includes/cache.php');
require_once($phpbb_root_path . 'includes/db/mysql.php');
require_once($phpbb_root_path . 'includes/constants.php');
require_once($phpbb_root_path . 'includes/functions.php');

$style_id = request_var('id', 0);
$lang = request_var('lang', '');

if (!$style_id || !$lang)
{
	http_response_code(404);
	die();
}

$db = new dbal_mysql();
$cache = new phpbb_cache();

if (!@$db->sql_connect($dbhost, $dbuser, $dbpasswd, $dbname, $dbport, false))
{
	http_response_code(503);
	die();
}
unset($dbpasswd);

$config = $cache->obtain_config();

$sql = 'SELECT s.style_id, c.theme_id, c.theme_data, c.theme_path, c.theme_name, c.theme_mtime, i.*, t.template_path
	FROM ' . STYLES_TABLE . ' s, ' . STYLES_TEMPLATE_TABLE . ' t, ' . STYLES_THEME_TABLE . ' c, ' . STYLES_IMAGESET_TABLE . ' i
	WHERE s.style_id = ' . $style_id . '
		AND t.template_id = s.template_id
		AND c.theme_id = s.theme_id
		AND i.imageset_id = s.imageset_id';
$result = $db->sql_query($sql, 300);
$theme = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$theme)
{
	http_response_code(404);
	die();
}

$sql = 'SELECT lang_dir
	FROM ' . LANG_TABLE . "
	WHERE lang_iso = '" . $db->sql_escape($lang) . "'";
$result = $db->sql_query($sql);
$lang = $db->sql_fetchfield('lang_dir');
$db->sql_freeresult($result);

if (!$lang)
{
	http_response_code(404);
	die();
}

$user_image_lang = (file_exists($phpbb_root_path . 'styles/' . $theme['imageset_path'] . '/imageset/' . $lang) ? $lang : $config['default_lang']);

// Same query in session.php
$sql = 'SELECT *
	FROM ' . STYLES_IMAGESET_DATA_TABLE . '
	WHERE imageset_id = ' . $theme['imageset_id'] . "
	AND image_filename <> ''
	AND image_lang IN ('" . $db->sql_escape($user_image_lang) . "', '')";
$result = $db->sql_query($sql, 3600);

$img_array = array();
while ($row = $db->sql_fetchrow($result))
{
	$img_array[$row['image_name']] = $row;
}
$db->sql_freeresult($result);

// gzip_compression
if ($config['gzip_compress'] && @extension_loaded('zlib') && !headers_sent())
{
	ob_start('ob_gzhandler');
}

// Expire time of seven days if not recached
$expire_time = 7*86400;
$recache = false;

// Re-cache stylesheet data if necessary
if ($config['load_tplcompile'] || empty($theme['theme_data']))
{
	$recache = (empty($theme['theme_data'])) ? true : false;
	$update_time = time();

	// We test for stylesheet.css because it is faster and most likely the only file changed on common themes
	if (!$recache && $theme['theme_mtime'] < @filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css'))
	{
		$recache = true;
		$update_time = @filemtime("{$phpbb_root_path}styles/" . $theme['theme_path'] . '/theme/stylesheet.css');
	}
	else if (!$recache)
	{
		$last_change = $theme['theme_mtime'];
		$dir = @opendir("{$phpbb_root_path}styles/{$theme['theme_path']}/theme");

		if ($dir)
		{
			while (($entry = readdir($dir)) !== false)
			{
				if (substr(strrchr($entry, '.'), 1) == 'css' && $last_change < @filemtime("{$phpbb_root_path}styles/{$theme['theme_path']}/theme/{$entry}"))
				{
					$recache = true;
					break;
				}
			}
			closedir($dir);
		}
	}
}

if ($recache)
{
	require_once($phpbb_root_path . 'includes/acp/acp_styles.php');

	$theme['theme_data'] = acp_styles::db_theme_data($theme);
	$theme['theme_mtime'] = $update_time;

	// Save CSS contents
	$sql_ary = array(
		'theme_mtime'	=> $theme['theme_mtime'],
		'theme_data'	=> $theme['theme_data']
	);

	$sql = 'UPDATE ' . STYLES_THEME_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $sql_ary) . "
		WHERE theme_id = {$theme['theme_id']}";
	$db->sql_query($sql);

	$cache->destroy('sql', STYLES_THEME_TABLE);
}

header('Content-Type: text/css; charset=UTF-8');

// Only set the expire time if the theme changed data is older than 5 minutes - to cope with changes from the ACP
if ($recache || $theme['theme_mtime'] > (time() - 300))
{
	header('Cache-Control: no-cache');
}
else
{
	header('Cache-Control: public, max-age=' . $expire_time);
}

// Parse Theme Data
$replace = array(
	'{T_THEME_PATH}'			=> "{$phpbb_root_path}styles/" . rawurlencode($theme['theme_path']) . '/theme',
	'{T_TEMPLATE_PATH}'			=> "{$phpbb_root_path}styles/" . rawurlencode($theme['template_path']) . '/template',
	'{T_IMAGESET_PATH}'			=> "{$phpbb_root_path}styles/" . rawurlencode($theme['imageset_path']) . '/imageset',
	'{T_IMAGESET_LANG_PATH}'	=> "{$phpbb_root_path}styles/" . rawurlencode($theme['imageset_path']) . '/imageset/' . $user_image_lang,
	'{T_STYLESHEET_NAME}'		=> $theme['theme_name'],
	'{S_USER_LANG}'				=> $lang,
);

$theme['theme_data'] = str_replace(array_keys($replace), array_values($replace), $theme['theme_data']);

$matches = array();
preg_match_all('#\{IMG_([A-Za-z0-9_]*?)_(WIDTH|HEIGHT|SRC)\}#', $theme['theme_data'], $matches);

$imgs = $find = $replace = array();
if (isset($matches[0]) && sizeof($matches[0]))
{
	foreach ($matches[1] as $i => $img)
	{
		$img = strtolower($img);
		$find[] = $matches[0][$i];

		if (!isset($img_array[$img]))
		{
			$replace[] = '';
			continue;
		}

		if (!isset($imgs[$img]))
		{
			$img_data = &$img_array[$img];
			$imgsrc = ($img_data['image_lang'] ? $img_data['image_lang'] . '/' : '') . $img_data['image_filename'];
			$imgs[$img] = array(
				'src'		=> $phpbb_root_path . 'styles/' . rawurlencode($theme['imageset_path']) . '/imageset/' . $imgsrc,
				'width'		=> $img_data['image_width'],
				'height'	=> $img_data['image_height'],
			);
		}

		switch ($matches[2][$i])
		{
			case 'SRC':
				$replace[] = $imgs[$img]['src'];
			break;

			case 'WIDTH':
				$replace[] = $imgs[$img]['width'];
			break;

			case 'HEIGHT':
				$replace[] = $imgs[$img]['height'];
			break;
		}
	}

	if (sizeof($find))
	{
		$theme['theme_data'] = str_replace($find, $replace, $theme['theme_data']);
	}
}

echo $theme['theme_data'];

if (!empty($cache))
{
	$cache->unload();
}
$db->sql_close();
