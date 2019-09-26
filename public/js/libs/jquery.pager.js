$.fn.pager = function(clas, options) {
	
	var settings = {		
		navId: 'nav',
		navClass: 'nav',
		navAttach: 'append',
		highlightClass: 'highlight',
		prevText: '&laquo;',
		nextText: '&raquo;',
		linkText: null,
		linkWrap: null,
		height: null
	}
	if(options) $.extend(settings, options);
	
		
	return this.each( function () {
		
		var me = $(this);
		var size;
	  	var i = 0;		
		var navid = '#'+settings.navId;
		
		function init () {
			size = $(clas, me).not(navid).size();
			if(settings.height == null) {			
				settings.height = getHighest();
			}
			if(size > 1) {
				makeNav();
				show();
				highlight();
			}			
			sizePanel();
			if(settings.linkWrap != null) {
				linkWrap();
			}
		}
		function makeNav () {		
			var str = '<div id="'+settings.navId+'" class="'+settings.navClass+'">';
			str += '<a href="#" rel="prev">'+settings.prevText+'</a>';
			for(var i = 0; i < size; i++) {
				var j = i+1;
				str += '<a href="#" rel="'+j+'">';
				str += (settings.linkText == null) ? j : settings.linkText[j-1];				
				str += '</a>';
			}
			str += '<a href="#" rel="next">'+settings.nextText+'</a>';
			str += '</div>';
			switch (settings.navAttach) {		
				case 'before':
					$(me).before(str);
					break;
				case 'after':		
					$(me).after(str);
					break;
				case 'prepend':
					$(me).prepend(str);
					break;
				default:
					$(me).append(str);
					break;
			}
		}
		function show () {
			$(me).find(clas).not(navid).hide();
			var show = $(me).find(clas).not(navid).get(i);
			$(show).show();
		}		
		function highlight () {
			$(me).find(navid).find('a').removeClass(settings.highlightClass);
			var show = $(me).find(navid).find('a').get(i+1);			
			$(show).addClass(settings.highlightClass);
		}

		function sizePanel () {
			if($.browser.msie) {
				$(me).find(clas).not(navid).css( {
					height: settings.height
				});	
			} else {
				$(me).find(clas).not(navid).css( {
					minHeight: settings.height
				});
			}
		}
		function getHighest () {
			var highest = 0;
			$(me).find(clas).not(navid).each(function () {
				
				if(this.offsetHeight > highest) {
					highest = this.offsetHeight;
				}
			});
			highest = highest + "px";
			return highest;
		}
		function getNavHeight () {
			var nav = $(navid).get(0);
			return nav.offsetHeight;
		}
		function linkWrap () {
			$(me).find(navid).find("a").wrap(settings.linkWrap);
		}
		init();
		$(this).find(navid).find("a").click(function () {

			if($(this).attr('rel') == 'next') {
				if(i + 1 < size) {
					i = i+1;
				}
			} else if($(this).attr('rel') == 'prev') { 
				if(i > 0) {	
					i = i-1;
				}
			} else {		
				var j = $(this).attr('rel');	
				i = j-1;		
			}
			show();
			highlight();
			return false;
		});
	});	
}