<?php
if (!defined('IN_PHPBB'))
{
   exit;
}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$help = array(
	array(
		0 => '--',
		1 => 'Section 1. Forum'
	),
	array(
		0 => '1.1. General Items',
		1 => 'These rules are valid for the entire forum, unless other items are stipulated separately. Ignorance of these and other rules of the forum cannot release you from responsibility for their violation, but it is supposed to be the violation and an aggravating circumstance. These rules can be changed or supplemented due to circumstances which are not covered by these rules. If a user violates the rules then some penalties can be applied.'
	),
	array(
		0 => '1.2. General rules',
		1 => 'Do not publish information containing: <ol><li>excessive amount of slang words, rude and obscene words or phrases;</li><li>advertising;</li><li>pornography;</li><li>someone else’s personal information;</li><li>insults, threats, slander;</li><li>objects of racism and incitement to ethnic strife;</li><li>incitement to violence or breach of current legislation.</li></ol>Penalty: editing or deletion of information and also notification or a ban against the user.'
	),
	array(
		0 => '--',
		1 => 'Section 2. Registration and user’s profile setting'
	),
	array(
		0 => '2.1. Registration',
		1 => 'Do not:<ol><li>specify a non-existent e-mail address;</li><li>create several accounts;</li><li>make false profiles of other users;</li><li>try to break other accounts by brute force.</li></ol>Penalty: ban or deletion of user’s account.'
	),
	array(
		0 => '2.2. Username',
		1 => 'Do not:<ol><li>use usernames which can be similar to usernames of other members of the board;</li><li>use special characters in a username (which are neither letters nor numbers);</li><li>use usernames, almost entirely consisting of digits;</li><li>use website addresses as a username.</li></ol>Penalty: forced changing of user’s name.<br />Recommended: use short and easily-remembered character combinations which are somehow related to you.'
	),
	array(
		0 => '2.3. Avatar',
		1 => 'Do not:<ol><li>use someone else’s avatar;</li><li>change avatar frequently (more than once a month).</li></ol>Penalty: removal of avatar, in the case of repeated violation — blocking the possibility of choosing avatars.<br />Recommended: use your photo or other memorizing image as an avatar.'
	),
	array(
		0 => '--',
		1 => 'Section 3. Topics and Messages'
	),
	array(
		0 => '3.1. Topics',
		1 => 'Do not:<ol><li>create topics addressed to particular members of the forum;</li><li>create vapid topics which are deliberately aimed at flood, off-topic and flame;</li><li>duplicate the existing topics;</li><li>create topics which do not correspond with the subject of the forum.</li></ol>Penalty: deletion of a topic or transferring it to an appropriate forum, notification addressed to the author.',
	),
	array(
		0 => '3.2. Topic Subject',
		1 => 'The rule: the topic subject should reflect its essence as clearly as possible.<br />Do not:<ol><li>use senseless expressions such as "Help!", "It’s urgent!", etc. as a topic subject;</li><li>write the whole subject or some part of it in capital letters, e.g.: "WHAT SHOULD BE DONE???";</li><li>use a large amount of grouped punctuation marks, e.g.: "The computer hangs at boot!!! What should I do???";</li><li>use the embellishment symbols, e.g.: "..:: Topic ::..";</li><li>use the name of the topic that is not connected logically with your message.</li></ol>Penalty: forced changing of the title, notification addressed to the autor.<br />Recommended: express the essence of the topic as briefly as possible: "The results of Quake III Championship"; use the subject that may intrigue a visitor: "The new version of Windows has appeared. What’s inside?". Remember: each visitor should identify the subject by the title of the topic.',
	),
	array(
		0 => '3.3. Messages',
		1 => 'Do not:<ol><li>write a message in capital letters;</li><li>abuse of BBCode, e.g. application to the entire message;</li><li>use writing techniques that can make the meaning of the message unclear to visitors, e.g. "1t 1s h4rd t0 und3rst4nd";</li><li>abuse of smiles (no more than one smile for 100 symbols);</li><li>use smiles from other websites through [img] BBCode;</li><li>insert images with the size of more than 250 kilobytes with [img] BBCode (you can give the link to an image and write some description);</li><li>post messages that consist of a single link without description (every user should know in advance what they will see after clicking on your link).</li></ol>Penalty: amendment or deletion of the message, notification addressed to the author, in case of repeated violation — a ban can be given.<br />Not recommended: to use an excessive amount of modern network "dialects" or professional terms, etc., unless the topic of discussion requires it.<br />Recommended: to write correctly, without grammar mistakes.',
	),
	array(
		0 => '3.4. Discussions',
		1 => 'Do not:<ol><li>discuss the moderators’ actions outside the administration forum;</li><li>flame — ignoring the boundaries of politeness during the discussion;</li><li>flood — the information that lacks for meaning or common sense, i.e. short messages such as "Cool", "It’s interesting",  etc.;</li><li>off-topic — messages that do not correspond to the topic, i.e. if car brands are under discussion but the message says that tomorrow you are going to the cinema;</li><li>exchange personal messages — you can use Private Messages for discussing personal issues;</li><li>use unfair methods of leading discussions by way of "distortion" the statements of your interlocutors, editing or deleting your own posts for the purpose of distorting or concealing their original meaning.</li></ol>Penalty: for off-topic, flame or flood — deletion of messages with the violations, notifications addressed to authors; for the "distortion" — restriction of the rights of violators for editing their own posts.'
	)
);

?>