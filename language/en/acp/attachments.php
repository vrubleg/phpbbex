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
	'ACP_ATTACHMENT_SETTINGS_EXPLAIN'	=> 'Here you can configure the main settings for attachments and the associated special categories.',
	'ACP_EXTENSION_GROUPS_EXPLAIN'		=> 'Here you can add, delete, modify or disable your extension groups. Further options include the assignment of a special category to them, changing the download mechanism and defining an upload icon which will be displayed in front of the attachment which belongs to the group.',
	'ACP_MANAGE_EXTENSIONS_EXPLAIN'		=> 'Here you can manage your allowed extensions. To activate your extensions, please refer to the extension groups management panel. We strongly recommend not to allow scripting extensions (such as <code>php</code>, <code>php3</code>, <code>php4</code>, <code>phtml</code>, <code>pl</code>, <code>cgi</code>, <code>py</code>, <code>rb</code>, <code>asp</code>, <code>aspx</code>, and so forth…).',
	'ACP_ORPHAN_ATTACHMENTS_EXPLAIN'	=> 'Here you are able to see orphaned files. This happens mostly if users are attaching files but not submitting the post. You are able to delete the files or attach them to existing posts. Attaching to posts requires a valid post ID, you have to determine this ID by yourself. This will assign the already uploaded attachment to the post you entered.',
	'ADD_EXTENSION'						=> 'Add extension',
	'ADD_EXTENSION_GROUP'				=> 'Add extension group',
	'ADMIN_UPLOAD_ERROR'				=> 'Errors while trying to attach file: “%s”.',
	'ALLOWED_FORUMS'					=> 'Allowed forums',
	'ALLOWED_FORUMS_EXPLAIN'			=> 'Able to post the assigned extensions at the selected (or all if selected) forums.',
	'ALLOWED_IN_PM_POST'				=> 'Allowed',
	'ALLOW_ATTACHMENTS'					=> 'Allow attachments',
	'ALLOW_ALL_FORUMS'					=> 'Allow all forums',
	'ALLOW_IN_PM'						=> 'Allowed in private messaging',
	'ALLOW_PM_ATTACHMENTS'				=> 'Allow attachments in private messages',
	'ALLOW_SELECTED_FORUMS'				=> 'Only forums selected below',
	'ASSIGNED_EXTENSIONS'				=> 'Assigned extensions',
	'ASSIGNED_GROUP'					=> 'Assigned extension group',
	'ATTACH_EXTENSIONS_URL'				=> 'Extensions',
	'ATTACH_EXT_GROUPS_URL'				=> 'Extension groups',
	'ATTACH_ID'							=> 'ID',
	'ATTACH_MAX_FILESIZE'				=> 'Maximum file size',
	'ATTACH_MAX_FILESIZE_EXPLAIN'		=> 'Maximum size of each file. If this value is 0, the uploadable filesize is only limited by your PHP configuration.',
	'ATTACH_MAX_PM_FILESIZE'			=> 'Maximum file size messaging',
	'ATTACH_MAX_PM_FILESIZE_EXPLAIN'	=> 'Maximum size of each file, with 0 being unlimited, attached to a private message.',
	'ATTACH_ORPHAN_URL'					=> 'Orphan attachments',
	'ATTACH_POST_ID'					=> 'Post ID',
	'ATTACH_QUOTA'						=> 'Total attachment quota',
	'ATTACH_QUOTA_EXPLAIN'				=> 'Maximum drive space available for attachments for the whole board, with 0 being unlimited.',
	'ATTACH_TO_POST'					=> 'Attach file to post',

	'CAT_IMAGES'				=> 'Images',
	'CAT_AUDIO_FILES'			=> 'HTML5 Audio',
	'CAT_VIDEO_FILES'			=> 'HTML5 Video',
	'CHECK_CONTENT'				=> 'Check attachment files',
	'CHECK_CONTENT_EXPLAIN'		=> 'Some browsers can be tricked to assume an incorrect mimetype for uploaded files. This option ensures that such files likely to cause this are rejected.',
	'CREATE_GROUP'				=> 'Create new group',
	'CREATE_THUMBNAIL'			=> 'Create thumbnail',
	'CREATE_THUMBNAIL_EXPLAIN'	=> 'Create a thumbnail in all possible situations.',

	'DEFINE_ALLOWED_IPS'			=> 'Define allowed IPs/hostnames',
	'DEFINE_DISALLOWED_IPS'			=> 'Define disallowed IPs/hostnames',
	'DOWNLOAD_ADD_IPS_EXPLAIN'		=> 'To specify several different IPs or hostnames enter each on a new line. To specify a range of IP addresses separate the start and end with a hyphen (-), to specify a wildcard use “*”.',
	'DOWNLOAD_REMOVE_IPS_EXPLAIN'	=> 'You can remove (or un-exclude) multiple IP addresses in one go using the appropriate combination of mouse and keyboard for your computer and browser. Excluded IPs have a blue background.',
	'DISPLAY_INLINED'				=> 'Display images inline',
	'DISPLAY_INLINED_EXPLAIN'		=> 'If set to No image attachments will show as a link.',
	'DISPLAY_ORDER'					=> 'Attachment display order',
	'DISPLAY_ORDER_EXPLAIN'			=> 'Display attachments ordered by time.',

	'EDIT_EXTENSION_GROUP'			=> 'Edit extension group',
	'EXCLUDE_ENTERED_IP'			=> 'Enable this to exclude the entered IP/hostname.',
	'EXCLUDE_FROM_ALLOWED_IP'		=> 'Exclude IP from allowed IPs/hostnames',
	'EXCLUDE_FROM_DISALLOWED_IP'	=> 'Exclude IP from disallowed IPs/hostnames',
	'EXTENSIONS_UPDATED'			=> 'Extensions successfully updated.',
	'EXTENSION_EXIST'				=> 'The extension %s already exists.',
	'EXTENSION_GROUP'				=> 'Extension group',
	'EXTENSION_GROUPS'				=> 'Extension groups',
	'EXTENSION_GROUP_DELETED'		=> 'Extension group successfully deleted.',
	'EXTENSION_GROUP_EXIST'			=> 'The extension group %s already exists.',

	'EXT_GROUP_ARCHIVES'			=> 'Archives',
	'EXT_GROUP_DOCUMENTS'			=> 'Documents',
	'EXT_GROUP_DOWNLOADABLE_FILES'	=> 'Downloadable Files',
	'EXT_GROUP_IMAGES'				=> 'Images',
	'EXT_GROUP_PLAIN_TEXT'			=> 'Plain Text',
	'EXT_GROUP_AUDIO'				=> 'HTML5 Audio',
	'EXT_GROUP_VIDEO'				=> 'HTML5 Video',

	'GO_TO_EXTENSIONS'		=> 'Go to extension management screen',
	'GROUP_NAME'			=> 'Group name',

	'IMAGE_LINK_SIZE'			=> 'Image link dimensions',
	'IMAGE_LINK_SIZE_EXPLAIN'	=> 'Display image attachment as an inline text link if image is larger than this. To disable this behaviour, set the values to 0px by 0px.',

	'MAX_ATTACHMENTS'				=> 'Maximum number of attachments per post',
	'MAX_ATTACHMENTS_PM'			=> 'Maximum number of attachments per private message',
	'MAX_EXTGROUP_FILESIZE'			=> 'Maximum file size',
	'MAX_IMAGE_SIZE'				=> 'Maximum image dimensions',
	'MAX_IMAGE_SIZE_EXPLAIN'		=> 'Maximum size of image attachments. Set both values to 0px by 0px to disable dimension checking.',
	'MAX_THUMB_WIDTH'				=> 'Maximum thumbnail width in pixel',
	'MAX_THUMB_WIDTH_EXPLAIN'		=> 'A generated thumbnail will not exceed the width set here.',
	'MIN_THUMB_FILESIZE'			=> 'Minimum thumbnail file size',
	'MIN_THUMB_FILESIZE_EXPLAIN'	=> 'Do not create a thumbnail for images smaller than this.',
	'MODE_INLINE'					=> 'Inline',
	'MODE_PHYSICAL'					=> 'Physical',

	'NOT_ALLOWED_IN_PM'			=> 'Only allowed in posts',
	'NOT_ALLOWED_IN_PM_POST'	=> 'Not allowed',
	'NOT_ASSIGNED'				=> 'Not assigned',
	'NO_EXT_GROUP'				=> 'None',
	'NO_EXT_GROUP_NAME'			=> 'No group name entered',
	'NO_EXT_GROUP_SPECIFIED'	=> 'No extension group specified.',
	'NO_FILE_CAT'				=> 'None',
	'NO_IMAGE'					=> 'No image',
	'NO_THUMBNAIL_SUPPORT'		=> 'Thumbnail support has been disabled. For proper functionality the GD extension need to be available.',
	'NO_UPLOAD_DIR'				=> 'The upload directory does not exist.',
	'NO_WRITE_UPLOAD'			=> 'The upload directory cannot be written to. Please alter the permissions to allow the webserver to write to it.',

	'ONLY_ALLOWED_IN_PM'	=> 'Only allowed in private messages',
	'ORDER_ALLOW_DENY'		=> 'Allow',
	'ORDER_DENY_ALLOW'		=> 'Deny',

	'REMOVE_ALLOWED_IPS'		=> 'Remove or un-exclude <em>allowed</em> IPs/hostnames',
	'REMOVE_DISALLOWED_IPS'		=> 'Remove or un-exclude <em>disallowed</em> IPs/hostnames',

	'SECURE_ALLOW_DENY'				=> 'Allow/Deny list',
	'SECURE_ALLOW_DENY_EXPLAIN'		=> 'Change the default behaviour when secure downloads are enabled of the Allow/Deny list to that of a <strong>whitelist</strong> (Allow) or a <strong>blacklist</strong> (Deny).',
	'SECURE_DOWNLOADS'				=> 'Enable secure downloads',
	'SECURE_DOWNLOADS_EXPLAIN'		=> 'With this option enabled, downloads are limited to IP’s/hostnames you define.',
	'SECURE_DOWNLOAD_NOTICE'		=> 'Secure Downloads are not enabled. The settings below will be applied after enabling secure downloads.',
	'SECURE_DOWNLOAD_UPDATE_SUCCESS'=> 'The IP list has been updated successfully.',
	'SECURE_EMPTY_REFERRER'			=> 'Allow empty referrer',
	'SECURE_EMPTY_REFERRER_EXPLAIN'	=> 'Secure downloads are based on referrers. Do you want to allow downloads for those omitting the referrer?',
	'SETTINGS_CAT_IMAGES'			=> 'Image category settings',
	'SPECIAL_CATEGORY'				=> 'Special category',
	'SPECIAL_CATEGORY_EXPLAIN'		=> 'Special categories differ between the way presented within posts.',
	'SUCCESSFULLY_UPLOADED'			=> 'Successfully uploaded.',
	'SUCCESS_EXTENSION_GROUP_ADD'	=> 'Extension group successfully added.',
	'SUCCESS_EXTENSION_GROUP_EDIT'	=> 'Extension group successfully updated.',

	'UPLOADING_FILES'				=> 'Uploading files',
	'UPLOADING_FILE_TO'				=> 'Uploading file “%1$s” to post number %2$d…',
	'UPLOAD_DENIED_FORUM'			=> 'You do not have the permission to upload files to forum “%s”.',
	'UPLOAD_ICON'					=> 'Upload icon',
));
