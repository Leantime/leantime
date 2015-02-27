/*
 * 	Additional function for forms.html
 *	Written by ThemePixels	
 *	http://themepixels.com/
 *
 *	Copyright (c) 2012 ThemePixels (http://themepixels.com)
 *	
 *	Built for Katniss Premium Responsive Admin Template
 *  http://themeforest.net/category/site-templates/admin-templates
 */

jQuery(document).ready(function(){
	
	// Transform upload file
	jQuery('.uniform-file').uniform();
	
	// Date Picker
	jQuery("#datepicker").datepicker();
	
	
	
	

	// Spinner
	jQuery("#spinner").spinner({min: 0, max: 100, increment: 2});
	
	// Character Counter
	jQuery("#textarea2").charCount({
		allowed: 120,		
		warning: 20,
		counterText: 'Characters left: '	
	});
	
	// Select with Search
	jQuery(".chzn-select").chosen();
	
	// Textarea Autogrow
	jQuery('#autoResizeTA').autogrow();	
	
	
	// With Form Validation
	jQuery("#form1").validate({
		rules: {
			firstname: "required",
			lastname: "required",
			email: {
				required: true,
				email: true,
			},
			location: "required",
			selection: "required"
		},
		messages: {
			firstname: "Please enter your first name",
			lastname: "Please enter your last name",
			email: "Please enter a valid email address",
			location: "Please enter your location"
		},
		highlight: function(label) {
			jQuery(label).closest('.control-group').addClass('error');
	    },
	    success: function(label) {
	    	label
	    		.text('Ok!').addClass('valid')
	    		.closest('.control-group').addClass('success');
	    }
	});
	
	jQuery('#timepicker1').timepicker();
	
});