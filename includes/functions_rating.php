<?php
/**
* @package phpBBex
* @copyright (c) 2015 phpBB Group, Vegalogic Software
* @license GNU Public License
*/

if (!defined('IN_PHPBB'))
{
	exit;
}

function resync_rates()
{
	global $db;

	// Remove rates for nonexistent posts or users.
	$sql = 'DELETE r
		FROM ' . POST_RATES_TABLE . ' r
		LEFT JOIN ' . POSTS_TABLE . ' p ON p.post_id = r.post_id
		LEFT JOIN ' . USERS_TABLE . ' u ON u.user_id = r.user_id
		WHERE p.post_id IS NULL
			OR u.user_id IS NULL';
	$db->sql_query($sql);

	// Rebuild the totals given and received by every user.
	$sql = 'UPDATE ' . USERS_TABLE . ' u
		LEFT JOIN (
			SELECT user_id,
				SUM(CASE WHEN rate < 0 THEN -rate ELSE 0 END) AS rated_negative,
				SUM(CASE WHEN rate > 0 THEN rate ELSE 0 END) AS rated_positive
			FROM ' . POST_RATES_TABLE . '
			GROUP BY user_id
		) given_rates ON given_rates.user_id = u.user_id
		LEFT JOIN (
			SELECT p.poster_id AS user_id,
				SUM(CASE WHEN r.rate < 0 THEN -r.rate ELSE 0 END) AS rating_negative,
				SUM(CASE WHEN r.rate > 0 THEN r.rate ELSE 0 END) AS rating_positive
			FROM ' . POST_RATES_TABLE . ' r
			INNER JOIN ' . POSTS_TABLE . ' p ON p.post_id = r.post_id
			GROUP BY p.poster_id
		) received_rates ON received_rates.user_id = u.user_id
		SET u.user_rated_negative = COALESCE(given_rates.rated_negative, 0),
			u.user_rated_positive = COALESCE(given_rates.rated_positive, 0),
			u.user_rating_negative = COALESCE(received_rates.rating_negative, 0),
			u.user_rating_positive = COALESCE(received_rates.rating_positive, 0)';
	$db->sql_query($sql);

	// Rebuild the totals received by every post.
	$sql = 'UPDATE ' . POSTS_TABLE . ' p
		LEFT JOIN (
			SELECT post_id,
				SUM(CASE WHEN rate < 0 THEN -rate ELSE 0 END) AS rating_negative,
				SUM(CASE WHEN rate > 0 THEN rate ELSE 0 END) AS rating_positive
			FROM ' . POST_RATES_TABLE . '
			GROUP BY post_id
		) post_rates ON post_rates.post_id = p.post_id
		SET p.post_rating_negative = COALESCE(post_rates.rating_negative, 0),
			p.post_rating_positive = COALESCE(post_rates.rating_positive, 0)';
	$db->sql_query($sql);
}

function remove_rate_row($rate_row)
{
	global $db;

	if ($rate_row['rate'] < 0)
	{
		$rate = abs($rate_row['rate']);
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rated_negative = user_rated_negative - {$rate} WHERE user_id = {$rate_row['user_id']}");
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rating_negative = user_rating_negative - {$rate} WHERE user_id = {$rate_row['poster_id']}");
		$db->sql_query('UPDATE ' . POSTS_TABLE . " SET post_rating_negative = post_rating_negative - {$rate} WHERE post_id = {$rate_row['post_id']}");
	}
	else
	{
		$rate = abs($rate_row['rate']);
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rated_positive = user_rated_positive - {$rate} WHERE user_id = {$rate_row['user_id']}");
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rating_positive = user_rating_positive - {$rate} WHERE user_id = {$rate_row['poster_id']}");
		$db->sql_query('UPDATE ' . POSTS_TABLE . " SET post_rating_positive = post_rating_positive - {$rate} WHERE post_id = {$rate_row['post_id']}");
	}

	$sql = 'DELETE
		FROM ' . POST_RATES_TABLE . "
		WHERE user_id = {$rate_row['user_id']} AND post_id = {$rate_row['post_id']}";
	$result = $db->sql_query($sql);
}

function remove_rate($user_id, $post_id)
{
	global $db;

	$user_id = intval($user_id);
	$post_id = intval($post_id);
	$sql = 'SELECT r.*, p.poster_id
		FROM ' . POST_RATES_TABLE . ' r
		LEFT JOIN ' . POSTS_TABLE . " p ON r.post_id = p.post_id
		WHERE r.user_id = {$user_id} AND r.post_id = {$post_id}";
	$result = $db->sql_query($sql);
	$rate_row = $db->sql_fetchrow($result);
	if (!$rate_row) return;

	remove_rate_row($rate_row);
}

function remove_rates_batch($type, $id, $negative = true, $positive = true, $from_time = false, $to_time = false)
{
	global $db;
	if (!$negative && !$positive) return;

	$sql = 'SELECT r.*, p.poster_id
		FROM ' . POST_RATES_TABLE . ' r
		LEFT JOIN ' . POSTS_TABLE . ' p ON r.post_id = p.post_id';

	switch ($type)
	{
		case 'user':
			$sql .= ' WHERE r.user_id = ' . $id;
		break;

		case 'post':
			$post_ids = is_array($id) ? array_unique(array_map('intval', $id)) : [(int) $id];
			if (!sizeof($post_ids))
			{
				return;
			}
			$sql .= ' WHERE ' . $db->sql_in_set('r.post_id', $post_ids);
		break;

		default:
			return;
		break;
	}

	if (!($negative && $positive))
	{
		if ($negative)
		{
			$sql .= ' AND r.rate < 0';
		}
		else
		{
			$sql .= ' AND r.rate > 0';
		}
	}

	$sql .= ($from_time ? ' AND r.rate_time >= ' . intval($from_time) : '');
	$sql .= ($to_time ? ' AND r.rate_time <= ' . intval($to_time) : '');

	$result = $db->sql_query($sql);

	while ($rate_row = $db->sql_fetchrow($result))
	{
		remove_rate_row($rate_row);
	}

	$db->sql_freeresult($result);
}
