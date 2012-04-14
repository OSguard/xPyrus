function language(){
	this.username_fail = "Dieser Username ist ung&uuml;ltig.";
	this.username_exist = "Diesen Usernamen gibt es bereits.";
	this.second_course = 'Nebenstudiengang ';
	this.please_chose = "-- Bitte Eintrag auswählen --";
	this.course_homepage =  "Homepage des Faches";
	this.course_homepage_title = "Einmal Klicken um zur Homepage des gewälten Faches zu gelangen; neues Fenster";
	this.course_add = "Fach hinzufügen";
	this.course_add_title = "Einmal Klicken um ausgewälte Fächer hinzuzufügen";
	this.search_result = 'Suchergebnisse';
	this.empty_text = 'Text ist Leer - keine Vorschau';
	this.empty_caption = 'Überschrieft ist Leer - keine Vorschau';
	this.empty_opner = 'Opener-Text ist Leer - keine Vorschau';
	this.no_date = 'kein Datum gefunden - keine Vorschau';
	this.email_patter = 'vorname.nachname';
	this.email_example = 'z.B. unihelp@about.me';
}

var Language = new language();

function sendCourseBox(){
	var option = this.firstChild.nextSibling;
	var box = option.parentNode.parentNode;
	
	var instance = box.id.substr(box.id.length-1);
	var url = '/index.php';
	var pars = {
		dest:        'box',
		method:      'ajaxSetCourse',
		bname:       'courses_files',
		instance:	 instance,
		view:        'ajax',
		course:      option.value 
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//alert('super');
			var node = box.firstChild;
			//alert(node);
			for ( ; node.nextSibling != null; node = node.nextSibling ) {
		        // skip introducting headline and links (hopefully the box icons ;)
		        if (node.nodeType == 1 && 
		                !(node.nodeName == 'H3' || node.nodeName == 'A')) {
		            break;
		        }
		    }
		    for ( ; node != null; node = node.nextSibling ) {
		        try {
		            Effect.BlindUp(node, { afterFinish: function(obj, element) { obj.element.remove(); } });
		        } catch(e) {
		        }
		    }
			var div = document.createElement('div');
            div.innerHTML += request.responseText;
            div.style.display = 'none';
            box.appendChild(div);
            Effect.BlindDown(div, { duration: 2.0 });
            Behaviour.apply();
		},
		onFailure: function(request) {
			return true;
		}
	});
	
	/* don't use default behavoir */
	return false;
}

/* for user registration */
function checkUsername() {
    var usernameBox = $('username_register');
    
    if(usernameBox.value.search(/[^a-zA-Z0-9\-_]/) != -1){
    	var checkBox = $("username_check");
    	text = Language.username_fail;
    	checkBox.innerHTML = text;
    	return;
    }
    var pars = "mod=usermanagement&view=ajax&method=ajaxCheckUsername&username="+usernameBox.value;
    var ajax = new Ajax.Request(
        "http://"+location.host+"/index.php", 
        {
            method: 'get',
            view:   'ajax',
            parameters: pars, 
            onComplete: jsonCheckUsername
        });
}
function jsonCheckUsername(req) {
    usernameCheck = eval(req.responseText);
    var checkBox = $("username_check");
    var text = "";
    if (usernameCheck == 1) {
        text = Language.username_exist;
    } else if (usernameCheck == 2) {
        text = Language.username_fail;
    }
    checkBox.innerHTML = text;
}

function minimizeBox(box) {
    var node = box.firstChild;
    for ( ; node.nextSibling != null; node = node.nextSibling ) {
        // skip introducting headline and links (hopefully the box icons ;)
        if (node.nodeType == 1 && 
                !(node.nodeName == 'H3' || node.nodeName == 'A')) {
            break;
        }
    }
    for ( ; node != null; node = node.nextSibling ) {
        try {
            Effect.Fade(node, { afterFinish: function(obj, element) { obj.element.remove(); } });
        } catch(e) {
        }
    }
    
    // AJAX part
    var pars = "mod=usermanagement&view=ajax&method=ajaxBoxMinimize&boxname="+box.id.substr(4);
    var ajax = new Ajax.Request(
        "http://"+location.host+"/index.php",
        {
            method: 'get',
            parameters: pars
        });

    // update box action icon
    var iconCollapse = $(box.id.substr(4) + '_collapse');
    iconCollapse.setAttribute('class', 'icon iconMaximize');
    iconCollapse.setAttribute('href', iconCollapse.getAttribute('href').replace(/minimize/, 'maximize'));
    iconCollapse.setAttribute('title', iconCollapse.getAttribute('title').replace(/minim/, 'maxim'));
    iconCollapse.onclick = function() { if (maximizeBox(box)) { return false; } return true; };
    
    return true;
}

function closeBox(box) {
    // AJAX part
    var pars = "mod=usermanagement&view=ajax&method=ajaxBoxClose&boxname="+box.id.substr(4);
    var ajax = new Ajax.Request(
        "http://"+location.host+"/index.php",
        {
            method: 'get',
            parameters: pars
        });
	
	var iconCollapse = $(box.id.substr(4) + '_collapse');
	if(iconCollapse != null){
		iconCollapse.style.display = 'none';
	}
	
	var iconClose = $(box.id.substr(4) + '_close');
	iconClose.style.display = 'none';
    try {
        Effect.DropOut(box, {afterFinish: function(obj) { box.parentNode.removeChild(box); } } );
    } catch(e) {
    }
    
    return true;
}

function maximizeBox(box) {
    // AJAX part
    var pars = "mod=usermanagement&view=ajax&method=ajaxBoxMaximize&boxname="+box.id.substr(4);
    var ajax = new Ajax.Request(
        "http://"+location.host+"/index.php",
        {
            method: 'get',
            parameters: pars,
            onComplete: function(req) {
                            var div = document.createElement('div');
                            div.innerHTML += req.responseText;
                            div.style.display = 'none';
                            box.appendChild(div);
                            Effect.Appear(div, { duration: 2.0 });
                            Behaviour.apply()
                        }
        });

    // update box action icon
    var iconCollapse = $(box.id.substr(4) + '_collapse');
    iconCollapse.setAttribute('class', 'icon iconMinimize');
    iconCollapse.setAttribute('href', iconCollapse.getAttribute('href').replace(/maximize/, 'minimize'));
    iconCollapse.setAttribute('title', iconCollapse.getAttribute('title').replace(/maxim/, 'minim'));
    iconCollapse.onclick = function() { if (minimizeBox(box)) { return false; } return true; };
    
    return true;
}

 
function sendSubmit() { 
    return _sendSubmit();
}

/*********************************************************************
 * 
 */
 
/*
 * helper functions
 */
function elementBlur(el, str) {
    if (el.value=='') {
        el.value=str; 
    }
}
function elementFocus(el, str) {
    if (el.value==str) {
        el.value='';
    }
}

function in_array(needle, haystack){
	if (typeof haystack != 'object'){
		return false;
	}
	for (a=0; a<haystack.length;a++){
		if (haystack[a] == needle){
			return true;
		}
	}
	return false;
}

function setOptions(elementId, params, selectedOptions){
	options = "";
	if (elementId == null || elementId == "" || 
		(typeof params != 'object') || !(element = document.getElementById(elementId))
		|| params.length==0){
		return false;
	}
	
	for (i=0;i<params.length;i++){
		optId = params[i][0];
		sel = (in_array(optId, selectedOptions) ? " selected=\"selected\"" : ""); //(params[i].length>2 && params[i][2] ? " selected=\"selected\"" : "");
		options += "<option value=\"" + optId +"\"" + sel +">" + params[i][1] + "</option>";
	}
	element.innerHTML += options;
	return true;
}

function showGuestbookFilter(displayFilter) {
    var url = '/index.php'
    var pars = {
        method:      'ajaxGuestbookFilterShow',
        mod:         'userinfo',
        view:        'ajax',
        show:        displayFilter
    }

    var myAjaxRequest = new Ajax.Request(url, {
        parameters: $H(pars).toQueryString(),
        onSuccess : function(request) {
            if (displayFilter) {
                //$('guestbookfilter').style.display = 'block';
                Effect.Appear('guestbookfilter');
                //$('guestbookFilterToggle').firstChild.data = '(ausblenden)';
                $('guestbookFilterToggle').firstChild.src = '/images/icons/delete.png';
            } else {
                //$('guestbookfilter').style.display = 'none';
                Effect.Fade('guestbookfilter');
                $('guestbookFilterToggle').firstChild.src = '/images/icons/add.png';
                //$('guestbookFilterToggle').firstChild.data = '(einblenden)';
            }
        }
    });
}

function showDiaryFilter(displayFilter) {
    var url = '/index.php'
    var pars = {
        method:      'ajaxDiaryFilterShow',
        mod:         'userinfo',
        view:        'ajax',
        show:        displayFilter
    }

    var myAjaxRequest = new Ajax.Request(url, {
        parameters: $H(pars).toQueryString(),
        onSuccess : function(request) {
            if (displayFilter) {
                //$('diaryfilter').style.display = 'block';
                Effect.Appear('diaryfilter');
                //$('diaryFilterToggle').firstChild.data = '(ausblenden)';
                $('diaryFilterToggle').firstChild.src = '/images/icons/delete.png';
            } else {
                //$('diaryfilter').style.display = 'none';
                Effect.Fade('diaryfilter');
                //$('diaryFilterToggle').firstChild.data = '(einblenden)';
                $('diaryFilterToggle').firstChild.src = '/images/icons/add.png';
            }
        }
    });
}

function sortUserOnline(){
	var sortType = this.innerHTML;
	if (sortType == 'AG') sortType = 'age';
	if (sortType == 'GE') sortType = 'gender';
	if (sortType == 'ST') sortType = 'status';
	if (sortType == 'SG') sortType = 'course';
	if (sortType == 'US') sortType = 'username';
	
	var url = '/index.php';
	var pars = {
		dest:        'box',
		method:      'ajaxSort',
		bname:       'user_online',
		instance:	 '1',
        view:        'ajax',
		sortBy:      sortType 
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			var list = $('user-online-list');;
			
			var ul = document.createElement('ul');
            ul.innerHTML += request.responseText;
            ul.style.display = 'none';

			list.parentNode.insertBefore(ul,list);

			try {
	            Effect.BlindUp(list, { afterFinish: function(obj, element) { obj.element.remove(); } });
	        } catch(e) {
	        }
			
            Effect.BlindDown(ul, { duration: 2.0 });
            Behaviour.apply();
			
		},
		onFailure: function(request) {
			return true;
		}
	});
	
	
	return false;
}

function forumQoute(){
	
	/*search selceted txt */
	var txt = '';
	if (window.getSelection){
		txt = window.getSelection();
	}else if (document.getSelection){
		txt = document.getSelection();
	}else if (document.selection)	{
		txt = document.selection.createRange().text;
	}else 
		return true;
		
	/* if nothing selcet execute link */
	if(txt=='') return true;
	
	var author = this.parentNode.title;
	
	/* insert Text with Quote */
	AddText('[quote='+author+']'+txt+'[/quote]',window)
	
	return false;
}

 function addStudyPath(){
	/* search after objekts */
	var select; var alabel;
	var testSelected; var testLabel;
	var nr;
	for(var i = 0; i < 5 ; i++){
		testSelected = $('study_path'+i);
		testLabel = $('label_study_path'+i)
		if(testSelected != null){
			select = testSelected;
			alabel = testLabel;
			nr = i + 1;
		}
	}
	/* we add only one if we select a course before */
	if(select.selectedIndex == 0){
		return false;
	}
	if(nr >= 5){
		return false;
	}
	
	/*create new objekt */
	var newSelect = select.cloneNode(true);
	var newLabel = alabel.cloneNode(true);
	
	/* change id and names*/
	var id = newSelect.id.substr(0,newSelect.id.length-1);
	newSelect.id = id + '' + nr;
	newSelect.name = id + '' + nr;
	newLabel.htmlFor = id + '' + nr;
	newLabel.id = 'label_' + id + '' + nr;
	
	/* change some stuff for second course */
	newLabel.innerHTML = Language.second_course + (nr);
	/* it is easy to remove and create a new one */
	if(newSelect.getElementsByTagName('option')[0].value == "0"){
		newSelect.removeChild(newSelect.getElementsByTagName('option')[0]);
	}
	var option = document.createElement('option');
	option.value = '0';
	option.label = Language.please_chose;
	option.innerHTML = Language.please_chose;
	newSelect.insertBefore(option,newSelect.firstChild);
	newSelect.selectedIndex = 0;
	
	/* adding */
	var br = document.createElement('br');
	select.parentNode.insertBefore(br,select.nextSibling)
	select.parentNode.insertBefore(newLabel,br.nextSibling);
	select.parentNode.insertBefore(newSelect,newLabel.nextSibling);
	
	return false;
}

function updateMaildomains(){
	var uniId = $('uniId');
	var uni = uniId.options[uniId.selectedIndex];

	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'usermanagement',
		method:      'ajaxGetMaildomains',
        view:        'ajax',
		uni_id:      uni.value 
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			/* remove all */
			var uniEmailDomain = $('uniEmailDomain');
			for(var i = 0; i < uniEmailDomain.options.length; i++){
				uniEmailDomain.options[i].style.display = 'none';
			}
			/* get needed */
			var items = eval(request.responseText);
			/* add back */
			var toSelect = null;
			for(var i = 0; i < uniEmailDomain.options.length; i++){
				for(var j = 0; j < items.length; j++){
					if(uniEmailDomain.options[i].value == items[j]){
						uniEmailDomain.options[i].style.display = 'block ';
						if(toSelect === null){ toSelect = i ;}
					}
				}
			}
			if(toSelect != null){ uniEmailDomain.selectedIndex = toSelect }
		},
		onFailure: function(request) {
			var uniEmailDomain = $('uniEmailDomain');
			for(var i = 0; i < uniEmailDomain.options.length; i++){
				uniEmailDomain.options[i].style.display = 'block';
			}
		}
	});
	
}

function createCourseSearchForum() {
	var homepage = document.createElement('input');
	homepage.type = "submit";
	homepage.name = "showcoursepage_form";
	homepage.id = "courseManagePage2"
	homepage.value = Language.course_homepage;
	homepage.title= Language.course_homepage_title;
	$('searchcourses').parentNode.insertBefore(homepage, $('searchcourses').nextSibling.nextSibling.nextSibling);
	
	var add = document.createElement('input');
	add.type = "submit";
	add.name = "addcourses_form";
	add.id = "courseManageAdd";
	add.value = Language.course_add;
	add.title= Language.course_add_title;
	add.style.marginLeft = "15em";
	$('searchcourses').parentNode.insertBefore(add, $('searchcourses').nextSibling.nextSibling.nextSibling);
	
	var br = document.createElement('br');
	$('searchcourses').parentNode.insertBefore(br, $('searchcourses').nextSibling.nextSibling.nextSibling);
		
	var select = document.createElement('select');
	select.id = 'findcourses';
	select.name = 'findcourses[]';
	select.size = "5";
	select.multiple = "multiple";
	select.className = "wide";
	$('searchcourses').parentNode.insertBefore(select, $('searchcourses').nextSibling.nextSibling.nextSibling);
	
	var newlabel = document.createElement('label');
	newlabel.htmlFor ='findcourses';
	newlabel.innerHTML = Language.search_result;
	$('searchcourses').parentNode.insertBefore(newlabel, $('searchcourses').nextSibling.nextSibling.nextSibling);
	
	var br2 = document.createElement('br');
	$('searchcourses').parentNode.insertBefore(br2, $('searchcourses').nextSibling.nextSibling.nextSibling);
	
}

function searchCourses(){
	var searchFor = this.value;

	if(searchFor.length < 3){
		return;
	}

	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'usermanagement',
		method:      'ajaxCourse',
        view:        'ajax',
		coursename:  searchFor 
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			var test = document.getElementById('findcourses');
			if(test == null){
				createCourseSearchForum();
				Behaviour.apply();
			}
			var select = $('findcourses');
			try{
				while(true){
					select.firstChild.remove();
				}
			}catch(e) {
	        }
			//alert(request.responseText);
			var items = eval("(" +request.responseText + ")");
			for(var i=0; i < items.length; i++) {
				var newOption = document.createElement('option');
				newOption.value = items[i].id;
				newOption.innerHTML = items[i].name;
				select.appendChild(newOption);
			}
			
			//alert('ok');
		},
		onFailure: function(request) {
			alert('fehler');
		}
	});
}

function courseManage(button){
	//alert(button);
	$('searchcourses').value = '';
	return true;
	
	return false;
}

/*
 * need tho have execute funtion not twice
 */
var global_execute_previewSubmit = false;
/*
 * Ajax Preview Forum
 * @var submit element
 */
function previewSubmitForum(me){
	// Funktion should not execute parallel
	if(global_execute_previewSubmit){
		return false;
	}else{
		global_execute_previewSubmit = true;
	}
	/* get text and test */
	var text = me.form.entryText.value;
	if(text == ""){
		alert(Language.empty_text);
		global_execute_previewSubmit = false;
		return false;
	}
    /* files need upload first */
	var upload = me.form.file_attachment1.value;
	if(upload != ""){
		//alert('Datei bitte erst hochladen für Vorschau');
		global_execute_previewSubmit = false;
		return true;
	}
	/* remove old preview */
	try {
        Effect.Fade($('previewdiv'), { afterFinish: function(obj, element) { obj.element.remove(); } });
    } catch(e) {
    }
	/* get caption */
	var caption = me.form.caption.value;
	/* look on thread or we create new */
	if(me.form.threadId){
		var thread = me.form.threadId.value;
	}else{
		var thread = 'new';
	}
	/* formatcode option */
	var enable_smileys = me.form.enable_smileys.checked;
	var enable_formatcode = me.form.enable_formatcode.checked;
	/* author is group? */
	if(me.form.for_group){
		var for_group = me.form.for_group.value;
	}else{
		var for_group = false;
	}
	/* we part of editMode */
	if(me.form.entryId){
		var entryId = me.form.entryId.value;
	}
	/* look we know the forumId */
	if(me.form.forumId){
		var forumId = me.form.forumId.value;
	}
	
	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'forum',
		method:      'ajaxPreview',
        view:        'ajax',
		entryText:   text, 
		caption:     caption,
		threadId:    thread,
		enable_smileys: enable_smileys,
		enable_formatcode: enable_formatcode,
		for_group:   for_group,
		entryId:     entryId,
		forumId:     forumId
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//document.write(request.responseText);
			
			var entry = $('entry').parentNode;
			
			var div = document.createElement('div');
			div.innerHTML = request.responseText;
			div.style.display = 'none';
			div.id = 'previewdiv';
			
			entry.parentNode.insertBefore(div, entry);
			
			Effect.Appear(div);
			global_execute_previewSubmit = false;
			//alert('sucsses');
		},
		onFailure: function(request) {
			alert('fehler');
			global_execute_previewSubmit = false;
		}
	});
	
	//alert('ok');
	
	return false;
}

function previewSubmitPM(me){
	// Funktion should not execute parallel
	if(global_execute_previewSubmit){
		return false;
	}else{
		global_execute_previewSubmit = true;
	}
	
	/* get text and test */
	var text = me.form.entryText.value;
	if(text == ""){
		alert(Language.empty_text);
		global_execute_previewSubmit = false;
		return false;
	}
    /* files need upload first */
	if(me.form.file_attachment1){
		var upload = me.form.file_attachment1.value;
		if(upload != ""){
			//alert('Datei bitte erst hochladen für Vorschau');
			global_execute_previewSubmit = false;
			return true;
		}
	}
	/* remove old preview */
	try {
        Effect.Fade($('pmtext'), { afterFinish: function(obj, element) { obj.element.remove(); } });
    } catch(e) {
    }
	/* get caption */
	var caption = me.form.caption.value;
	var receivers = me.form.receivers.value;

	/* formatcode option */
	var enable_smileys = me.form.enable_smileys.checked;
	var enable_formatcode = me.form.enable_formatcode.checked;
	
	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'pm',
		method:      'ajaxPmPreview',
        view:        'ajax',
		entryText:   text, 
		caption:     caption,
		receivers:   receivers,
		enable_smileys: enable_smileys,
		enable_formatcode: enable_formatcode
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//document.write(request.responseText);
			
			var entry = $('entry');
			
			var fieldset = document.createElement('fieldset');
			fieldset.innerHTML = request.responseText;
			fieldset.style.display = 'none';
			fieldset.id = 'pmtext';
			
			entry.parentNode.insertBefore(fieldset, entry);
			
			Effect.Appear(fieldset);
			global_execute_previewSubmit = false;
			//alert('sucsses');
		},
		onFailure: function(request) {
			alert('fehler');
			global_execute_previewSubmit = false;
		}
	});
	
	return false;
}

function NachOben () {
  var y = 0;
  if (window.pageYOffset) {
    y = window.pageYOffset;
  } else if (document.body && document.body.scrollTop) {
    y = document.body.scrollTop;
  }
  if (y > 0) {
    window.scrollBy(0, -10);
    setTimeout("NachOben()", 10);
  }
}

function previewSubmitNews(me){
	//alert('test');
	
	// Funktion should not execute parallel
	if(global_execute_previewSubmit){
		return false;
	}else{
		global_execute_previewSubmit = true;
	}
	
	/* get text and test */
	var text = me.form.entryText.value;
	if(text == ""){
		alert(Language.empty_text);
		global_execute_previewSubmit = false;
		return false;
	}
    /* files need upload first */
	if(me.form.file_attachment1){
		var upload = me.form.file_attachment1.value;
		if(upload != ""){
			//alert('Datei bitte erst hochladen für Vorschau');
			global_execute_previewSubmit = false;
			return true;
		}
	}
	/* remove old preview */
	try {
        Effect.Fade($('newsPreview'), { afterFinish: function(obj, element) { obj.element.remove(); } });
    } catch(e) {
    }
	/* get caption */
	var caption = me.form.caption.value;
	if(caption == ""){
		alert(Language.empty_caption);
		global_execute_previewSubmit = false;
		return false;
	}

	var openerText = me.form.openerText.value;
	if(openerText == ""){
		alert(Language.empty_opner);
		global_execute_previewSubmit = false;
		return false;
	}
	//alert(me.form.groupId);
	/* search group */
	if(me.form.groupId){
		var groupId = me.form.groupId.value;
	}else{
		var groupId = false;
	}
	
	if(me.form.newsId){
		var newsId = me.form.newsId.value;
	}else{
		var newsId = false;
	}
	
	/* search date */
	try{
		var startDay = me.form.startDay.value;
		var startMonth = me.form.startMonth.value;
		var startYear = me.form.startYear.value;
		var endDay = me.form.endDay.value;
		var endMonth = me.form.endMonth.value;
		var endYear = me.form.endYear.value;
	}catch(e) {
		alert(Language.no_date);
		return false;
    }
	
	/* formatcode option */
	var enable_smileys = me.form.enable_smileys.checked;
	var enable_formatcode = me.form.enable_formatcode.checked;
	
	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'index',
		method:      'ajaxNewsPreview',
        view:        'ajax',
		entryText:   text, 
		caption:     caption,
		openerText:  openerText,
		groupId:     groupId,
		newsId:      newsId,
		startDay:    startDay,
		startMonth:  startMonth,
		startYear:   startYear,
		endDay:      endDay,
		endMonth:    endMonth,
		endYear:     endYear,
		enable_smileys: enable_smileys,
		enable_formatcode: enable_formatcode
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//document.write(request.responseText);
			
			var entry = $('insertNews');
			
			var div = document.createElement('div');
			div.innerHTML = request.responseText;
			div.style.display = 'none';
			div.id = 'newsPreview';
			
			entry.parentNode.insertBefore(div, entry);
			
			Effect.Appear(div);
			global_execute_previewSubmit = false;
			NachOben();
			//alert('sucsses');
		},
		onFailure: function(request) {
			alert('fehler');
			global_execute_previewSubmit = false;
		}
	});
	
	
	return false;
}

function previewSubmitDiary(me){
	
	// Funktion should not execute parallel
	if(global_execute_previewSubmit){
		return false;
	}else{
		global_execute_previewSubmit = true;
	}
	
	/* get text and test */
	var text = me.form.entry_text.value;
	if(text == ""){
		alert(Language.empty_text);
		global_execute_previewSubmit = false;
		return false;
	}

    /* files need upload first */
	if(me.form.file_attachment1){
		var upload = me.form.file_attachment1.value;
		if(upload != ""){
			//alert('Datei bitte erst hochladen für Vorschau');
			global_execute_previewSubmit = false;
			return true;
		}
	}
	/* remove old preview */
	try {
        Effect.Fade($('userinfo_preview'), { afterFinish: function(obj, element) { obj.element.remove(); } });
    } catch(e) {
    }

	/* formatcode option */
	var enable_smileys = me.form.enable_smileys.checked;
	var enable_formatcode = me.form.enable_formatcode.checked;
	
	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'userinfo',
		method:      'ajaxDiaryPreview',
        view:        'ajax',
		entry_text:   text, 
		enable_smileys: enable_smileys,
		enable_formatcode: enable_formatcode
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//document.write(request.responseText);
			
			var entry = $('makeEntry');
			
			var div = document.createElement('div');
			div.innerHTML = request.responseText;
			div.style.display = 'none';
			div.id = 'userinfo_preview';
			
			entry.parentNode.insertBefore(div, entry);
			
			Effect.Appear(div);
			global_execute_previewSubmit = false;
			//alert('sucsses');
		},
		onFailure: function(request) {
			alert('fehler');
			global_execute_previewSubmit = false;
		}
	});
	
	return false;
}

function previewSubmitGB(me){
	
	// Funktion should not execute parallel
	if(global_execute_previewSubmit){
		return false;
	}else{
		global_execute_previewSubmit = true;
	}
	
	/* get text and test */
	var text = me.form.entry_text.value;
	if(text == ""){
		alert(Language.empty_text);
		global_execute_previewSubmit = false;
		return false;
	}

    /* files need upload first */
	if(me.form.file_attachment1){
		var upload = me.form.file_attachment1.value;
		if(upload != ""){
			//alert('Datei bitte erst hochladen für Vorschau');
			global_execute_previewSubmit = false;
			return true;
		}
	}
	/* remove old preview */
	try {
        Effect.Fade($('userinfo_preview'), { afterFinish: function(obj, element) { obj.element.remove(); } });
    } catch(e) {
    }

	/* formatcode option */
	var enable_smileys = me.form.enable_smileys.checked;
	var enable_formatcode = me.form.enable_formatcode.checked;
	var bewertung = 0;
	for (var i = 0; i < me.form.bewertung.length ; i++ ){
        if (me.form.bewertung[i].checked) {
            bewertung = me.form.bewertung[i].value;
	 	}
    }
	
	
	/* get username from page headline */
	var username = $('pagename').innerHTML;
	
	if(me.form.pluspunkt){
		var pluspunkt = me.form.pluspunkt.value;
	}else{
		var pluspunkt = 0;
	}
	
	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'userinfo',
		method:      'ajaxGuestbookPreview',
        view:        'ajax',
		entry_text:   text, 
		username:     username,
		bewertung:    bewertung,
		pluspunkt:    pluspunkt,
		enable_smileys: enable_smileys,
		enable_formatcode: enable_formatcode
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//document.write(request.responseText);
			
			var entry = $('makeEntry');
			
			var div = document.createElement('div');
			div.innerHTML = request.responseText;
			div.style.display = 'none';
			div.id = 'userinfo_preview';
			
			entry.parentNode.insertBefore(div, entry);
			
			Effect.Appear(div);
			global_execute_previewSubmit = false;
			//alert('sucsses');
		},
		onFailure: function(request) {
			alert('fehler');
			global_execute_previewSubmit = false;
		}
	});
	
	return false;
}

function showRating(me){
	//alert(me.href);
	/* the form is alrady shown */
	if($('course-rating-all') != null){
		return true;
	}
	
	if($('couseFileIsDownloaded') != null){
		return true;
	}
	
	var fileId = me.parentNode.title;
	
	var iframe = document.createElement('iframe');
	iframe.name= "courseDownloadiFrame";
	iframe.id= "courseDownloadiFrame";
	iframe.style.display = "none";
	
	me.parentNode.insertBefore(iframe, me);
	me.target = "courseDownloadiFrame";
	
	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'courses',
		method:      'ajaxShowRating',
        view:        'ajax',
		id:   		 fileId
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//document.write(request.responseText);
			
			var objekt = $('fileComments');
			
			var div = document.createElement('div');
			div.innerHTML = request.responseText;
			div.id = 'newCourseRating';
			div.style.display = "none";
			
			objekt.parentNode.insertBefore(div, objekt);
			Effect.Appear(div);			

			//alert('sucsses');
		},
		onFailure: function(request) {
			alert('fehler');
		}
	});
	
	return true;
}

/*
 * clean the output on wait during upload
 */
function removeWait(){
	try{
		$('wait').style.display = 'none';
		$('wait').remove();
		$('overlay').style.display = 'none';
		$('overlay').remove();

	}catch(e) {
	}
}

autounloadFunctions.push(removeWait);


function addWait2(){

	var add = $('endPage');

    var wait = document.createElement('div');
	wait.style.position = 'fixed';
	wait.style.top = '45%';
	wait.style.left = '40%';
	wait.style.height = '40px';
	wait.style.width = '120px';
	wait.style.padding = '10px';
	wait.style.display = 'block';
	wait.style.background = '#fff';
	wait.style.border = '1px solid black';
	wait.style.textAlign = 'center';
	wait.style.color = '#000';
	wait.style.fontSize = '2em';
	//wait.innerHTML = '<img src="/images/upload.gif" alt="upload" />';
	wait.id = 'wait';
	wait.style.zIndex = '3001';
	
	imgPreloader = new Image();
	imgPreloader.src = '/images/upload.gif';
	
	var img = document.createElement('img');
	img.alt = 'upload';
	
	add.parentNode.insertBefore(wait, add);
	wait.appendChild(img);
	img.src = imgPreloader.src;
	img.complete;
	
	new Effect.Appear(wait, { duration: 1, from: 0.0, to: 1.0 , afterFinish: function() { imgPreloader = new Image();
	imgPreloader.src = '/images/upload.gif'; }});
	
	return false;
}

function correctEndDate(type){
	var startDay = parseInt($(type+'StartDateDay').value,10);
	var startMonth = parseInt($(type+'StartDateMonth').value,10);
	var startYear = parseInt($(type+'StartDateYear').value,10);
	
	var endDay = parseInt($(type+'EndDateDay').value,10);
	var endMonth = parseInt($(type+'EndDateMonth').value,10);
	var endYear = parseInt($(type+'EndDateYear').value,10);

	if(endYear < startYear){
		$(type+'EndDateYear').value = startYear;
		endYear = startYear
	}
	if(endYear == startYear && endMonth < startMonth){
		if(startMonth < 10){
			$(type+'EndDateMonth').value = '0'+startMonth;
		}else{
			$(type+'EndDateMonth').value = startMonth;
		}
		endMonth = startMonth;
	}
	if(endYear == startYear && endMonth == startMonth && endDay < startDay){
		if(startDay < 10){
			$(type+'EndDateDay').value = '0'+startDay;
		}else{
			$(type+'EndDateDay').value = startDay;
		}
		endMonth = startDay;
	}
	return false;
}

function soccerGameTypeChange() {
    var gameType = $('selectGameType').value;
    $$('.gameType').each(function(el) { el.hide();});
    $('gameType' + gameType).show();
}

//-----------------------------------------------------------

// execute autoload functions
var oldOnload = window.onload;
window.onload = function() {
  for(i=0; i<autoloadFunctions.length; i++) {
    autoloadFunctions[i]();
  }
  if (oldOnload) {
    oldOnload();
  }
}
var oldOnUnload = window.onunload;
window.onunload = function(){
	for(i=0; i<autounloadFunctions.length; i++) {
    	autounloadFunctions[i]();
    }
	if (oldOnUnload) {
    oldOnUnload();
    }
}

var lastNamePosition = 0;
var lastNameLength = 0;

var myrules = {
    // BOX: user search 
    '#username_search' : function(el) {
        var str = 'User';
        el.onblur = function() { elementBlur(this, str); };
        el.onfocus = function() { elementFocus(this, str); };	
    },
    'input.suggestUser' : function(el) {
      
      var url='/index.php?view=ajax&mod=usermanagement&method=ajaxSearchUser';

      var	s=new Ajax.Autocompleter(el.id, el.id + '_choices', url, {
        paramName: 'username',
        minChars: 3,        
        callback: function(inp, defau){ if(!$('box_user_search_search_user').checked){return '';} return defau; },
        afterUpdateElement: function(inp, sel) { if($('usersearch_form')) { $('usersearch_form').submit(); } }
      });
    },
    // BOX: user login
    'input.loginname' : function(el) {
        var str = 'Dein Username';
        if (el.value == '') { el.value = str; }
        el.onblur = function() { elementBlur(this, str); };
        el.onfocus = function() { elementFocus(this, str); };
    },
    // for username on user registration page
    'input #username_register' : function(el) {
        el.onblur = function(){
        	checkUsername();
        }
    },
    // for email on user registration page
    'input #uniEmail' : function(el) {
        var str = Language.email_pattern;
        el.onblur = function() { elementBlur(this, str); };
        el.onfocus = function() { elementFocus(this, str); };
    },
    // for private email on user registration page
    'input #privateEmail' : function(el) {
        var str = Language.email_example;
        if (!el.value) {
            el.value = str;
        }
        el.onblur = function() { elementBlur(this, str); };
        el.onfocus = function() { elementFocus(this, str); };
        el.parentNode.parentNode.onsubmit = function(){
        		var email = $('privateEmail');
        		var str = Language.email_example;
        		if (email.value == str){
        			email.value = '';
        		}
        	}
    },
    '#ajax_submit' : function(el) {
        el.onsubmit = function() { return sendSubmit(); }
     },
     
    '#guestbookFilterToggle' : function(el) {
        el.onclick = function() {
            showGuestbookFilter(el.firstChild.src.search(/add/) != -1);
            return false;
        }
    },
    '#diaryFilterToggle' : function(el) {
        el.onclick = function() {
            showDiaryFilter(el.firstChild.src.search(/add/) != -1);
            return false;
        }
    },

    // box action - minimization
    'a.iconMinimize' : function(el) {
        el.onclick = function() { if (minimizeBox(el.parentNode)) { return false; } return true; };
    },
    // box action - minimization
    'a.iconMaximize' : function(el) {
        el.onclick = function() { if (maximizeBox(el.parentNode)) { return false; } return true; };
    },
    // box action - close
    'a.iconClose' : function(el) {
        el.onclick = function() { if (closeBox(el.parentNode)) { return false; } return true; };
    }, 
    
	'.webcam' : function(el) {
		var rxRand = new RegExp("r=[\\d\\.]+");
		window.setTimeout(function() {
			var img = el.down('img');
			img.src = img.src.replace(rxRand, "r=" + Math.random());
		}, 5 * 60 * 1000);
	},
    
    '#selectGameType' : function(el) {
        soccerGameTypeChange();
    },
    
     '.box-coursefiles form' : function(el) {
        el.onsubmit = sendCourseBox;
	 }, 
	 
	 '.box-coursefiles select' : function(el) {
        el.onchange = function(){ el.parentNode.onsubmit(); };
	 }, 
     '#user-online-sort-links a' : function(el) {
     	el.onclick = sortUserOnline;
     },
     '#navigation ul li' : function(el){
     	el.onmouseover = function(){
     		return;
     		/*var list = this.getElementsByTagName('ul');
     		list = list[0];
     		if(list == null) return;
     		//alert(list.id);
     		var elem = list.getElementsByTagName('li');
     		for(var i = 0; i < elem.length; i++){
     			elem[i].style.display = 'none';
     			Effect.Appear(elem[i], {duration:0.3, afterFinish: function(obj, element) { }  });
     		}*/
     		//Effect.SlideDown(list);
     	}
     },
     'a.forumQuote' : function(el){
     	el.onclick = forumQoute
     },
     '#canvassname' : function (el) {
        el.onkeyup = function() {
            if (lastNamePosition == 0) {
                lastNamePosition = $('canvassdefault').value.indexOf("'Name'");
                $('canvassdefault').value = $('canvassdefault').value.replace(/'Name'/, el.value);
            } else {
                $('canvassdefault').value = $('canvassdefault').value.substring(0, lastNamePosition) 
                        + el.value + $('canvassdefault').value.substring(lastNamePosition + lastNameLength);
            }
            lastNameLength = el.value.length;
        }
     },
     '#add_studypath_form' : function (el){
     	el.onclick = addStudyPath
     },
     '#uniId' : function (el){
     	updateMaildomains();
     	el.onchange = updateMaildomains;
     },
     '#searchcourses' : function (el){
     	el.onkeyup = searchCourses;
     },
     '#courseManageAdd' : function(el){
     	el.onclick = function() {
     		return courseManage('Add');}
     },
     '#courseManageDel' : function(el){
     	el.onclick = function() {
     		return courseManage('Del');}
     },
     '#courseManagePage1' : function(el){
     	el.onclick = function() {
     		return courseManage('Page1');}
     },
     '#courseManagePage2' : function(el){
     	el.onclick = function() {
     		return courseManage('Page2');}
     },
     'courseManageSearch' : function(el){
     	/*el.onclick = function() {
     		return courseManage('Search');}*/
     },
     // input to create preview threadEntry
     '#forum_preview_submit' : function(el){
     	el.onclick = function(){
     		return previewSubmitForum(this);
     	}
     },
     '#pm_preview_submit' : function(el){
     	el.onclick = function(){
     		return previewSubmitPM(this);
     	}
     },
     // display wait Screen on upload
     '#upload_submit' : function(el){
     	el.onclick = function(){
     		addWait2();
     	}
     },
     /*
      * needed to deactivate this because
      * user is not informed about erroneous download */
      '.courseFileDownloadJS' : function(el){
     	el.onclick = function(){
     		return showRating(this);
     	}
     },
     '#news_preview_submit' : function(el){
     	el.onclick = function(){
     		return previewSubmitNews(this);
     	}
     },
     '#diary_preview_submit' : function(el){
     	el.onclick = function(){
     		return previewSubmitDiary(this);
     	}
     },
     '#gb_preview_submit' : function(el){
     	el.onclick = function(){
     		return previewSubmitGB(this);
     	}
     },
     '#eventStartDateDay' : function(el){
     	el.onchange = function(){
     		return correctEndDate('event');
     	};
     	$('eventStartDateMonth').onchange = function(){
     		return correctEndDate('event');
     	};
     	$('eventStartDateYear').onchange = function(){
     		return correctEndDate('event');
     	};
     }
};

Behaviour.register(myrules);
