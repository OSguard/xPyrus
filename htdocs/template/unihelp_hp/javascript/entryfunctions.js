// JavaScript Document
// $Id: entryfunctions.js 1935 2006-11-14 00:00:55Z trehn $
// $HeadURL: svn://unihelp.de/unihelp_dev/v2/trunk/htdocs/template/unihelp_de/javascript/entryfunctions.js $

// code adapted from
// http://blog.vishalon.net/Post/57.aspx
function getCaretPosition (element) {
    var caretPos = 0;
    // bad browser
    if (document.selection) {
        element.focus();
        var selection = document.selection.createRange ();
        selection.moveStart ('character', -element.value.length);
        caretPos = selection.text.length;
    }
    // good browser
    else if (element.selectionStart || element.selectionStart == '0') {
        caretPos = element.selectionStart;
    }

    return caretPos;
}
function setCaretPosition(element, pos){
    if(element.setSelectionRange) {
        element.focus();
        element.setSelectionRange(pos,pos);
    } else if (element.createTextRange) {
        var range = element.createTextRange();
        range.collapse(true);
        range.moveEnd('character', pos);
        range.moveStart('character', pos);
        range.select();
    }
}

function AddText(text,win) {
  var comment = win.document.getElementById('entrytext');
  pos = getCaretPosition(comment);
  if (comment.value.charAt(pos-1) != ' ') text=' ' + text;
  if (comment.value.charAt(pos) != ' ') text=text + ' ';
  comment.value = comment.value.substr(0,pos) + text + comment.value.substr(pos);
  setCaretPosition(comment, pos+text.length);
}

function AddTextOpener(text,win) {
  var comment = win.document.getElementById('opener');
  pos = getCaretPosition(comment);
  if (comment.value.charAt(pos-1) != ' ') text=' ' + text;
  if (comment.value.charAt(pos) != ' ') text=text + ' ';
  comment.value = comment.value.substr(0,pos) + text + comment.value.substr(pos);
  setCaretPosition(comment, pos+text.length);
}

function AddTextInternal(text) {
  AddText(text,window);
  // return false for onclick
  return false;
}

function AddTextExternal(text) {
  AddText(text,opener);
  // return false for onclick
  return false;
}

function SurroundText(element, before, after) {
  // bad browser
  if (document.selection) {
    var selection = document.selection.createRange();
	  // hack for IE7, because the padding of selection with text
	  // seems not to work on empty selections
	  if (!selection.text.length) {
			AddTextInternal(before + after);
			return;
		}
		
		selection.text = before + selection.text + after;
		selection.collapse(false);
		selection.select();
  }
  // good browser
  else if (element.selectionStart || element.selectionStart == '0') {
		var string = element.value;
		var selEnd = element.selectionEnd;
		string = string.substring(0, element.selectionStart) + before + string.substring(element.selectionStart, element.selectionEnd) + after + string.substr(element.selectionEnd);
		element.value = string;
		var pos = selEnd + before.length + after.length;
		element.setSelectionRange(pos,pos);

  }
	element.focus();
}

/* inline attachment */
function inlineAtm(id) {
  AddTextInternal('[atm=' + id + ']');
}

function inlineAtmOpener(id){
  AddTextOpener('[atm=' + id + ']',window);
  return false;
}

/* formatting functions */
function bold() {
	SurroundText($('entrytext'), '[b]', '[/b]');
}

function italicize() {
  SurroundText($('entrytext'), '[i]', '[/i]');
}

function underline() {
  SurroundText($('entrytext'), '[u]', '[/u]');
}

function hr() {
  AddTextInternal('[hr]');
}

function size() {
  SurroundText($('entrytext'), '[size=16]', '[/size]');
}

function showcode() {
  SurroundText($('entrytext'), '[code]', '[/code]');
}

function showcode2() {
  var lang = prompt("Wähle eine Sprache:\nc, c++, c89, perl, php, java, vb, c#, ruby, python, pascal, sql","c")
  var valid = new Array('c','c++','c89','perl','php','java','vb','c#','ruby','python','pascal','sql')
  // trim input
  lang = lang.replace(/\s+/g, "");
  for (var i = 0; i < valid.length; ++i){
  	if(valid[i] == lang){
  		SurroundText($('entrytext'), '[code='+lang+']', '[/code]');
  		return;
  	}
  }
  
  SurroundText($('entrytext'), '[code]', '[/code]');
}

function showcolor(color) {
	SurroundText($('entrytext'), '[color='+color+']', '[/color]');
}

function showfontsize(size) {
  SurroundText($('entrytext'), '[size='+size+']', '[/size]');
}

function showfontfamily(fontfamily) {
  SurroundText($('entrytext'), '[font='+fontfamily+']', '[/font]');
}

function image() {
  SurroundText($('entrytext'), '[img]', '[/img]');
}

// TODO
/*function emai1() {
  SurroundText($('entrytext'), '[email]', '[/email]');
}*/

function quote() {
  SurroundText($('entrytext'), '[quote]', '[/quote]');
}

function list1() {
  AddTextInternal("[list]\n[*]\n[*]\n[*]\n[/list]");
}
function list2() {
  AddTextInternal("[list=1]\n[*]\n[*]\n[*]\n[/list]");
}
function tex() {
  SurroundText($('entrytext'), "[tex]", "[/tex]");
}
function align(type){
  SurroundText($('entrytext'), '[align='+type+']', '[/align]');
}

autoloadFunctions.push( function() {
    var sl = document.getElementById('smiley_link');
    if (!sl) return;
    
    sl.href="/smileys";
    sl.onclick="window.open('','smilies','width=700,height=500,resizable=YES,menubar=no,status=no,toolbar=no,location=no,scrollbars=YES')";
  }
);
