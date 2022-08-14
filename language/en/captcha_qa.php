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
	$lang = array();
}

$lang = array_merge($lang, array(
	'CAPTCHA_QA'				=> 'Q&amp;A',
	'CONFIRM_QUESTION_EXPLAIN'	=> 'This question is a means of preventing automated form submissions by spambots.',
	'CONFIRM_QUESTION_WRONG'	=> 'You have provided an invalid answer to the question.',

	'QUESTION_ANSWERS'			=> 'Answers',
	'ANSWERS_EXPLAIN'			=> 'Please enter valid answers to the question, one per line.',
	'CONFIRM_QUESTION'			=> 'Question',

	'ANSWER'					=> 'Answer',
	'EDIT_QUESTION'				=> 'Edit Question',
	'QUESTIONS'					=> 'Questions',
	'QUESTIONS_EXPLAIN'			=> 'For every form submission where you have enabled the Q&amp;A plugin, users will be asked one of the questions specified here. To use this plugin at least one question must be set in the default language. These questions should be easy for your target audience to answer but beyond the ability of a bot capable of running a Google™ search. Using a large and regularly changed set of questions will yield the best results. Enable the strict setting if your question relies on mixed case, punctuation or whitespace.',
	'QUESTION_DELETED'			=> 'Question deleted',
	'QUESTION_LANG'				=> 'Language',
	'QUESTION_LANG_EXPLAIN'		=> 'The language this question and its answers are written in.',
	'QUESTION_STRICT'			=> 'Strict check',
	'QUESTION_STRICT_EXPLAIN'	=> 'Enable to enforce mixed case, punctuation and whitespace.',

	'QUESTION_TEXT'				=> 'Question',
	'QUESTION_TEXT_EXPLAIN'		=> 'The question presented to the user.',

	'QA_ERROR_MSG'				=> 'Please fill in all fields and enter at least one answer.',
	'QA_LAST_QUESTION'			=> 'You cannot delete all questions while the plugin is active.',

));
