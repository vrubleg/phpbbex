/**
* bbCode control by subBlue design [ www.subBlue.com ]
* Includes unixsafe colour palette selector by SHS`
*/

// Startup variables
var bbcodeEnabled = true;
var theSelection = false;

/**
* bbstyle
*/
function bbstyle(bbnumber, event)
{
	var textarea = document.forms[form_name].elements[text_name];
	if (bbnumber != -1)
	{
		bbfontstyle(bbtags[bbnumber], bbtags[bbnumber+1], event);
	}
	textarea.focus();
}

function insert_listitem()
{
	var textarea = document.forms[form_name].elements[text_name];
	if (!textarea.selectionEnd || (textarea.selectionEnd - textarea.selectionStart == 0))
	{
		insert_text('[*]');
	}
	else
	{
		// Automatic [*] for each line
		var selLength = (typeof(textarea.textLength) == 'undefined') ? textarea.value.length : textarea.textLength;
		var selStart = textarea.selectionStart;
		var selEnd = textarea.selectionEnd;
		var scrollTop = textarea.scrollTop;

		if (selEnd == 1 || selEnd == 2)
		{
			selEnd = selLength;
		}

		var before = (textarea.value).substring(0,selStart);
		var selected = (textarea.value).substring(selStart, selEnd);
		var after = (textarea.value).substring(selEnd, selLength);

		var parts = selected.match(/^(\s*\[list[\w\d=]*\])((?:.|[\r\n])*)(\[\/list\]\s*)$/i);
		if (parts)
		{
			before += parts[1];
			selected = parts[2];
			after = parts[3] + after;
		}

		var items = selected.split(/\r\n|\r|\n/);
		selected = '';
		items.forEach(function(value)
		{
			if (selected != '') { selected += '\n'; }
			value = value.trim();
			if (!value) return true;
			if (value.indexOf('[*]') !== 0) selected += '[*]';
			selected += value;
		});

		textarea.value = before + selected + after;
		textarea.selectionStart = before.length;
		textarea.selectionEnd = before.length + selected.length;
		textarea.scrollTop = scrollTop;
	}
	textarea.focus();
}

/**
* Prepare URL for [url] and [img] bbcodes
*/
function prepare_url(url)
{
	if (!url) return '';
	url = url.replace('[', '%5B').replace(']', '%5D');
	if (url.charAt(0) == '/' && url.charAt(1) == '/') return 'http:' + url;
	if (url.match(/^[\w\d]+(\.php|\/|$)/i)) return './' + url;
	if (!url.match(/^[\w\d]+:/i) && !url.match(/^[.]?\//i)) return 'http://' + url;
	return url;
}

/**
* Apply bbcodes
*/
function bbfontstyle(bbopen, bbclose, event)
{
	theSelection = false;

	var textarea = document.forms[form_name].elements[text_name];

	var bbname = bbopen.match(/^\[([\w\d]+)/i);
	if (bbname) bbname = bbname[1].toLowerCase();
	var bbtext = '';
	var selected = textarea.value.substring(textarea.selectionStart, textarea.selectionEnd).trim();

	switch (bbname)
	{
		case 'url':
			var url = selected.match(/^((https?|ftp):\/\/[\w\d].*|[-\w\d]+(\.[-\w\d]+)+)$/i) ? selected : prompt(lang.enter_link_url, '');
			if (url === null) return;
			if (url)
			{
				url = prepare_url(url);
				bbopen = '[url=' + url + ']';
				bbtext = (url.match(/^https?:\/\/[^\/]+\/?$/i)) ? url.replace(/(https?:|\/)/ig, '') : url;
			}
		break;
		case 'quote':
			if (event.ctrlKey)
			{
				var name = prompt(lang.enter_quote_name, '');
				if (name === null) return;
				if (name) bbopen = '[quote="' + name + '"]';
			}
		break;
		case 'spoiler':
			if (event.ctrlKey)
			{
				var title = prompt(lang.enter_title, '');
				if (title === null) return;
				if (title) bbopen = '[spoiler="' + title + '"]';
			}
		break;
		case 'list':
			if (event.ctrlKey)
			{
				var start = prompt(lang.enter_list_start, '1');
				if (start === null) return;
				bbopen = '[list=' + start + ']';
			}
		break;
	}

	textarea.focus();

	if (textarea.selectionEnd && (textarea.selectionEnd - textarea.selectionStart > 0))
	{
		mozWrap(textarea, bbopen, bbclose);
		textarea.focus();
		theSelection = '';
		return;
	}

	var sel_after = false;
	switch (bbname)
	{
		case 'img':
			bbtext = prompt(lang.enter_image_url, '');
			if (bbtext === null) return;
			bbtext = prepare_url(bbtext);
			sel_after = true;
		break;
	}
	if (!bbtext) bbtext = '';

	//The new position for the cursor after adding the bbcode
	var caret_pos = textarea.selectionStart;
	var start_pos = caret_pos + bbopen.length;
	var end_pos = caret_pos + bbopen.length + bbtext.length;
	var after_pos = caret_pos + bbopen.length + bbtext.length + bbclose.length;

	// Open tag
	insert_text(bbopen + bbtext + bbclose);

	// Center the cursor when we don't have a selection
	textarea.selectionStart = sel_after ? after_pos : start_pos;
	textarea.selectionEnd = sel_after ? after_pos : end_pos;
	textarea.focus();
	return;
}

/**
* Insert text at position
*/
function insert_text(text, spaces, popup)
{
	var textarea;

	if (!popup)
	{
		textarea = document.forms[form_name].elements[text_name];
		var textarea_pos = parseInt(jQuery(textarea).position().top);
		var visible_from = jQuery(document).scrollTop();
		var visible_to = visible_from + jQuery(window).height();
		if (textarea_pos < visible_from || textarea_pos > (visible_to - 20))
		{
			jQuery(document).scrollTop(textarea_pos);
		}
	}
	else
	{
		textarea = opener.document.forms[form_name].elements[text_name];
	}
	if (spaces)
	{
		text = ' ' + text + ' ';
	}

	if (!isNaN(textarea.selectionStart))
	{
		var sel_start = textarea.selectionStart;
		var sel_end = textarea.selectionEnd;

		mozWrap(textarea, text, '');
		textarea.selectionStart = sel_start + text.length;
		textarea.selectionEnd = sel_end + text.length;
	}
	else
	{
		textarea.value = textarea.value + text;
	}

	if (!popup)
	{
		textarea.focus();
	}
}

/**
* Add inline attachment at position
*/
function attach_inline(index, filename)
{
	insert_text('[attachment=' + index + ']' + filename + '[/attachment]');
	document.forms[form_name].elements[text_name].focus();
}

/**
* Add quote text to message
*/
function addquote(post_id, username)
{
	var selection = window.getSelection().toString();

	if (!selection)
	{
		var divarea = document.getElementById('message_' + post_id);
		if (!divarea) { return; }

		selection = divarea.innerHTML
			.replace(/<br>/ig, '\n')
			.replace(/<br\/>/ig, '\n')
			.replace(/&lt\;/ig, '<')
			.replace(/&gt\;/ig, '>')
			.replace(/&amp\;/ig, '&')
			.replace(/&nbsp\;/ig, ' ');
	}

	selection = selection.trim();
	if (!selection) { return; }

	if (bbcodeEnabled)
	{
		insert_text('[quote="' + username + '"]' + selection + '[/quote]\n');
	}
	else
	{
		insert_text(username + ':' + '\n');
		var lines = split_lines(selection);
		for (i = 0; i < lines.length; i++)
		{
			insert_text('> ' + lines[i] + '\n');
		}
	}
}

function split_lines(text)
{
	var lines = text.split('\n');
	var splitLines = new Array();
	var j = 0;
	for(i = 0; i < lines.length; i++)
	{
		if (lines[i].length <= 80)
		{
			splitLines[j] = lines[i];
			j++;
		}
		else
		{
			var line = lines[i];
			do
			{
				var splitAt = line.indexOf(' ', 80);

				if (splitAt == -1)
				{
					splitLines[j] = line;
					j++;
				}
				else
				{
					splitLines[j] = line.substring(0, splitAt);
					line = line.substring(splitAt);
					j++;
				}
			}
			while(splitAt != -1);
		}
	}
	return splitLines;
}
/**
* From http://www.massless.org/mozedit/
*/
function mozWrap(txtarea, open, close)
{
	var selLength = (typeof(txtarea.textLength) == 'undefined') ? txtarea.value.length : txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	var scrollTop = txtarea.scrollTop;

	if (selEnd == 1 || selEnd == 2)
	{
		selEnd = selLength;
	}

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd);
	var s3 = (txtarea.value).substring(selEnd, selLength);

	txtarea.value = s1 + open + s2 + close + s3;
	txtarea.selectionStart = selStart + open.length;
	txtarea.selectionEnd = selEnd + open.length;
	txtarea.focus();
	txtarea.scrollTop = scrollTop;
}

/**
* Color pallette
*/
function colorPalette(dir, width, height)
{
	var r = 0, g = 0, b = 0;
	var numberList = ['00', '40', '80', 'BF', 'FF'];
	var color = '';

	document.write('<table cellspacing="1" cellpadding="0" border="0">');

	for (r = 0; r < 5; r++)
	{
		if (dir == 'h')
		{
			document.write('<tr>');
		}

		for (g = 0; g < 5; g++)
		{
			if (dir == 'v')
			{
				document.write('<tr>');
			}

			for (b = 0; b < 5; b++)
			{
				color = numberList[r] + numberList[g] + numberList[b];
				document.write('<td bgcolor="#' + color + '" style="width: ' + width + 'px; height: ' + height + 'px; cursor: pointer" onclick="bbfontstyle(\'[color=#' + color + ']\', \'[/color]\')" title="#' + color + '"></td>');
			}

			if (dir == 'v')
			{
				document.write('</tr>');
			}
		}

		if (dir == 'h')
		{
			document.write('</tr>');
		}
	}
	document.write('</table>');
}
