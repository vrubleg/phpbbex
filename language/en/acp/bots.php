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

if (empty($lang) || !is_array($lang))
{
	$lang = [];
}

// Bot settings
$lang = array_merge($lang, [
	'BOTS'              => 'Manage bots',
	'BOTS_EXPLAIN'      => '“Bots”, “spiders” or “crawlers” are automated agents most commonly used by search engines to update their databases. Since they rarely make proper use of sessions they can distort visitor counts, increase load and sometimes fail to index sites correctly. Here you can define bot user-agent and IP matches.',
	'BOT_ACTIVATE'      => 'Activate',
	'BOT_ACTIVE'        => 'Bot active',
	'BOT_ADD'           => 'Add bot',
	'BOT_ADDED'         => 'New bot successfully added.',
	'BOT_AGENT'         => 'Agent match',
	'BOT_AGENT_EXPLAIN' => 'A string matching the bots browser agent, partial matches are allowed.',
	'BOT_DEACTIVATE'    => 'Deactivate',
	'BOT_DELETED'       => 'Bot deleted successfully.',
	'BOT_EDIT'          => 'Edit bots',
	'BOT_EDIT_EXPLAIN'  => 'Here you can add or edit an existing bot entry. You may define an agent string and/or one or more IP addresses (or range of addresses) to match. Be careful when defining matching agent strings or addresses.',
	'BOT_LAST_VISIT'    => 'Last visit',
	'BOT_IP'            => 'Bot IP address',
	'BOT_IP_EXPLAIN'    => 'Partial matches are allowed, separate addresses with a comma.',
	'BOT_NAME'          => 'Bot name',
	'BOT_NAME_EXPLAIN'  => 'Used only for your own information.',
	'BOT_NAME_TAKEN'    => 'The name is already in use on your board and can’t be used for the Bot.',
	'BOT_NEVER'         => 'Never',
	'BOT_UPDATED'       => 'Existing bot updated successfully.',

	'ERR_BOT_AGENT_MATCHES_UA'  => 'The bot agent you supplied is similar to the one you are currently using. Please adjust the agent for this bot.',
	'ERR_BOT_NO_IP'             => 'The IP addresses you supplied were invalid or the hostname could not be resolved.',
	'ERR_BOT_NO_MATCHES'        => 'You must supply at least one of an agent or IP for this bot match.',

	'NO_BOT'        => 'Found no bot with the specified ID.',
]);
