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
		1 => 'Вход на форум и регистрация'
	),
	array(
		0 => 'Зачем мне вообще нужно регистрироваться?',
		1 => 'Вы можете этого и не делать. Но в таком случае вы не сможете размещать сообщения. Более того, регистрация дает вам дополнительные возможности, которые недоступны анонимным пользователям: аватары, личные сообщения, отправка email-сообщений, участие в группах и т.д. Регистрация займёт у вас всего пару минут, поэтому мы рекомендуем это сделать.'
	),
	array(
		0 => 'Я не могу войти. Что делать?',
		1 => 'Сначала проверьте свои имя пользователя и пароль. Попробуйте восстановить их, пройдя по ссылке «Забыли пароль?». Если вы только что зарегистрированы, на указанный email должны быть отправлены инструкции по активации, которые необходимо выполнить до входа. Если email-сообщение не получено, то возможно, что вы указали неправильный адрес email, либо оно заблокировано спам-фильтром. В таком случае обратитесь за помощью к администратору форума.'
	),
	array(
		0 => 'Почему мне периодически приходится повторять ввод имени и пароля?',
		1 => 'Если вы не отметили флажком пункт <em>Автоматически входить при каждом посещении</em>, вы сможете оставаться под своим именем на форуме только некоторое ограниченное время. Это сделано для того, чтобы никто другой не смог воспользоваться вашей учётной записью. Для того чтобы вам не приходилось вводить имя пользователя и пароль каждый раз, вы можете выбрать указанный пункт при входе на форум. Не рекомендуется делать это на общедоступном компьютере, например в библиотеке, интернет-кафе, университете и т.д.'
	),
	array(
		0 => 'Почему я не могу зарегистрироваться?',
		1 => 'Возможно, администратор форума заблокировал ваш IP-адрес или запретил имя, под которым вы пытаетесь зарегистрироваться. Он также мог отключить регистрацию новых пользователей. Обратитесь за помощью к администратору форума.',
	),
	array(
		0 => '--',
		1 => 'Параметры и настройки пользователя'
	),
	array(
		0 => 'На форуме неправильное время!',
		1 => 'Возможно, отображается время, относящееся к другому часовому поясу, а не к тому, в котором находитесь вы. В этом случае измените в личных настройках часовой пояс на тот, в котором вы находитесь. Учтите, что изменять часовой пояс, как и большинство настроек, могут только зарегистрированные пользователи. Если вы не зарегистрированы, то сейчас удачный момент сделать это. '
	),
	array(
		0 => 'Чем отличаются закладки от подписки?',
		1 => 'Закладки в phpBBex больше похожи на закладки в вашем веб-браузере. Вы не будете предупреждены о произошедших изменениях, но сможете вернуться в тему позже. Однако, оформив подписку, вы будете получать уведомления об изменениях в теме или разделе на форуме предпочтительным вам способом или способами.'
	),
	array(
		0 => 'Как мне подписаться на определённую тему или раздел?',
		1 => 'Чтобы подписаться на определённый раздел, зайдите на него и щёлкните по ссылке «Подписаться на раздел». Чтобы подписаться на тему, поставьте соответствующую галочку при отправке ответа, либо щёлкните по ссылке «Подписаться на тему» на странице просмотра темы.'
	),
	array(
		0 => 'Как мне отказаться от подписки?',
		1 => 'Для отказа от подписки перейдите в вашу личную панель управления и щёлкните по ссылке «Подписки».'
	),
	array(
		0 => '--',
		1 => 'Создание сообщений'
	),
	array(
		0 => 'Что такое BBCode? Как форматировать текст сообщений и вставлять изображения?',
		1 => 'BBCode — это специальный язык разметки, предлагающий большие возможности по форматированию отдельных частей сообщения, вставке изображений и т.д. Возможность использования BBCode определяется администратором, однако BBCode также может быть отключен на уровне сообщения в форме для его отправки. BBCode очень похож на HTML, но теги в нём заключаются в квадратные скобки [ и ], а не в &lt; и &gt;. За дополнительной информацией о BBCode обратитесь к <a href="./faq.php?mode=bbcode">руководству по BBCode</a>.'
	),
	array(
		0 => 'Что означает кнопка «Сохранить» при создании сообщения?',
		1 => 'Эта кнопка позволяет вам сохранять сообщения для того, чтобы закончить и отправить их позже. Для загрузки сохранённого сообщения перейдите в раздел «Черновики» вашей личной панели управления.'
	),
	array(
		0 => 'Почему моё сообщение требует одобрения?',
		1 => 'Администратор форума может решить, что сообщения требуют предварительного просмотра перед отправкой. Возможно также, что администратор включил вас в группу пользователей, сообщения которых, по его или её мнению, должны быть предварительно просмотрены перед отправкой. Пожалуйста, свяжитесь с администратором форума для получения дополнительной информации.'
	),
	array(
		0 => 'Как мне вновь поднять мою тему?',
		1 => 'Щёлкнув по ссылке «Поднять тему» при просмотре темы, вы можете «поднять» её в верхнюю часть первой страницы раздела. Если этого не происходит, то это означает, что возможность поднятия тем могла быть отключена, или время, которое должно пройти до повторного поднятия темы, ещё не прошло. Также можно поднять тему, просто ответив на неё, однако удостоверьтесь, что тем самым вы не нарушаете правила форума, на котором находитесь.'
	),
	array(
		0 => '--',
		1 => 'Уровни пользователей и группы'
	),
	array(
		0 => 'Кто такие администраторы?',
		1 => 'Администраторы — это пользователи, наделённые высшим уровнем контроля над форумом. Они могут управлять всеми аспектами работы форума, включая разграничение прав доступа, отключение пользователей, создание групп пользователей, назначение модераторов и т.п., в зависимости от прав, предоставленных им создателем форума. Они также могут обладать всеми возможностями модераторов во всех разделах, в зависимости от настроек, произведённых создателем форума.'
	),
	array(
		0 => 'Кто такие модераторы?',
		1 => 'Модераторы — это пользователи (или группы пользователей), которые ежедневно следят за форумом. Они имеют право редактировать или удалять сообщения, закрывать, открывать, перемещать, удалять и объединять темы в разделах, за которые они отвечают. Основные задачи модераторов — не допускать несоответствия содержания сообщений обсуждаемым темам (оффтопик), оскорблений.'
	),
	array(
		0 => 'Что такое группы пользователей?',
		1 => 'Группы пользователей разбивают сообщество на структурные части, управляемые администратором форума. Каждый пользователь может состоять в нескольких группах, и каждой группе могут быть назначены индивидуальные права доступа. Это облегчает администраторам назначение прав доступа одновременно большому количеству пользователей, например, изменение модераторских прав или предоставление пользователям доступа к приватным разделам.'
	),
	array(
		0 => 'Что такое группа по умолчанию?',
		1 => 'Если вы состоите более чем в одной группе, ваша группа по умолчанию используется для того, чтобы определить, какие групповые цвет и звание должны быть вам присвоены. Администратор форума может предоставить вам разрешение самому изменять вашу группу по умолчанию в личной панели управления.'
	)
);
