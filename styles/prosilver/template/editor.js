/**
* bbCode control by subBlue design [ www.subBlue.com ]
* Includes unixsafe colour palette selector by SHS`
*/

// Startup variables
var bbcodeEnabled = true;
var theSelection = false;
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var is_ie = ((clientPC.indexOf('msie') != -1) && (clientPC.indexOf('opera') == -1));
var baseHeight;

/**
* Fix a bug involving the TextRange object. From
* http://www.frostjedi.com/terra/scripts/demo/caretBug.html
*/
function initInsertions()
{
	var doc;

	if (document.forms[form_name])
	{
		doc = document;
	}
	else
	{
		doc = opener.document;
	}

	var textarea = doc.forms[form_name].elements[text_name];

	if (is_ie && typeof(baseHeight) != 'number')
	{
		textarea.focus();
		baseHeight = doc.selection.createRange().duplicate().boundingHeight;

		if (!document.forms[form_name])
		{
			document.body.focus();
		}
	}
}

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
		var is_first = true;
		jQuery.each(items, function(index, value)
		{
			if (!is_first) selected += '\n';
			is_first = false;
			value = jQuery.trim(value);
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
	var selected = jQuery.trim((textarea.value).substring(textarea.selectionStart, textarea.selectionEnd));

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

	if (is_ie)
	{
		// Get text selection
		theSelection = document.selection.createRange().text;

		if (theSelection)
		{
			// Add tags around selection
			document.selection.createRange().text = bbopen + theSelection + bbclose;
			textarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (textarea.selectionEnd && (textarea.selectionEnd - textarea.selectionStart > 0))
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
	var caret_pos = getCaretPosition(textarea).start;
	var start_pos = caret_pos + bbopen.length;
	var end_pos = caret_pos + bbopen.length + bbtext.length;
	var after_pos = caret_pos + bbopen.length + bbtext.length + bbclose.length;

	// Open tag
	insert_text(bbopen + bbtext + bbclose);

	// Center the cursor when we don't have a selection
	// Gecko and proper browsers
	if (!isNaN(textarea.selectionStart))
	{
		textarea.selectionStart = sel_after ? after_pos : start_pos;
		textarea.selectionEnd = sel_after ? after_pos : end_pos;
	}
	// IE
	else if (document.selection)
	{
		var range = textarea.createTextRange();
		range.move("character", sel_after ? after_pos : end_pos);
		range.select();
		storeCaret(textarea);
	}

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

	// Since IE9, IE also has textarea.selectionStart, but it still needs to be treated the old way.
	// Therefore we simply add a !is_ie here until IE fixes the text-selection completely.
	if (!isNaN(textarea.selectionStart) && !is_ie)
	{
		var sel_start = textarea.selectionStart;
		var sel_end = textarea.selectionEnd;

		mozWrap(textarea, text, '');
		textarea.selectionStart = sel_start + text.length;
		textarea.selectionEnd = sel_end + text.length;
	}
	else if (textarea.createTextRange && textarea.caretPos)
	{
		if (baseHeight != textarea.caretPos.boundingHeight)
		{
			textarea.focus();
			storeCaret(textarea);
		}

		var caret_pos = textarea.caretPos;
		caret_pos.text = caret_pos.text.charAt(caret_pos.text.length - 1) == ' ' ? caret_pos.text + text + ' ' : caret_pos.text + text;
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
	var message_name = 'message_' + post_id;
	var theSelection = '';
	var divarea = false;

	if (document.all)
	{
		divarea = document.all[message_name];
	}
	else
	{
		divarea = document.getElementById(message_name);
	}

	// Get text selection - not only the post content :(
	// IE9 must use the document.selection method but has the *.getSelection so we just force no IE
	if (window.getSelection && !is_ie && !window.opera)
	{
		theSelection = window.getSelection().toString();
	}
	else if (document.getSelection && !is_ie)
	{
		theSelection = document.getSelection();
	}
	else if (document.selection)
	{
		theSelection = document.selection.createRange().text;
	}

	if (theSelection == '' || typeof theSelection == 'undefined' || theSelection == null)
	{
		if (divarea.innerHTML)
		{
			theSelection = divarea.innerHTML.replace(/<br>/ig, '\n');
			theSelection = theSelection.replace(/<br\/>/ig, '\n');
			theSelection = theSelection.replace(/&lt\;/ig, '<');
			theSelection = theSelection.replace(/&gt\;/ig, '>');
			theSelection = theSelection.replace(/&amp\;/ig, '&');
			theSelection = theSelection.replace(/&nbsp\;/ig, ' ');
		}
		else if (document.all)
		{
			theSelection = divarea.innerText;
		}
		else if (divarea.textContent)
		{
			theSelection = divarea.textContent;
		}
		else if (divarea.firstChild.nodeValue)
		{
			theSelection = divarea.firstChild.nodeValue;
		}
	}

	theSelection = jQuery.trim(theSelection);
	if (theSelection)
	{
		if (bbcodeEnabled)
		{
			insert_text('[quote="' + username + '"]' + theSelection + '[/quote]\n');
		}
		else
		{
			insert_text(username + ':' + '\n');
			var lines = split_lines(theSelection);
			for (i = 0; i < lines.length; i++)
			{
				insert_text('> ' + lines[i] + '\n');
			}
		}
	}

	return;
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

	return;
}

/**
* Insert at Caret position. Code from
* http://www.faqts.com/knowledge_base/view.phtml/aid/1052/fid/130
*/
function storeCaret(textEl)
{
	if (textEl.createTextRange && document.selection)
	{
		textEl.caretPos = document.selection.createRange().duplicate();
	}
}

/**
* Color pallette
*/
function colorPalette(dir, width, height)
{
	var r = 0, g = 0, b = 0;
	var numberList = new Array(6);
	var color = '';

	numberList[0] = '00';
	numberList[1] = '40';
	numberList[2] = '80';
	numberList[3] = 'BF';
	numberList[4] = 'FF';

	document.writeln('<table cellspacing="1" cellpadding="0" border="0">');

	for (r = 0; r < 5; r++)
	{
		if (dir == 'h')
		{
			document.writeln('<tr>');
		}

		for (g = 0; g < 5; g++)
		{
			if (dir == 'v')
			{
				document.writeln('<tr>');
			}

			for (b = 0; b < 5; b++)
			{
				color = String(numberList[r]) + String(numberList[g]) + String(numberList[b]);
				document.write('<td bgcolor="#' + color + '" style="width: ' + width + 'px; height: ' + height + 'px;">');
				document.write('<a href="#" onclick="bbfontstyle(\'[color=#' + color + ']\', \'[/color]\'); return false;"><img src="images/spacer.gif" width="' + width + '" height="' + height + '" alt="#' + color + '" title="#' + color + '" /></a>');
				document.writeln('</td>');
			}

			if (dir == 'v')
			{
				document.writeln('</tr>');
			}
		}

		if (dir == 'h')
		{
			document.writeln('</tr>');
		}
	}
	document.writeln('</table>');
}


/**
* Caret Position object
*/
function caretPosition()
{
	var start = null;
	var end = null;
}


/**
* Get the caret position in an textarea
*/
function getCaretPosition(txtarea)
{
	var caretPos = new caretPosition();

	// simple Gecko/Opera way
	if(txtarea.selectionStart || txtarea.selectionStart == 0)
	{
		caretPos.start = txtarea.selectionStart;
		caretPos.end = txtarea.selectionEnd;
	}
	// dirty and slow IE way
	else if(document.selection)
	{

		// get current selection
		var range = document.selection.createRange();

		// a new selection of the whole textarea
		var range_all = document.body.createTextRange();
		range_all.moveToElementText(txtarea);

		// calculate selection start point by moving beginning of range_all to beginning of range
		var sel_start;
		for (sel_start = 0; range_all.compareEndPoints('StartToStart', range) < 0; sel_start++)
		{
			range_all.moveStart('character', 1);
		}

		txtarea.sel_start = sel_start;

		// we ignore the end value for IE, this is already dirty enough and we don't need it
		caretPos.start = txtarea.sel_start;
		caretPos.end = txtarea.sel_start;
	}

	return caretPos;
}
