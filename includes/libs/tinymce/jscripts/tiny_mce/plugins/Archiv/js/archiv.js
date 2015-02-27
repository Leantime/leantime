/**
 * Archiv main object
 * 
 * @id: $Id: archiv.js,v 1.7 2009/10/27 20:15:55 wvankuipers Exp $
 * @version 1.0
 * @author Wouter van Kuipers (Archiv@pwnd.nl)
 * @copyright 2008-2009 PWND
 * @license LGPL 
 * @see http://archiv.pwnd.nl
 * @uses js/jquery/jquery-1.3.2.min.js
 * @uses js/jquery/jquery-ui-1.7.1.custom.min.js
 * @uses js/SWFupload/swfupload.min.js
 * @uses js/SWFupload/handlers.min.js
 * @uses js/flash_detect_min.js
 * @uses js/json2.min.js
 */
var archiv = {
	/**
	 * Pre-initialize the plugin
	 * @return void		
	 */
	preInit : function() {
		/* language pack */
		tinyMCEPopup.requireLangPack();		
	},

	/**
	 * Initialize the vars & gets settings from config.php
	 * @return void
	 */	
	init : function(){
		try{
			/* set error handling */
			window.onerror = function(message, url, line){
					alert(message+' '+url+' '+line);	
			};
		
			/* check flash version */
			if(!FlashDetect.installed || FlashDetect.major < 9){
				$('#main').hide();
				this.alert('Flash', tinyMCEPopup.getLang('Archiv.ErrorOldFlashVersion')+ ', <a href="http://get.adobe.com/flashplayer/" title="'+tinyMCEPopup.getLang('Archiv.PleaseUpdateFlashVersion')+'">'+tinyMCEPopup.getLang('Archiv.PleaseUpdateFlashVersion')+'</a>!<br />');     	
			}
			else{
				/* set AJAX loading images */
				$("#loader").ajaxStart(function(){
				   $(this).show();
				 });
				 
				$("#loader").ajaxStop(function(){
				   $(this).hide();
				 });			
					
				/* Globals */
				this.SettingsFile 		= tinyMCE.activeEditor.getParam('Archiv_settings_file');	
				this.Connector   		= 'php/connector.php';						/* connector file */	
				this.CurrentDirectory  	= null; 									/* the dir we are currently watching (element) */
				this.CurrentPath 		= null;										/* the path of the current dir we are watching */
				this.DirectoryList 		= Array(); 									/* array of directory's we have fetched from the server */
				this.ContentList 		= Array(); 									/* array of files we have fetched from the server */
				this.InfoArray   		= Array();									/* array to store advanced file info in */
				this.BrowserType 		= tinyMCEPopup.getWindowArg('browserType');	/* find out if we are a file or a image browser */
				this.Parameter 			= this.timestamp();							/* Some browsers have a problem with cashing a HTTPX request, so we add a time parameter */
				this.Debug				= "true";									/* Debug settings (enabled by default) */
				this.SecurityAdd		= null;			
							
				/* settings */
				archiv.getSettings();
			}						
		}
		catch(e){
			this.debug(arguments.callee,e);
		}	
	},
	
	/**
	 * Register all vars & starts the processing
	 * @return void
	 */	
	postinit : function(){
		try{
			/* Set debug */
			this.Debug = this._debug;
		
			/* Load the functions */
			this.build_swfupload();	
			this.getTreePopulation();
			this.getContentPopulation(null,"/");
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * Get the settings form config.php
	 * @return void
	 */	
	getSettings : function (){	
		try{
			$.post(
				this.Connector,
				{ 
					get:'settings', 
					settings_file: this.SettingsFile 
				},
				function(data, textStatus)
				{
					if(textStatus == 'success'){
						try{		
					   	 	archiv._path 				= data.Archiv_path;
					   	 	archiv._files 				= data.Archiv_files;
					   	 	archiv._image_files 		= data.Archiv_image_files;
					   	 	archiv._file_size_limit 	= data.Archiv_file_size_limit;
					   	 	archiv._file_upload_limit 	= data.Archiv_file_upload_limit;
							archiv._debug 				= data.Archiv_debug;
						   	 	
					   	 	archiv.postinit();
						 }
						 catch(e2){
								archiv.debug(arguments.callee,e2);
						 }
					}
					else{
						archiv.displayError(tinyMCEPopup.getLang('Archiv.ErrorReadingSettingsFile') + " `" + tinyMCE.activeEditor.getParam('Archiv_settings_file'));
					}
				},
				'json'
			  );			
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * Get the current timestamp
	 * @return void
	 */	
	timestamp : function(){
		var now 	= new Date();
		var hour 	= now.getHours();
		var min 	= now.getMinutes();
		var sec 	= now.getSeconds();	
		
		return hour+''+min+''+sec;
	},
		
	/**
	 * Get the dir list from connector and call populateTreeView()
	 * @return void
	 */	
	getTreePopulation : function (){
		try{			
			 $.post(
			   this.Connector,
			   { get:'dirList', settings_file:this.SettingsFile, time:this.Parameter, browser:this.BrowserType },
			   function(data, textStatus){
				   	if(textStatus == 'success'){
						archiv.populateTreeView(data);				    	
					}
					else{
						archiv.displayError(tinyMCEPopup.getLang('Archiv.ErrorLoadingTree'));
					}				
			   },
			   'json'
			 );
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * Get the dir list from connector and call populateTreeView()
	 * @param domObject|null directoryObj object that calls the function (null if initializing)
	 * @param string directoryPath path of the current directory
	 * @return void
	 */	
	getContentPopulation : function (directoryObj, directoryPath){
		try{			
			if(directoryObj !== null){
				this.setCurrentDirectory(directoryObj, directoryPath);
			}				
			
			this.CurrentPath = directoryPath;	
			
			$.post(
			   this.Connector,
			   { get:'dirContent', settings_file:this.SettingsFile, time:this.Parameter, directoryRoot:this.CurrentPath, browser:this.BrowserType },
			   function(data, textStatus){
			   	if (textStatus == 'success') {
			   		archiv.clearDirectoryContent();			   		
			   		if (archiv.CurrentPath != "/") {
			   			$("#removeDirectory").css("display", "block");
			   		}
			   		else {
			   			$("#removeDirectory").css("display", "none");
			   		}
			   	
			   		if (data.dircontent.length > 0) {
			   			archiv.populateContentView(data);			   			
			   		}
			   		else {
			   			archiv.displayError(tinyMCEPopup.getLang('Archiv.NoFiles'));
			   			
			   		}
			   	}
			   	else{
			   		archiv.displayError(tinyMCEPopup.getLang('Archiv.ErrorLoadingDirectoryContent'));
			   	}
			   },
			   'json'
			 );
		}
		catch(e){
			this.debug(arguments.callee,e);
		}	
	},

	/**
	 * Set the current dir to a given var
	 * @param domObject Obj the element that called the function
	 * @param string directoryPath the path of the directory to set
	 * @return void
	 */
	setCurrentDirectory : function (Obj, directoryPath){
		try{
			if(this.CurrentDirectory !== null){
				$(this.CurrentDirectory).removeClass("selectedDirectory");
			}
			
			this.CurrentDirectory = Obj;
			$(this.CurrentDirectory).addClass("selectedDirectory");
			
			this.setCurrentDirectoryLabel(directoryPath);
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * (Re)Populate the treeview with the given array
	 * @param JSON content json response that contains the items of the current directory
	 * @return void
	 */
	populateTreeView : function (content){	
		try{			
			if(content.dirlist !== false && $(content.dirlist).length > 0){				
				this.DirectoryList = "<ul id=\"direcotryList\">"+
							"<li><span href=\"javascript:void(0);\" onclick=\"archiv.getContentPopulation(this,'/');\">/</span>"+
							"<ul>"+
							this.dataToUL(content.dirlist)+
							"</ul></li>"+
						"</ul>";
			}
			else{
				this.DirectoryList = "<ul id=\"direcotryList\">"+
								"<li><span onclick=\"archiv.getContentPopulation(this,'/');\">/</span></li>"+							
								"</ul>";	
			}						
			
			this.setDirectoryList(this.DirectoryList);
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	
	/**
	 * Transform JSON data into a nice list of dirs
	 * @param JSON node list of nodes
	 * @param string path current path we are working in (optional)
	 * @return void
	 */
	dataToUL : function(node, path) {	
		try{
			path = (path) ? path : "";
			var list = "";
		
			if(typeof(node) != 'undefined'){
				$(node).each(function(i,val){			
					if(typeof(val) == 'string'){					
						list += "<li><span onclick=\"archiv.getContentPopulation(this,'"+path+"/"+val+"');\" id=\""+path+"/"+val+"\">"+val+"</span>";
					}
					else{
						list += "<li><span onclick=\"archiv.getContentPopulation(this,'"+path+"/"+$(val).get(0)+"');\" id=\""+path+"/"+$(val).get(0)+"\">"+$(val).get(0)+"</span>";
						list+="<ul>"+archiv.dataToUL($(val).get(1), path+"/"+$(val).get(0))+"</ul>";
					}
					list+="</li>";
				});
				
				return list;
			}			
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * (Re)Populate the contentview with the given array
	 * @param JSON content list of content items
	 * @return void
	 */
	populateContentView : function(content){
		try{
			dirContent 	= "";
			infoArray 	= Array();		
			path 		= (this.CurrentPath !== null) ? this.CurrentPath : "/";
	
			files = content.dircontent;
			$(files).each(function(i){
				dirContent += "<div class=\"file ui-state-default ui-corner-all\">"+
								"<img src=\""+this.thumb+"\""+ 
										"alt=\""+this.name+"\" id=\"file"+i+"\""+  
										"onclick=\"archiv.insertFile('"+path+"','"+this.name+"')\" />"+
								this.short_name+
								"<div class=\"delete\""+ 
										"id=\"file"+i+"_delete\""+ 
										"title=\""+tinyMCEPopup.getLang('Archiv.FileDelete')+"\""+ 
										"onclick=\"archiv.deleteFile('"+this.name+"','"+path+"',this);\"></div>"+
								"</div>";
								
				infoArray[i] = Array("file"+i,"<table><tr><th>"+tinyMCEPopup.getLang('Archiv.FileName')+":</th><td>"+this.name+"</td></tr><tr><th>"+tinyMCEPopup.getLang('Archiv.FileType')+":</th><td>"+this.type+"</td></tr><tr><th>"+tinyMCEPopup.getLang('Archiv.FileSize')+":</th><td>"+this.fileSize+"</td></tr><tr><th>"+tinyMCEPopup.getLang('Archiv.FileDimentions')+":</th><td>"+this.imageSize+"</td></tr></table>");
			});
			 
			this.setDirectoryContent(dirContent,infoArray);
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * Adds a directory to the dirList, then refreshes the dirList
	 * @param string directoryName the name of the directory to add
	 * @return void
	 */
	addDirectory : function(directoryName){	
		try{
			directoryRoot = (this.CurrentPath !== null) ? this.CurrentPath : "/";

			$.post(
				this.Connector,
				{ doAction:'addDirectory', settings_file: this.SettingsFile, time:this.Parameter ,dirName:directoryName, dirRoot:directoryRoot },
				function(data, textStatus){
				   	if(textStatus == 'success'){			  
					    if(data.message == "ok"){
							archiv.getTreePopulation();
							archiv.displayError("Directory `"+directoryName+"` added!",2000);
					    }
					    else{
					    	archiv.displayError(data.message, 2000);
					    }
				 	}
					else{
						archiv.displayError(tinyMCEPopup.getLang('Archiv.ErrorAddingDirectory'));
					}
				},
				'json'
			   );
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * Set the directory structure in the HTML
	 * @param JSON dirList list of directorys to display
	 * @return void
	 */
	setDirectoryList : function(dirList){
		$("#treeView").html(dirList);
		
		/* set variables */
        var $tree = $("#direcotryList li");
		var $roots = $tree.find('li');

        $tree.find('li:last-child > a').addClass('last_dir');
		
		/* hide left bar of last li item */
		/* iterate through all list items */
        $roots.each(function(){		
			/* if list-item contains a child list */
            if ($(this).children('ul').length > 0) {			
				/* add expand/contract control */
                $(this).addClass('root');				
            }            
        });
	},

	/**
	 * Ask for the name of the new directory
	 * @return void
	 */
	askDirectoryName : function(){
		try{
			directoryName = this.prompt(tinyMCEPopup.getLang('Archiv.QuestionDirectoryName')+":", this.set_directoryname);			
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	
	/**
	 * Insert a file into the editor by the way the browser type is defined (file or image)
	 * @param string thePath path of the current directory
	 * @param string theFile name of the file to insert
	 * @return void
	 */
	insertFile : function(thePath, theFile){
		try{
			absPath = this._path;
			thePath = (thePath !== '/') ? thePath.substr(1) + '/' : '';		
			
			if(this.BrowserType == "images"){
				/* add the image and close the window */
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, "<img src=\""+absPath+thePath+theFile+"\" alt=\""+theFile+"\" />");
			}
			else{
				/* add the link and close the window */
				tinyMCEPopup.editor.execCommand('mceInsertContent', false, "<a href=\""+absPath+thePath+theFile+"\" title=\""+theFile+"\">"+theFile+"</a>");
			}
			tinyMCEPopup.close();
		}
		catch(e){
			this.debug(arguments.callee,e);
		}			
	},	

	/**
	 * SWFupload builder, creates a SWFupload instance or updates parameters
	 * @return void
	 */
	build_swfupload : function(){
		try{
			/* get the absolute path of the plugin */
			archiv.plugin_abspath = window.location.href.substring(0,window.location.href.lastIndexOf("/")+1);
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
		
		try{
			if(!this.swfu){
				this.swfu = new SWFUpload({
					/* Backend Settings */
					upload_url: archiv.plugin_abspath+archiv.Connector,	/* Relative to the SWF file */
					post_params: {
									doAction: 		'addFile',
									settings_file: 	archiv.SettingsFile,
									path: 			$('#currentDirectory').text(),
									browser: 		archiv.BrowserType
									},
					
					/* File Upload Settings */
					file_size_limit 		: archiv._file_size_limit, 											 				//"52100",	// 50MB
					file_types 				: (archiv.BrowserType == "images") ? archiv._image_files  : archiv._files ,	//"*.jpg",
					file_types_description 	: (archiv.BrowserType == "images") ? "Images" : "Files",
					file_upload_limit 		: archiv._file_upload_limit, 											 				//"0",
					file_queue_limit 		: "0",
					
					/* Event Handler Settings - these functions as defined in Handlers.js */
					/*  The handlers are not part of SWFUpload but are part of my website and control how */
					/*  my website reacts to the SWFUpload events. */
					file_queue_error_handler 		: fileQueueError,
					file_dialog_complete_handler 	: fileDialogComplete,
					upload_progress_handler 		: uploadProgress,
					upload_error_handler 			: uploadError,
					upload_success_handler 			: uploadSuccess,
					upload_complete_handler 		: uploadComplete,
					 
					/* Button */
					button_image_url 		: "",	/* Relative to the SWF file */
					button_placeholder_id 	: "spanButtonPlaceholder",
					button_width			: 180,
					button_height			: 22,
					button_text 			: '<span class="button">Select Files</span>',
					button_text_style 		: '.button { font-family:Verdana, Arial, Helvetica, sans-serif; font-size: 12pt; text-align:right; } .buttonSmall { font-size: 10pt; }',
					button_text_top_padding	: 2,
					button_text_left_padding: 0,
					button_window_mode		: SWFUpload.WINDOW_MODE.TRANSPARENT,
					button_cursor			: SWFUpload.CURSOR.HAND,					
		
					/* Flash Settings */
					flash_url : archiv.plugin_abspath+"swf/swfupload.swf",	/* Relative to this file */
		
					custom_settings : {
						upload_target : "divFileProgressContainer"
					},
					
					/* Debug Settings */
					debug: false
				});					
			}
			else{
				this.swfu.setPostParams({
					doAction: 		'addFile',
					settings_file: 	archiv.SettingsFile,
					path: 			$('#currentDirectory').text(),
					browser: 		archiv.BrowserType
				});
			}
		}
		catch(ee){
			this.debug(arguments.callee,ee);
		}				
	},

    

	/**
	 * Set the current directory in HTML and SWFupload
	 * @param string dirName the name of the current path
	 * @return void
	 */
    setCurrentDirectoryLabel : function(dirName){   
    	try{ 	
	    	$('#currentDirectory').text(dirName);
			this.build_swfupload();
	    }
		catch(e){
			this.debug(arguments.callee,e);
		}
    },    
		
	/**
	 * Set the content of the dir and the additional file info
	 * @param string dirContent html to insert into the fileHolder
	 * @param array infoArray all the information of the file
	 * @return void
	 */	  
	setDirectoryContent : function(dirContent, infoArray){
		try{
			tipTop 		= "<div class=\"title\">"+tinyMCEPopup.getLang('Archiv.FileInfo')+"</div>";
			tipBottom 	= "<div class=\"bottom\">"+tinyMCEPopup.getLang('Archiv.FileClickToAdd')+"</div>";
			$("#fileHolder").html(dirContent);
	
			ids = '';
			for(i=0; i < infoArray.length;i++){
			
				this.InfoArray[infoArray[i][0]] = infoArray[i][1];
				
				ids = (ids === '') ? "#"+infoArray[i][0] : ids + ',' + "#"+infoArray[i][0]; 		
			}			
		
			$(ids).mouseenter(function(){	
				$('#tooltip').slideUp(100).html(tipTop+"<div class=\"content\">"+archiv.InfoArray[this.id]+"</div>"+tipBottom).slideDown('fast', function(){
					$(ids).mouseout(function(){				
						$('#tooltip').slideUp(100);			
					});
				});
			});
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},
	

	/**
	 * Clear the dircontent
	 * @return void
	 */
	clearDirectoryContent : function(){
		try{
			$("#fileHolder").html("");
			this.hideError();
		}
		catch(e){
			this.debug(arguments.callee,e);
		}
	},

	/**
	 * Delete a file from the server
	 * @param string fileName the name of the file to delete
	 * @param string path the name of the current path
	 * @param domObject obj the object that contains the current file
	 * @return void
	 */
	deleteFile : function(fileName, path, obj){
		try{
			$('body').append('<div id="dialog">'+tinyMCEPopup.getLang('Archiv.ConfirmDeleteFile')+' `'+fileName+'`?'+'</div>');
		
			$('#dialog').dialog({
				bgiframe: true,
				buttons: {
					"Ok": function(){
						$.post(
							archiv.Connector,
							{ 
								doAction:		'deleteFile', 
								settings_file:	archiv.SettingsFile, 
								time:			archiv.Parameter, 
								fileName:		fileName, 
								fileRoot:		path 
							},
						    function(data, textStatus){
						   		if(textStatus == 'success'){					   
								    if(data.message=="ok"){
								   	 	$(obj).parent().hide('slow');
								   	 	archiv.displayError(tinyMCEPopup.getLang('Archiv.FileDeleted') + ": `" + fileName + "`!",5000);
								    }
								    else{
								    	archiv.displayError(data.message);
								    }
								}
								else{
									archiv.displayError(tinyMCEPopup.getLang('Archiv.ErrorDeletingFile'));
								}
							 },
						  	'json'
						 );						
						$(this).dialog("close");					
					},
					Cancel: function(){
						$(this).dialog("close");
					}					
				},
				modal: true,
				resizable: false,
				width:'auto',
				title: tinyMCEPopup.getLang('Archiv.FileDelete'),
				close: function(){
					$(this).dialog( 'destroy' ).remove();
				}				
			});
		}
		catch(e){
			this.debug(arguments.callee,e);
		}	
	},
	
	/**
	 * Deletes a directory from the server
	 * @param string path the name of the path to delete
	 * @return void
	 */
	deleteDirectory : function(path){
		try{
			$('body').append('<div id="dialog">'+tinyMCEPopup.getLang('Archiv.ConfirmDeleteDirectory')+' `'+path+'`?'+'</div>');
		
			$('#dialog').dialog({
				bgiframe: true,
				buttons: {
					"Ok": function(){
						$.post(archiv.Connector, 
								{
									doAction: 		'deleteDirectory',
									settings_file: 	archiv.SettingsFile,
									time: 			archiv.Parameter,
									fileRoot: 		path
								}, 
								function(data, textStatus){
									if (textStatus == 'success') {
										if (data.message == "ok") {
											archiv.getTreePopulation();											
											archiv.displayError(tinyMCEPopup.getLang('Archiv.DirectoryDeleted') + ": `" + path + "`!",5000);
											/* set the path to the parent directory */
											dirs = path.split('/');
											dirs.pop();
											if(dirs.length > 1){												
												path = dirs.join('/');
											}
											else{
												path = '/';
											}
											archiv.setCurrentDirectoryLabel(path);
											archiv.getContentPopulation(null, path);
										}
										else {
											archiv.displayError(data.message);
										}
									}
									else {
										archiv.displayError(tinyMCEPopup.getLang('Archiv.ErrorDeletingDirectory'));
									}									
								}, 
								'json'
								);						
						$(this).dialog("close");					
					},
					Cancel: function(){
						$(this).dialog("close");
					}					
				},
				modal: true,
				resizable: false,
				width:'auto',
				title: tinyMCEPopup.getLang('Archiv.FileDelete'),
				close: function(){
					$(this).dialog( 'destroy' ).remove();
				}				
			});
		}
		catch(e){
			this.debug(arguments.callee,e);
		}		
	},
	
	/**
	 * Display a error
	 * @param string errorMsg message to display
	 * @param int displayTime the time that the error will be displayed (optional)
	 * @return void
	 */
	displayError: function(errorMsg, displayTime){
		$('#error').html(errorMsg);
		$('#error').fadeIn('slow', function(){
			if(displayTime !== null){
				setTimeout(function(){
					archiv.hideError();
				}, displayTime);
			}				
		});		
	},
	
	/**
	 * Hide a error
	 * @return void
	 */
	hideError: function(){
		$('#error').fadeOut('fast');
	},
	
	/**
	 * Display a debug message when a error is triggered
	 * @param function callerFunction function that triggered the error
	 * @param object message catched try exception
	 * @return void
	 */
	debug: function(callerFunction, message){	
		if(this.Debug == "true"){
			this.alert('ERROR', message.message.replace("\r","<br />") + "\r\n\r\n FUNCTION:\r\n" + callerFunction);
		}
		else{
			this.alert('ERROR', tinyMCEPopup.getLang('Archiv.FatalError'));
		}
	},
	
	/**
	 * Promp the user for a directory name
	 * @param string text the text to display
	 * @param function function_callback the function to call when de directory is added
	 * @param string val the value to add to the inner text input field
	 * @return void
	 */
	prompt:	function(text, function_callback, val){
		this._callback = function_callback;
		
		$('body').append('<div id="dialog" ><input type="text" name="edtDirectoryName" id="edtDirectoryName" value="" /></div>');
		
		$('#dialog').dialog({
			bgiframe: true,
			buttons: {
				"Ok": function(){
					el = $(this).find("#edtDirectoryName");
					if($(el).val().length > 0){
						archiv._callback($(el).val());
						$(this).dialog("close");
					}
				},
				"Cancel": function(){
					$(this).dialog("close");
				}
			},
			modal: true,
			resizable: false,
			height:130,
			minHeight:130, 
			title: text,
			close: function(){
				$(this).dialog( 'destroy' ).remove();
			}				
		});
	},
	
	/**
	 * Show a jQuery dialog like a alert
	 * @param string title the title for the dialog
	 * @param string text the text that is displayed in the dialog
	 * @return void
	 */
	alert : function(title, text){
		$('body').append('<div id="dialog">'+text+'</div>');
		
		$('#dialog').dialog({
			bgiframe: true,
			buttons: {
				"Ok": function(){
					$(this).dialog("close");					
				}
			},
			modal: true,
			resizable: false,
			width:'auto',
			title: title,
			close: function(){
				$(this).dialog( 'destroy' ).remove();
			}				
		});
	},
	/**
	 * Set the directory name
	 * @param string directoryName the new name of the directory
	 * @return void
	 */
	set_directoryname: function(directoryName){
		if(directoryName !== "" && directoryName !== null){
			this.addDirectory(directoryName);			
		}
	},
	
	/**
	 * Start dragging of the given object
	 * @param domObject obj the object to drag
	 * @return void
	 */
	startDrag: function(obj){
		$("body").css('cursor', 'e-resize');
		$().bind('mousemove', function(e){ archiv.mousePos(e); });		
		$().mouseup(function(){  $().unbind(); $("body").css('cursor', ''); });
	},
	
	/**
	 * Set the width and left of the fileTree and fileBrowser according to the mouse position
	 * @param mouseObject e x,y coardinates of the current mouse position
	 * @return void
	 */
	mousePos: function(e){
		if(e.pageX > 100 && e.pageX <500){	
			if($.boxModel === true){			
				$('#fileTree').css('width',e.pageX-5+'px');
				$('#splitter').css('left',e.pageX+'px');
				$('#fileBrowser').css('left',e.pageX+7+'px');
			}
			else{
				$('#fileTree').css('width',e.pageX-5+'px');
				$('#splitter').css('left',e.pageX-10+'px');
				$('#fileBrowser').css('left',e.pageX-5+'px');			}
		}		
	}
};

/**
 * Pre initialize the archiv object
 */
archiv.preInit();	

/**
 * Register the archiv object at the TinyMCE instance
 */
tinyMCEPopup.onInit.add(archiv.init, archiv);	