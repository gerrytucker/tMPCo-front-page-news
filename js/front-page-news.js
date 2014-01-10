(function(window,undefined) {

	var
		$ = window.jQuery, document = window.document;

	/* Copyright (c) 2006-2007 Mathias Bank (http://www.mathias-bank.de)
	 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
	 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
	 * 
	 * Version 2.1
	 * 
	 * Thanks to 
	 * Hinnerk Ruemenapf - http://hinnerk.ruemenapf.de/ for bug reporting and fixing.
	 * Tom Leonard for some improvements
	 * 
	 */
	jQuery.fn.extend({
	/**
	* Returns get parameters.
	*
	* If the desired param does not exist, null will be returned
	*
	* To get the document params:
	* @example value = $(document).getUrlParam("paramName");
	* 
	* To get the params of a html-attribut (uses src attribute)
	* @example value = $('#imgLink').getUrlParam("paramName");
	*/ 
	 getUrlParam: function(strParamName){
			strParamName = escape(unescape(strParamName));
			
			var returnVal = new Array();
			var qString = null;
			
			if ($(this).attr("nodeName")=="#document") {
				//document-handler
			
			if (window.location.search.search(strParamName) > -1 ){
				
				qString = window.location.search.substr(1,window.location.search.length).split("&");
			}
				
			} else if ($(this).attr("src")!="undefined") {
				
				var strHref = $(this).attr("src")
				if ( strHref.indexOf("?") > -1 ){
					var strQueryString = strHref.substr(strHref.indexOf("?")+1);
					qString = strQueryString.split("&");
				}
			} else if ($(this).attr("href")!="undefined") {
				
				var strHref = $(this).attr("href")
				if ( strHref.indexOf("?") > -1 ){
					var strQueryString = strHref.substr(strHref.indexOf("?")+1);
					qString = strQueryString.split("&");
				}
			} else {
				return null;
			}
				
			
			if (qString==null) return null;
			
			
			for (var i=0;i<qString.length; i++){
				if (escape(unescape(qString[i].split("=")[0])) == strParamName){
					returnVal.push(qString[i].split("=")[1]);
				}
				
			}
			
			
			if (returnVal.length==0) return null;
			else if (returnVal.length==1) return returnVal[0];
			else return returnVal;
		}
	});
			
	var ajax_url = fpn_data['admin_url'];

	var btn = $('input#save');
	
	$(function() {

		var
			$href = $(location).attr('href'),
			$pathname = $(location).attr('pathname'),
			$hash = $(location).attr('hash');
	
		if($(document).getUrlParam('message') == 'saved') {
			$('.wrap h2').after('<div id="message" class="updated below-h2"><p>Selected news order updated successfully.</p></div>');
		}
	

		$('.droptrue').sortable({
			connectWith: '.connectedSortable',
			placeholder: 'ui-state-highlight',
			receive: function(e, ui) {
				var list = $(this);
				if (list.attr('id') != "available") {
					if (list.children().length > 3) {
						$(ui.sender).sortable('cancel');
					}
				}
			}
		}).disableSelection();

		btn.on('click', function(e) {
			e.preventDefault();
			save_changes();
		});

		function save_changes()
		{
			var news_ids = Array(), i=0;
			
			$('#selected li').each(function(i) {
				news_ids[i++] = $(this).data('id');
			});
	
			var data = {
				action: 'fpn_save_news_action',
				news_ids: news_ids
			}
			
			btn.attr({disabled:true})
			$('span.spinner').css({display: 'inline', marginLeft: '0.8rem'});
			
			$.ajax({
				url: ajax_url,
				data: data,
				type: 'POST',
				dataType: 'json',
				success: function(resp) {
					window.location.href = removeURLParam($href, 'message') + '&message=saved';
				}
			});
				
			
			return;
		}

		function removeURLParam(url, param)
		{
		 var urlparts= url.split('?');
		 if (urlparts.length>=2)
		 {
			var prefix= encodeURIComponent(param)+'=';
			var pars= urlparts[1].split(/[&;]/g);
			for (var i=pars.length; i-- > 0;)
			 if (pars[i].indexOf(prefix, 0)==0)
				pars.splice(i, 1);
			if (pars.length > 0)
			 return urlparts[0]+'?'+pars.join('&');
			else
			 return urlparts[0];
		 }
		 else
			return url;
		}

	});

})(window); // end closure