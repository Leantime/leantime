/*
 * 	Additional function for media.html
 *	Written by ThemePixels	
 *	http://themepixels.com/
 *
 *	Copyright (c) 2012 ThemePixels (http://themepixels.com)
 *	
 *	Built for Katniss Premium Responsive Admin Template
 *  http://themeforest.net/category/site-templates/admin-templates
 */
 
jQuery(document).ready(function(){

	// List of Files: Click to Select 
	jQuery('.listfile li').click(function(e){
		if(!e.ctrlKey && !e.cmdKey){
         	jQuery('.listfile li.selected').removeClass('selected');  
        }
		if(!jQuery(this).hasClass('selected')) {
			jQuery(this).addClass('selected');
		} else {
			jQuery(this).removeClass('selected');
		}
	});
	
	// Trash
	jQuery('.trash').click(function(){
		var count = 0;
		var items = new Array();
		jQuery('.listfile li').each(function(){
			if(jQuery(this).hasClass('selected')) {
				items[count] = jQuery(this);
				count++;
			}
		});
		if(items.length > 0) {
			var msg = (items.length > 1)? 'files' : 'file';
			if(confirm('Delete '+items.length+' '+msg+'?')) {
				for(var a=0;a<count;a++) {
					jQuery(items[a]).fadeOut(function(){
						jQuery(this).remove();
					});	
				}
			}
		} else {
			alert('No file selected');
		}
		return false;
	});
	
	// Colorbox
	jQuery(".listfile a").colorbox();
								 
});