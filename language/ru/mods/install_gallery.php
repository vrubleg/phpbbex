<?php
/**
*
* install_gallery [Russian] (Pthelovod v1.1.4)
*
* @package phpBB Gallery
* @version $Id$
* @copyright (c) 2007 nickvergessen nickvergessen@gmx.de http://www.flying-bits.org
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
**/

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
	'BBCODES_NEEDS_REPARSE'	    	=> 'BBCode нуждается в переобработке.',

	'CAT_CONVERT'			    	=> 'конвертирование phpBB2',
	'CAT_CONVERT_TS'		    	=> 'конвертирование TS Gallery',
	'CAT_UNINSTALL'			     	=> 'удаление phpBB Gallery',

	'CHECK_TABLES'			     	=> 'проверка таблиц',
	'CHECK_TABLES_EXPLAIN'	     	=> 'Следующие таблицы должны существовать, чтобы была возможность конвертирования.',

	'CONVERT_SMARTOR_INTRO'			=> 'Конвертер из „Album-MOD“ от smartor в „phpBB Gallery“',
	'CONVERT_SMARTOR_INTRO_BODY'	=> 'С помощью этого конвертера, Вы можете ковертировать Ваши альбомы, изображения, рейтинги и комментарии из <a href="http://www.phpbb.com/community/viewtopic.php?f=16&t=74772">Album-MOD</a> от Smartor (тестировано с v2.0.56) и <a href="http://www.phpbbhacks.com/download/5028">Full Album Pack</a> (тестировано с v1.4.1) в phpBB галерею.<br /><br /><strong>Примечание:</strong> <strong>Права доступа</strong> <strong>не будут скопированы</strong>.',
	'CONVERT_TS_INTRO'				=> 'Конвертер из „TS Gallery“ в „phpBB Gallery“',
	'CONVERT_TS_INTRO_BODY'			=> 'С помощью этого конвертера, Вы можете ковертировать Ваши альбомы, изображения, рейтинги и комментарии из <a href="http://www.phpbb.com/community/viewtopic.php?f=70&t=610509">TS Gallery</a> (тестированно с v0.2.1) в phpBB галерею.<br /><br /><strong>Примечание:</strong> <strong>Права доступа</strong> <strong>не будут скопированы</strong>.',
	'CONVERT_COMPLETE_EXPLAIN'		=> 'Конвертация из Вашей галереи в phpBB галерею v%s прошло успешно.<br />Удостоверьтесть, что все настройки перенеслись правильно, перед тем как включить конференцию, удалив папку install.<br /><br /><strong>Не забудьте, что права доступа не копировались.</strong><br /><br />Вы так же можете очистить базу данных от пустых записей, для которых изображения отсутствуют. Это можно сделать в ".MODs > phpBB галерея > очистка галереи".',

	'CONVERTED_ALBUMS'			    => 'Альбомы успешно скопированы.',
	'CONVERTED_COMMENTS'	     	=> 'Комментарии успешно скопированы.',
	'CONVERTED_IMAGES'	    		=> 'Изображения успешно скопированы.',
	'CONVERTED_MISC'		    	=> 'Конвертирование остального.',
	'CONVERTED_PERSONALS'	    	=> 'Персональные альбомы успешно скопированы.',
	'CONVERTED_RATES'		    	=> 'Рейтинги успешно скопированы.',
	'CONVERTED_RESYNC_ALBUMS'    	=> 'Пересчет статистики.',
	'CONVERTED_RESYNC_COMMENTS'   	=> 'Пересчет комментариев.',
	'CONVERTED_RESYNC_COUNTS'   	=> 'Пересчет счетчиков.',
	'CONVERTED_RESYNC_RATES'    	=> 'Пересчет рейтингов.',

	'FILE_DELETE_FAIL'				=> 'Файл не может быть удален автоматически, Вам надо это сделать вручную',
	'FILE_STILL_EXISTS'				=> 'Файл все еще существует',
	'FILES_REQUIRED_EXPLAIN'		=> '<strong>Требования</strong> - Для корректного функционирования, phpBB галерее нужно иметь доступ на запись к некотрым файлам и папкам. Если Вы видите надпись “Недоступно”, вы должны изменить права доступа для файла или папки так, чтоб phpBB мог записывать в них.',
	'FILES_DELETE_OUTDATED'			=> 'Удалить устаревшие файлы',
	'FILES_DELETE_OUTDATED_EXPLAIN'	=> 'Действие необратимо, файлы удаляются полностью и не могут быть восстановлены!<br /><br />Примечание:<br />Если у Вас несколько стилей и языков, Вам нужно удалить файлы вручную.',
	'FILES_OUTDATED'				=> 'Устаревшие файлы',
	'FILES_OUTDATED_EXPLAIN'		=> '<strong>Устаревшие</strong> - Для предотвращения харерского доступа, удалите следующие файлы.',
	'FOUND_INSTALL'					=> 'Повторная установка',
	'FOUND_INSTALL_EXPLAIN'			=> '<strong>Повторная установка</strong> - Найдена установленная галерея! Если вы продолжите установку, все данные будут перезаписаны. Все альбомы, изображения и комментарии будут удалены! <strong>Рекомендуется %1$sобновление%2$s галереи.</strong>',
	'FOUND_VERSION'					=> 'Была обнаружена следующая версия',
	'FOUNDER_CHECK'					=> 'Вы являетесь "Основателем" на этой конференции',
	'FOUNDER_NEEDED'				=> 'Вы должны быть "Основателем" на этой конференции!',

	'INSTALL_CONGRATS_EXPLAIN'	=> 'Вы успешно установили phpBB Галерею v%s.<br/><br/><strong>Теперь удалите, переместите или переименуйте папку install перед использованием конференции. Пока папка будет присутствовать, иначе будет доступен только Администраторский раздел (ACP).</strong>',
	'INSTALL_INTRO_BODY'		=> 'С этими настройками, установка phpBB Галереи на Вашу конференцию возможна.',

	'GOTO_GALLERY'				=> 'Перейти в phpBB Галерею',
	'GOTO_INDEX'				=> 'Перейти на главную страницу форума',
	
	'MISSING_CONSTANTS'			=> 'Перед запуском скрипта установки, Вам необходимо загрузить отредактированные файлы, в первую очередь includes/constants.php.',
	'MODULES_CREATE_PARENT'		=> 'Создание родительского стандартного модуля',
	'MODULES_PARENT_SELECT'		=> 'Сменить родительский модуль',
	'MODULES_SELECT_4ACP'		=> 'Сменить родительский модуль в "администраторском разделе"',
	'MODULES_SELECT_4LOG'		=> 'Сменить родительский модуль в "Gallery log"',
	'MODULES_SELECT_4MCP'		=> 'Сменить родительский модуль в "модераторском разделе"',
	'MODULES_SELECT_4UCP'		=> 'Сменить родительский модуль в "личном разделе"',
	'MODULES_SELECT_NONE'		=> 'нет родительского модуля',

	'NO_INSTALL_FOUND'			=> 'Установка не найдена!',

	'OPTIONAL_EXIFDATA'				=> 'Функция "exif_read_data" доступна',
	'OPTIONAL_EXIFDATA_EXP'			=> 'Exif-модуль не загружен или не установлен.',
	'OPTIONAL_EXIFDATA_EXPLAIN'		=> 'Если функция доступна, exif-данные изображения отображаются на странице изображения',
	'OPTIONAL_IMAGEROTATE'			=> 'Функция "imagerotate" доступна',
	'OPTIONAL_IMAGEROTATE_EXP'		=> 'Вы должны обновить версию GD, текущая версия - "%s".',
	'OPTIONAL_IMAGEROTATE_EXPLAIN'	=> 'Если функция доступна, вы сможете вращать изображения при загрузке и редактировании.',

	'PAYPAL_DEV_SUPPORT'				=> '</p><div class="errorbox">
	<h3>Примечания автора</h3>
	<p>Созадание, обслуживание и обновление этого мода требует много времени и усилий. Если вам нравится мод и есть желание выразить свою благодарность через пожертвования, они будут высоко оценены. Мой Paypal ID - <strong>nickvergessen@gmx.de</strong>, или же свяжитесь со мной по email.<br /><br /> Рекомендуемый взнос 25,00€ (но буду благодарен за любую сумму).</p><br />
	<a href="http://www.flying-bits.org/go/paypal"><input type="submit" value="Make PayPal-Donation" name="paypal" id="paypal" class="button1" /></a>
</div><p>',

	'PHP_SETTINGS'				=> 'Настройки PHP',
	'PHP_SETTINGS_EXP'			=> 'Эти настройки PHP требуются для установки и запуска галереи.',
	'PHP_SETTINGS_OPTIONAL'		=> 'Дополнительные настройки PHP',
	'PHP_SETTINGS_OPTIONAL_EXP'	=> 'Эти настройки PHP <strong>НЕ</strong> требуются нормального функционирования галереи, но они дадут возможность использовать дополнительные функции.',

	'REQ_GD_LIBRARY'			=> 'GD-библиотека установлена',
	'REQ_PHP_VERSION'			=> 'php версии >= %s',
	'REQ_PHPBB_VERSION'			=> 'phpBB версии >= %s',
	'REQUIREMENTS_EXPLAIN'		=> 'Перед переходом к основной установке, phpBB проведет тестирование настроек Вашего сервера и проверит некотрые файлы, чтобы убедиться в возможности установки и запуска phpBB галереи. Внимательно ознакомьтесь с результатами тестирования и не принимайте никаких действий, пока тестирование не завершится.',

	'STAGE_ADVANCED_EXPLAIN'		=> 'Выберите родительский модуль для модулей галереи. Обычно не требуется менять.',
	'STAGE_COPY_TABLE'				=> 'Копирование таблиц базы данных',
	'STAGE_COPY_TABLE_EXPLAIN'		=> 'Таблицы базы данных для альбомных и пользовательских данных имеют одинаковые имена в TS Gallery и phpBB Gallery. Мы делаем копию, чтобы иметь возможность конвертировать данные.',
	'STAGE_CREATE_TABLE_EXPLAIN'	=> 'Таблицы базы данных для phpBB Галереи созданы и заполнены первоначальными данными. Проследуйте на следующий экран для завершения установки.',
	'STAGE_DELETE_TABLES'			=> 'Очистка Базы Данных',
	'STAGE_DELETE_TABLES_EXPLAIN'	=> 'Содержимое Базы Данных галереи было удалено. Проследуйте далее для завершения удаления phpBB галереи.',
	'SUPPORT_BODY'					=> '<p>Полная поддержка будет оказываться на текущий стабильный релиз phpBB галереи бесплатно по следующим вопросам:</p><ul><li>установка</li><li>настройка</li><li>технические вопросы</li><li>проблемы, связанные с потенциальными ошибками в программном обеспечении</li><li>обновление с Release Candidate (RC) версий до последней стабильной версии.</li><li>конвертирование из Smartor\'s Album-MOD для phpBB 2.0.x в phpBB Галерею для phpBB3</li><li>конвертирование из TS Gallery в phpBB Галерею</li></ul><p>Использование бета-версий рекомендуется с осторожностью. Если выходят обновления, их рекомендуется устанавливать в кратчайшие сроки.</p><p>Поддержка оказывается на следующих конференциях</p><ul><li><a href="http://www.flying-bits.org/">flying-bits.org - MOD-Autor nickvergessen\'s board</a></li><li><a href="http://www.phpbb.de/">phpbb.de</a></li><li><a href="http://www.phpbb.com/">phpbb.com</a></li></ul></p><p>Актуальный русский перевод доступен на сайте официальной российской поддержки phpBB <a href="http://www.phpbbguru.net/">www.phpbbguru.net</a></p>',

	'TABLE_ALBUM'		     		=> 'таблица, содержащая изображения',
	'TABLE_ALBUM_CAT'		    	=> 'таблица, содержащая альбомы',
	'TABLE_ALBUM_COMMENT'	    	=> 'таблица, содержащая комментарии',
	'TABLE_ALBUM_CONFIG'	    	=> 'таблица, содержащая настройки',
	'TABLE_ALBUM_RATE'		    	=> 'таблица, содержащая оценки',
	'TABLE_EXISTS'			    	=> 'найдены',
	'TABLE_MISSING'			    	=> 'отсутствуют',
	'TABLE_PREFIX_EXPLAIN'	    	=> 'Префикс phpBB2-установки',

	'UNINSTALL_INTRO'					=> 'Удаление галереи',
	'UNINSTALL_INTRO_BODY'				=> 'С помощью этой функции можно удалить phpBB галерею с вашей конференции.<br /><br /><strong>ВНИМАНИЕ: Все альбомы, изображения и комментарии будут удалены без возможности восстановления.!</strong>',
	'UNINSTALL_REQUIREMENTS'			=> 'Требования',
	'UNINSTALL_REQUIREMENTS_EXPLAIN'	=> 'Прежде чем перейти к полному удалению phpBB галереи, будут проведены некоторые тесты, чтобы убедиться, есть ли у вас право удалять phpBB галерею.',
	'UNINSTALL_START'					=> 'Удаление',
	'UNINSTALL_FINISHED'				=> 'Удаление почти закончено',
	'UNINSTALL_FINISHED_EXPLAIN'		=> 'Вы успешно удалили phpBB галерею.<br/><br/><strong>Теперь вам осталось только отменить изменения файлов конференции, описанные в install.xml и удалить файлы галереи. После этого, ваша конференция будет полностью очищена от галереи.</strong>',

	'UPDATE_INSTALLATION_EXPLAIN'    	=> 'Здесь Вы можете обновить версию phpBB Галереи.',

	'VERSION_NOT_SUPPORTED'		        => 'Извините, но обновление с версий ниже < 1.0.6 не поддерживаются текущей версией инсталятора.',
));

?>