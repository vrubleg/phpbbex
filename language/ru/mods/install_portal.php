<?php
/**
*
* @package - Board3portal
* @version $Id: install_portal.php 483 2009-03-18 16:40:32Z kevin74 $
* @copyright (c) kevin / saint ( www.board3.de/ ), (c) Ice, (c) nickvergessen ( www.flying-bits.org/ ), (c) redbull254 ( www.digitalfotografie-foren.de ), (c) Christian_N ( www.phpbb-projekt.de )
* @installer based on: phpBB Gallery by nickvergessen, www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

/**
* DO NOT CHANGE
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
	'INSTALL_CONGRATS_EXPLAIN'		=> 	'<p>Вы успешно установили Board3 Portal v%s<br/><br/><strong>Удалите папку «install», прежде чем пользоваться конференцией. Пока эта папка не удалена, доступным будет только администраторский раздел.</strong></p>',
	'INSTALL_INTRO_BODY'			=> 	'Этот инсталлятор установит Board3 Portal на вашу конференцию.',

	'MISSING_CONSTANTS'			=> 	'Перед тем как запустить установку портала, вы должны загрузить на сервер отредактированные файлы, в особенности это касается файла /includes/constants.php.',
	'MODULES_CREATE_PARENT'		=> 	'Создать родительский стандартный модуль',
	'MODULES_PARENT_SELECT'		=> 	'Выбрать родительский модуль',
	'MODULES_SELECT_4ACP'		=> 	'Родительский модуль для ACP',
	'MODULES_SELECT_NONE'		=> 	'Нет родительского модуля',

	'STAGE_ADVANCED_EXPLAIN'        =>  'Укажите родительский модуль для модулей Board3 Portal. Обычно нет необходимости изменять настройки по умолчанию.',
	'STAGE_CREATE_TABLE_EXPLAIN'	=> 	'Таблицы базы данных портала созданы и заполнены начальными данными. Перейдите к следующему этапу установки Board3 Portal.',
	'STAGE_ADVANCED_SUCCESSFUL'		=> 	'Модули Board3 Portal созданы. Переходите к окончанию установки Board3 Portal.',
	'STAGE_UNINSTALL'				=> 	'Удаление',

	'FILES_EXISTS'				=> 	'Файл всё ещё существует',
	'FILES_OUTDATED'			=> 	'Устаревшие файлы',
	'FILES_OUTDATED_EXPLAIN'	=> 	'<strong>Устаревшие файлы</strong> — пожалуйста, удалите эти файлы во избежание проблем с безопасностью.',
	'REQUIREMENTS_EXPLAIN'		=> 	'Для продолжения обновления удалите с сервера все устаревшие файлы.',
	'NOT_REQUIREMENTS_EXPLAIN'	=> 	'Устаревших файлов на сервере не обнаружено, вы можете продолжить обновление.',

	'UPDATE_INSTALLATION'			=> 	'Обновление Board3 Portal',
	'UPDATE_INSTALLATION_EXPLAIN'	=> 	'Обновление вашего Board3 Portal до последней версии.',
	'UPDATE_CONGRATS_EXPLAIN'		=> 	'<p>Вы успешно обновили Board3 Portal до v%s<br/><br/><strong>Удалите папку «install», прежде чем пользоваться конференцией. Пока эта папка не удалена, доступным будет только администраторский раздел.</strong></p>',

	'UNINSTALL_INTRO'				=> 	'Мастер удаления',
	'UNINSTALL_INTRO_BODY'			=> 	'Мастер проведёт вас по этапам удаления Board3 Portal с вашей конференции.',
	'CAT_UNINSTALL'					=> 	'Удаление',
	'UNINSTALL_CONGRATS'			=> 	'<h1>Board3 Portal удалён.</h1>
									Вы успешно удалили Board3 Portal.',
	'UNINSTALL_CONGRATS_EXPLAIN'	=> 	'<strong>Удалите папку «install», прежде чем пользоваться конференцией. Пока эта папка не удалена, доступным будет только администраторский раздел.<br /><br />Убедитесь, что удалили файлы, относящиеся к порталу и вернули в изначальный вид все файлы конференции, модифицированные при установке портала.</strong></p>',

	'SUPPORT_BODY'		=> 	'Поддержка последней стабильной версии Board3 Portal:</p><ul><li>Установка</li><li>Технические вопросы</li><li>Проблемы, связанные с кодом</li><li>Обновление релиз-андидатов (RC) или стабильных версий до последней стабильной версии</li></ul><p>Поддержку вы найдёте на форумах:</p><ul><li><a href="http://www.board3.de/">board3.de — Домашняя страница Кевина, автора мода</a></li><li><a href="http://www.phpbb.de/">phpbb.de</a></li><li><a href="http://www.phpbb.com/">phpbb.com</a></li><li><a href="http://www.phpbbguru.net/">phpbbguru.net — русская поддержка phpBB</a></li></ul><p>',
	'GOTO_INDEX'		=> 	'Перейти к конференции',
	'GOTO_PORTAL'		=> 	'Перейти к порталу',
	
	'CAT_CONVERT'					=> 'Конвертировать phpBB3 Portal',
	'CONVERT_P3P_INTRO'				=> 'Конвертация вашего phpBB3 Portal в Board3 Portal',
	'STAGE_REMOVE_P3P'				=> 'Удалить phpBB3 Portal',
	'STAGE_REMOVE_TABLE'			=> 'Удалить таблицы базы данных',
	'STAGE_REMOVE_TABLE_EXPLAIN'	=> 'Таблицы базы данных phpBB3 Portal удалены. Переходите к следующему этапу установки Board3 Portal.',
	'CONVERT_COMPLETE_EXPLAIN'		=> 'phpBB3 Portal успешно конвертирован в Board3 Portal v%s.<br /><br /><strong>Удалите папку «install», прежде чем пользоваться конференцией. Пока эта папка не удалена, доступным будет только администраторский раздел.</strong></p>',
));

?>