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

// Privacy policy and T&C
$lang = array_merge($lang, array(
	'TERMS_OF_USE_CONTENT'	=> 'Заходя на форум «%1$s», вы подтверждаете своё согласие со следующими условиями. Если вы не согласны с ними, пожалуйста, не заходите и не пользуйтесь форумом «%1$s». Мы оставляем за собой право изменять эти правила в любое время и сделаем всё возможное, чтобы уведомить вас об этом, однако с вашей стороны было бы разумным регулярно просматривать этот текст на предмет изменений, так как использование форума «%1$s» после обновленния/исправленния условий означает ваше согласие с ними.<br />
	<br />
	Вы соглашаетесь не размещать оскорбительных, угрожающих, клеветнических сообщений, порнографических сообщений, призывов к национальной розни и прочих сообщений, которые могут нарушить законы вашей страны или международное право. Попытки размещения таких сообщений могут привести к вашему немедленному отключению от форума, при этом ваш провайдер будет поставлен в известность, если мы сочтём это нужным. IP-адреса всех сообщений сохраняются для возможности проведения такой политики. Вы соглашаетесь с тем, что администраторы форума «%1$s» имеют право удалить, отредактировать, перенести или закрыть любую тему в любое время по своему усмотрению. Как пользователь вы согласны с тем, что введённая вами информация будет храниться в базе данных. Хотя эта информация не будет открыта третьим лицам без вашего разрешения, администрация форума «%1$s» не может быть ответственна за действия хакеров, которые могут привести к несанкционированному доступу к ней.<br />
	<br />
	Также вы соглашаетесь соблюдать все <a href="faq.php?mode=rules">правила форума</a> «%1$s».
	',

	'PRIVACY_POLICY'		=> 'Это соглашение подробно объясняет, как «%1$s» и его подразделения (в дальнейшем «мы», «наш», «%1$s», «%2$s») и phpBBex (в дальнейшем «программное обеспечение phpBBex», «разработчики phpBBex») используют информацию, полученную во время любой из ваших пользовательских сессий (в дальнейшем «ваша информация»).<br />
	<br />
	Ваша информация собирается двумя способами. Во-первых, просмотр «%1$s» приведёт к созданию программным обеспечением phpBBex определённого числа cookies (небольшие текстовые файлы, загружаемые в каталог временных файлов вашего браузера). Первые две cookie содержат только идентификатор пользователя (в дальнейшем «user-id») и идентификатор анонимной сессии (в дальнейшем «session-id»), автоматически присвоенные вам программным обеспечением phpBBex. Третья cookie будет создана после просмотра одной из тем форума «%1$s» и будет использоваться для хранения информации о прочтённых вами темах, повышая таким образом удобство работы с форумом.<br />
	<br />
	Также во время просмотра форума «%1$s» мы можем установить cookies, внешние по отношению к программному обеспечению phpBBex, однако они выходят за рамки этого документа, целью которого является рассмотрение страниц, созданных исключительно программным обеспечением phpBBex. Вторым источником получения вашей информации являются данные, которые вы отправляете на форум. Этими данными могут быть, но не исчерпываются, следующие данные: сообщения, размещённые под учётной записью Гостя (в дальнейшем «анонимные сообщения»), данные, указанные при регистрации на форуме «%1$s» (в дальнейшем «ваша учётная запись») и сообщения, оставленные вами после регистрации и авторизации (в дальнейшем «ваши сообщения»).<br />
	<br />
	Ваша учётная запись будет содержать, как минимум, однозначно идентифицируемое имя (в дальнейшем «ваше имя пользователя»), индивидуальный пароль для входа под вашей учётной записью (далее «ваш пароль») и реальный адрес email (в дальнейшем «ваш адрес email»). Ваша информация из вашей учётной записи на форуме «%1$s» охраняется законами о защите компьютерной информации, применяемыми в стране, предоставляющей нам услуги хостинга. Любая информация, запрашиваемая при регистрации на форуме «%1$s», кроме вашего имени пользователя, вашего пароля и вашего адреса email, может быть как необходимой, так и необязательной ко вводу, на усмотрение администрации форума «%1$s». В любом случае у вас есть возможность выбрать, какая информация из вашей учётной записи будет общедоступна. Кроме того, у вас есть возможность согласиться/отказаться от получения сообщений, автоматически сгенерированных программным обеспечением phpBBex.<br />
	<br />
	Ваш пароль надёжно зашифрован (односторонним хэшированием). Однако не рекомендуется использовать этот же самый пароль, регистрируясь на других сайтах. Ваш пароль является средством доступа к вашей учётной записи на форуме «%1$s», пожалуйста, храните его в тайне, ни при каких обстоятельствах ни представители «%1$s», ни разработчики phpBBex, ни другое третье лицо не вправе спрашивать ваш пароль. В случае, если вы забудете ваш пароль к вашей учётной записи, вы сможете воспользоваться функцией восстановления пароля «Забыли пароль?», предусмотренной программным обеспечением phpBBex. Вам будет необходимо ввести ваше имя пользователя и ваш адрес email, после чего программное обеспечение phpBBex сгенерирует вам новый пароль для вашей учётной записи.<br />
	',
));

// Common language entries
$lang = array_merge($lang, array(
	'ACCOUNT_ACTIVE'				=> 'Ваша учётная запись была активирована. Спасибо за регистрацию.',
	'ACCOUNT_ACTIVE_ADMIN'			=> 'Ваша учётная запись была активирована.',
	'ACCOUNT_ACTIVE_PROFILE'		=> 'Ваша учётная запись успешно была повторно активирована.',
	'ACCOUNT_ADDED'					=> 'Спасибо за регистрацию, учётная запись была создана. Вы можете войти в систему, используя ваши имя и пароль.',
	'ACCOUNT_COPPA'					=> 'Ваша учётная запись была создана, но теперь она должна быть одобрена, более подробная информация была выслана вам по email.',
	'ACCOUNT_EMAIL_CHANGED'			=> 'Ваша учётная запись была обновлена. Однако на этом форуме необходимо повторно активировать учётную запись при изменении адреса email. Ключ для активирования был отправлен на указанный вами новый адрес email. Проверьте свою электронную почту для получения более подробной информации.',
	'ACCOUNT_EMAIL_CHANGED_ADMIN'	=> 'Ваша учётная запись была обновлена. Однако на этом форуме необходимо повторное активирование учётной записи администратором при изменении адреса email. Сообщение email было отправлено администратору. Вы будете уведомлены, когда ваша учётная запись будет повторно активирована.',
	'ACCOUNT_INACTIVE'				=> 'Учётная запись была создана. Однако на этом форуме требуется активация учётной записи, ключ для активации был выслан на введённый вами адрес. Проверьте свою электронную почту для получения более подробной информации.',
	'ACCOUNT_INACTIVE_ADMIN'		=> 'Учётная запись была создана. Однако на этом форуме требуется активация новой учётной записи группой администраторов. Им было отправлено сообщение email. Вы будете уведомлены, когда ваша учётная запись будет активирована.',
	'ACTIVATION_EMAIL_SENT'			=> 'Письмо для активации учётной записи было выслано на ваш адрес email.',
	'ACTIVATION_EMAIL_SENT_ADMIN'	=> 'Письма для активации учётной записи были высланы на адреса email администраторов.',
	'ADD'							=> 'Добавить',
	'ADD_BCC'						=> 'Добавить [Копия]',
	'ADD_FOES'						=> 'Добавить новых недругов',
	'ADD_FOES_EXPLAIN'				=> 'Вы можете ввести несколько имён пользователей, каждое на отдельной строке.',
	'ADD_FOLDER'					=> 'Добавить папку',
	'ADD_FRIENDS'					=> 'Добавить новых друзей',
	'ADD_FRIENDS_EXPLAIN'			=> 'Вы можете ввести несколько имён пользователей, каждое на отдельной строке.',
	'ADD_NEW_RULE'					=> 'Добавить новое правило',
	'ADD_RULE'						=> 'Добавить правило',
	'ADD_TO'						=> 'Добавить [Кому]',
	'ADD_USERS_UCP_EXPLAIN'			=> 'Здесь вы можете добавлять новых пользователей в группу. Вы можете выбрать, станет ли эта группа группой по умолчанию для добавленных в неё пользователей. Вводите каждое имя пользователя на отдельной строке.',
	'ADMIN_EMAIL'					=> 'Получать email-рассылки администрации',
	'AGREE'							=> 'Я согласен с этими условиями',
	'ALLOW_PM'						=> 'Разрешить пользователям посылать вам личные сообщения',
	'ALLOW_PM_EXPLAIN'				=> 'Учтите, что администраторы и модераторы всегда смогут посылать вам сообщения.',
	'ALREADY_ACTIVATED'				=> 'Вы уже активировали свою учётную запись.',
	'ATTACHMENTS_EXPLAIN'			=> 'Это список вложений в сообщениях, оставленных на этом форуме.',
	'ATTACHMENTS_DELETED'			=> 'Вложения успешно удалены.',
	'ATTACHMENT_DELETED'			=> 'Вложение успешно удалено.',
	'AVATAR_CATEGORY'				=> 'Категория',
	'AVATAR_EXPLAIN'				=> 'Максимальные размеры: %1$d×%2$d пикселей, %3$.2f КБ. Допустимые форматы файлов: JPG, PNG, GIF.',
	'AVATAR_FEATURES_DISABLED'		=> 'Аватары в настоящее время отключены.',
	'AVATAR_GALLERY'				=> 'Галерея аватар',
	'AVATAR_GENERAL_UPLOAD_ERROR'	=> 'Невозможно закачать аватару в %s.',
	'AVATAR_NOT_ALLOWED'			=> 'Ваша аватара не может быть отображена, поскольку аватары запрещены.',
	'AVATAR_PAGE'					=> 'Страница',
	'AVATAR_TYPE_NOT_ALLOWED'		=> 'Ваша текущая аватара не может быть отображена, поскольку её тип запрещён.',

	'BACK_TO_DRAFTS'			=> 'Вернуться к сохранённым черновикам',
	'BACK_TO_LOGIN'				=> 'Вернуться на страницу входа',
	'BIRTHDAY'					=> 'День рождения',
	'BIRTHDAY_EXPLAIN'			=> 'Если вы укажете год рождения, ваш возраст будет отображаться на форуме.',
	'BOARD_DATE_FORMAT'			=> 'Формат даты',
	'BOARD_DATE_FORMAT_EXPLAIN'	=> 'Синтаксис идентичен функции <a href="http://www.php.net/date">date()</a> языка PHP.',
	'BOARD_DST'					=> 'Сейчас действует летнее время',
	'BOARD_LANGUAGE'			=> 'Язык',
	'BOARD_STYLE'				=> 'Стиль форума',
	'BOARD_TIMEZONE'			=> 'Часовой пояс',
	'BOOKMARKS'					=> 'Закладки',
	'BOOKMARKS_EXPLAIN'			=> 'Вы можете добавлять темы в закладки для будущего обращения. Установите флажок для любой закладки, которую вы хотите удалить, затем нажмите кнопку <em>Удалить отмеченные закладки</em>.',
	'BOOKMARKS_DISABLED'		=> 'Закладки на этом форуме отключены.',
	'BOOKMARKS_REMOVED'			=> 'Закладки были успешно удалены.',

	'CANNOT_EDIT_MESSAGE_TIME'	=> 'Вы больше не можете отредактировать или удалить данное сообщение.',
	'CANNOT_MOVE_TO_SAME_FOLDER'=> 'Сообщения не могут быть перемещены в папку, которую вы собираетесь удалить.',
	'CANNOT_MOVE_FROM_SPECIAL'	=> 'Сообщения не могут быть удалены из папки «Исходящие».',
	'CANNOT_RENAME_FOLDER'		=> 'Данная папка не может быть переименована.',
	'CANNOT_REMOVE_FOLDER'		=> 'Данная папка не может быть удалена.',
	'CHANGE_DEFAULT_GROUP'		=> 'Изменить группу по умолчанию',
	'CHANGE_PASSWORD'			=> 'Изменить пароль',
	'CLICK_GOTO_FOLDER'			=> '%1$sПерейти в папку «%3$s»%2$s',
	'CLICK_RETURN_FOLDER'		=> '%1$sВернуться в папку «%3$s»%2$s',
	'CONFIRMATION'				=> 'Подтверждение регистрации',
	'CONFIRM_CHANGES'			=> 'Подтвердите изменения',
	'CONFIRM_EMAIL'				=> 'Подтвердите email',
	'CONFIRM_EMAIL_EXPLAIN'		=> 'Указывайте email только если вы хотите его поменять.',
	'CONFIRM_EXPLAIN'			=> 'Для предотвращения автоматических регистраций на форуме требуется ввести код подтверждения. Код показан на картинке, которая находится ниже. Если вы не видите код на картинке, то обратитесь к %sадминистратору%s.',
	'VC_REFRESH'				=> 'Обновить код подтверждения',
	'VC_REFRESH_EXPLAIN'		=> 'Если невозможно прочесть данный код, вы можете запросить новый, нажав на эту кнопку.',

	'CONFIRM_PASSWORD'			=> 'Подтвердите новый пароль',
	'CONFIRM_PASSWORD_EXPLAIN'	=> 'Указывайте пароль только если вы изменили его выше.',
	'COPPA_BIRTHDAY'			=> 'Для продолжения регистрации, пожалуйста, укажите дату рождения.',
	'COPPA_COMPLIANCE'			=> 'Согласие по COPPA',
	'COPPA_EXPLAIN'				=> 'Учтите, что ваша учётная запись будет создана после отправки формы. Тем не менее она не будет активирована до тех пор, пока родитель или опекун не одобрит вашу регистрацию. Вам будет выслана копия email с необходимой формой и указаниями, куда её нужно отправить.',
	'CREATE_FOLDER'				=> 'Добавить папку…',
	'CURRENT_IMAGE'				=> 'Текущее изображение',
	'CURRENT_PASSWORD'			=> 'Текущий пароль',
	'CURRENT_PASSWORD_EXPLAIN'	=> 'Если вы хотите изменить имя пользователя или адрес email, вы должны указать текущий пароль.',
	'CURRENT_CHANGE_PASSWORD_EXPLAIN' => 'Если вы хотите изменить пароль, адрес email или имя пользователя, вы должны указать текущий пароль.',
	'CUR_PASSWORD_EMPTY'		=> 'Вы не ввели свой текущий пароль.',
	'CUR_PASSWORD_ERROR'		=> 'Введённый вами пароль не совпадает с текущим паролем',
	'CUSTOM_DATEFORMAT'			=> 'Другой…',

	'DEFAULT_ACTION'			=> 'Действие по умолчанию',
	'DEFAULT_ACTION_EXPLAIN'	=> 'Это действие будет выполнено, если ни одно из вышеуказанных правил не может быть применено.',
	'DEFAULT_ADD_SIG'			=> 'Всегда присоединять мою подпись',
	'DEFAULT_BBCODE'			=> 'BBCode всегда включён',
	'DEFAULT_NOTIFY'			=> 'Всегда сообщать мне об ответах',
	'DEFAULT_SMILIES'			=> 'Смайлики всегда включены',
	'DEFINED_RULES'				=> 'Определённые правила',
	'DELETED_TOPIC'				=> 'Тема была удалена.',
	'DELETE_ATTACHMENT'			=> 'Удалить вложение',
	'DELETE_ATTACHMENTS'		=> 'Удалить вложения',
	'DELETE_ATTACHMENT_CONFIRM'	=> 'Вы уверены, что хотите удалить данное вложение?',
	'DELETE_ATTACHMENTS_CONFIRM'=> 'Вы уверены, что хотите удалить данные вложения?',
	'DELETE_AVATAR'				=> 'Удалить изображение',
	'DELETE_COOKIES_CONFIRM'	=> 'Вы уверены, что хотите удалить все cookie, установленные данным форумом?',
	'DELETE_MARKED_PM'			=> 'Удалить отмеченные',
	'DELETE_MARKED_PM_CONFIRM'	=> 'Вы уверены, что хотите удалить все отмеченные сообщения?',
	'DELETE_OLDEST_MESSAGES'	=> 'Удалить самые старые сообщения',
	'DELETE_MESSAGE'			=> 'Удалить сообщение',
	'DELETE_MESSAGE_CONFIRM'	=> 'Вы уверены, что хотите удалить данное сообщение?',
	'DELETE_MESSAGES_IN_FOLDER'	=> 'Удалить все сообщения, которые содержатся в удаляемой папке',
	'DELETE_RULE'				=> 'Удалить правило',
	'DELETE_RULE_CONFIRM'		=> 'Вы уверены, что хотите удалить данное правило?',
	'DEMOTE_SELECTED'			=> 'Отказаться от лидерства',
	'DISABLE_CENSORS'			=> 'Разрешить автоцензор',
	'DISPLAY_GALLERY'			=> 'Показать галерею',
	'DOMAIN_NO_MX_RECORD_EMAIL'	=> 'Введённый домен email не имеет корректной почтовой записи в DNS (MX record).',
	'DOWNLOADS'					=> 'Скачивания',
	'DRAFTS_DELETED'			=> 'Все отмеченные черновики были успешно удалены.',
	'DRAFTS_EXPLAIN'			=> 'Здесь вы можете просмотреть, отредактировать и удалить ваши сохранённые черновики.',
	'DRAFT_UPDATED'				=> 'Черновик был успешно обновлён.',

	'EDIT_DRAFT_EXPLAIN'		=> 'Здесь вы можете редактировать черновик. Черновики не содержат вложений и опросов.',
	'EMAIL_BANNED_EMAIL'		=> 'Введённый адрес email запрещён к использованию.',
	'EMAIL_REMIND'				=> 'Адрес email, связанный с вашей учётной записью. Если вы не изменили его в панели пользователя, то это адрес email, указанный вами при регистрации.',
	'EMAIL_TAKEN_EMAIL'			=> 'Введённый адрес email уже используется другим пользователем.',
	'EMPTY_DRAFT'				=> 'Вы должны ввести сообщение, чтобы подтвердить изменения.',
	'EMPTY_DRAFT_TITLE'			=> 'Вы должны ввести название черновика.',
	'EXPORT_AS_XML'				=> 'Экспорт в XML',
	'EXPORT_AS_CSV'				=> 'Экспорт в CSV',
	'EXPORT_AS_CSV_EXCEL'		=> 'Экспорт в CSV (Excel)',
	'EXPORT_AS_TXT'				=> 'Экспорт в TXT',
	'EXPORT_AS_MSG'				=> 'Экспорт в MSG',
	'EXPORT_FOLDER'				=> 'Этот список',

	'FIELD_REQUIRED'					=> 'Не заполнено поле «%s».',
	'FIELD_TOO_SHORT'					=> 'Значение поля «%1$s» слишком короткое, минимально допустимая длина составляет %2$d символов.',
	'FIELD_TOO_LONG'					=> 'Значение поля «%1$s» слишком длинное, максимально допустимая длина составляет %2$d символов.',
	'FIELD_TOO_SMALL'					=> 'Значение поля «%1$s» слишком маленькое, минимально допустимым значением является %2$d.',
	'FIELD_TOO_LARGE'					=> 'Значение поля «%1$s» слишком большое, максимально допустимым значением является %2$d.',
	'FIELD_INVALID_CHARS_NUMBERS_ONLY'	=> 'Поле «%s» содержит недопустимые символы, разрешены только цифры.',
	'FIELD_INVALID_CHARS_ALPHA_ONLY'	=> 'Поле «%s» содержит недопустимые символы, разрешены только буквы и цифры.',
	'FIELD_INVALID_CHARS_SPACERS_ONLY'	=> 'Поле «%s» содержит недопустимые символы, разрешены только буквы, цифры, пробелы, а также символы -_.',
	'FIELD_INVALID_DATE'				=> 'Поле «%s» содержит недопустимую дату.',
	'FIELD_INVALID_VALUE'				=> 'Поле «%s» содержит недопустимое значение.',

	'FOE_MESSAGE'				=> 'Сообщение от недруга',
	'FOES_EXPLAIN'				=> 'Недруги — это пользователи, которые будут игнорироваться по умолчанию. Сообщения этих пользователей будут скрыты. Однако личные сообщения от недругов разрешены. Учтите, что вы не можете игнорировать модераторов или администраторов.',
	'FOES_UPDATED'				=> 'Список недругов был успешно обновлён.',
	'FOLDER_ADDED'				=> 'Папка была успешно добавлена.',
	'FOLDER_MESSAGE_STATUS'		=> '%1$d из %2$d сообщений',
	'FOLDER_NAME_EMPTY'			=> 'Необходимо ввести имя для этой папки.',
	'FOLDER_NAME_EXIST'			=> 'Папка <strong>%s</strong> уже существует.',
	'FOLDER_OPTIONS'			=> 'Свойства папки',
	'FOLDER_RENAMED'			=> 'Папка была успешно переименована.',
	'FOLDER_REMOVED'			=> 'Папка была успешно удалена.',
	'FOLDER_STATUS_MSG'			=> 'Папка заполнена на %1$d%% (%2$d из %3$d сообщений)',
	'FORWARD_PM'				=> 'Переслать ЛС',
	'FORCE_PASSWORD_EXPLAIN'	=> 'Для дальнейшего использования форума вам необходимо изменить свой пароль.',
	'FRIEND_MESSAGE'			=> 'Сообщение от друга',
	'FRIENDS'					=> 'Друзья',
	'FRIENDS_EXPLAIN'			=> 'Список друзей позволяет вам получить быстрый доступ к пользователям, с которыми вы часто общаетесь. При наличии соответствующей поддержки в стиле форума, все сообщения ваших друзей будут выделены при просмотре.',
	'FRIENDS_OFFLINE'			=> 'Не в сети',
	'FRIENDS_ONLINE'			=> 'В сети',
	'FRIENDS_UPDATED'			=> 'Список друзей был успешно обновлён.',
	'FULL_FOLDER_OPTION_CHANGED'=> 'Действие, выполняемое в случае переполнения папки, изменено.',
	'FWD_ORIGINAL_MESSAGE'		=> '-------- Исходное сообщение --------',
	'FWD_SUBJECT'				=> 'Тема: %s',
	'FWD_DATE'					=> 'Дата: %s',
	'FWD_FROM'					=> 'От: %s',
	'FWD_TO'					=> 'Кому: %s',

	'GLOBAL_ANNOUNCEMENT'		=> 'Важная',

	'HIDE_ONLINE'				=> 'Скрывать моё пребывание на форуме',
	'HIDE_ONLINE_EXPLAIN'		=> 'Изменение настройки вступит в силу только со следующего посещения форума.',
	'HOLD_NEW_MESSAGES'			=> 'Не принимать новые сообщения (новые сообщения будут отложены до появления достаточного количества свободного места)',
	'HOLD_NEW_MESSAGES_SHORT'	=> 'Новые сообщения будут отложены',

	'IF_FOLDER_FULL'			=> 'Если папка заполнена',
	'IMPORTANT_NEWS'			=> 'Важные объявления',
	'INVALID_USER_BIRTHDAY'		=> 'Введённая дата дня рождения имеет неверный формат.',
	'INVALID_CHARS_USERNAME'	=> 'Имя пользователя содержит запрещённые символы.',
	'INVALID_CHARS_NEW_PASSWORD'=> 'Пароль не содержит требуемых символов.',
	'ITEMS_REQUIRED'			=> 'Поля вашего профиля, отмеченные *, обязательны к заполнению.',

	'JOIN_SELECTED'				=> 'Вступить в выбранную',

	'LANGUAGE'					=> 'Язык',
	'LINK_REMOTE_AVATAR'		=> 'Внешняя ссылка',
	'LINK_REMOTE_AVATAR_EXPLAIN'=> 'Введите URL, по которому находится файл с изображением, он будет использован в качестве вашей аватары.',
	'LINK_REMOTE_SIZE'			=> 'Размеры аватары',
	'LINK_REMOTE_SIZE_EXPLAIN'	=> 'Укажите высоту и ширину аватары или оставьте поля пустыми для их автоматической проверки.',
	'LOGIN_EXPLAIN_UCP'			=> 'Пожалуйста, авторизуйтесь для входа в вашу панель пользователя.',
	'LOGIN_REDIRECT'			=> 'Вы успешно вошли в систему.',
	'LOGOUT_FAILED'				=> 'Вы не вышли из форума, так как запрос не соответствовал параметрам вашей сессии. Свяжитесь с администратором форума, если проблема повторится.',
	'LOGOUT_REDIRECT'			=> 'Вы успешно вышли из системы.',

	'MARK_IMPORTANT'				=> 'Пометить / снять пометку',
	'MARKED_MESSAGE'				=> 'Помеченное сообщение',
	'MAX_FOLDER_REACHED'			=> 'Достигнуто максимальное количество пользовательских папок.',
	'MESSAGE_BY_AUTHOR'				=> '',
	'MESSAGE_COLOURS'				=> 'Цвета сообщений',
	'MESSAGE_DELETED'				=> 'Сообщение успешно удалено.',
	'MESSAGE_EDITED'				=> 'Сообщение успешно изменено.',
	'MESSAGE_HISTORY'				=> 'История сообщений',
	'MESSAGE_REMOVED_FROM_OUTBOX'	=> 'Автор удалил это сообщение.',
	'MESSAGE_SENT_ON'				=> 'от',
	'MESSAGE_STORED'				=> 'Ваше сообщение успешно отправлено.',
	'MESSAGE_TO'					=> 'Кому',
	'MESSAGES_DELETED'				=> 'Сообщения успешно удалены',
	'MOVE_DELETED_MESSAGES_TO'		=> 'Переместить сообщения из удаляемой папки в папку',
	'MOVE_DOWN'						=> 'Сдвинуть вниз',
	'MOVE_MARKED_TO_FOLDER'			=> 'Переместить отмеченные в папку %s',
	'MOVE_PM_ERROR'					=> 'Во время перемещения сообщений в новую папку произошла ошибка, перенесено сообщений: %1$d из %2$d.',
	'MOVE_TO_FOLDER'				=> 'Переместить в папку',
	'MOVE_UP'						=> 'Сдвинуть вверх',

	'NEW_EMAIL_CONFIRM_EMPTY'		=> 'Вы не ввели подтверждение адреса email.',
	'NEW_EMAIL_ERROR'				=> 'Введённые вами адреса email не совпадают.',
	'NEW_FOLDER_NAME'				=> 'Новое имя папки',
	'NEW_PASSWORD'					=> 'Новый пароль',
	'NEW_PASSWORD_CONFIRM_EMPTY'	=> 'Вы не ввели подтверждение пароля.',
	'NEW_PASSWORD_ERROR'			=> 'Введённые вами пароли не совпадают.',
	'NOTIFY_METHOD'					=> 'Способ уведомления',
	'NOTIFY_METHOD_BOTH'			=> 'Оба способа',
	'NOTIFY_METHOD_EMAIL'			=> 'Только email',
	'NOTIFY_METHOD_EXPLAIN'			=> 'Средство отправки сообщений, посылаемых этим форумом.',
	'NOTIFY_METHOD_IM'				=> 'Только Jabber',
	'NOTIFY_ON_PM'					=> 'Уведомлять меня о новых личных сообщениях',
	'NOT_ADDED_FRIENDS_ANONYMOUS'	=> 'Вы не можете добавить гостя в список друзей.',
	'NOT_ADDED_FRIENDS_BOTS'		=> 'Вы не можете добавить бота в список друзей.',
	'NOT_ADDED_FRIENDS_FOES'		=> 'Вы не можете добавлять пользователей из списка недругов в список друзей.',
	'NOT_ADDED_FRIENDS_SELF'		=> 'Вы не можете добавить самого себя в список друзей.',
	'NOT_ADDED_FOES_MOD_ADMIN'		=> 'Вы не можете добавлять администраторов и модераторов в список недругов.',
	'NOT_ADDED_FOES_ANONYMOUS'		=> 'Вы не можете добавить гостя в список недругов.',
	'NOT_ADDED_FOES_BOTS'			=> 'Вы не можете добавить бота в список недругов.',
	'NOT_ADDED_FOES_FRIENDS'		=> 'Вы не можете добавлять пользователей из списка друзей в список недругов.',
	'NOT_ADDED_FOES_SELF'			=> 'Вы не можете добавить самого себя в список недругов.',
	'NOT_AGREE'						=> 'Я не согласен с этими условиями',
	'NOT_ENOUGH_SPACE_FOLDER'		=> 'Папка-получатель «%s» заполнена. Запрошенное действие не было выполнено.',
	'NOT_MOVED_MESSAGE'				=> 'Папка с вашими личными сообщениями заполнена. Отложенных сообщений: 1.',
	'NOT_MOVED_MESSAGES'			=> 'Папка с вашими личными сообщениями заполнена. Отложенных сообщений: %d.',
	'NO_ACTION_MODE'				=> 'Не выбрано действие для сообщения.',
	'NO_AUTHOR'						=> 'Не указан автор сообщения',
	'NO_AVATAR_CATEGORY'			=> 'Нет',

	'NO_AUTH_DELETE_MESSAGE'		=> 'У вас нет доступа к удалению личных сообщений.',
	'NO_AUTH_EDIT_MESSAGE'			=> 'У вас нет доступа к редактированию личных сообщений.',
	'NO_AUTH_FORWARD_MESSAGE'		=> 'У вас нет доступа к пересылке личных сообщений.',
	'NO_AUTH_GROUP_MESSAGE'			=> 'У вас нет доступа к отправке личных сообщений в группы.',
	'NO_AUTH_PASSWORD_REMINDER'		=> 'У вас нет доступа к получению нового пароля.',
	'NO_AUTH_READ_HOLD_MESSAGE'		=> 'У вас нет доступа к чтению отложенных личных сообщений.',
	'NO_AUTH_READ_MESSAGE'			=> 'У вас нет доступа к чтению личных сообщений.',
	'NO_AUTH_READ_REMOVED_MESSAGE'	=> 'Вы не можете прочесть это сообщение, потому что оно было удалено его автором.',
	'NO_AUTH_SEND_MESSAGE'			=> 'У вас нет доступа к отправке личных сообщений.',
	'NO_AUTH_SIGNATURE'				=> 'У вас нет доступа к редактированию подписи.',

	'NO_BCC_RECIPIENT'			=> 'Нет',
	'NO_BOOKMARKS'				=> 'У вас нет закладок.',
	'NO_BOOKMARKS_SELECTED'		=> 'Вы не отметили закладки.',
	'NO_EDIT_READ_MESSAGE'		=> 'Личное сообщение не может быть отредактировано, так как уже было прочитано.',
	'NO_EMAIL_USER'				=> 'Введённая информация о email/имени пользователя не найдена.',
	'NO_FOES'					=> 'Список недругов пуст',
	'NO_FRIENDS'				=> 'Список друзей пуст',
	'NO_FRIENDS_OFFLINE'		=> 'Нет друзей вне сети',
	'NO_FRIENDS_ONLINE'			=> 'Нет друзей в сети',
	'NO_GROUP_SELECTED'			=> 'Группа не выбрана.',
	'NO_IMPORTANT_NEWS'			=> 'Нет важных объявлений.',
	'NO_MESSAGE'				=> 'Личное сообщение не найдено.',
	'NO_NEW_FOLDER_NAME'		=> 'Вы должны указать новое имя папки.',
	'NO_NEWER_PM'				=> 'Нет новых сообщений.',
	'NO_OLDER_PM'				=> 'Нет старых сообщений.',
	'NO_PASSWORD_SUPPLIED'		=> 'Вы не можете войти без пароля.',
	'NO_RECIPIENT'				=> 'Получатель сообщения не выбран.',
	'NO_RULES_DEFINED'			=> 'Правил не установлено.',
	'NO_SAVED_DRAFTS'			=> 'Нет сохранённых черновиков.',
	'NO_TO_RECIPIENT'			=> 'Нет',
	'NO_WATCHED_FORUMS'			=> 'Вы не подписаны на какие-либо разделы.',
	'NO_WATCHED_SELECTED'		=> 'Вы не выбрали тем или разделов, на которые хотели бы подписаться.',
	'NO_WATCHED_TOPICS'			=> 'Вы не подписаны на какие-либо темы.',

	'PASS_TYPE_ALPHA_EXPLAIN'	=> 'От %1$d до %2$d знаков, должен содержать буквы разных регистров и цифры.',
	'PASS_TYPE_ANY_EXPLAIN'		=> 'От %1$d до %2$d знаков.',
	'PASS_TYPE_CASE_EXPLAIN'	=> 'От %1$d до %2$d знаков, должен содержать буквы разных регистров.',
	'PASS_TYPE_SYMBOL_EXPLAIN'	=> 'От %1$d до %2$d знаков, должен содержать буквы разных регистров, цифры и символы.',
	'PASSWORD'					=> 'Пароль',
	'PASSWORD_ACTIVATED'		=> 'Ваш новый пароль активирован.',
	'PASSWORD_UPDATED'			=> 'Новый пароль успешно отправлен на ваш регистрационный адрес email.',
	'PERMISSIONS_RESTORED'		=> 'Ваши права доступа восстановлены.',
	'PERMISSIONS_TRANSFERRED'	=> 'Имитация прав доступа, установленных для <strong>%s</strong>, успешно проведена. Сейчас вы можете просматривать форум с ограничениями, установленными для данного пользователя.<br />Пожалуйста, помните, что права администратора отключены. Вы можете прервать имитацию в любое время.',
	'PM_DISABLED'				=> 'Личные сообщения на этом форуме отключены.',
	'PM_FROM'					=> 'От',
	'PM_FROM_REMOVED_AUTHOR'	=> 'Это сообщение от пользователя, учётная запись которого удалена.',
	'PM_ICON'					=> 'Значок ЛС',
	'PM_INBOX'					=> 'Входящие',
	'PM_NO_USERS'				=> 'Запрашиваемые для добавления пользователи не существуют.',
	'PM_OUTBOX'					=> 'Исходящие',
	'PM_SENTBOX'				=> 'Доставленные',
	'PM_SUBJECT'				=> 'Тема сообщения',
	'PM_TO'						=> 'Кому',
	'PM_USERS_REMOVED_NO_PM'	=> 'Некоторые пользователи не могут быть добавлены, так как они отключили получение личных сообщений.',
	'POPUP_ON_PM'				=> 'Всплывающее окно при получении личного сообщения',
	'POST_EDIT_PM'				=> 'Редактировать',
	'POST_FORWARD_PM'			=> 'Переслать',
	'POST_NEW_PM'				=> 'Создать сообщение',
	'POST_PM_LOCKED'			=> 'Личное сообщение заблокировано.',
	'POST_PM_POST'				=> 'Цитировать',
	'POST_QUOTE_PM'				=> 'Цитировать сообщение',
	'POST_REPLY_PM'				=> 'Ответить',
	'PRINT_PM'					=> 'Для печати',
	'PREFERENCES_UPDATED'		=> 'Ваши настройки обновлены.',
	'PROFILE_INFO_NOTICE'		=> 'Пожалуйста, помните, что эта информация может быть доступна другим пользователям. Будьте осторожны при выборе указываемых персональных данных. Любые поля, обозначенные звёздочкой (*), должны быть заполнены.',
	'PROFILE_UPDATED'			=> 'Ваш профиль обновлён.',

	'RECIPIENT'							=> 'Получатель',
	'RECIPIENTS'						=> 'Получатели',
	'REGISTRATION'						=> 'Регистрация',
	'RELEASE_MESSAGES'					=> '%sДобавить все отложенные сообщения%s… которые будут рассортированы в соответствующей папке при наличии свободного места',
	'REMOVE_ADDRESS'					=> 'Удалить адрес',
	'REMOVE_SELECTED_BOOKMARKS'			=> 'Удалить выбранные закладки',
	'REMOVE_SELECTED_BOOKMARKS_CONFIRM'	=> 'Вы уверены, что хотите удалить все выбранные закладки?',
	'REMOVE_BOOKMARK_MARKED'			=> 'Удалить отмеченные закладки',
	'REMOVE_FOLDER'						=> 'Удалить папку',
	'REMOVE_FOLDER_CONFIRM'				=> 'Вы уверены, что хотите удалить эту папку?',
	'RENAME'							=> 'Переименовать',
	'RENAME_FOLDER'						=> 'Переименовать папку',
	'REPLIED_MESSAGE'					=> 'Отвеченные сообщения',
	'REPLY_TO_ALL'						=> 'Ответ отправителю и всем получателям.',
	'REPORT_PM'							=> 'Пожаловаться на личное сообщение',
	'RESIGN_SELECTED'					=> 'Покинуть выбранную',
	'RETURN_FOLDER'						=> '%1$sВернуться в предыдущую папку%2$s',
	'RETURN_UCP'						=> '%sВернуться в панель пользователя%s',
	'RULE_ADDED'						=> 'Правило успешно добавлено.',
	'RULE_ALREADY_DEFINED'				=> 'Такое правило уже было добавлено ранее.',
	'RULE_DELETED'						=> 'Правило успешно удалено.',
	'RULE_LIMIT_REACHED'				=> 'Невозможно добавить правило, так как достигнуто максимально возможное их количество.',
	'RULE_NOT_DEFINED'					=> 'Правило указано некорректно.',
	'RULE_REMOVED_MESSAGE'				=> 'Фильтрами ЛС было удалено личных сообщений: 1',
	'RULE_REMOVED_MESSAGES'				=> 'Фильтрами ЛС было удалено личных сообщений: %d',

	'SAME_PASSWORD_ERROR'		=> 'Введённый вами новый пароль совпадает с вашим текущим.',
	'SEARCH_YOUR_POSTS'			=> 'Показать ваши сообщения',
	'SEARCH_YOUR_TOPICS'		=> 'Показать ваши темы',
	'SEND_PASSWORD'				=> 'Отослать пароль',
	'SENT_AT'					=> 'Отправлено',
	'SHOW_EMAIL'				=> 'Показывать мой адрес email',
	'SIGNATURE_EXPLAIN'			=> 'Это текст, который может автоматически добавляться к вашим сообщениям. Может быть не более %1$d символов и не более %2$s строк.',
	'SIGNATURE_PREVIEW'			=> 'Ваша подпись в сообщениях будет выглядеть так',
	'SIGNATURE_TOO_LONG'		=> 'Вы ввели слишком длинную подпись.',
	'SORT'						=> 'Сортировать',
	'SORT_COMMENT'				=> 'Комментарии',
	'SORT_DOWNLOADS'			=> 'Скачивания',
	'SORT_EXTENSION'			=> 'Расширение',
	'SORT_FILENAME'				=> 'Имя файла',
	'SORT_POST_TIME'			=> 'Время',
	'SORT_SIZE'					=> 'Размер',

	'TIMEZONE'					=> 'Часовой пояс',
	'TO'						=> 'Кому',
	'TOO_MANY_RECIPIENTS'		=> 'Вы попытались отправить личное сообщение слишком большому числу получателей.',
	'TOO_MANY_REGISTERS'		=> 'Вы исчерпали предельное количество попыток регистрации для данной сессии. Повторите попытку позднее.',

	'UCP'						=> 'Панель пользователя',
	'UCP_ACTIVATE'				=> 'Активировать учётную запись',
	'UCP_ADMIN_ACTIVATE'		=> 'Обратите внимание на то, что вы должны ввести правильный адрес электронной почты перед активацией. Администратор проверит вашу учётную запись и отправит на указанный адрес письмо, содержащее ссылку для активации учётной записи.',
	'UCP_ATTACHMENTS'			=> 'Вложения',
	'UCP_COPPA_BEFORE'			=> 'До %s',
	'UCP_COPPA_ON_AFTER'		=> '%s и после',
	'UCP_EMAIL_ACTIVATE'		=> 'Обратите внимание на то, что вы должны ввести правильный адрес электронной почты перед активацией. На указанный вами адрес придёт письмо, содержащее ссылку для активации учётной записи.',
	'UCP_ICQ'					=> 'ICQ',
	'UCP_JABBER'				=> 'Jabber',

	'UCP_MAIN'					=> 'Обзор',
	'UCP_MAIN_ATTACHMENTS'		=> 'Вложения',
	'UCP_MAIN_BOOKMARKS'		=> 'Закладки',
	'UCP_MAIN_DRAFTS'			=> 'Черновики',
	'UCP_MAIN_FRONT'			=> 'Начало',
	'UCP_MAIN_SUBSCRIBED'		=> 'Подписки',

	'UCP_NO_ATTACHMENTS'		=> 'Вы не создали ни одного вложения.',

	'UCP_PREFS'					=> 'Личные настройки',
	'UCP_PREFS_PERSONAL'		=> 'Общие настройки',
	'UCP_PREFS_POST'			=> 'Отправка сообщений',
	'UCP_PREFS_VIEW'			=> 'Настройки отображения',

	'UCP_PM'					=> 'Личные сообщения',
	'UCP_PM_COMPOSE'			=> 'Новое сообщение',
	'UCP_PM_DRAFTS'				=> 'Управление черновиками',
	'UCP_PM_OPTIONS'			=> 'Правила, папки и настройки',
	'UCP_PM_POPUP'				=> 'Личные сообщения',
	'UCP_PM_POPUP_TITLE'		=> 'Всплывающее окно о новом личном сообщении',
	'UCP_PM_UNREAD'				=> 'Непрочитанные сообщения',
	'UCP_PM_VIEW'				=> 'Просмотр сообщений',

	'UCP_PROFILE'				=> 'Профиль',
	'UCP_PROFILE_AVATAR'		=> 'Аватара',
	'UCP_PROFILE_PROFILE_INFO'	=> 'Личные данные',
	'UCP_PROFILE_REG_DETAILS'	=> 'Регистрационные данные',
	'UCP_PROFILE_SIGNATURE'		=> 'Подпись',

	'UCP_USERGROUPS'			=> 'Группы',
	'UCP_USERGROUPS_MEMBER'		=> 'Участие в группах',
	'UCP_USERGROUPS_MANAGE'		=> 'Управление группами',

	'UCP_REGISTER_DISABLE'			=> 'Создание новой учётной записи на текущий момент невозможно.',
	'UCP_REMIND'					=> 'Отослать пароль',
	'UCP_RESEND'					=> 'Послать письмо для активации учётной записи',
	'UCP_WELCOME'					=> 'Добро пожаловать в вашу личную панель управления. Отсюда вы можете просматривать и изменять настройки, информацию о себе и подписку на разделы и темы. Также, если вам это разрешено, вы можете посылать личные сообщения (ЛС) другим пользователям. Перед тем как продолжить, убедитесь, что вы прочли все объявления администрации.',
	'UCP_ZEBRA'						=> 'Друзья и недруги',
	'UCP_ZEBRA_FOES'				=> 'Список недругов',
	'UCP_ZEBRA_FRIENDS'				=> 'Список друзей',
 	'UNDISCLOSED_RECIPIENT'			=> 'Неизвестный получатель',
	'UNKNOWN_FOLDER'				=> 'Неизвестная папка',
	'UNWATCH_MARKED'				=> 'Отписаться от выделенного',
	'UPLOAD_AVATAR_FILE'			=> 'Загрузить с вашего компьютера',
	'UPLOAD_AVATAR_URL'				=> 'Загрузить по URL',
	'UPLOAD_AVATAR_URL_EXPLAIN'		=> 'Введите URL, по которому находится файл с изображением. Оно будет скопировано на этот сайт.',
	'USERNAME_ALPHA_ONLY_EXPLAIN'	=> 'От %1$d до %2$d знаков, только буквы.',
	'USERNAME_ALPHA_SPACERS_EXPLAIN'=> 'От %1$d до %2$d знаков, разрешены буквы, пробел или символы -._',
	'USERNAME_ASCII_EXPLAIN'		=> 'От %1$d до %2$d знаков, только латинские буквы.',
	'USERNAME_LETTER_NUM_EXPLAIN'	=> 'От %1$d до %2$d знаков, только буквы или цифры.',
	'USERNAME_LETTER_NUM_SPACERS_EXPLAIN'=> 'От %1$d до %2$d знаков, разрешены буквы, цифры, пробел или символы -._',
	'USERNAME_CHARS_ANY_EXPLAIN'	=> 'От %1$d и до %2$d знаков.',
	'USERNAME_TAKEN_USERNAME'		=> 'Извините, пользователь с таким именем уже существует',
	'USERNAME_DISALLOWED_USERNAME'	=> 'Введённое вами имя пользователя было запрещено или содержит запрещённое слово. Выберите другое имя.',
	'USER_NOT_FOUND_OR_INACTIVE'	=> 'Введённое вами имя пользователя не найдено, либо данный пользователь ещё не прошел процедуру активации.',

	'VIEW_AVATARS'				=> 'Показывать аватары',
	'VIEW_EDIT'					=> 'Просмотреть/изменить',
	'VIEW_FLASH'				=> 'Показывать Flash-анимацию',
	'VIEW_IMAGES'				=> 'Показывать изображения в сообщениях',
	'VIEW_NEXT_HISTORY'			=> 'Следующее ЛС в архиве',
	'VIEW_NEXT_PM'				=> 'Следующее ЛС',
	'VIEW_PM'					=> 'Посмотреть сообщение',
	'VIEW_PM_INFO'				=> 'Информация',
	'VIEW_PM_MESSAGE'			=> 'Сообщений: 1',
	'VIEW_PM_MESSAGES'			=> 'Сообщений: %d',
	'VIEW_PREVIOUS_HISTORY'		=> 'Предыдущее ЛС в архиве',
	'VIEW_PREVIOUS_PM'			=> 'Предыдущее ЛС',
	'VIEW_SIGS'					=> 'Показывать подписи',
	'VIEW_SMILIES'				=> 'Заменять смайлики изображениями',
	'VIEW_TOPICS_DAYS'			=> 'Показывать темы за',
	'VIEW_TOPICS_DIR'			=> 'Порядок сортировки тем',
	'VIEW_TOPICS_KEY'			=> 'Поле сортировки тем',
	'VIEW_POSTS_DAYS'			=> 'Показывать сообщения за',
	'VIEW_POSTS_DIR'			=> 'Порядок сортировки сообщений',
	'VIEW_POSTS_KEY'			=> 'Поле сортировки сообщений',
	'USER_TOPICS_PER_PAGE'		=> 'Тем на страницу',
	'USER_POSTS_PER_PAGE'		=> 'Сообщений на страницу',

	'WATCHED_EXPLAIN'			=> 'Ниже расположен список разделов и тем, на которые вы подписаны. Вы будете оповещены о появлении в них новых сообщений. Чтобы отписаться от них, выделите раздел или тему и нажмите кнопку <em>Отписаться от выделенного</em>.',
	'WATCHED_FORUMS'			=> 'Ваши подписки на разделы',
	'WATCHED_TOPICS'			=> 'Ваши подписки на темы',
	'WRONG_ACTIVATION'			=> 'Ключ активации, указанный вами, отсутствует в базе данных.',

	'YOUR_DETAILS'				=> 'Ваша активность на форуме',
	'YOUR_FOES'					=> 'Ваши недруги',
	'YOUR_FOES_EXPLAIN'			=> 'Чтобы убрать пользователей из списка, выделите их и нажмите «Отправить».',
	'YOUR_FRIENDS'				=> 'Ваши друзья',
	'YOUR_FRIENDS_EXPLAIN'		=> 'Чтобы убрать пользователей из списка, выделите их и нажмите «Отправить».',
	'YOUR_WARNINGS'				=> 'Получено предупреждений',

	'PM_ACTION' => array(
		'PLACE_INTO_FOLDER'	=> 'Поместить в папку',
		'MARK_AS_READ'		=> 'Пометить как прочтённое',
		'MARK_AS_IMPORTANT'	=> 'Пометить как важное',
		'DELETE_MESSAGE'	=> 'Удалить сообщение'
	),
	'PM_CHECK' => array(
		'SUBJECT'	=> 'Тема',
		'SENDER'	=> 'Отправитель',
		'MESSAGE'	=> 'Сообщение',
		'STATUS'	=> 'Статус сообщения',
		'TO'		=> 'Получатель'
	),
	'PM_RULE' => array(
		'IS_LIKE'		=> 'содержит',
		'IS_NOT_LIKE'	=> 'не содержит',
		'IS'			=> 'соответствует',
		'IS_NOT'		=> 'не соответствует',
		'BEGINS_WITH'	=> 'начинается с',
		'ENDS_WITH'		=> 'оканчивается на',
		'IS_FRIEND'		=> 'друг',
		'IS_FOE'		=> 'недруг',
		'IS_USER'		=> 'пользователь',
		'IS_GROUP'		=> 'входит в группу',
		'ANSWERED'		=> 'отвеченное',
		'FORWARDED'		=> 'отправленное',
		'TO_GROUP'		=> 'в мою группу по умолчанию',
		'TO_ME'			=> 'мне'
	),


	'GROUPS_EXPLAIN'	=> 'Группы дают администратору форума больше возможностей по управлению пользователями. По умолчанию вы помещены в определённую группу. От того, в какой из групп вы состоите по умолчанию, зависит ваше отображение на форуме: цвет вашего имени, аватара, звание и т. п. В зависимости от того, разрешено ли это администратором, вы можете изменить заданную по умолчанию группу. Вы можете быть помещены или вам может быть разрешено вступить в другую группу. Участие в некоторых группах может давать дополнительные права доступа к разделам форума или другие возможности.',
	'GROUP_LEADER'		=> 'Лидер группы',
	'GROUP_MEMBER'		=> 'Участник группы',
	'GROUP_PENDING'		=> 'Кандидат на вступление в группы',
	'GROUP_NONMEMBER'	=> 'Не состоит в группах',
	'GROUP_DETAILS'		=> 'Информация о группах',

	'NO_LEADER'		=> 'Нет лидеров группы',
	'NO_MEMBER'		=> 'Нет членов группы',
	'NO_PENDING'	=> 'Нет кандидатов в члены группы',
	'NO_NONMEMBER'	=> 'Нет пустых групп',

	'QUICK_REPLY_DISPLAY'		=> 'Показывать панель быстрого ответа в темах',
	'QUICK_POST_DISPLAY'		=> 'Показывать панель быстрого создания тем в разделах',

));
