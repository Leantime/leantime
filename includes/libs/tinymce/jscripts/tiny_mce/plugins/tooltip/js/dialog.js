tinyMCEPopup.requireLangPack();

var ExampleDialog = {
	init : function() {
		var f = document.forms[0];
		var inst = tinyMCEPopup.editor;
		var elm = inst.selection.getNode();
		title = inst.dom.getAttrib(elm, 'title');
		
		// Get the selected contents as text and place it in the input
		
		if(title==''){
			f.title.value = tinyMCEPopup.editor.selection.getContent({format : 'text'});
		}else{
			f.title.value = title;
		}
	},

	insert : function() {
		// Insert the contents from the input into the document
		
		if(title==''){
			tinyMCEPopup.editor.selection.setContent('<span class="tooltip" title="'+ document.forms[0].title.value+'" style="color:#0065B5;">' + tinyMCEPopup.editor.selection.getContent() + '</span>');
		}else{
			var inst = tinyMCEPopup.editor;
			var elm = inst.selection.getNode();
			elm.setAttribute('title', document.forms[0].title.value);
		}
		tinyMCEPopup.close();
		
		
	},
	
	remove: function(){
		
		if(title!=''){
			var inst = tinyMCEPopup.editor;
			var elm = inst.selection.getNode();
			elm.setAttribute('title', '');
			elm.setAttribute('class', '');
			elm.setAttribute('style', '');
			
		}
		tinyMCEPopup.close();
		
	}
};



tinyMCEPopup.onInit.add(ExampleDialog.init, ExampleDialog);
