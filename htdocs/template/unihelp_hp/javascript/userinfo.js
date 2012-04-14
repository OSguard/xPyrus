  function loadTab(tabName) {
	var _method= '';
  	if (tabName == 'user_stats') {
  		_method = 'ajaxUserStats';
  	} else if (tabName == 'smallworld') {
  		_method = 'ajaxSmallWorld';
  	} else if (tabName == 'description') {
  		_method = 'ajaxUserDescription';
  	} else if (tabName == 'guestbook_stats') {
  		_method = 'ajaxGuestbookStats';
  	} else if (tabName == 'user_contact') {
        _method = 'ajaxUserContact';
    } else if (tabName == 'user_awards') {
    	_method = 'ajaxUserAward';
    }
  	
  	if (!_method) {
  		return false;
  	}
  	
  	var url = '/index.php'
	var pars = {
		method:      _method,
		mod:         'userinfo',
        view:        'ajax',
		username:	 $('pagename').innerHTML
	}
	
	var myAjaxRequest = new Ajax.Request(url, {
		parameters: $H(pars).toQueryString(),
		onSuccess : function(request) {
			$('tabcontent').innerHTML = request.responseText;
			var tabs = ['tablink_description', 'tablink_user_contact', 'tablink_user_stats', 'tablink_smallworld', 'tablink_guestbook_stats', 'tablink_user_awards'];
			for (var i=0; i < tabs.length; i++) { 
                if ($(tabs[i]) === null) {
					continue;
				}
				$(tabs[i]).setAttribute('class', '');
			}
			$('tablink_' + tabName).setAttribute('class', 'active');
		},
		onFailure: function(request) {
			$('tabcontent').innerHTML = 'Fehler beim Laden des Inhalts.';
		}
	});
	
	// dont follow href of link
	return false;
  }