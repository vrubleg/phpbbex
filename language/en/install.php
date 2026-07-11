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

$lang = array_merge($lang, [
	'ADMIN_CONFIG'              => 'Administrator configuration',
	'ADMIN_PASSWORD'            => 'Administrator password',
	'ADMIN_PASSWORD_CONFIRM'    => 'Confirm administrator password',
	'ADMIN_PASSWORD_EXPLAIN'    => 'Please enter a password between 6 and 30 characters in length.',
	'ADMIN_TEST'                => 'Check administrator settings',
	'ADMIN_USERNAME'            => 'Administrator username',
	'ADMIN_USERNAME_EXPLAIN'    => 'Please enter a username between 3 and 20 characters in length.',
	'AVAILABLE'                 => 'Available',

	'BACKUP_NOTICE'                 => 'Please backup your board before updating in case any problems arise during the update process.',

	'CATEGORY'                  => 'Category',
	'CACHE_STORE'               => 'Cache type',
	'CACHE_STORE_EXPLAIN'       => 'The physical location where data is cached, filesystem is preferred.',
	'CAT_CONVERT'               => 'Convert',
	'CAT_INSTALL'               => 'Install',
	'CAT_OVERVIEW'              => 'Overview',
	'CAT_UPDATE'                => 'Update',
	'CHANGE'                    => 'Change',
	'CHECK_TABLE_PREFIX'        => 'Please check your table prefix and try again.',
	'CONFIG_FILE_UNABLE_WRITE'  => 'It was not possible to write the configuration file.',
	'CONFIG_FILE_WRITTEN'       => 'The configuration file has been written. You may now proceed to the next step of the installation.',
	'CONFIG_RETRY'              => 'Retry',
	'CONTINUE_LAST'             => 'Continue last statements',
	'CONVERT'                   => 'Convert',

	'COULD_NOT_COPY'            => 'Could not copy file <strong>%1$s</strong> to <strong>%2$s</strong><br /><br />Please check that the target directory exists and is writable by the webserver.',
	'COULD_NOT_FIND_PATH'       => 'Could not find path to your former board. Please check your settings and try again.<br />» %s was specified as the source path.',

	'DBMS'                      => 'Database type',
	'DB_CONFIG'                 => 'Database configuration',
	'DB_CONNECTION'             => 'Database connection',
	'DB_HOST'                   => 'Database server hostname',
	'DB_HOST_EXPLAIN'           => 'Usually it is localhost.',
	'DB_NAME'                   => 'Database name',
	'DB_PASSWORD'               => 'Database password',
	'DB_PORT'                   => 'Database server port',
	'DB_PORT_EXPLAIN'           => 'Leave this blank unless you know the server operates on a non-standard port.',
	'DB_UPDATE_NOT_SUPPORTED'   => 'We are sorry, but this script does not support updating from versions of phpBB prior to “%1$s”. The version you currently have installed is “%2$s”. Please update to a previous version before running this script. Assistance with this is available in the Support Forum on phpBB.com.',
	'DB_USERNAME'               => 'Database username',
	'DB_TEST'                   => 'Test connection',
	'DEFAULT_LANG'              => 'Default board language',
	'DIRECTORIES_AND_FILES'     => 'Directory and file setup',
	'DISABLE_KEYS'              => 'Disabling keys',
	'DLL_CURL'                  => 'curl (for remote avatars)',
	'DLL_GD'                    => 'gd (for graphical CAPTCHA)',
	'DLL_INTL'                  => 'intl (for fast UTF-8)',
	'DLL_MBSTRING'              => 'mbstring (for fast UTF-8)',
	'DLL_MYSQLI'                => 'mysqli (for MySQL support)',
	'DLL_XML'                   => 'xml (for Jabber)',
	'DLL_ZLIB'                  => 'zlib (for gzip output compression)',
	'DL_CONFIG'                 => 'Download config',
	'DL_CONFIG_EXPLAIN'         => 'You may download the complete config.php to your own PC. You will then need to upload the file manually, replacing any existing config.php in your phpBBex root directory. When you have uploaded the config.php please click “Done” to move to the next stage.',
	'DL_DOWNLOAD'               => 'Download',
	'DONE'                      => 'Done',

	'ENABLE_KEYS'               => 'Re-enabling keys. This can take a while.',

	'FILES_OPTIONAL'            => 'Optional files and directories',
	'FILES_OPTIONAL_EXPLAIN'    => '<strong>Optional</strong> - These files, directories or permission settings are not required. The installation system will attempt to use various techniques to create them if they do not exist or cannot be written to. However, the presence of these will speed installation.',
	'FILES_REQUIRED'            => 'Files and Directories',
	'FILES_REQUIRED_EXPLAIN'    => '<strong>Required</strong> - In order to function correctly phpBBex needs to be able to access or write to certain files or directories. If you see “Not Found” you need to create the relevant file or directory. If you see “Unwritable” you need to change the permissions on the file or directory to allow phpBBex to write to it.',
	'FILLING_TABLE'             => 'Filling table <strong>%s</strong>',
	'FILLING_TABLES'            => 'Filling tables',

	'FINAL_STEP'                => 'Process final step',
	'FOUND'                     => 'Found',

	'GPL'                       => 'General Public License',

	'INITIAL_CONFIG'            => 'Basic configuration',
	'INITIAL_CONFIG_EXPLAIN'    => 'Now that install has determined your server can run phpBBex you need to supply some specific information. If you do not know how to connect to your database please contact your hosting provider (in the first instance) or use the phpBBex support forums. When entering data please ensure you check it thoroughly before continuing.',
	'INSTALL_CONGRATS'          => 'Congratulations!',
	'INSTALL_CONGRATS_EXPLAIN'  => 'You have successfully installed phpBBex %1$s. Go live with your phpBBex!',
	'INSTALL_INTRO'             => 'Welcome to phpBBex installation!',
	'INSTALL_INTRO_BODY'        => 'phpBBex is an extended fork of the classic phpBB 3.0 forum software that is as lightweight as the original in contrary to newer versions of phpBB. It is compatible with PHP 7.4+ and 8.0+.
	<p>In order to proceed, you will need your MySQL database settings:
	<ul>
		<li>The address of the MySQL database server;</li>
		<li>The name of the database on the server;</li>
		<li>The login and password to access the database.</li>
	</ul>
	<p>See also:
	<ul>
		<li><a target="_blank" href="//phpbbex.com/forum/viewforum.php?f=5">phpBBex Support Forum</a></li>
		<li><a target="_blank" href="//phpbbex.com/forum/viewtopic.php?t=28">What\'s new in phpBBex?</a></li>
	</ul>',
	'INSTALL_INTRO_NEXT'        => 'To commence the installation, please press the button below.',
	'INSTALL_LOGIN'             => 'Login',
	'INSTALL_NEXT'              => 'Next stage',
	'INSTALL_NEXT_FAIL'         => 'Some tests failed and you should correct these problems before proceeding to the next stage. Failure to do so may result in an incomplete installation.',
	'INSTALL_NEXT_PASS'         => 'All the basic tests have been passed and you may proceed to the next stage of installation. If you have changed any permissions, modules, etc. and wish to re-test you can do so if you wish.',
	'INSTALL_PANEL'             => 'Installation Panel',
	'INSTALL_SEND_CONFIG'       => 'Unfortunately phpBBex could not write the configuration information directly to your config.php. This may be because the file does not exist or is not writable. A number of options will be listed below enabling you to complete installation of config.php.',
	'INSTALL_START'             => 'Start install',
	'INSTALL_TEST'              => 'Test again',
	'INST_ERR'                  => 'Installation error',
	'INST_ERR_DB_CONNECT'       => 'Could not connect to the database, see error message below.',
	'INST_ERR_DB_FORUM_PATH'    => 'The database file specified is within your board directory tree. You should put this file in a non web-accessible location.',
	'INST_ERR_DB_INVALID_PREFIX'=> 'The prefix you entered is invalid. It must start with a letter and must only contain letters, numbers and underscores.',
	'INST_ERR_DB_NO_ERROR'      => 'No error message given.',
	'INST_ERR_DB_NO_MYSQLI'     => 'MySQL 5.5 and newer is required.',
	'INST_ERR_DB_NO_NAME'       => 'No database name specified.',
	'INST_ERR_EMAIL_INVALID'    => 'The email address you entered is invalid.',
	'INST_ERR_FATAL'            => 'Fatal installation error',
	'INST_ERR_FATAL_DB'         => 'A fatal and unrecoverable database error has occurred. This may be because the specified user does not have appropriate permissions to <code>CREATE TABLES</code> or <code>INSERT</code> data, etc. Further information may be given below. Please contact your hosting provider in the first instance or the support forums of phpBBex for further assistance.',
	'INST_ERR_MISSING_DATA'     => 'You must fill out all fields in this block.',
	'INST_ERR_NO_DB'            => 'Cannot load the PHP module for the selected database type.',
	'INST_ERR_PASSWORD_MISMATCH'    => 'The passwords you entered did not match.',
	'INST_ERR_PASSWORD_TOO_LONG'    => 'The password you entered is too long. The maximum length is 30 characters.',
	'INST_ERR_PASSWORD_TOO_SHORT'   => 'The password you entered is too short. The minimum length is 6 characters.',
	'INST_ERR_PREFIX'           => 'Tables with the specified prefix already exist, please choose an alternative.',
	'INST_ERR_PREFIX_INVALID'   => 'The table prefix you have specified is invalid for your database. Please try another, removing characters such as the hyphen.',
	'INST_ERR_PREFIX_TOO_LONG'  => 'The table prefix you have specified is too long. The maximum length is %d characters.',
	'INST_ERR_USER_TOO_LONG'    => 'The username you entered is too long. The maximum length is 20 characters.',
	'INST_ERR_USER_TOO_SHORT'   => 'The username you entered is too short. The minimum length is 3 characters.',
	'INVALID_PRIMARY_KEY'       => 'Invalid primary key : %s',

	'LONG_SCRIPT_EXECUTION'     => 'Please note that this can take a while... Please do not stop the script.',

	// mbstring
	'MBSTRING_CHECK'                        => '<samp>mbstring</samp> module check',
	'MBSTRING_CHECK_EXPLAIN'                => '<strong>Required</strong> - <samp>mbstring</samp> is a PHP module that provides multibyte string functions. Certain features of mbstring are not compatible with phpBBex and must be disabled.',
	'MBSTRING_FUNC_OVERLOAD'                => 'Function overloading',
	'MBSTRING_FUNC_OVERLOAD_EXPLAIN'        => '<var>mbstring.func_overload</var> must be set to 0.',
	'MBSTRING_ENCODING_TRANSLATION'         => 'Transparent character encoding',
	'MBSTRING_ENCODING_TRANSLATION_EXPLAIN' => '<var>mbstring.encoding_translation</var> must be set to 0.',

	'MAKE_FOLDER_WRITABLE'      => 'Please make sure that this folder exists and is writable by the webserver then try again:<br />»<strong>%s</strong>.',
	'MAKE_FOLDERS_WRITABLE'     => 'Please make sure that these folders exist and are writable by the webserver then try again:<br />»<strong>%s</strong>.',

	'NEXT_STEP'                 => 'Proceed to next step',
	'NOT_FOUND'                 => 'Cannot find',

	'OVERVIEW_BODY'             => 'Welcome to phpBBex!<br /><br />phpBBex (phpBB extended) is an attempt to create a more advanced version of phpBB, which in this case will remain just a forum, not a combination for all occasions. phpBBex is developed on the professional level, the source code is under Mercurial version control. Most of the changes made do not exist in the form of mods. Almost all the installed mods existing outside phpBBex were reworked a good deal: bugs fixed, the code was adapted to the latest version of phpBB. If phpBBex without modification suits you more than the standard phpBB 3, it will undoubtedly become the best choice for you. <a href="//phpbbex.com/forum/viewtopic.php?t=28">More...</a><br /><br />This installation system will guide you through installing phpBB3. For more information, we encourage you to read <a href="../docs/INSTALL.html">the installation guide</a>. To install, please select the appropriate tab above.',

	'PHP_OPTIONAL_MODULE'           => 'Optional modules',
	'PHP_OPTIONAL_MODULE_EXPLAIN'   => '<strong>Optional</strong> - These PHP modules are optional. However, if they are available they will enable extra features.',
	'PHP_REQUIRED_MODULE'           => 'Required modules',
	'PHP_REQUIRED_MODULE_EXPLAIN'   => '<strong>Required</strong> - These PHP modules are required. Installation cannot continue unless they are available.',
	'PHP_SETTINGS'                  => 'PHP version and settings',
	'PHP_SETTINGS_EXPLAIN'          => '<strong>Required</strong> - You must be running at least version 7.4 of PHP in order to install phpBBex.',
	'PHP_URL_FOPEN_SUPPORT'         => 'PHP setting <var>allow_url_fopen</var> is enabled',
	'PHP_URL_FOPEN_SUPPORT_EXPLAIN' => '<strong>Optional</strong> - This setting is optional, however certain phpBBex functions will not work properly without it.',
	'PHP_VERSION_REQD'              => 'PHP version ≥ 7.4',
	'POST_ID'                       => 'Post ID',

	'REQUIREMENTS_TITLE'        => 'Installation compatibility',
	'REQUIREMENTS_EXPLAIN'      => 'Before proceeding with the full installation phpBBex will carry out some tests on your server configuration and files to ensure that you are able to install and run phpBBex. Please ensure you read through the results thoroughly and do not proceed until all the required tests are passed. If you wish to use any of the features depending on the optional tests, you should ensure that these tests are passed also.',
	'RETRY_WRITE'               => 'Retry writing config',
	'RETRY_WRITE_EXPLAIN'       => 'If you wish you can change the permissions on config.php to allow phpBBex to write to it. Should you wish to do that you can click Retry below to try again. Remember to return the permissions on config.php after phpBBex has finished installation.',

	'SELECT_LANG'               => 'Select language',
	'SERVER_CONFIG'             => 'Server configuration',
	'SOFTWARE'                  => 'Board software',
	'SPECIFY_OPTIONS'           => 'Specify conversion options',
	'STAGE_ADMINISTRATOR'       => 'Administrator details',
	'STAGE_ADVANCED'            => 'Advanced settings',
	'STAGE_ADVANCED_EXPLAIN'    => 'The settings on this page are only necessary to set if you know that you require something different from the default. If you are unsure, just proceed to the next page, as these settings can be altered from the Administration Control Panel later.',
	'STAGE_CONFIG_FILE'         => 'Configuration file',
	'STAGE_CREATE_TABLE'        => 'Create database tables',
	'STAGE_CREATE_TABLE_EXPLAIN'    => 'The database tables used by phpBBex have been created and populated with some initial data. Proceed to the next screen to finish installing phpBBex.',
	'STAGE_DATABASE'            => 'Database settings',
	'STAGE_FINAL'               => 'Final stage',
	'STAGE_INTRO'               => 'Introduction',
	'STAGE_IN_PROGRESS'         => 'Conversion in progress',
	'STAGE_REQUIREMENTS'        => 'Requirements',
	'STAGE_SETTINGS'            => 'Settings',
	'STEP_PERCENT_COMPLETED'    => 'Step <strong>%d</strong> of <strong>%d</strong>',
	'SUB_INTRO'                 => 'Introduction',
	'SUB_LICENSE'               => 'License',
	'SUB_SUPPORT'               => 'Support',
	'SUCCESSFUL_CONNECT'        => 'Successful connection',

	'TABLE_PREFIX'              => 'Prefix for tables in database',
	'TABLE_PREFIX_EXPLAIN'      => 'The prefix must start with a letter and must only contain letters, numbers and underscores.',
	'TESTS_PASSED'              => 'Tests passed',
	'TESTS_FAILED'              => 'Tests failed',

	'UNABLE_WRITE_LOCK'         => 'Unable to write lock file.',
	'UNAVAILABLE'               => 'Unavailable',
	'UNWRITABLE'                => 'Unwritable',
	'UPDATE_TOPICS_POSTED'      => 'Generating topics posted information',
	'UPDATE_TOPICS_POSTED_ERR'  => 'An error occurred while generating topics posted information. You can retry this step in the ACP after the conversion process is completed.',
	'VERIFY_OPTIONS'            => 'Verifying conversion options',
	'VERSION'                   => 'Version',

	'WELCOME_INSTALL'           => 'Welcome to phpBBex Installation',
	'WRITABLE'                  => 'Writable',
]);

// Updater
$lang = array_merge($lang, [
	'BACK'              => 'Back',
	'DONE'              => 'Done',
	'ERROR'             => 'Error',
	'NO_ERRORS'         => 'No errors',
	'NO_UPDATES_REQUIRED'       => 'No updates required',
	'PREVIOUS_VERSION'          => 'Previous version',
	'PROGRESS'                  => 'Progress',
	'RESULT'                    => 'Result',
	'SOME_QUERIES_FAILED'       => 'Some queries failed, the statements and errors are listed below.',
	'SQL'                       => 'SQL',
	'SQL_FAILURE_EXPLAIN'       => 'This is probably nothing to worry about, update will continue. Should this fail to complete you may need to seek help at our support forums.',
	'UPDATE_COMPLETED'              => 'Update completed',
	'UPDATE_DATABASE_SCHEMA'        => 'Updating database schema',
	'UPDATE_FILES_NOTICE'           => 'Please make sure you have updated your board files too, this file is only updating your database.',
	'UPDATE_VERSION_OPTIMIZE'       => 'Updating version and optimising tables',
	'UPDATING_DATA'                 => 'Updating data',
	'UPDATING_TO_LATEST_STABLE'     => 'Updating database to latest stable release',
	'UPDATED_VERSION'               => 'Updated version',
]);

// Default database schema entries...
$lang = array_merge($lang, [
	'CONFIG_BOARD_EMAIL_SIG'        => 'Thanks, The Management',
	'CONFIG_SITE_DESC'              => 'A short text to describe your forum',
	'CONFIG_SITENAME'               => 'Your phpBBex',

	'DEFAULT_INSTALL_POST'          => 'This is an example post in your phpBBex installation. Everything seems to be working. You may delete this post if you like and continue to set up your board. During the installation process your first category and your first forum are assigned an appropriate set of permissions for the predefined usergroups administrators, global moderators, guests and registered users. If you also choose to delete your first category and your first forum, do not forget to assign permissions for all these usergroups for all new categories and forums you create. It is recommended to rename your first category and your first forum and copy permissions from these while creating new categories and forums. Have fun!',

	'FORUMS_FIRST_CATEGORY'         => 'Your first category',
	'FORUMS_TEST_FORUM_DESC'        => 'Description of your first forum.',
	'FORUMS_TEST_FORUM_TITLE'       => 'Your first forum',

	'RANKS_SITE_ADMIN_TITLE'        => 'Site Admin',
	'REPORT_WAREZ'                  => 'The post contains links to illegal or pirated software.',
	'REPORT_SPAM'                   => 'The reported post has the only purpose to advertise for a website or another product.',
	'REPORT_OFF_TOPIC'              => 'The reported post is off topic.',
	'REPORT_OTHER'                  => 'The reported post does not fit into any other category, please use the further information field.',

	'TOPICS_TOPIC_TITLE'            => 'Welcome to phpBBex',

	'WARNING_POST_DEFAULT'          => 'Violation of rules',
	'BOARD_DISABLE_DEFAULT'         => 'Forum in maintenance',
]);
