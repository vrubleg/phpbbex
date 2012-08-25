<?php
class module_rating
{
	function __construct($config = array())
	{
		
	}

	function action_plus()
	{
		global $user;
		$this->rate_post(get('post_id', 0), $user->data['user_id'], 'plus');
	}

	function action_minus()
	{
		global $user;
		$this->rate_post(get('post_id', 0), $user->data['user_id'], 'minus');
	}

	function rate_post($post_id, $user_id, $rate)
	{
		global $db, $config;
	
		response::type('application/json');
		response::expire(false);

		try
		{
			if (!$post_id) throw new exception('post_id is required');

			// Get current user rate
			$sql = 'SELECT *
				FROM ' . POST_RATES_TABLE . '
				WHERE user_id = ' . $user_id . '
					AND post_id = ' . $post_id;
			$result = $db->sql_query($sql);
			$user_rate = $db->sql_fetchrow($result);
			if (!$user_rate) $user_rate = array('rate' => 0, 'rate_time' => 0);

			// Get post
			$sql = 'SELECT p.*, t.topic_first_post_id
				FROM ' . POSTS_TABLE . ' p
				LEFT JOIN ' . TOPICS_TABLE . ' t ON t.topic_id = p.topic_id
				WHERE p.post_id = ' . $post_id;
			$result = $db->sql_query($sql);
			$post = $db->sql_fetchrow($result);
			if (!$post) throw new exception('post not exists');
			$rate_time = ($post['topic_first_post_id'] != $post['post_id'] || !isset($config['rate_topic_time']) || $config['rate_topic_time'] == -1) ? $config['rate_time'] : $config['rate_topic_time'];

			$can = false;
			switch ($rate)
			{
				case 'minus':
					$can = $config['rate_enabled'] && ($user_id != ANONYMOUS) && ($user_id != $post['poster_id']) && (empty($config['rate_only_topics']) || $post['topic_first_post_id'] == $post['post_id']) && ($rate_time > 0 ? $rate_time + $post['post_time'] > time() : true) && ($user_rate['rate'] >= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_negative'] ? $user_rate['rate'] != 0 : true);
					if ($can) $user_rate['rate']--;
					if ($user_rate['rate'] < -1) $user_rate['rate'] = -1;
				break;
				case 'plus':
					$can = $config['rate_enabled'] && ($user_id != ANONYMOUS) && ($user_id != $post['poster_id']) && (empty($config['rate_only_topics']) || $post['topic_first_post_id'] == $post['post_id']) && ($rate_time > 0 ? $rate_time + $post['post_time'] > time() : true) && ($user_rate['rate'] <= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_positive'] ? $user_rate['rate'] != 0 : true);
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
						WHERE user_id = ' . $user_id . '
							AND post_id = ' . $post_id;
				}
				else
				{
					$user_rate['rate_time'] = time();
					$sql = 'REPLACE 
						INTO ' . POST_RATES_TABLE . '
						SET rate = ' . $user_rate['rate'] . ',
							rate_time = ' . time() . ',
							user_id = ' . $user_id . ',
							post_id = ' . $post_id;
				}
				$db->sql_query($sql);
			}

			// Update post rating
			$sql = 'SELECT rate, COUNT(*) as count
				FROM ' . POST_RATES_TABLE . '
				WHERE post_id = ' . $post_id . '
				GROUP BY rate';
			$result = $db->sql_query($sql);

			$post_rating_negative = 0;
			$post_rating_positive = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['rate'] < 0)
				{
					$post_rating_negative += abs($row['rate'] * $row['count']);
				}
				else
				{
					$post_rating_positive += abs($row['rate'] * $row['count']);
				}
			}

			$sql = 'UPDATE ' . POSTS_TABLE . '
				SET post_rating_positive = ' . $post_rating_positive . ',
					post_rating_negative = ' . $post_rating_negative . '
				WHERE post_id = ' . $post_id;
			$db->sql_query($sql);

			// Update poster rating
			$sql = 'SELECT rate, COUNT(*) as count
				FROM ' . POST_RATES_TABLE . ' r
				LEFT JOIN ' . POSTS_TABLE . ' p ON r.post_id = p.post_id
				WHERE p.poster_id = ' . $post['poster_id'] . '
				GROUP BY rate';
			$result = $db->sql_query($sql);

			$poster_rating_negative = 0;
			$poster_rating_positive = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['rate'] < 0)
				{
					$poster_rating_negative += abs($row['rate'] * $row['count']);
				}
				else
				{
					$poster_rating_positive += abs($row['rate'] * $row['count']);
				}
			}

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_rating_positive = ' . $poster_rating_positive . ',
					user_rating_negative = ' . $poster_rating_negative . '
				WHERE user_id = ' . $post['poster_id'];
			$db->sql_query($sql);
			
			// Update rater info
			$sql = 'SELECT rate, COUNT(*) as count
				FROM ' . POST_RATES_TABLE . '
				WHERE user_id = ' . $user_id . '
				GROUP BY rate';
			$result = $db->sql_query($sql);

			$user_rated_negative = 0;
			$user_rated_positive = 0;
			while ($row = $db->sql_fetchrow($result))
			{
				if ($row['rate'] < 0)
				{
					$user_rated_negative += abs($row['rate'] * $row['count']);
				}
				else
				{
					$user_rated_positive += abs($row['rate'] * $row['count']);
				}
			}

			$sql = 'UPDATE ' . USERS_TABLE . '
				SET user_rated_positive = ' . $user_rated_positive . ',
					user_rated_negative = ' . $user_rated_negative . '
				WHERE user_id = ' . $user_id;
			$db->sql_query($sql);

			$result = array(
				'status'				=> 'ok',
				'user_can_minus'		=> $config['rate_enabled'] && ($user_id != ANONYMOUS) && ($user_id != $post['poster_id']) && (empty($config['rate_only_topics']) || $post['topic_first_post_id'] == $post['post_id']) && ($rate_time > 0 ? $rate_time + $post['post_time'] > time() : true) && ($user_rate['rate'] >= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_negative'] ? $user_rate['rate'] != 0 : true),
				'user_can_plus'			=> $config['rate_enabled'] && ($user_id != ANONYMOUS) && ($user_id != $post['poster_id']) && (empty($config['rate_only_topics']) || $post['topic_first_post_id'] == $post['post_id']) && ($rate_time > 0 ? $rate_time + $post['post_time'] > time() : true) && ($user_rate['rate'] <= 0) && ($user_rate['rate'] != 0 && $config['rate_change_time'] > 0 ? $config['rate_change_time'] + $user_rate['rate_time'] > time() : true) && ($config['rate_no_positive'] ? $user_rate['rate'] != 0 : true),
				'user_rate'				=> $user_rate['rate'],
				'post_rating'			=> ($config['rate_no_positive'] ? 0 : $post_rating_positive) - ($config['rate_no_negative'] ? 0 : $post_rating_negative),
				'post_rating_negative'	=> $post_rating_negative,
				'post_rating_positive'	=> $post_rating_positive,
				'poster_id'				=> $post['poster_id'],
				'poster_rating'			=> ($config['rate_no_positive'] ? 0 : $poster_rating_positive) - ($config['rate_no_negative'] ? 0 : $poster_rating_negative),
				'poster_rating_negative'=> $poster_rating_negative,
				'poster_rating_positive'=> $poster_rating_positive,
				'user_id'				=> $user_id,
				'user_rated'			=> ($config['rate_no_positive'] ? 0 : $user_rated_positive) - ($config['rate_no_negative'] ? 0 : $user_rated_negative),
				'user_rated_negative'	=> $user_rated_negative,
				'user_rated_positive'	=> $user_rated_positive,
			);

			echo json::encode($result);
		}
		catch (exception $e)
		{
			echo json::encode(array('error' => $e->getMessage(), 'code' => $e->getCode()));
		}
	}
}
