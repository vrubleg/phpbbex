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

// Forum Admin
$lang = array_merge($lang, array(
	'AUTO_PRUNE_DAYS'			=> 'Автоочистка по дате последнего сообщения',
	'AUTO_PRUNE_DAYS_EXPLAIN'	=> 'Количество дней с последнего сообщения, по прошествии которых тема будет удалена.',
	'AUTO_PRUNE_FREQ'			=> 'Частота автоматической очистки',
	'AUTO_PRUNE_FREQ_EXPLAIN'	=> 'Время в днях между выполнением автоочистки.',
	'AUTO_PRUNE_VIEWED'			=> 'Автоочистка по времени просмотра',
	'AUTO_PRUNE_VIEWED_EXPLAIN'	=> 'Количество дней с последнего просмотра, по прошествии которых тема будет удалена.',

	'CONTINUE'						=> 'Продолжить',
	'COPY_PERMISSIONS'				=> 'Копировать права доступа из',
	'COPY_PERMISSIONS_EXPLAIN'		=> 'В целях упрощения настройки прав доступа для нового раздела вы можете скопировать в него права из другого существующего раздела.',
	'COPY_PERMISSIONS_ADD_EXPLAIN'	=> 'Вновь созданному разделу будут присвоены те же права доступа, что и у выбранного из списка. Если ничего не выбрано, созданный раздел не будет отображаться на форуме до установки прав доступа.',
	'COPY_PERMISSIONS_EDIT_EXPLAIN'	=> 'Если вы выбрали копирование прав доступа, разделу будут присвоены те же права доступа, что и выбранному здесь. Все ранее установленные права доступа к этому разделу будут при этом заменены. Если раздел не выбран, будут сохранены текущие права доступа.',
	'COPY_TO_ACL'					=> 'Кроме того, вы также можете %sнастроить новые права%s для этого раздела.',
	'CREATE_FORUM'					=> 'Создать раздел',

	'DECIDE_MOVE_DELETE_CONTENT'		=> 'Удалить содержимое или переместить в раздел',
	'DECIDE_MOVE_DELETE_SUBFORUMS'		=> 'Удалить подразделы или переместить в раздел',
	'DEFAULT_STYLE'						=> 'Стиль по умолчанию',
	'DELETE_ALL_POSTS'					=> 'Удалить сообщения',
	'DELETE_SUBFORUMS'					=> 'Удалить подразделы и сообщения',
	'DISPLAY_ACTIVE_TOPICS'				=> 'Включить активные темы',
	'DISPLAY_ACTIVE_TOPICS_EXPLAIN'		=> 'Если включено, в данной категории будут отображаться активные темы из выбранных подразделов.',

	'EDIT_FORUM'					=> 'Редактирование раздела',
	'ENABLE_INDEXING'				=> 'Включить поисковое индексирование',
	'ENABLE_INDEXING_EXPLAIN'		=> 'Если включено, то сообщения этого раздела будут индексироваться для поиска.',
	'ENABLE_POST_REVIEW'			=> 'Включить просмотр сообщений',
	'ENABLE_POST_REVIEW_EXPLAIN'	=> 'Если включено, пользователи смогут пересмотреть своё сообщение, если во время его создания в теме появились новые сообщения. Эту опцию желательно отключать на чат-разделах.',
	'ENABLE_RECENT'					=> 'Показывать активные темы',
	'ENABLE_RECENT_EXPLAIN'			=> 'Если включено, то темы этого раздела будут отображаться в списке активных тем.',
	'ENABLE_TOPIC_ICONS'			=> 'Включить значки тем',

	'FORUM_ADMIN'						=> 'Управление разделами',
	'FORUM_ADMIN_EXPLAIN'				=> 'phpBBex основан на разделах. Категория является особым типом раздела. Каждый раздел может иметь неограниченное количество подразделов, и вы можете определять, разрешено в нём создавать темы или нет. Здесь вы можете добавлять, редактировать, закрывать, открывать каждый из разделов, устанавливать некоторые дополнительные настройки. Если ваши сообщения и темы рассинхронизированы, вы можете также синхронизировать раздел. <strong>Вы должны скопировать или установить нужные права для того, чтобы вновь созданный раздел отображался в списке разделов.</strong>',
	'FORUM_AUTO_PRUNE'					=> 'Включить автоочистку',
	'FORUM_AUTO_PRUNE_EXPLAIN'			=> 'Очищает раздел от тем, установите параметры периодичности/времени ниже.',
	'FORUM_CREATED'						=> 'Раздел успешно создан.',
	'FORUM_DATA_NEGATIVE'				=> 'Параметры очистки не могут быть отрицательными.',
	'FORUM_DESC_TOO_LONG'				=> 'Описание раздела слишком длинное. Описание не должно превышать 4000 символов.',
	'FORUM_DELETE'						=> 'Удаление раздела',
	'FORUM_DELETE_EXPLAIN'				=> 'Форма ниже позволяет вам удалить раздел. Если в разделе разрешено создавать сообщения, вы можете решить, куда переместить все имеющиеся в нём темы (разделы).',
	'FORUM_DELETED'						=> 'Раздел успешно удалён.',
	'FORUM_DESC'						=> 'Описание',
	'FORUM_DESC_EXPLAIN'				=> 'Любая заданная здесь разметка будет отображена в этом же виде.',
	'FORUM_EDIT_EXPLAIN'				=> 'Форма ниже позволяет вам настраивать этот раздел. Учтите, что настройки модерирования и количества сообщений производятся в правах доступа к разделам для каждого отдельного пользователя или группы.',
	'FORUM_IMAGE'						=> 'Значок раздела',
	'FORUM_IMAGE_EXPLAIN'				=> 'Путь относительно корневого каталога phpBBex к дополнительному изображению, ассоциированному с этим разделом.',
	'FORUM_IMAGE_NO_EXIST'				=> 'Указанный значок раздела не существует',
	'FORUM_LINK_EXPLAIN'				=> 'Полная ссылка (URL, включая протокол, например <samp>http://</samp>), на которую будет перенаправлен пользователь при щелчке по данному разделу.',
	'FORUM_LINK_TRACK'					=> 'Отслеживать переходы',
	'FORUM_LINK_TRACK_EXPLAIN'			=> 'Записывает количество щелчков по ссылке на раздел.',
	'FORUM_NAME'						=> 'Имя раздела',
	'FORUM_NAME_EMPTY'					=> 'Необходимо ввести имя этого раздела.',
	'FORUM_PARENT'						=> 'Родительский раздел',
	'FORUM_PASSWORD'					=> 'Пароль к разделу',
	'FORUM_PASSWORD_CONFIRM'			=> 'Подтверждение пароля к разделу',
	'FORUM_PASSWORD_CONFIRM_EXPLAIN'	=> 'Необходимо только в случае, если задан пароль к разделу.',
	'FORUM_PASSWORD_EXPLAIN'			=> 'Устанавливает пароль для этого раздела, предпочтительно использование системы прав доступа.',
	'FORUM_PASSWORD_UNSET'				=> 'Удалить пароль раздела',
	'FORUM_PASSWORD_UNSET_EXPLAIN'		=> 'Отметьте, если хотите удалить пароль раздела.',
	'FORUM_PASSWORD_OLD'				=> 'Данный пароль раздела использует устаревший метод шифрования и должен быть изменён.',
	'FORUM_PASSWORD_MISMATCH'			=> 'Введённые пароли не совпадают.',
	'FORUM_PRUNE_SETTINGS'				=> 'Параметры очистки раздела',
	'FORUM_RESYNCED'					=> 'Раздел «%s» успешно синхронизирован',
	'FORUM_RULES_EXPLAIN'				=> 'Правила раздела отображаются на каждой странице в пределах данного раздела.',
	'FORUM_RULES_LINK'					=> 'Ссылка на правила раздела',
	'FORUM_RULES_LINK_EXPLAIN'			=> 'Здесь вы можете задать ссылку (URL) на страницу/сообщение с правилами раздела. При этом текст правил раздела будет заменён.',
	'FORUM_RULES_PREVIEW'				=> 'Просмотр правил раздела',
	'FORUM_RULES_TOO_LONG'				=> 'Правила раздела не должны превышать 4000 символов.',
	'FORUM_SETTINGS'					=> 'Настройки раздела',
	'FORUM_STATUS'						=> 'Статус раздела',
	'FORUM_STYLE'						=> 'Стиль раздела',
	'FORUM_DISPLAY_SETTINGS'			=> 'Настройки отображения раздела',
	'FORUM_TOPICS_PAGE'					=> 'Тем на странице',
	'FORUM_TOPICS_PAGE_EXPLAIN'			=> 'Если отлично от нуля, это значение заменит настройку количества тем на страницу по умолчанию.',
	'FORUM_TOPICS_DAYS'					=> 'Показывать темы за',
	'FORUM_TOPICS_DIR'					=> 'Порядок сортировки тем',
	'FORUM_TOPICS_KEY'					=> 'Поле сортировки тем',
	'FORUM_TYPE'						=> 'Тип раздела',
	'FORUM_UPDATED'						=> 'Сведения о разделе успешно обновлены.',

	'FORUM_WITH_SUBFORUMS_NOT_TO_LINK'		=> 'Вы хотите изменить раздел с сообщениями и подразделами на ссылку. Переместите все подразделы в другой раздел перед выполнением этой процедуры, иначе вы больше не увидите подразделы, связанные с этим разделом.',

	'GENERAL_FORUM_SETTINGS'	=> 'Общие настройки раздела',

	'LINK'					=> 'Ссылка',
	'LIST_INDEX'			=> 'Показывать раздел в списке подразделов',
	'LIST_INDEX_EXPLAIN'	=> 'Отображает ссылку на данный раздел в списке подразделов родительского раздела, если таковой существует.',
	'LIST_SUBFORUMS'		=> 'Показывать подразделы в списке',
	'LIST_SUBFORUMS_EXPLAIN'=> 'Отображает подразделы этого раздела на главной и других страницах как ссылку в списке, если для этих подразделов включена функция «Показывать раздел в списке подразделов».',
	'LOCKED'				=> 'Закрыт',

	'MOVE_POSTS_NO_POSTABLE_FORUM'	=> 'Выбранный для перемещения сообщений раздел закрыт. Выберите открытый раздел.',
	'MOVE_POSTS_TO'					=> 'Переместить сообщения в',
	'MOVE_SUBFORUMS_TO'				=> 'Переместить подразделы в',

	'NO_DESTINATION_FORUM'			=> 'Не указан раздел для перемещения содержимого.',
	'NO_FORUM_ACTION'				=> 'Не задано действие для содержимого раздела.',
	'NO_PARENT'						=> 'Нет',
	'NO_PERMISSIONS'				=> 'Не копировать права доступа',
	'NO_PERMISSION_FORUM_ADD'		=> 'У вас нет необходимых прав для добавления разделов.',
	'NO_PERMISSION_FORUM_DELETE'	=> 'У вас нет необходимых прав для удаления разделов.',

	'PARENT_IS_LINK_FORUM'		=> 'Указанный родительский раздел является ссылкой. Ссылки не могут содержать подразделов. Укажите категорию или раздел в качестве родительского раздела.',
	'PARENT_NOT_EXIST'			=> 'Родительский раздел не существует.',
	'PRUNE_ANNOUNCEMENTS'		=> 'Очистить объявления',
	'PRUNE_STICKY'				=> 'Очистить закреплённые темы',
	'PRUNE_OLD_POLLS'			=> 'Очистить старые опросы',
	'PRUNE_OLD_POLLS_EXPLAIN'	=> 'Удалять темы, в опросах которых не было голосов за указанное выше количество дней с последнего сообщения.',

	'REDIRECT_ACL'	=> 'Теперь вы можете %sустановить права доступа%s для этого раздела.',

	'SYNC_IN_PROGRESS'			=> 'Синхронизация раздела',
	'SYNC_IN_PROGRESS_EXPLAIN'	=> 'Идёт синхронизация тем %1$d/%2$d.',

	'TYPE_CAT'			=> 'Категория',
	'TYPE_FORUM'		=> 'Раздел',
	'TYPE_LINK'			=> 'Ссылка',

	'UNLOCKED'			=> 'Открыт',
));
