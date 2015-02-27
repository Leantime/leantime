
$(document).ready(function() 
    	{ 
	
			$.tablesorter.addParser({
				id: 'germandate',
				
			    is: function(s) {
					return false;
				},
				
				format: function(s) {
					if(s == ''){
						s = '01.01.0001';
					}
					var a = s.split('.');
					
					a[1] = a[1].replace(/^[0]+/g,"");
					return new Date(a.reverse().join("/")).getTime();
				},
				type: 'numeric'
			});
	
    });
