function menuToggle(id) {
	
	var menuDisplay = jQuery('#menu-'+id).css('display');
	console.log('#menu-'+id+'='+menuDisplay);
	if(menuDisplay == 'none') {
		jQuery('#menu-'+id).css('display', 'block');
	}
	else {
		jQuery('#menu-'+id).css('display', 'none');
	}
}
