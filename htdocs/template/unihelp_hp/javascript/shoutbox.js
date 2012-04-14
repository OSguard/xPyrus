function UnihelpShoutbox() {
    var thisObject = this;
	var shCharCount = $('shbox_char_count');
	var shBoxText = $('shbox_text');
	var emptyShoutMessage = $('empty_shout_message');
	var shoutboxLastUpdateId = 0;
    var btnSubmit = $('shbox_submit');
    var lblStatus = $('shbox_status');
    var shoutboxDiv = $('shoutbox');
    
    this.inRequest = false;

	var buildShoutItem = function(item) {
		/* 
		 * the DOM node with the empty shout message wasn't found. This should 
		 * NEVER happen!
		 */
		if(!emptyShoutMessage)
			return null;
	
		var msg = emptyShoutMessage.cloneNode(true);		
		msg.id='shout_item_' + item.entryTime;
		
		var etime = new Date();
		etime.setTime(item.entryTime * 1000);
	
		var userLink = msg.getElementsByTagName('a')[0];	
		userLink.setAttribute('href', '/user/' + encodeURI(item.username));
		var img = msg.getElementsByTagName('img')[0];
		
		img.setAttribute('alt', encodeURI(item.username));
		
		if(item.tinyuserpic != '')
			img.setAttribute('src', item.tinyuserpic); 	
	
		var msgText = msg.getElementsByTagName('span')[0];
		
		var userLink = '<a href="' + userLink.getAttribute('href') + '">' + item.username + '</a>'; 
		
		if(item.isMeMessage)
			msgText.innerHTML = '<span style="font-size: x-small;">(' + etime.toLocaleTimeString() + ') </span><strong>' + userLink + '</strong> ';
		else
			msgText.innerHTML = '<strong>' + userLink + '</strong>, <span style="font-size: x-small;">' + etime.toLocaleTimeString() +'</span>: ';
				
		msgText.innerHTML = msgText.innerHTML + item.text;
		msg.style.display = 'none';
		
		return msg;
	};

    
	this.updateCharCount = function() {
		 shCharCount.innerHTML = (160 - shBoxText.value.length);
	};
	
	this.getLatestEntries = function()
	{	
	    // skip this function, if box is minimized or not available
	    if (shoutboxLastUpdateId == -1 || !shoutboxDiv || 
           $('shoutbox:1_collapse') != null && $('shoutbox:1_collapse').className.search(/Maximize/) != -1) {
	      return;
	    }
        
        if(thisObject.inRequest == true)
            return;
            
        thisObject.inRequest = true;
		
		new Ajax.Request('/shoutboxupdate', 
            { 
                method: "get",
    			parameters: $H({lastUpdate: shoutboxLastUpdateId}).toQueryString(),
    			onFailure: function(request) {
    				shoutboxLastUpdateId = -1;
                    thisObject.inRequest = false;
    				return;
	    	    },
    			onComplete: function (request) {
    				var items = eval(request.responseText);
    				
    				if(items[0] > 0) {
    					shoutboxLastUpdateId = items[0];
    				}
    	
    				for(var i=0; i < items[1].length; i++) {
    					var newItem = buildShoutItem(items[1][i]);
    					
    					/* we didn't find the empty shout message DOM node */
    					if(newItem == null)
    						continue;
    					
    					shoutboxDiv.insertBefore(newItem, $('shoutbox').firstChild);
    					var divs = shoutboxDiv.getElementsByTagName('div');
    					Effect.Appear(newItem.id, { duration: (1.5 + i*0.5) });
    					Effect.Fade(divs[divs.length - (divs.length - 11)], 
    								{ duration: 0.5, afterFinish: function(obj, effect) {
    						obj.element.remove();
    					} });
    				}
                    
                    thisObject.inRequest = false; 
    			}
		    }
        );
	};
	    
	this.sendShout = function () {
		/* element not found use the native behavior */
		if(!shBoxText)
			return true;	
		
		btnSubmit.disabled = true;
		shBoxText.disabled = true;
		lblStatus.innerHTML = "Sende...";
		
		var pars = {
			dest:        'box',
			method:      'addToShoutbox',
			bname:       'shoutbox',
	        view:        'ajax',
			shout_text:  shBoxText.value 
		}
		
		var myAjaxRequest = new Ajax.Request('/index.php', 
            {
    			parameters: $H(pars).toQueryString(),
    			onSuccess : function(request) {
    				lblStatus.innerHTML = "Erfolgreich!";
    				btnSubmit.disabled = false;
    				shBoxText.value = '';
    				thisObject.updateCharCount();
    				shBoxText.disabled = false;
                    shBoxText.focus();
                    //thisObject.getLatestEntries();
    			},
    			onFailure: function(request) {
    				lblStatus.innerHTML = "Fehler!";
    				btnSubmit.disabled = false;
    				shBoxText.disabled = false;
    			}
    		}
        );
			
		/* don't use default behaviour */
		return false;
	};
    
    return this;
}

var shoutbox = UnihelpShoutbox();

var shoutboxRules = {
    // install shoutbox updater only if shoutbox is available
    '#shoutbox' : function(el) {
        /*install only the executor, when shoutbox is available */
       shoutbox.getLatestEntries();
        new PeriodicalExecuter(shoutbox.getLatestEntries, 30);
    },
    '#shbox_text' : function(el) {
		el.setAttribute("autocomplete","off");
        el.onkeyup = shoutbox.updateCharCount;
	 },
	 
     '#shbox_submit' : function(el) {
        el.onsubmit = shoutbox.sendShout;
	 }
}

Behaviour.register(shoutboxRules);
Behaviour.apply();