/**
* bbCode control by subBlue design [ www.subBlue.com ]
* Includes unixsafe colour palette selector by SHS`
*/

/**
* Shows the help messages in the helpline window
*/
function helpline(help)
{
	document.forms[form_name].helpbox.value = help_line[help];
}

/**
* bbstyle
*/
function bbstyle(bbnumber)
{
	if (bbnumber != -1)
	{
		bbfontstyle(bbtags[bbnumber], bbtags[bbnumber+1]);
	}
	else
	{
		insert_text('[*]');
		document.forms[form_name].elements[text_name].focus();
	}
}

/**
* Apply bbcodes
*/
function bbfontstyle(bbopen, bbclose)
{
	var textarea = document.forms[form_name].elements[text_name];

	textarea.focus();

	if (document.forms[form_name].elements[text_name].selectionEnd && (document.forms[form_name].elements[text_name].selectionEnd - document.forms[form_name].elements[text_name].selectionStart > 0))
	{
		mozWrap(document.forms[form_name].elements[text_name], bbopen, bbclose);
		document.forms[form_name].elements[text_name].focus();
		return;
	}

	//The new position for the cursor after adding the bbcode
	var caret_pos = textarea.selectionStart;
	var new_pos = caret_pos + bbopen.length;

	// Open tag
	insert_text(bbopen + bbclose);

	// Center the cursor when we don't have a selection
	textarea.selectionStart = new_pos;
	textarea.selectionEnd = new_pos;
	textarea.focus();
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

	insert_text('[quote="' + username + '"]' + selection + '[/quote]');
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

	document.write('<table class="type2" cellspacing="1" cellpadding="0" border="0">');

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
