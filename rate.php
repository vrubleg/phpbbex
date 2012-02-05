<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);

$rate		= request_var('rate', 'none');
$post_id	= request_var('post_id', 0);

try
{
	if (!$post_id) throw new exception('post_id is required');

	// Get current user rate
	$sql = 'SELECT *
		FROM ' . POST_RATES_TABLE . '
		WHERE user_id = ' . $user->data['user_id'] . '
			AND post_id = ' . $post_id;
	$result = $db->sql_query($sql);
	$user_rate = $db->sql_fetchrow($result);
	if (!$user_rate) $user_rate = array('rate' => 0, 'rate_time' => 0);

	// Get post
	$sql = 'SELECT *
		FROM ' . POSTS_TABLE . '
		WHERE post_id = ' . $post_id;
	$result = $db->sql_query($sql);
	$post = $db->sql_fetchrow($result);

	$can = false;
	switch ($rate)
	{
		case 'minus':
			$can = $config['rate_enabled'] && ($user->data['user_id'] != ANONYMOUS) && ($user->data['user_id'] != $post['poster_id']) && ($config['rate_time'] > 0 ? $config['rate_time'] + $post['post_time'] > time() : true) && ($user_rate['rate'] >= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_negative'] ? $user_rate['rate'] != 0 : true);
			if ($can) $user_rate['rate']--;
			if ($user_rate['rate'] < -1) $user_rate['rate'] = -1;
		break;
		case 'plus':
			$can = $config['rate_enabled'] && ($user->data['user_id'] != ANONYMOUS) && ($user->data['user_id'] != $post['poster_id']) && ($config['rate_time'] > 0 ? $config['rate_time'] + $post['post_time'] > time() : true) && ($user_rate['rate'] <= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_positive'] ? $user_rate['rate'] != 0 : true);
			if ($can) $user_rate['rate']++;
			if ($user_rate['rate'] > 1) $user_rate['rate'] = 1;
		break;
	}

	if ($can)
	{
		if ($user_rate['rate'] == 0)
		{
			$user_rate['rate_time'] = 0;
			$sql = 'DELETE
				FROM ' . POST_RATES_TABLE . '
				WHERE user_id = ' . $user->data['user_id'] . '
					AND post_id = ' . $post_id;
		}
		else
		{
			$user_rate['rate_time'] = time();
			$sql = 'REPLACE 
				INTO ' . POST_RATES_TABLE . '
				SET rate = ' . $user_rate['rate'] . ',
					rate_time = ' . time() . ',
					user_id = ' . $user->data['user_id'] . ',
					post_id = ' . $post_id;
		}
		$db->sql_query($sql);
	}

	$sql = 'SELECT rate, COUNT(*) as count
		FROM ' . POST_RATES_TABLE . '
		WHERE post_id = ' . $post_id . '
		GROUP BY rate';
	$result = $db->sql_query($sql);

	$negative = 0;
	$positive = 0;
	while ($row = $db->sql_fetchrow($result))
	{
		if ($row['rate'] < 0)
		{
			$negative += abs($row['rate'] * $row['count']);
		}
		else
		{
			$positive += abs($row['rate'] * $row['count']);
		}
	}

		$sql = 'UPDATE ' . POSTS_TABLE . '
			SET post_rating_positive = ' . $positive . ',
				post_rating_negative = ' . $negative . '
			WHERE post_id = ' . $post_id;
		$db->sql_query($sql);

	$result = array(
		'status'				=> 'ok',
		'user_can_minus'		=> $config['rate_enabled'] && ($user->data['user_id'] != ANONYMOUS) && ($user->data['user_id'] != $post['poster_id']) && ($config['rate_time'] > 0 ? $config['rate_time'] + $post['post_time'] > time() : true) && ($user_rate['rate'] >= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_negative'] ? $user_rate['rate'] != 0 : true),
		'user_can_plus'			=> $config['rate_enabled'] && ($user->data['user_id'] != ANONYMOUS) && ($user->data['user_id'] != $post['poster_id']) && ($config['rate_time'] > 0 ? $config['rate_time'] + $post['post_time'] > time() : true) && ($user_rate['rate'] <= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_positive'] ? $user_rate['rate'] != 0 : true),
		'user_rate'				=> $user_rate['rate'],
		'post_rating'			=> ($config['rate_no_positive'] ? 0 : $positive) - ($config['rate_no_negative'] ? 0 : $negative),
		'post_rating_negative'	=> $negative,
		'post_rating_positive'	=> $positive,
	);

	echo json_encode($result);
}
catch (exception $e)
{
	echo json_encode(array('error' => $e->getMessage(), 'code' => $e->getCode()));
}
