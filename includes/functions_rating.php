<?php
function resync_rates()
{
	global $db;

	// Remove rates for nonexistent posts
	$sql = 'DELETE
		FROM r USING ' . POST_RATES_TABLE . ' r
		LEFT JOIN ' . POSTS_TABLE . ' p ON r.post_id = p.post_id
		WHERE p.post_id IS NULL';
	$db->sql_query($sql);

	// Remove rates from nonexistent users
	$sql = 'DELETE
		FROM r USING ' . POST_RATES_TABLE . ' r
		LEFT JOIN ' . USERS_TABLE . ' u ON r.user_id = u.user_id
		WHERE u.user_id IS NULL';
	$db->sql_query($sql);

	// Clear rating fields
	$db->sql_query('UPDATE ' . USERS_TABLE . ' SET user_rated_negative = 0, user_rated_positive = 0, user_rating_negative = 0, user_rating_positive = 0');
	$db->sql_query('UPDATE ' . POSTS_TABLE . ' SET post_rating_negative = 0, post_rating_positive = 0');

	// Update user_rated_negative
	$sql = 'SELECT user_id, ABS(SUM(rate)) as rates
		FROM ' . POST_RATES_TABLE . '
		WHERE rate < 0
		GROUP BY user_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rated_negative = {$row['rates']} WHERE user_id = {$row['user_id']}");
	}
	$db->sql_freeresult($result);

	// Update user_rated_positive
	$sql = 'SELECT user_id, ABS(SUM(rate)) as rates
		FROM ' . POST_RATES_TABLE . '
		WHERE rate > 0
		GROUP BY user_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rated_positive = {$row['rates']} WHERE user_id = {$row['user_id']}");
	}
	$db->sql_freeresult($result);

	// Update user_rating_negative
	$sql = 'SELECT p.poster_id, ABS(SUM(r.rate)) AS rates
		FROM ' . POST_RATES_TABLE . ' r
		INNER JOIN ' . POSTS_TABLE . ' p ON r.post_id = p.post_id
		WHERE r.rate < 0
		GROUP BY p.poster_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rating_negative = {$row['rates']} WHERE user_id = {$row['poster_id']}");
	}
	$db->sql_freeresult($result);

	// Update user_rating_positive
	$sql = 'SELECT p.poster_id, ABS(SUM(r.rate)) AS rates
		FROM ' . POST_RATES_TABLE . ' r
		INNER JOIN ' . POSTS_TABLE . ' p ON r.post_id = p.post_id
		WHERE r.rate > 0
		GROUP BY p.poster_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_query('UPDATE ' . USERS_TABLE . " SET user_rating_positive = {$row['rates']} WHERE user_id = {$row['poster_id']}");
	}
	$db->sql_freeresult($result);

	// Update post_rating_negative
	$sql = 'SELECT post_id, ABS(SUM(rate)) AS rates
		FROM ' . POST_RATES_TABLE . '
		WHERE rate < 0
		GROUP BY post_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_query('UPDATE ' . POSTS_TABLE . " SET post_rating_negative = {$row['rates']} WHERE post_id = {$row['post_id']}");
	}
	$db->sql_freeresult($result);

	// Update post_rating_positive
	$sql = 'SELECT post_id, ABS(SUM(rate)) AS rates
		FROM ' . POST_RATES_TABLE . '
		WHERE rate > 0
		GROUP BY post_id';
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		$db->sql_query('UPDATE ' . POSTS_TABLE . " SET post_rating_positive = {$row['rates']} WHERE post_id = {$row['post_id']}");
	}
	$db->sql_freeresult($result);
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
			$sql .= ' WHERE r.post_id = ' . $id;
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
