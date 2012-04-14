/* blogAdvancePreview */

global_execute_previewSubmit = false;

function previewSubmitBlog(me){
	
	// Funktion should not execute parallel
	if(global_execute_previewSubmit){
		return false;
	}else{
		global_execute_previewSubmit = true;
	}
	
	/* get text and test */
	var text = me.form.entry_text.value;
	if(text == ""){
		alert('Text ist Leer - keine Vorschau');
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
        Effect.Fade($('blogPreview'), { afterFinish: function(obj, element) { obj.element.remove(); } });
    } catch(e) {
    }
	/* get caption */
	var caption = me.form.entry_title.value;
	if(caption == ""){
		alert('Überschrieft ist Leer - keine Vorschau');
		global_execute_previewSubmit = false;
		return false;
	}

	var entry_trackbacks = me.form.entry_trackbacks.value;

	if(me.form.entry_id){
		var entry_id = me.form.entry_id.value;
	}else{
		var entry_id = false;
	}
	
	
	/* formatcode option */
	var enable_smileys = me.form.enable_smileys.checked;
	var enable_formatcode = me.form.enable_formatcode.checked;
	
	var url = '/index.php';
	var pars = {
		dest:        'modul',
		mod:         'blogadvanced',
		method:      'ajaxPreviewEntry',
        view:        'ajax',
		entry_text:   text, 
		entry_title: caption,
		entry_trackbacks:  entry_trackbacks,
		entry_id:    entry_id,		
		enable_smileys: enable_smileys,
		enable_formatcode: enable_formatcode
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			//document.write(request.responseText);
			
			var entry = $('insertPreview');
			
			var div = document.createElement('div');
			div.innerHTML = request.responseText;
			div.style.display = 'none';
			div.id = 'blogPreview';
			div.className = 'blogbox entry preview';
			
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

// ------------------------------------------------------------------------
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

var myrules = {
    
     '#blogAdvancePreview' : function(el){
     	el.onclick = function(){
     		return previewSubmitBlog(this);
     	}
     }
};

Behaviour.register(myrules);