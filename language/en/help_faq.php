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

$help = array(
	array(
		0 => '--',
		1 => 'Login and Registration Issues'
	),
	array(
		0 => 'Why do I need to register at all?',
		1 => 'You may not have to, it is up to the administrator of the board as to whether you need to register in order to post messages. However; registration will give you access to additional features not available to guest users such as definable avatar images, private messaging, emailing of fellow users, usergroup subscription, etc. It only takes a few moments to register so it is recommended you do so.'
	),
	array(
		0 => 'Why can’t I login?',
		1 => 'First, check your username and password. If they are correct, then one of two things may have happened. Some boards will also require new registrations to be activated, either by yourself or by an administrator before you can logon; this information was present during registration. If you were sent an email, follow the instructions. If you did not receive an email, you may have provided an incorrect email address or the email may have been picked up by a spam filer. If you are sure the email address you provided is correct, try contacting an administrator.'
	),
	array(
		0 => 'Why do I get logged off automatically?',
		1 => 'If you do not check the <em>Log me in automatically</em> box when you login, the board will only keep you logged in for a preset time. This prevents misuse of your account by anyone else. To stay logged in, check the box during login. This is not recommended if you access the board from a shared computer, e.g. library, internet cafe, university computer lab, etc. If you do not see this checkbox, it means the board administrator has disabled this feature.'
	),
	array(
		0 => 'Why can’t I register?',
		1 => 'It is possible the website owner has banned your IP address or disallowed the username you are attempting to register. The website owner could have also disabled registration to prevent new visitors from signing up. Contact a board administrator for assistance.',
	),
	array(
		0 => '--',
		1 => 'User Preferences and settings'
	),
	array(
		0 => 'The times are not correct!',
		1 => 'It is possible the time displayed is from a timezone different from the one you are in. If this is the case, visit your User Control Panel and change your timezone to match your particular area. Please note that changing the timezone, like most settings, can only be done by registered users. If you are not registered, this is a good time to do so.'
	),
	array(
		0 => 'What is the difference between bookmarking and subscribing?',
		1 => 'Bookmarking in phpBBex is much like bookmarking in your web browser. You aren’t alerted when there’s an update, but you can come back to the topic later. Subscribing, however, will notify you when there is an update to the topic or forum on the board via your preferred method or methods.'
	),
	array(
		0 => 'How do I subscribe to specific forums or topics?',
		1 => 'To subscribe to a specific forum, click the “Subscribe forum” link upon entering the forum. To subscribe to a topic, reply to the topic with the subscribe checkbox checked or click the “Subscribe topic” link within the topic itself.'
	),
	array(
		0 => 'How do I remove my subscriptions?',
		1 => 'To remove your subscriptions, go to your User Control Panel and follow the links to your subscriptions.'
	),
	array(
		0 => '--',
		1 => 'Posting Issues'
	),
	array(
		0 => 'What is BBCode? How to change formatting of my messages and add images?',
		1 => 'BBCode is a special markup language, offering great formatting control on particular objects in a post, adding images, and more. The use of BBCode is granted by the administrator, but it can also be disabled on a per post basis from the posting form. BBCode itself is similar in style to HTML, but tags are enclosed in square brackets [ and ] rather than &lt; and &gt;. For more information on BBCode see <a href="./faq.php?mode=bbcode">the guide</a>.'
	),
	array(
		0 => 'What is the “Save” button for in topic posting?',
		1 => 'This allows you to save passages to be completed and submitted at a later date. To reload a saved passage, visit the User Control Panel.'
	),
	array(
		0 => 'Why does my post need to be approved?',
		1 => 'The board administrator may have decided that posts in the forum you are posting to require review before submission. It is also possible that the administrator has placed you in a group of users whose posts require review before submission. Please contact the board administrator for further details.'
	),
	array(
		0 => 'How do I bump my topic?',
		1 => 'By clicking the “Bump topic” link when you are viewing it, you can “bump” the topic to the top of the forum on the first page. However, if you do not see this, then topic bumping may be disabled or the time allowance between bumps has not yet been reached. It is also possible to bump the topic simply by replying to it, however, be sure to follow the board rules when doing so.'
	),
	array(
		0 => '--',
		1 => 'User Levels and Groups'
	),
	array(
		0 => 'What are Administrators?',
		1 => 'Administrators are members assigned with the highest level of control over the entire board. These members can control all facets of board operation, including setting permissions, banning users, creating usergroups or moderators, etc., dependent upon the board founder and what permissions he or she has given the other administrators. They may also have full moderator capabilities in all forums, depending on the settings put forth by the board founder.'
	),
	array(
		0 => 'What are Moderators?',
		1 => 'Moderators are individuals (or groups of individuals) who look after the forums from day to day. They have the authority to edit or delete posts and lock, unlock, move, delete and split topics in the forum they moderate. Generally, moderators are present to prevent users from going off-topic or posting abusive or offensive material.'
	),
	array(
		0 => 'What are usergroups?',
		1 => 'Usergroups are groups of users that divide the community into manageable sections board administrators can work with. Each user can belong to several groups and each group can be assigned individual permissions. This provides an easy way for administrators to change permissions for many users at once, such as changing moderator permissions or granting users access to a private forum.'
	),
	array(
		0 => 'What is a “Default usergroup”?',
		1 => 'If you are a member of more than one usergroup, your default is used to determine which group colour and group rank should be shown for you by default. The board administrator may grant you permission to change your default usergroup via your User Control Panel.'
	)
);
