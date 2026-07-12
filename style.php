<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

define('IN_PHPBB', true);
if (!defined('PHPBB_ROOT_PATH')) { define('PHPBB_ROOT_PATH', './'); }
require_once(PHPBB_ROOT_PATH . 'includes/startup.php');

if (!defined('PHPBB_INSTALLED'))
{
	exit;
}

// Always respond that cached data with this particular mtime of stylesheet.css is not stale.
if (($mtime = (int) ($_GET['mtime'] ?? 0)) && $mtime > 999999999 && $mtime == $_GET['mtime'])
{
	// Reverse proxy appends "-gzip" to our etag, so we should match etag using strpos instead of strict equality.
	if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && strpos(trim($_SERVER['HTTP_IF_NONE_MATCH'], '"'), (string) $mtime) === 0)
	{
		http_response_code(304); // Not Modified
		die();
	}
	header('Etag: "' . $mtime . '"');
}

require_once(PHPBB_ROOT_PATH . 'includes/acm/acm_' . $acm_type . '.php');
require_once(PHPBB_ROOT_PATH . 'includes/cache.php');
require_once(PHPBB_ROOT_PATH . 'includes/db/mysql.php');
require_once(PHPBB_ROOT_PATH . 'includes/constants.php');
require_once(PHPBB_ROOT_PATH . 'includes/functions.php');

$style_id = request_var('id', 0);
$lang_code = request_var('lang', '');

if (!$style_id || !$lang_code)
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

$sql = 'SELECT theme_dir, imageset_dir
	FROM ' . STYLES_TABLE . '
	WHERE style_id = ' . $style_id;
$result = $db->sql_query($sql, 300);
$theme = $db->sql_fetchrow($result);
$db->sql_freeresult($result);

if (!$theme)
{
	http_response_code(404);
	die();
}

$sql = 'SELECT lang_code
	FROM ' . LANG_TABLE . "
	WHERE lang_code = '" . $db->sql_escape($lang_code) . "'";
$result = $db->sql_query($sql);
$lang_code = $db->sql_fetchfield('lang_code');
$db->sql_freeresult($result);

if (!$lang_code)
{
	http_response_code(404);
	die();
}

if (!file_exists(PHPBB_ROOT_PATH . 'styles/' . $theme['imageset_dir'] . '/imageset/' . $lang_code))
{
	$lang_code = $config['default_lang_code'];
}

$theme_dir_path = PHPBB_ROOT_PATH . 'styles/' . $theme['theme_dir'] . '/theme/';
$theme_css_path = $theme_dir_path . 'stylesheet.css';
$theme_mtime = @filemtime($theme_css_path);

if (!$theme_mtime)
{
	http_response_code(503);
	die();
}

$cache_key = "_style_{$theme['theme_dir']}_{$theme['imageset_dir']}_theme_{$lang_code}";
$cache_data = $cache->get($cache_key) ?: [];

if (($cache_data['mtime'] ?? 0) == $theme_mtime)
{
	$theme_data = $cache_data['data'];
}
else
{
	$theme_data = file_get_contents($theme_css_path);

	$replace = [
		'{T_THEME_PATH}'            => PHPBB_ROOT_PATH . 'styles/' . rawurlencode($theme['theme_dir']) . '/theme',
		'{T_IMAGESET_PATH}'         => PHPBB_ROOT_PATH . 'styles/' . rawurlencode($theme['imageset_dir']) . '/imageset',
		'{T_IMAGESET_LANG_PATH}'    => PHPBB_ROOT_PATH . 'styles/' . rawurlencode($theme['imageset_dir']) . '/imageset/' . $lang_code,
		'{S_USER_LANG}'             => $lang_code,
	];

	$theme_data = str_replace(array_keys($replace), array_values($replace), $theme_data);

	$matches = [];
	preg_match_all('#\{IMG_([A-Za-z0-9_]*?)_(WIDTH|HEIGHT|SRC)\}#', $theme_data, $matches);
	$img_array = $cache->obtain_style_imageset($theme['imageset_dir'], $lang_code);
	$imgs = $find = $replace = [];

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
				$imgs[$img] = [
					'src'       => PHPBB_ROOT_PATH . 'styles/' . rawurlencode($theme['imageset_dir']) . '/imageset/' . $imgsrc,
					'width'     => $img_data['image_width'],
					'height'    => $img_data['image_height'],
				];
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
			$theme_data = str_replace($find, $replace, $theme_data);
		}
	}

	$cache->put($cache_key, [
		'mtime' => $theme_mtime,
		'data'  => $theme_data,
	]);
}

$cache->unload();
$db->sql_close();

header('Content-Type: text/css; charset=UTF-8');
header('Cache-Control: public, max-age=' . (7*86400));

if ($config['gzip_compress'] && @extension_loaded('zlib') && !headers_sent())
{
	ob_start('ob_gzhandler');
}

echo $theme_data;
